# SETTLEMENT_SHIFT_SCOPE_DIAGNOSTIC_REPORT.md (Backend)

**Fecha:** 2026-06-16  
**Estado:** Diagnóstico — sin cambios de código  
**Caso:** Cajera `mabel` (user #14), Caja #7, Turno ID 3 (Noche · 2026-06-14), Sucursal #2 — KPIs con montos ajenos a su operación.

---

## 1. Conclusión ejecutiva

**Los KPIs no vienen de “otro endpoint” ni de un bug de frontend.** Vienen de `GET /api/v1/settlements/current-shift`, que **sí filtra por `official_shift_id`**, pero ese ID es el **turno OPEN de la sucursal** (`resolveOpenShiftId`), **no** el alcance operativo de la caja/cajera actual.

En el escenario de la captura:

| Hecho | Implicación |
|-------|-------------|
| Turno ID 3 sigue `OPEN` con `business_date = 2026-06-14` | Es un turno **viejo / stale** que nunca se cerró |
| Caja #7 de mabel tiene `official_shift_id = 3` | Coincide con el turno sucursal → `context` se ve “correcto” |
| `staff_settlements` con `official_shift_id = 3` incluye isa, carla, etc. | Los KPIs (290 chicas, 40 limpieza, 170 piezas…) son **reales** para ese turno |
| mabel no hizo ventas en **su** caja | Pero las liquidaciones son **del turno compartido**, no de su caja |

**Veredicto:** mezcla por **alcance incorrecto para pantalla de cajera** (turno sucursal compartido + turno stale acumulado) + **liquidaciones ya generadas** de otras operaciones en el mismo `official_shift_id`. No es mezcla cross-tenant ni cross-branch.

---

## 2. Respuestas a las preguntas del análisis

### 2.1 Contexto real usado (por endpoint)

| Campo | `current-shift` | `pending-sources` | `generate-current-shift` | `close-check` |
|-------|-----------------|-------------------|--------------------------|---------------|
| `auth_user_id` | Sí (interno) | Sí | Sí | Sí |
| `tenant_id` | Header/middleware | Idem | Idem | Idem |
| `branch_id` | Header/middleware | Idem | Idem | Idem |
| `cash_session_id` | Solo en `context` (display) | Solo en `context` | **No** (null en context) | Sí |
| `cash_session.official_shift_id` | **No usado para datos** | **No usado** | N/A | **Sí — fuente de bloqueos** |
| `current_official_shift_id` | `resolveOpenShiftId()` | `resolveOpenShiftId()` | `ensureOperationalShift()` | `session.official_shift_id` |
| `business_date` / `shift_type` | Del registro `official_shifts` del shiftId | Idem | Idem | Implícito vía shiftId |

**Inconsistencia crítica:**

```
GetCurrentShiftSettlementsUseCase:
  shiftId = resolveOpenShiftId(tenant, branch)     ← turno OPEN sucursal

GetCashSessionCloseCheckUseCase:
  shiftId = cashSession.officialShiftId            ← turno de LA caja
```

En condiciones normales suelen coincidir. En turno stale abierto desde días atrás, **ambos apuntan al mismo turno viejo (ID 3)** → la comparación `cash_session.official_shift_id === current_official_shift_id` **pasa**, pero el turno contiene días de actividad ajena a mabel.

**Campos que faltan en `context` hoy** (útiles para debug):

- `cash_session.official_shift_id` (explícito)
- `shift_match` (bool: session shift === resolved open shift)
- `shift_opened_at`, `shift_ends_at`, `shift_status`
- `settlements_count_for_shift`

Archivo: `SettlementOperationalContextBuilder.php` — solo expone `current_shift_id`, no el shift de la caja ni comparación.

---

### 2.2 Fuente de verdad del turno (por use case)

| Use case | Fuente de turno | ¿Usa caja? | ¿Usa fecha/created_at? |
|----------|-----------------|------------|-------------------------|
| `GetCurrentShiftSettlementsUseCase` | `resolveOpenShiftId()` — primer `official_shifts.status=OPEN` de la sucursal | Solo metadata en `context` | No |
| `GetSettlementPendingSourcesUseCase` | Idem | Solo metadata | No |
| `GenerateCurrentShiftSettlementsUseCase` | `ensureOperationalShift()` — reutiliza OPEN o crea/rota AUTO | No | Ventana horaria solo en rotación AUTO |
| `GetCashSessionCloseCheckUseCase` | `cash_sessions.official_shift_id` | **Sí** | No |

**Regla deseada por negocio (cajera):** liquidaciones operativas por `cash_session.official_shift_id` + ideally solo fuentes ligadas a esa caja.

**Regla implementada hoy:** liquidaciones por **turno OPEN de sucursal** — compartido entre todas las cajas del turno.

---

### 2.3 Queries de resumen (`GetCurrentShiftSettlementsUseCase`)

Flujo:

1. `$shiftId = resolveOpenShiftId($tenantId, $branchId)`
2. `$overview = getCurrentShiftOverview($tenantId, $branchId, $shiftId, $onlyStaffUserId)`

`getCurrentShiftOverview` (`EloquentStaffSettlementRepository.php`):

```sql
-- KPIs = agregación de staff_settlements, NO ventas en vivo
SELECT * FROM staff_settlements
WHERE tenant_id = ? AND branch_id = ? AND official_shift_id = ?
[AND staff_user_id = ? si onlyStaffUserId]
```

`buildSummary()` suma columnas de esas filas:

| KPI captura | Origen en summary |
|-------------|-------------------|
| Total chicas 290 | `settlement_type = GIRL` → `total_amount` |
| Total limpieza 40 | `settlement_type = CLEANING` |
| Consumos 120 | `consumption_total` de settlements GIRL |
| Piezas 170 | `pieces_total` de settlements GIRL |
| Pendiente 40 | settlements con `status = PENDING` |

**No hay query directa a `room_services` / `sales` para los KPIs** — solo a `staff_settlements` ya generadas.

`sources_summary` (context) sí consulta fuentes crudas vía `countShiftSources()` con `official_shift_id = $shiftId` — también **sin filtro de caja**.

---

### 2.4 Pending sources y warning de garzones

`GetSettlementPendingSourcesUseCase::staffReadiness()`:

- Lista **todos** los garzones activos de la sucursal **sin** `waiter_commission_percent > 0`
- **No** filtra por actividad en el turno actual
- **No** usa `official_shift_id`

→ El warning *"Garzones sin porcentaje: victor"* es **Hipótesis confirmada parcial**: aviso global de configuración, no de actividad del turno.

Las fuentes pendientes (`unpaid_*_count`) **sí** filtran por `official_shift_id = resolveOpenShiftId()`.

---

### 2.5 Generate settlements

`GenerateCurrentShiftSettlementsUseCase`:

1. `$shift = ensureOperationalShift(...)` — puede reutilizar o rotar turno AUTO
2. `generateForShift($tenantId, $branchId, $shift->id)`

`generateForShift` filtra correctamente:

| Fuente | Filtro |
|--------|--------|
| sale_items | `sales.official_shift_id = ?` |
| bracelets | `official_shift_id = ?` |
| room_services | `official_shift_id = ?` AND `status = FINISHED` |
| shows | `official_shift_id = ?` |
| cleaning_tasks | `official_shift_id = ?` AND `status = DONE` |

**No** busca PENDING globales del tenant. **No** usa `created_at = today`.

**Problema:** si el turno OPEN es ID 3 stale, generar liquidaciones **procesa todas las fuentes históricas aún no liquidadas de ese turno**, aunque mabel no haya operado.

---

## 3. Validación de hipótesis

| ID | Hipótesis | Resultado |
|----|-----------|-----------|
| **A** | `GetCurrentShiftSettlements` usa turno sucursal, no caja | **CONFIRMADA** — `resolveOpenShiftId()`, no `cash_session.official_shift_id` |
| **B** | `staff_settlements` sin filtro `official_shift_id` | **RECHAZADA** — sí filtra; el shiftId es el compartido/stale |
| **C** | pending sources = todas las fuentes PENDING tenant | **RECHAZADA** para contadores; **CONFIRMADA** para `staffReadiness` (garzones) |
| **D** | Liquidaciones PENDING viejas se muestran en pantalla | **CONFIRMADA** — KPIs leen `staff_settlements` del turno 3 |
| **E** | Frontend muestra context caja pero datos de otro scope | **PARCIAL** — frontend fiel al API; desalineación conceptual context vs datos |

**Hipótesis adicional F — Turno stale:**

| ID | Hipótesis | Resultado |
|----|-----------|-----------|
| **F** | Turno AUTO nunca cerrado acumula días en mismo `official_shift_id` | **CONFIRMADA** (test diagnóstico previo: comanda día+1 atribuida al mismo shift) |

Turno captura: **Noche 2026-06-14 (auto)** operando el **2026-06-15 23:xx** → ventana `ends_at` (2026-06-15 09:00) ya pasó, pero turno sigue OPEN.

---

## 4. Endpoint que devuelve los KPIs mezclados

| Endpoint | Responsable | Repo / método |
|----------|-------------|---------------|
| `GET /api/v1/settlements/current-shift` | `GetCurrentShiftSettlementsUseCase` | `EloquentStaffSettlementRepository::getCurrentShiftOverview()` + `buildSummary()` |

Frontend (`/nightpos/settlements`) consume **directamente** `data.summary` de este endpoint — sin transformación que mezcle turnos.

---

## 5. ¿Backend, frontend o ambos?

| Capa | Responsabilidad |
|------|-----------------|
| **Backend** | **Principal.** Alcance turno-sucursal vs expectativa caja/cajera; turno stale; `SettlementAccessPolicy` da visión total a cajeras con `settlements.generate/pay/history`; warning garzones sin scope turno |
| **Frontend** | **Secundario.** Muestra KPIs del API tal cual; banner mezcla turno + caja sin advertir que los montos son del turno sucursal completo; `summaryHasData` ignora consumos/piezas para mensaje vacío |

---

## 6. Tablas y `official_shift_id`

| Tabla | Tiene `official_shift_id` | Notas |
|-------|---------------------------|-------|
| `official_shifts` | PK | |
| `cash_sessions` | Sí | Caja #7 → debería ser 3 |
| `staff_settlements` | Sí | **Fuente de KPIs** |
| `sales` | Sí | |
| `orders` | Sí | |
| `order_items` | **No** | Via `orders.official_shift_id` |
| `sale_items` | **No** | Via `sales.official_shift_id` |
| `bracelets` | Sí | |
| `room_services` | Sí | Piezas 170 → items GIRL en settlements |
| `shows` | Sí | |
| `cleaning_tasks` | Sí | Limpieza 40 → carla PENDING |

---

## 7. Consultas SQL de diagnóstico (caso captura)

Reemplazar IDs según entorno. Valores de la captura: **caja 7, turno 3, cajera 14, sucursal 2**.

### 7.1 Caja #7

```sql
SELECT id, tenant_id, branch_id, official_shift_id, opened_by_user_id,
       status, opening_amount, opened_at, closed_at
FROM cash_sessions
WHERE id = 7;
```

### 7.2 Turno ID 3

```sql
SELECT id, tenant_id, branch_id, name, shift_type, business_date,
       starts_at, ends_at, status, opened_at, closed_at, notes
FROM official_shifts
WHERE id = 3;
```

Verificar: `status = 'OPEN'`, `business_date = '2026-06-14'`, `notes` contiene auto.

### 7.3 Liquidaciones del turno 3 (origen de KPIs)

```sql
SELECT ss.id, ss.staff_user_id, u.name AS staff_name,
       ss.settlement_type, ss.status, ss.total_amount,
       ss.created_at, ss.paid_at, ss.paid_by_user_id
FROM staff_settlements ss
JOIN users u ON u.id = ss.staff_user_id
WHERE ss.official_shift_id = 3
ORDER BY ss.settlement_type, ss.id;
```

**Esperado vs captura:**

| staff | tipo | monto | estado | KPI |
|-------|------|-------|--------|-----|
| isa | GIRL | ~290 | PAID | Total chicas, consumos/piezas desglosados |
| carla | CLEANING | 40 | PENDING | Total limpieza, Pendiente 40 |

### 7.4 Desglose isa (consumos 120, piezas 170)

```sql
SELECT ssi.source_type, SUM(ssi.amount) AS total, COUNT(*) AS lines
FROM staff_settlement_items ssi
JOIN staff_settlements ss ON ss.id = ssi.staff_settlement_id
WHERE ss.official_shift_id = 3 AND ss.settlement_type = 'GIRL'
GROUP BY ssi.source_type;
```

### 7.5 ¿Hay liquidaciones en otros turnos misma sucursal?

```sql
SELECT official_shift_id, COUNT(*) AS cnt,
       SUM(CASE WHEN status='PENDING' THEN 1 ELSE 0 END) AS pending
FROM staff_settlements
WHERE tenant_id = (SELECT tenant_id FROM official_shifts WHERE id = 3)
  AND branch_id = 2
GROUP BY official_shift_id
ORDER BY official_shift_id DESC;
```

### 7.6 Fuentes crudas del turno 3 (sources_summary)

```sql
-- Ventas
SELECT COUNT(*) FROM sale_items si
JOIN sales s ON s.id = si.sale_id
WHERE s.official_shift_id = 3 AND s.branch_id = 2;

-- Piezas finished
SELECT id, girl_user_id, total_amount, status, created_at
FROM room_services
WHERE official_shift_id = 3 AND branch_id = 2 AND status = 'FINISHED';

-- Limpieza done
SELECT id, cleaning_user_id, status, created_at
FROM cleaning_tasks
WHERE official_shift_id = 3 AND branch_id = 2 AND status = 'DONE';
```

### 7.7 ¿Ventas solo de caja #7 vs turno completo?

```sql
SELECT cash_session_id, COUNT(*) AS sales_count, SUM(sp.amount) AS total
FROM sales s
JOIN sale_payments sp ON sp.sale_id = s.id
WHERE s.official_shift_id = 3 AND s.branch_id = 2
GROUP BY cash_session_id;
```

Si solo hay ventas en otras cajas pero KPIs > 0 → confirma que **liquidaciones no son por caja**.

### 7.8 Comparación context API (tinker)

```php
// Como mabel, branch EL JEFE:
$tenantId = ...; $branchId = 2; $userId = 14;

$openShift = OfficialShiftModel::where('tenant_id',$tenantId)
    ->where('branch_id',$branchId)->where('status','OPEN')->value('id');

$session = CashSessionModel::where('id',7)->first();

dump([
    'resolveOpenShiftId' => $openShift,
    'cash_session_shift' => $session->official_shift_id,
    'match' => $openShift === $session->official_shift_id,
    'settlements_shift_3' => StaffSettlementModel::where('official_shift_id',3)->count(),
]);
```

---

## 8. Mapeo captura → registros probables

| UI captura | Registro probable |
|------------|-------------------|
| Turno ID 3 · Noche · 2026-06-14 | `official_shifts.id=3` OPEN stale |
| Caja #7 · Cajera #14 | `cash_sessions.id=7`, `opened_by_user_id=14` |
| Total chicas 290 | `staff_settlements` GIRL de **isa**, PAID por laura |
| Total limpieza 40 / Pendiente 40 | `staff_settlements` CLEANING de **carla**, PENDING |
| Consumos 120 / Piezas 170 | `staff_settlement_items` GIRL_CONSUMPTION / GIRL_ROOM de isa |
| Warning victor | `staffReadiness()` — garzón sin % comisión, **sin actividad turno** |

---

## 9. Plan de corrección por prioridad (sin implementar)

### P0 — Causa raíz operativa

1. **Turno stale:** cerrar/rotar turnos AUTO cuando `business_date`/ventana ≠ operación actual (o exigir cierre manual en fiscalización).
2. **Auditoría datos:** ejecutar SQL §7 en producción/staging CASA22 · EL JEFE.

### P1 — Alcance pantalla cajera

3. **`GetCurrentShiftSettlementsUseCase`:** decidir regla:
   - **Opción A (negocio turno sucursal):** mantener scope turno pero **exigir turno vigente** (no stale).
   - **Opción B (negocio cajera):** filtrar KPIs por `cash_session_id` / ventas de la caja actual.
4. **Alinear** `current-shift`, `pending-sources`, `generate`, `close-check` en la misma fuente de `official_shift_id`.

### P2 — Contexto debug

5. Extender `context` con `cash_session_official_shift_id`, `shift_match`, `shift_business_date`, `shift_ends_at`, conteos.

### P3 — Warnings y permisos

6. `staffReadiness`: solo garzones/chicas con fuentes en el turno actual.
7. Revisar `SettlementAccessPolicy`: cajera básica con `settlements.history` ve **todo** el turno.

### P4 — Tests de regresión

8. Turno stale + cajera nueva + caja en 0 → KPIs 0.
9. Dos cajas mismo turno vigente → admin ve ambas; cajera ve solo su caja (si aplica regla B).

---

## 10. Archivos clave auditados

| Archivo | Rol en el bug |
|---------|---------------|
| `GetCurrentShiftSettlementsUseCase.php` | Resuelve turno sucursal, no caja |
| `EloquentStaffSettlementRepository.php` | KPIs desde `staff_settlements` por shiftId |
| `SettlementOperationalContextBuilder.php` | Context incompleto vs close-check |
| `GetSettlementPendingSourcesUseCase.php` | Garzones sin scope turno |
| `SettlementAccessPolicy.php` | Cajera ve todas las liquidaciones del turno |
| `EnsureOperationalShiftUseCase.php` | Rotación AUTO (solo en algunos flujos, no en GET current-shift) |
| `GetCashSessionCloseCheckUseCase.php` | Usa shift de caja (referencia correcta para cajera) |

---

## 11. Referencia frontend

Ver `frontend/SETTLEMENT_SHIFT_SCOPE_DIAGNOSTIC_REPORT.md`.
