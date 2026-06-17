# Auditoría permisos — Tab «Más» cajera

**Fecha:** 2026-06-17  
**Fuente:** `SeedsNightPosFoundation.php` (roles `cashier`, `cashier_senior`, `admin`)

## Respuestas rápidas (cajera básica)

| # | Pregunta | Cajera básica | Cajera senior |
|---|----------|---------------|---------------|
| 1 | ¿Puede crear productos (`products.create`)? | **No** | **No** (sí `products.quick_create`) |
| 2 | ¿Puede editar precios (`products.create` / config)? | **No** | **No** (sí `product_prices.quick_create`) |
| 3 | ¿Puede entrar a servicios? | **Sí** (manillas, piezas, shows) | **Sí** |
| 4 | ¿Puede crear piezas (`rooms.create`)? | **No** (sí `room_services.create`) | **Sí** `rooms.create` |
| 5 | ¿Puede asignar mesas? | **No** | **Sí** `settings.waiter_assignments` |
| 6 | ¿Puede crear salones/mesas (manage)? | **No** (solo ver `settings.service_areas/tables`) | **Sí** `.manage` en mesas |
| 7 | ¿Puede ver reportes? | **No** `reports.access` | **No** |
| 8 | ¿Puede cerrar turno? | **No** `shifts.close` | **Sí** |
| 9 | ¿Puede pagar liquidaciones? | **Sí** `settlements.pay` | **Sí** |
| 10 | ¿Puede registrar movimientos? | **Sí** `cash.access` | **Sí** |

## Matriz permiso → rol → Mostrar en «Más»

| Permiso | Cajera básica | Cajera senior | Admin | Mostrar en «Más» |
|---------|---------------|---------------|-------|------------------|
| `sales.charge` | Sí | Sí | Sí | Tab Cobrar (shell) |
| `sales.direct_create` | Sí | Sí | Sí | Tab Venta (shell) |
| `cash.access` | Sí | Sí | Sí | Tab Caja (shell) |
| `settlements.access` | Sí | Sí | Sí | Liquidaciones |
| `sales.list` | Sí | Sí | Sí | Ventas del turno |
| `shift_console.access` | Sí | Sí | Sí | Consola de turno |
| `bracelets.access` | Sí | Sí | Sí | Manillas |
| `room_services.access` | Sí | Sí | Sí | Piezas |
| `shows.access` | Sí | Sí | Sí | Shows |
| `room_services.cleaning_view` | Sí | Sí | Sí | Control piezas |
| `rooms.access` | Sí | Sí | Sí | Habitaciones |
| `products.list` | Sí | Sí | Sí | Productos, Vista precios |
| `product-categories.list` | Sí | Sí | Sí | Categorías |
| `products.create` | No | No | Sí | — (no en Más) |
| `products.quick_create` | Sí | Sí | Sí | — (flujo inline POS) |
| `product_prices.quick_create` | No | Sí | Sí | — (flujo inline) |
| `settings.cash_reasons` | Sí | Sí | Sí | Motivos de caja |
| `settings.payment_methods` | Sí | Sí | Sí | Métodos de pago |
| `settings.service_areas` | Sí | Sí | Sí | Ambientes |
| `settings.service_tables` | Sí | Sí | Sí | Mesas |
| `settings.waiter_assignments` | No | Sí | Sí | Asignar mesas |
| `settings.room_types` | Sí | Sí | Sí | Tipos habitación |
| `settlements.history` | Sí | Sí | Sí | Historial liquidaciones |
| `shifts.close` | No | Sí | Sí | Cierre de turno |
| `shifts.access` | Sí | Sí | Sí | Turno actual |
| `reports.access` | No | No | Sí | Reportes |
| `admin.cash_sessions.list` | No | Sí | Sí | Fiscalización cajas |

## Backend — sin cambios de permisos

Los seeders actuales son coherentes con el diseño operativo:

- Cajera básica: operar caja, cobrar, liquidar, servicios, catálogo en lectura, configuración operativa sin `.manage`.
- Cajera senior: + cierre turno, gestión mesas, asignar garzones, fiscalización cajas.

## Cash movements y liquidaciones

Verificado en `SettlementPaymentMethodTest` (10 tests OK):

- Pago liquidación CASH → egreso CASH, baja `expected_cash`.
- Pago QR → egreso QR, baja `expected_by_method.qr`.
- Pago CARD → egreso CARD, baja `expected_by_method.card`.
- `payment_method` obligatorio en mark-paid.

## Close cash session

API actual mantiene solo `declared_closing_amount` (efectivo físico). QR/tarjeta verificados se registran en `closing_notes` desde frontend (sin migración).
