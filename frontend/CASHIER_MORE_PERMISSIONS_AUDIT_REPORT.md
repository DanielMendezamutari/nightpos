# Auditoría permisos frontend — Tab «Más» cajera

**Fecha:** 2026-06-17

## Estado previo

`cashier/more.vue` mostraba solo 4 ítems:

- Liquidaciones ✓
- Ventas del turno ✓
- Cierre de turno ✗ (cajera básica sin `shifts.close`)
- Reportes ✗ (cajera básica sin `reports.access`)

Resultado: cajera básica veía **2 opciones** — menú poco útil.

## Guards y routing

| Archivo | Comportamiento |
|---------|----------------|
| `cashierRouting.js` | Allowlist paths para cajera básica |
| `guards.js` | Redirige admin routes → shell; bloquea paths fuera de allowlist |
| `definePage meta.permission` | Bloquea ruta sin permiso (no 403 page, redirect) |

**Problema detectado:** allowlist no incluía `/nightpos/products`, `/nightpos/services`, etc. — rutas del menú «Más» redirigían al shell aunque el usuario tuviera permiso.

## Permisos por página (rutas secundarias)

| Ruta | Permiso requerido | Cajera básica |
|------|-------------------|---------------|
| `nightpos-settlements` | `settlements.access` | Sí |
| `nightpos-sales` | `sales.list` | Sí |
| `nightpos-shift-console` | `shift_console.access` | Sí |
| `nightpos-services-*` | `bracelets/room_services/shows.*` | Sí |
| `nightpos-rooms-*` | `rooms.access` | Sí |
| `nightpos-products` | `products.list` | Sí |
| `nightpos-products-create` | `products.create` | No (no en Más) |
| `nightpos-categories` | `products.list` | Sí |
| `nightpos-catalog-prices` | `products.list` | Sí |
| `nightpos-settings-*` | según setting | Parcial |
| `nightpos-staff-waiter-assignments` | `settings.waiter_assignments` | No |
| `nightpos-shifts-close` | `shifts.close` | No |
| `nightpos-finance-reports` | `reports.access` | No |

## Regla UI aplicada

- Mostrar ítem solo si `can(permission)`.
- No incluir rutas de admin completo (usuarios, roles, SaaS).
- Senior/admin mantienen menú vertical completo (`nightpos-r4.js`).

## Validación manual sugerida

1. Login cajera básica → Más muestra Operación + Catálogo + Config (sin Cierre turno ni Reportes).
2. Login cajera senior → Más incluye Cierre turno, Asignar mesas, Fiscalización cajas.
3. Admin → menú completo, sin shell forzado.
4. Clic en ítem sin permiso → no aparece en lista.
