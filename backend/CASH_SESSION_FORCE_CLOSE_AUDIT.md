# CASH_SESSION_FORCE_CLOSE_AUDIT.md (Backend)

**Feature:** Cierre administrativo de caja por admin de sucursal  
**Fecha auditoría:** 2026-06-21  
**Estado:** Implementado (2026-06-21). Ver `CASH_SESSION_FORCE_CLOSE_IMPLEMENTATION_REPORT.md`.

---

## 1. Veredicto

El modelo actual **no soporta** cierre administrativo auditable. Requiere:

- **Migración** en `cash_sessions` (campos de force-close + snapshots)
- **Nuevo permiso** (recomendado: `admin.cash_sessions.force_close`)
- **Nuevo use case + endpoint** admin
- **Extensión** de mappers, fiscalización admin, audit log y SSE payload
- **Suite de tests** dedicada

El flujo de cierre normal de cajera (`CloseCashSessionUseCase`) puede reutilizarse parcialmente para el cálculo financiero, pero **no** puede usarse tal cual: está acoplado a `findOpenForUser`, exige blockers vacíos y requiere `declared_closing_amount` del cajero.

---

## 2. Modelo actual (`cash_sessions`)

**Migración base:** `database/migrations/2026_06_03_100008_create_cash_tables.php`  
**Extensión turno:** `database/migrations/2026_06_03_100013_add_official_shift_id_to_operational_tables.php`

| Campo existente | Uso actual |
|-----------------|------------|
| `opened_by_user_id` | Cajera que abrió (equivale a `original_cashier_user_id`) |
| `closed_by_user_id` | Usuario que cerró (hoy siempre la cajera) |
| `status` | Solo `OPEN` / `CLOSED` (`CashSessionStatus`) |
| `expected_amount` | Calculado al cerrar (solo movimientos CASH) |
| `declared_closing_amount` | Arqueo declarado por cajera (obligatorio en cierre normal) |
| `difference_amount` | `declared − expected` |
| `closing_notes` | Notas libres de cierre normal |
| `official_shift_id` | Turno operativo al abrir |

### Campos propuestos — **no existen**

| Campo propuesto | Estado |
|-----------------|--------|
| `forced_closed` / `is_forced_close` | ❌ |
| `forced_closed_by_user_id` | ❌ |
| `forced_closed_at` | ❌ |
| `forced_close_reason` | ❌ |
| `forced_close_notes` | ❌ |
| `close_blockers_snapshot` (json) | ❌ |
| `financial_summary_snapshot` (json) | ❌ |

**Búsqueda en repo:** cero coincidencias de `force_close`, `forced_close`, `FORCED_CLOSED`.

### Decisión de diseño recomendada sobre `status`

- Mantener `status = CLOSED` (no agregar `FORCED_CLOSED` como status operativo).
- Diferenciar con `is_forced_close = true` + snapshots.
- En UI/reportes: badge **“Cierre administrativo”** / **“Cerrada con observaciones”** cuando `is_forced_close` y snapshot de blockers no vacío.

Motivo: el resto del sistema (reportes, shift close-check, listados) ya filtra por `OPEN`/`CLOSED`. Un tercer status obligaría revisar muchos queries.

---

## 3. Flujo de cierre actual (cajera)

| Paso | Implementación |
|------|----------------|
| Pre-check | `GET /api/v1/cash/session/current/close-check` → `CashSessionCloseCheckBuilder` |
| Cierre | `POST /api/v1/cash/session/close` → `CloseCashSessionUseCase` |
| Permiso | `cash.access` |
| Sesión objetivo | Solo la caja **abierta del usuario actual** (`findOpenForUser`) |
| Blockers | Hard stop server-side si `can_close === false` |
| Audit | `cash_session.closed` |
| SSE | `cash.session.closed` |

### Blockers actuales (`CashSessionCloseCheckBuilder`)

Scope: `tenant_id` + `branch_id` + `official_shift_id` + opcional `cash_session_id` (liquidaciones).

| Código | Condición |
|--------|-----------|
| `active_orders` | Comandas `OPEN` / `SENT_TO_BAR` en el turno |
| `active_room_services` | Piezas `ACTIVE` / `DUE` |
| `settlements_not_generated` | Fuentes sin liquidar |
| `settlements_pending_payment` | Liquidaciones `PENDING` |
| `pending_waiter_settlements` | Garzones pendientes |
| `pending_girl_settlements` | Chicas pendientes |
| `pending_cleaning_settlements` | Limpieza pendientes |

`warnings[]` siempre vacío hoy.

---

## 4. Apertura de caja y bloqueo operativo real

**`OpenCashSessionUseCase`:**

