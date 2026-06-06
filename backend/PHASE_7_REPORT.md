# PHASE_7_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Fase:** 7 — Comandas (órdenes operativas, sin ventas ni caja)  
**Fecha:** 2026-06-02  
**Referencias:** `DOMAIN_DESIGN.md`, `PHASE_6_REPORT.md`

---

## 1. Tablas creadas

Migración `2026_06_03_100006_create_orders_tables.php`:

| Tabla | Campos clave |
| ----- | ------------ |
| `orders` | `tenant_id`, `branch_id`, `order_number`, `status`, `table_label`, `waiter_user_id`, `opened_by_user_id`, `subtotal`, `total`, `currency`, `sent_to_bar_at`, `cancelled_at` |
| `order_items` | `order_id`, `product_id`, `product_name`, `sale_mode`, `quantity`, `unit_price`, `line_total`, `girl_amount`, `house_amount`, `girl_user_id`, `item_status` |
| `order_status_history` | `order_id`, `status`, `changed_by_user_id`, `created_at` |

Numeración por sucursal: `C-0001`, `C-0002`, …

---

## 2. Endpoints creados

Middleware: `auth:api`, `nightpos.tenant`, `nightpos.branch:required`, `nightpos.branch.access`, `nightpos.permission:orders.access`

| Método | Ruta | Acción |
| ------ | ---- | ------ |
| GET | `/api/v1/orders` | Listar comandas de la sucursal (`?status=OPEN` opcional) |
| POST | `/api/v1/orders` | Abrir comanda |
| GET | `/api/v1/orders/{id}` | Detalle con ítems |
| POST | `/api/v1/orders/{id}/items` | Agregar producto (precio resuelto en backend) |
| POST | `/api/v1/orders/{id}/send-to-bar` | Enviar a barra |
| POST | `/api/v1/orders/{id}/cancel` | Cancelar |

**Roles (seeder existente):** `orders.access` en `tenant_owner`, `cashier`, `waiter`.

---

## 3. Reglas implementadas

| Regla | Implementación |
| ----- | -------------- |
| Precio desde catálogo, no desde frontend | `ProductPriceResolver` en `AddOrderItemUseCase` |
| Estados: OPEN → SENT_TO_BAR → … | `OrderStatus` + `updateStatus` + historial |
| No modificar ítems si BILLED/CANCELLED | `OrderStatus::allowsItemChanges()` |
| Enviar a barra solo desde OPEN y con ítems | `SendOrderToBarUseCase` |
| CON_ACOMPANANTE exige `girl_user_id` al enviar | Validación en envío (y al agregar ítem si se indica modo) |
| Aislamiento por tenant | Repositorio filtra `tenant_id` |
| `table_label` texto libre | Hasta módulo mesas/salones |
| Sin ventas, caja, inventario ni impresión | Fuera de alcance de esta fase |

---

## 4. Capas hexagonales

```
Domain/Order/
  Entities: Order, OrderItem
  ValueObjects: OrderStatus
  Exceptions: OrderDomainException, OrderNotFoundException
  Repositories: OrderRepositoryInterface

Domain/Product/Services/
  ProductPriceResolver (reutilizado)

Application/Order/
  UseCases: Create, List, Get, AddItem, SendToBar, Cancel
  Support: OrderMapper

Infrastructure/
  EloquentOrderRepository, OrderModel, OrderItemModel, OrderStatusHistoryModel

Http/
  OrderController, CreateOrderRequest, AddOrderItemRequest
```

---

## 5. Tests

Archivo `tests/Feature/Api/V1/OrderApiTest.php`:

- Garzón crea comanda y agrega ítem con precio resuelto
- Envío a barra (`SENT_TO_BAR`, ítems `SENT`)
- CON_ACOMPANANTE sin chica → 422 al enviar
- Cancelación de comanda abierta
- Aislamiento cross-tenant (404)
- Sucursal obligatoria (`X-Branch-Code`)

---

## 6. Comandos

```bash
cd backend
php artisan migrate
php artisan test
```

---

## 7. Próxima fase sugerida

- **Fase 8:** Ventas / cobro (BILLED), caja y arqueo  
- **Fase 6.5+ (frontend):** UI de comandas consumiendo estos endpoints  
- **Mesas/salones:** reemplazar `table_label` por entidad `tables`
