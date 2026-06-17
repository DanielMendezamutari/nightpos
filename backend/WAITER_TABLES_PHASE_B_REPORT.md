# Fase B — Modelo de mesas MVP (Backend)

**Fecha:** 2026-06-16  
**Estado:** ✅ Completado (backend)  
**Auditoría origen:** `backend/WAITER_TABLES_COMPANION_BRACELET_AUDIT.md` (Parte 1)  
**Pendiente:** Fase C frontend («Mis mesas» garzón + config admin)

---

## Objetivo

Reemplazar el flujo principal `table_label` libre del garzón por mesas físicas catalogadas, asignación garzón↔mesa y tap-to-open idempotente.

---

## Schema

### `service_tables`

| Campo | Notas |
|-------|-------|
| `tenant_id`, `branch_id` | Scope multi-tenant |
| `service_area_id` | Salón (FK `service_areas`) |
| `code` | Único por sucursal |
| `label` | Display «Mesa 1» |
| `sort_order` | Orden en grid |
| `status` | `active` / `inactive` |

### `waiter_table_assignments`

| Campo | Notas |
|-------|-------|
| `waiter_user_id` | Garzón |
| `service_table_id` | Mesa |
| `official_shift_id` | Nullable — null = plantilla permanente |
| `assigned_by_user_id`, `assigned_at` | Auditoría |

### `orders.service_table_id`

FK nullable. Se mantiene `table_label` denormalizado para tickets e histórico.

---

## Estados operativos (runtime)

Solo dos estados para el garzón:

| Estado | Condición |
|--------|-----------|
| `FREE` | Sin comanda activa en la mesa |
| `OCCUPIED` | Comanda en `OPEN`, `SENT_TO_BAR`, `IN_PREPARATION` o `READY` |

Al cobrar o cancelar, la mesa vuelve a `FREE` por ausencia de comanda activa.

---

## API Admin / Cajera Senior

| Método | Ruta | Permiso |
|--------|------|---------|
| `GET` | `/api/v1/service-tables` | `settings.service_tables` |
| `POST` | `/api/v1/service-tables` | `settings.service_tables.manage` |
| `PUT` | `/api/v1/service-tables/{id}` | `settings.service_tables.manage` |
| `GET` | `/api/v1/waiter-table-assignments` | `settings.waiter_assignments` |
| `PUT` | `/api/v1/waiter-table-assignments/sync` | `settings.waiter_assignments.manage` |

**Sync body:**

```json
{
  "waiter_user_id": 12,
  "service_table_ids": [1, 2, 3]
}
```

Reemplaza todas las asignaciones permanentes del garzón en la sucursal.

---

## API Garzón

| Método | Ruta | Permiso |
|--------|------|---------|
| `GET` | `/api/v1/waiter/my-tables` | `waiter.my_tables` |
| `POST` | `/api/v1/waiter/my-tables/{tableId}/open` | `waiter.my_tables` |

**GET response (ejemplo):**

```json
{
  "tables": [
    { "id": 1, "label": "Mesa 1", "area": "VIP", "status": "FREE", "order_id": null },
    { "id": 2, "label": "Mesa 2", "area": "VIP", "status": "OCCUPIED", "order_id": 100, "total": "250.00" }
  ]
}
```

**POST open:**

- Mesa `FREE` → crea comanda (`service_table_id`, `table_label`, `service_area_id`, turno) → `201`, `created: true`
- Mesa `OCCUPIED` → devuelve comanda existente → `200`, `created: false`
- Transacción DB: nunca dos comandas activas en la misma mesa

---

## Capas implementadas

- Models: `ServiceTableModel`, `WaiterTableAssignmentModel`
- Repositories: `ServiceTableRepositoryInterface`, `WaiterTableAssignmentRepositoryInterface`
- Use cases: CRUD mesas, sync asignaciones, `GetWaiterMyTablesUseCase`, `OpenWaiterTableUseCase`
- `OrderRepository::findActiveByServiceTable()` + `create()` con `service_table_id`
- Permisos en migración + `SeedsNightPosFoundation`

---

## Tests

`tests/Feature/Api/V1/WaiterTablesPhaseBTest.php` (10 escenarios):

1. Admin crea mesa  
2. Admin asigna mesas a garzón  
3. Garzón ve solo sus mesas  
4. Mesa libre crea comanda  
5. Mesa ocupada devuelve comanda existente  
6. No dos comandas activas en la misma mesa  
7. Cobrar libera mesa  
8. Otra sucursal no visible  
9. Otro garzón no ve mesas ajenas  
10. `table_label` histórico sigue funcionando  

**Suite completa:** 529 tests passing.

---

## Sin romper

- `table_label` libre en `POST /orders` (excepcional / histórico)
- `service_areas` existentes
- Combos CBA, SSE P0, liquidaciones, venta directa

---

## Siguiente paso — Fase C (Frontend)

- Home garzón «Mis mesas» (grid LIBRE/OCUPADA)
- Configuración / Operación / Mesas
- Personal / Garzones / Asignar mesas
- Botón secundario «Otra mesa» (flujo `table_label` excepcional)