- Bloquea solo si **el mismo usuario** ya tiene sesión `OPEN` en la sucursal (`sessionAlreadyOpen`).
- **Multicaja:** otra cajera **sí puede** abrir su propia sesión aunque exista una abierta ajena.
- Asocia sesión al turno de `EnsureOperationalShiftUseCase` (puede reutilizar turno OPEN o rotar AUTO vencido).

### Consecuencias operativas hoy

| Escenario | Comportamiento actual |
|-----------|----------------------|
| Cajera A deja caja abierta | A **no puede** abrir otra; B **sí puede** abrir la suya |
| Cierre de turno fiscal | Bloqueado por `open_cash_sessions` mientras haya cualquier caja OPEN |
| Liquidaciones cajera B | Scope `my_cash_session`: **no hereda** pendientes de la sesión de A |
| Pendientes de A | Siguen en BD ligados a `official_shift_id` / `cash_session_id` de A |
| Comandas/piezas del turno de A | Siguen activas en ese turno; no desaparecen al force-close |

Force-close **libera** la sesión stuck (status CLOSED) y permite que A vuelva a operar; **no paga ni cancela** pendientes (alineado al requisito).

---

## 5. Admin fiscalización (solo lectura)

| Endpoint | Permiso |
|----------|---------|
| `GET /admin/cash-sessions` | `admin.cash_sessions.list` |
| `GET /admin/cash-sessions/summary` | `admin.cash_sessions.summary` |
| `GET /admin/cash-sessions/{id}` | `admin.cash_sessions.view` |

**No hay** POST/PATCH/DELETE. `AdminCashSessionController` es read-only.

Roles con fiscalización: `super_admin`, `tenant_owner`, `cashier_senior` (no cajera básica).

---

## 6. Permisos — gap

Existen:

- `admin.cash_sessions.list`
- `admin.cash_sessions.view`
- `admin.cash_sessions.summary`

**No existe** `cash_sessions.force_close` ni `admin.cash_sessions.force_close`.

Recomendación: **`admin.cash_sessions.force_close`** (consistente con prefijo admin existente).

Asignación sugerida:

| Rol | Force close |
|-----|-------------|
| `tenant_owner` | ✅ |
| `super_admin` | ✅ |
| `cashier_senior` | ✅ (decisión de negocio) |
| `cashier` | ❌ |
| garzón / limpieza / chica | ❌ |

---

## 7. Endpoint propuesto

```
POST /api/v1/admin/cash-sessions/{id}/force-close
```

Permiso: `admin.cash_sessions.force_close`

### Request body sugerido

```json
{
  "forced_close_reason": "cashier_left | operational_error | blockers_unresolved | shift_change | other",
  "forced_close_notes": "texto obligatorio",
  "declared_closing_amount": null
}
```

### Validaciones

1. Permiso force-close
2. Sesión existe, mismo `tenant_id`, mismo `branch_id` del contexto
3. `status === OPEN`
4. `forced_close_reason` enum válido
5. `forced_close_notes` no vacío (min length)
6. Ejecutar `CashSessionCloseCheckBuilder` → **guardar snapshot**, no bloquear
7. Ejecutar `CashSessionFinancialSummaryBuilder` → guardar snapshot
8. Cerrar sesión (ver §8)
9. Audit: `cash_session.force_closed` (nuevo, no reutilizar solo `cash_session.closed`)
10. SSE: `cash.session.closed` con payload extendido (`forced: true`, `opened_by_user_id`, `forced_by_user_id`)

### Endpoint auxiliar recomendado

```
GET /api/v1/admin/cash-sessions/{id}/close-check
```

Permiso: `admin.cash_sessions.force_close` (o `view` + force_close para preview).  
Reutiliza `CashSessionCloseCheckBuilder` con el `official_shift_id` y `id` de la sesión objetivo — hoy no existe close-check por id.

---

## 8. Cierre financiero en force-close — decisión pendiente

`EloquentCashSessionRepository::close()` exige `declaredClosingAmount` y calcula diferencia.

Opciones:

| Opción | Pros | Contras |
|--------|------|---------|
| A) `declared = expected` automático | Siempre hay diferencia 0 artificial | Oculta que no hubo arqueo real |
| B) `declared = null`, `difference = null` | Honesto: “sin arqueo” | Requiere permitir nulls en cierres forzados + UI |
| C) Admin ingresa monto opcional | Máxima trazabilidad si hubo conteo | Más UI/complejidad |

**Recomendación:** Opción B + mostrar en fiscalización **“Sin arqueo — cierre administrativo”** y conservar `expected_amount` calculado del sistema en snapshot.

