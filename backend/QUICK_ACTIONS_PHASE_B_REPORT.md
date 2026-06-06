# Quick Actions — Fase B (Backend)

**Fecha:** 2026-06-04  
**Referencia:** `SYSTEM_QUICK_ACTIONS_AUDIT.md` (QB-01 a QB-05)

## Resumen

Fase B añade endpoints transaccionales/administrativos, catálogo `show_types`, garzón rápido, precio rápido en comanda y fuentes pendientes de liquidación. **163 tests** pasando (`QuickActionsPhaseBTest` — 10 casos).

## Flujos corregidos

| ID | Antes | Después |
|----|--------|---------|
| QB-01 | Crear empresa → salir → sucursal → admin | `POST /admin/platform/setup` transaccional (tenant + roles + branch + admin) |
| QB-02 | Sin garzón rápido | `POST /staff/quick-waiters` con comisión % (default 5%) |
| QB-03 | Enum fijo PRIVATE/STAGE/SPECIAL | Tabla `show_types` + CRUD API |
| QB-04 | Error opaco sin precio | Mensaje claro + `POST /products/{id}/quick-prices` |
| QB-05 | Piezas ACTIVE sin aviso en liquidaciones | `GET /settlements/current-shift/pending-sources` |

## Endpoints creados

| Método | Ruta | Permiso |
|--------|------|---------|
| POST | `/api/v1/admin/platform/setup` | `platform.setup` |
| GET | `/api/v1/staff/waiters` | `staff.quick_create_waiter` |
| POST | `/api/v1/staff/quick-waiters` | `staff.quick_create_waiter` |
| GET | `/api/v1/show-types` | `show_types.access` |
| POST | `/api/v1/show-types` | `show_types.create` |
| PUT | `/api/v1/show-types/{id}` | `show_types.update` |
| POST | `/api/v1/products/{id}/quick-prices` | `product_prices.quick_create` |
| GET | `/api/v1/settlements/current-shift/pending-sources` | `settlements.pending_sources` |

### Platform setup (transaccional)

Payload: `{ tenant: {}, branch: {}, admin: {} }`  
Respuesta: `{ tenant, branch, admin }`  
Provisiona roles operativos del tenant vía `TenantRoleProvisioner` (owner, cashier, waiter, cleaning).

### Quick waiter

- `staff_role`: WAITER  
- Username autogenerado (`WaiterUsernameGenerator`)  
- Comisión obligatoria (default `5.00` si no se envía)  
- Acceso sucursal actual  

### Show types

Migración `show_types`: `tenant_id`, `branch_id` nullable, `name`, `suggested_price`, `status`.  
Shows guardan `show_type` como **nombre** del catálogo (string libre validado en request).

### Pending sources

```json
{
  "active_room_services_count": 0,
  "unpaid_finished_room_services_count": 0,
  "unpaid_bracelets_count": 0,
  "unpaid_shows_count": 0
}
```

## Permisos nuevos

| Slug | Super admin | Admin (owner) | Cajera |
|------|-------------|---------------|--------|
| `platform.setup` | Sí | No | No |
| `staff.quick_create_waiter` | Sí* | Sí | No |
| `show_types.access` | Sí* | Sí | Sí |
| `show_types.create` | Sí* | Sí | Sí |
| `show_types.update` | Sí* | Sí | No |
| `product_prices.quick_create` | Sí* | Sí | No |
| `settlements.pending_sources` | Sí* | Sí | Sí |

\*Super admin recibe todos los permisos del seeder.

Migración: `2026_06_06_100028_assign_quick_actions_phase_b_permissions.php`

## Validación manual

1. Superadmin → `POST /admin/platform/setup` con slug único.  
2. Admin → `POST /staff/quick-waiters`.  
3. `POST /show-types` + registrar show con ese nombre.  
4. Comanda sin precio → 422 con mensaje explícito → `POST quick-prices`.  
5. `GET pending-sources` con pieza ACTIVE > 0.

## Fase C (pendiente)

Ver `SYSTEM_QUICK_ACTIONS_AUDIT.md`: cliente rápido, mesas catalogadas, métodos de pago configurables, producto rápido desde comanda, sugerencia habitaciones alternativas.
