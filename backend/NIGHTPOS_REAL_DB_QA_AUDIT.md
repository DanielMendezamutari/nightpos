# NightPOS — QA con base real del hosting (Backend)

**Fecha:** 2026-06-30  
**Base:** MySQL local `nigtpos` (dump producción Ribersoft)  
**Método:** SQL directo + API interna JWT (usuarios reales, sin seeders/factories/SQLite)  
**Alcance:** Diagnóstico únicamente — **sin correcciones aplicadas**

---

## Entorno confirmado

```
php artisan about
  Environment ........ local
  Debug Mode ......... ENABLED
  Database ........... mysql
  DB_DATABASE ........ nigtpos (import hosting)
  Timezone ........... America/La_Paz
  public/storage ..... NOT LINKED
```

| Tabla | Registros |
|-------|-----------|
| tenants | 3 |
| branches | 3 |
| users | 27 |
| official_shifts | 6 |
| cash_sessions | 4 |
| orders | 23 |
| sales | 13 |
| staff_settlements | 7 |
| document_sequences | 2 |
| print_jobs | 105 |

**Tenants reales:**

| id | slug | status | Notas |
|----|------|--------|-------|
| 1 | casa-demo | **suspended** | Datos demo/seeder — login PIN/password falla |
| 2 | C22 | active | Operación real (EL JEFE) |
| 3 | R | active | Ribersoft demo operativo |

---

## SQL de auditoría — resultados

### 1. Tickets duplicados (mismo tenant/branch)

```sql
-- 0 filas
```

**OK** — no hay duplicados en scope `(tenant_id, branch_id, ticket_number)`.

### 2. document_sequences vs tickets

| tenant | branch | period | last_value | max ticket | Estado |
|--------|--------|--------|------------|------------|--------|
| 3 | 3 | 2026 | 2 | 2 | OK |
| 2 | 2 | 2026 | **3** | **3** | OK (post-fix) |

Liquidaciones C22 pagadas: `000002` (id=5), `000003` (id=4). Secuencia alineada tras fix final.

### 3. Cajas abiertas antiguas (>14 h)

| id | tenant | branch | opened_at |
|----|--------|--------|-----------|
| **2** | 3 | 3 | 2026-06-29 00:07:10 |

C22 tiene caja **4 OPEN** (2026-06-29 22:47) — vigente operativamente.

### 4. Turnos abiertos antiguos (>14 h)

| id | tenant | branch | business_date | opened_at |
|----|--------|--------|---------------|-----------|
| **1** | 1 | 1 | 2026-06-28 | 2026-06-28 06:08:13 |
| **2** | 3 | 3 | 2026-06-28 | 2026-06-28 23:05:54 |

C22 turno actual: **id=6 OPEN** (2026-06-30).

### 5. Comandas pendientes antiguas (>14 h)

| id | tenant | status | table | created_at |
|----|--------|--------|-------|--------------|
| 1,2,3,6 | 1 | OPEN/SENT_TO_BAR | demo | 2026-06-28 |
| 8 | 3 | OPEN | Pieza pieza 1 | 2026-06-28 |

C22: **0** comandas OPEN/SENT_TO_BAR (solo BILLED/CANCELLED recientes).

### 6. Habitaciones ocupadas sin pieza activa

Sin `room_pieces`. Habitaciones: todas AVAILABLE excepto **room 7 = CLEANING** (tenant 3).  
Pieza id=1 **FINISHED**. **No hay OCCUPIED huérfanas.**

### 7. Liquidaciones PENDING en turnos cerrados

| id | tenant | type | shift_id | shift_status | net |
|----|--------|------|----------|--------------|-----|
| **3** | 2 | WAITER | 5 | CLOSED | 53 |
| **6** | 2 | GIRL | 5 | CLOSED | 190 |
| **7** | 2 | GIRL | 5 | CLOSED | 90 |

Turno actual C22 = **6 OPEN**. Estas 3 liquidaciones quedaron **colgadas del turno 5** (2026-06-29).

### 8. PAID sin cash_movement_id

**0 filas** — todas las PAID tienen movimiento de caja.

### 9. Multas APPLIED sin settlement

**0 filas** — sin multas huérfanas.

### 10. print_jobs

| status | count |
|--------|-------|
| PENDING | **1** (id=105, SETTLEMENT_PAYMENT, 2026-06-30 09:16) |
| PRINTED | 104 |
| FAILED | 0 |

### 11. Usuarios activos sin sucursal

**0 filas** (superadmin excluido lógicamente).

### 12. Roles — permisos mínimos