Extender `close()` o crear `forceClose()` que acepte flags y no sobrescriba `closed_by_user_id` con ambigüedad:

- `opened_by_user_id` → cajera original (sin cambio)
- `closed_by_user_id` → **admin que force-close** (auditoría de quién cerró)
- `forced_closed_by_user_id` → redundante si `closed_by_user_id` es admin; **preferir campo dedicado** `forced_closed_by_user_id` y dejar `closed_by_user_id` null en force-close, **o** usar ambos: `closed_by_user_id = admin`, `forced_closed_by_user_id = admin` (explícito).

Propuesta clara:

- `closed_by_user_id` = admin que ejecutó el cierre
- `forced_closed_by_user_id` = mismo admin (redundante pero explícito para queries)
- `is_forced_close` = true

---

## 9. Qué NO debe hacer (compatibilidad actual)

| Requisito | Estado actual | Riesgo al implementar |
|-----------|---------------|------------------------|
| No pagar liquidaciones auto | ✅ Cierre normal tampoco paga | No llamar `MarkSettlementPaidUseCase` |
| No eliminar pagos pendientes | ✅ | Solo cambiar status sesión |
| No borrar comandas activas | ✅ | Blockers snapshot, no DELETE |
| No ocultar diferencias | ✅ | Mostrar snapshot + expected |
| No cambiar ventas | ✅ | Sin tocar `sales` |
| No mover dinero entre cajas | ✅ | Sin movimientos nuevos |
| No cerrar otra sucursal | Parcial | Validar `branch_id` en use case (patrón admin existente) |

---

## 10. Pendientes post force-close

Los pendientes **permanecen** en:

- Comandas / piezas → filtro por `official_shift_id` del turno de la sesión cerrada
- Liquidaciones `PENDING` → scope `cash_session_id` / turno de A
- Admin detail hoy muestra solo `settlements_paid` — **falta panel de pendientes no resueltos** en sesiones force-closed (usar snapshot + query live)

Cajera B con nueva sesión:

- `SettlementShiftScopeResolver` scope `my_cash_session` → solo su `cash_session_id`
- **No ve** liquidaciones pendientes de A en su UI operativa ✅

---

## 11. SSE y audit

| Evento | Emisor actual | Cambio sugerido |
|--------|---------------|-----------------|
| `cash.session.closed` | Solo `CloseCashSessionUseCase` | También emitir desde force-close |
| Payload | `entity`, `summary`, `refresh` | Agregar `forced: true`, ids relevantes |
| Audit | `cash_session.closed` | Agregar `cash_session.force_closed` con snapshots en metadata |

---

## 12. Tests existentes vs requeridos

### Existentes (cierre normal)

- `tests/Feature/Api/V1/CashierCloseCheckTest.php` (9 tests)
- `tests/Feature/Api/V1/CashApiTest.php`
- `tests/Feature/Api/V1/AdminCashSessionsTest.php` (solo lectura)
- Varios tests de settlements/scope

### A crear (checklist del spec)

1. cajera básica no puede force-close  
2. admin puede cerrar caja OPEN de su sucursal  
3. admin no puede cerrar caja de otra sucursal  
4. motivo obligatorio  
5. blockers snapshot guardado  
6. no paga liquidaciones  
7. permite abrir nueva caja (misma u otra cajera según caso)  
8. aparece en fiscalización con metadata  
9. close-check normal sigue bloqueando cajera básica  
10. SSE emitido  

---

## 13. Archivos clave a tocar (implementación futura)

| Área | Archivos |
|------|----------|
| Migración | nueva migration `add_force_close_to_cash_sessions` |
| Domain | `CashSession.php`, `CashSessionStatus.php` (si aplica) |
| Repository | `EloquentCashSessionRepository.php`, interface |
| Use cases | nuevo `ForceCloseCashSessionAdminUseCase`, opcional `GetCashSessionCloseCheckAdminUseCase` |
| HTTP | `AdminCashSessionController`, Form Request, `routes/api.php` |
| Mappers | `CashMapper`, `AdminCashSessionMapper` |
| Permisos | migration + `SeedsNightPosFoundation.php` + `ManageablePermissionCatalog.php` |
| Tests | nuevo `AdminCashSessionForceCloseTest.php` |

---

## 14. Conclusión

**Migración obligatoria** para trazabilidad operativa y fiscalización. El cierre normal no puede extenderse con un flag `force` en el mismo endpoint de cajera: separar responsabilidades y permisos es más seguro.

El diseño propuesto es **compatible** con multicaja, scope de liquidaciones y shift close-check existentes, siempre que force-close solo cambie el estado de la sesión y persista evidencia sin mutar pendientes.
