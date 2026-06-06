# OPERATIONAL_ROLE_FLOW_FIX_REPORT.md

**Proyecto:** NightPOS — Backend Laravel  
**Fecha:** 2026-06-02  
**Referencias:** `NIGHTPOS_OPERATION_FLOW_AUDIT.md`, `DOMAIN_DESIGN.md`, `PHASE_C4_WAITER_REPORT.md`

---

## 1. Problemas corregidos

| ID | Problema | Corrección |
|----|----------|------------|
| C-01 | Cajera sin vista clara para cobrar comandas | API `GET /orders?scope=cashier_chargeable` + campos `waiter_name`, `opened_at`, `items_count` |
| W-01 | Botón «Ver» confundía con solo lectura en OPEN | Frontend: acciones por estado (Gestionar / Agregar extra / mensaje cobro) |
| W-02 | + Bebida deshabilitado en SENT_TO_BAR pese a API | Detalle garzón habilita «Agregar extra» cuando `canModifyOrder` |
| G-01 | GIRL mapeaba a rol `waiter` y entraba a `/nightpos/waiter` | Rol RBAC `girl`, guards, home route `/nightpos/girl` |
| G-02 | Sin pantalla de ingresos para chica | `GET /api/v1/girl/shift-earnings` + permisos `girl.dashboard`, `girl.earnings.view` |
| S-01 | Wizard SaaS con permisos garzón incompletos (4 vs 11) | `TenantDefaultRolePermissions::waiter()` alineado al seed demo |
| S-02 | Cajera SaaS sin `orders.create`, `shift_console.access`, etc. | `TenantDefaultRolePermissions::cashier()` ampliado |
| S-03 | Sin rol `girl` en provisionamiento tenant | `TenantRoleProvisioner` + método `girl()` |
| P-01 | `WaiterOrderAccessPolicy` trataba GIRL como garzón por `roleSlug` | Solo `staff_role === 'WAITER'` |

---

## 2. Nuevo flujo cajera

1. Menú **Operación → Cobrar comandas** → `/nightpos/cashier/orders`
2. Lista comandas con `scope=cashier_chargeable`: `OPEN`, `SENT_TO_BAR`, `IN_PREPARATION`, `READY`
3. Muestra garzón, mesa, hora, estado, total
4. Si caja cerrada → botón «Abrir caja ahora» (frontend)
5. Si caja abierta → «Cobrar» → detalle comanda con `?charge=1`

**API:** `GET /api/v1/orders?scope=cashier_chargeable` (permiso `orders.access`)  
**Cobro:** `POST /api/v1/orders/{id}/charge` (permiso `sales.charge` + caja abierta)

---

## 3. Nuevo flujo garzón

| Estado | Card | Detalle |
|--------|------|---------|
| OPEN | Gestionar, + Producto, Enviar barra | Acciones de modificación visibles |
| SENT_TO_BAR | Ver, Agregar extra | Alerta «En barra» |
| IN_PREPARATION / READY | Ver + mensaje pendiente cobro | Solo lectura |
| BILLED | Ver historial | Solo lectura |

`WaiterOrderAccessPolicy` restringe acceso a comandas propias solo para `staff_role WAITER`.

---

## 4. Nuevo flujo chica

1. Login PIN chica (`chica.centro` / `9012`) → home `/nightpos/girl`
2. Guard bloquea rutas fuera de `/nightpos/girl/*`
3. **No** tiene `waiter.dashboard`, `orders.create`, ni rol `waiter`
4. `GET /girl/shift-earnings` devuelve consumos, manillas, piezas, shows, total pendiente/pagado
5. Rol RBAC `girl` provisionado en tenants nuevos

---

## 5. Permisos corregidos (wizard SaaS)

### Garzón (`waiter`)
`waiter.dashboard`, `waiter.orders`, `orders.access`, `orders.create`, `orders.add_items`, `orders.send_to_bar`, `products.list`, `product-categories.list`, `settings.service_areas`, `staff.quick_create_girl`, `notifications.*`

### Cajera (`cashier`)
Incluye además: `orders.create`, `orders.add_items`, `orders.send_to_bar`, `shift_console.access`, `staff.quick_create_waiter`, `settings.cash_reasons`, `settings.payment_methods`, `settings.service_areas`, `settings.room_types`

### Limpieza (`cleaning`)
+ `cleaning.earnings.view`

### Chica (`girl`) — nuevo rol
`girl.dashboard`, `girl.earnings.view`, `notifications.access`, `notifications.read`

---

## 6. Archivos modificados

| Archivo |
|---------|
| `app/Application/Tenant/Support/TenantDefaultRolePermissions.php` |
| `app/Application/Tenant/Support/TenantRoleProvisioner.php` |
| `app/Application/User/Support/StaffRoleToRoleResolver.php` |
| `app/Application/Waiter/Services/WaiterOrderAccessPolicy.php` |
| `app/Application/Order/DTOs/ListOrdersInput.php` |
| `app/Application/Order/UseCases/ListOrdersUseCase.php` |
| `app/Application/Order/Support/OrderMapper.php` |
| `app/Application/Girl/UseCases/GetGirlShiftEarningsUseCase.php` |
| `app/Http/Controllers/Api/V1/GirlController.php` |
| `app/Http/Controllers/Api/V1/OrderController.php` |
| `app/Domain/Order/Repositories/OrderRepositoryInterface.php` |
| `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentOrderRepository.php` |
| `database/seeders/Concerns/SeedsNightPosFoundation.php` |
| `routes/api.php` |
| `tests/Feature/Api/V1/OperationalRoleFlowFixTest.php` |

---

## 7. Validación manual

| # | Paso | Esperado |
|---|------|----------|
| 1 | Login cajera `1234` | Consola o menú operativo |
| 2 | Operación → Cobrar comandas | Lista con estados mezclados |
| 3 | Sin caja | Alerta + Abrir caja ahora |
| 4 | Abrir caja + Cobrar | Comanda → BILLED |
| 5 | Login garzón `5678` | OPEN muestra «Gestionar» |
| 6 | Agregar producto + enviar barra | Estados correctos |
| 7 | Login chica `9012` | Redirect `/nightpos/girl` |
| 8 | Chica intenta `/nightpos/waiter` | Redirect a girl home |
| 9 | Superadmin wizard nueva empresa | Garzón/limpieza/chica con permisos completos |

**Tests automáticos:** `php artisan test tests/Feature/Api/V1/OperationalRoleFlowFixTest.php` — 12/12 OK