| role | permisos |
|------|----------|
| girl | 15 |
| cleaning | 21 |
| waiter | 45 |
| cashier | 148 |
| tenant_owner | 255 |

Sin roles vacíos.

---

## API real (JWT usuarios hosting)

Script: `php scripts/real_db_qa_internal.php`  
Resultados: `storage/app/real_db_qa_api_results.json`

| Usuario real | Endpoint | HTTP | Hallazgo |
|--------------|----------|------|----------|
| LAURA (owner C22) | auth/me | 200 | OK |
| LAURA | settlements/current-shift | 200 | **count=0** |
| LAURA | settlements/history | 200 | OK |
| lizvania (cashier C22) | cash/session/current | 200 | OPEN |
| lizvania | cash close-check | 200 | OK |
| lizvania | settlements/current-shift | 200 | **count=5** |
| lizvania | shift close-check | **403** | Sin permiso `shifts.close` |
| lizvania | settlement/6 pay-preview | 200 | Pago técnicamente posible |
| HUGO (waiter) | waiter/orders/active | 200 | **count=2** |
| lizvania | orders (all / SENT_TO_BAR) | 200 | OK |
| admin.demo (casa-demo) | auth/me | 200 | Tenant **suspended** en login real |

**Login password/PIN vía HTTP:** ruta correcta `POST /api/v1/auth/login-password` y `login-pin`. Demo tenant rechazado por suscripción suspendida.

---

## Tabla de hallazgos

| Problema | Sev. | Evidencia DB/API | Cómo reproducir | Recomendación |
|----------|------|------------------|-----------------|---------------|
| 3 liquidaciones PENDING del turno 5 (CLOSED) sin pagar | **P1** | `staff_settlements` id 3,6,7 → shift_id=5 CLOSED; turno actual=6 | Cajera C22 → liquidaciones turno anterior | Proceso: pagar/cancelar/migrar; o regenerar en turno 6 |
| Caja OPEN antigua tenant R (id=2) >24 h | **P1** | `cash_sessions` id=2 OPEN desde 2026-06-29 | Admin tenant R | Cerrar o forzar cierre con auditoría |
| Turnos OPEN zombie tenant demo (1) y R (2) | **P2** | `official_shifts` id 1,2 OPEN desde 2026-06-28 | Login contexto demo/R | Cerrar turnos huérfanos |
| Comandas OPEN/SENT_TO_BAR demo tenant 1 | **P2** | orders id 1,2,3,6 status antiguos | Garzón demo (tenant suspended) | Limpiar datos demo o reactivar tenant solo en dev |
| Cajera C22 no puede close-check turno (403) | **P1** | API `shifts/current/close-check` → Permiso `shifts.close` | Login lizvania → cerrar turno | Asignar permiso a cashier o flujo owner-only documentado |
| Owner ve 0 liquidaciones turno actual vs cajera 5 | **P2** | LAURA count=0, lizvania count=5 mismo endpoint | Comparar roles en UI | Verificar filtro por rol/permiso en `current-shift` |
| print_job 105 PENDING (ticket liquidación) | **P2** | job 09:16; device last_seen 08:40 | Pagar liquidación sin agente | No bloquea pago; limpiar job o reconectar agente |
| Tenant casa-demo suspended | **P2** | `tenants.status=suspended` | Login PIN demo | Esperado si demo apagado; no usar para QA prod |
| BILLED orders shift 5 sin cobrar (ids 17-23) | **P1** | 7 comandas BILLED en turno cerrado | Caja → cobrar | Cobrar o cancelar antes de cierre fiscal |
| document_sequences desfasada (histórico) | **P0→resuelto** | Era last_value=1 con ticket 000002 | 2do pago → 409 | Fix final desplegado; DB ahora OK (last_value=3) |
| public/storage NOT LINKED | **P3** | `php artisan about` | Subir logo/adjuntos | `php artisan storage:link` |

---

## Scripts QA reutilizables

```bash
# SQL batch
cmd /c "C:\xampp\mysql\bin\mysql.exe -u root nigtpos < scripts\real_db_qa_audit.sql"

# API JWT (sin mutar datos)
php scripts/real_db_qa_internal.php
```

---

## Conclusión backend

**Bloqueadores operativos actuales en C22 (tenant real):**

1. Liquidaciones pendientes del turno anterior (3 registros).
2. Comandas BILLED sin cobro en turno 5.
3. Permiso de cierre de turno ausente en rol cajera.

**Numeración documental:** alineada en DB tras fix; pay-preview settlement 6 responde 200.

**Siguiente paso sugerido:** decidir P0/P1 a corregir antes de noche operativa (sin tocar aún en este QA).
