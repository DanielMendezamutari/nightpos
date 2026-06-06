# Fase C1 — Correcciones críticas de operación (Backend)

**Fecha:** 2026-06-02  
**Referencia:** `NIGHTPOS_OPERATION_AUDIT.md` (P1)

## Resumen

Se cerraron riesgos P1 que afectan dinero, comisiones y ventas: garzón obligatorio en comandas, producto rápido con precios, avisos de liquidación y soporte API para habitaciones en limpieza. **171 tests** pasando (`PhaseC1Test` — 7 casos).

## Cambios por ítem

| ID | Implementación |
|----|----------------|
| C1-1 | `CreateOrderUseCase`: si `staff_role === WAITER` o rol `waiter` → garzón = usuario actual; si no → `waiter_user_id` obligatorio y validado |
| C1-2 | Sin cambio API (reutiliza `POST /staff/quick-waiters` existente) |
| C1-3 | `POST /api/v1/products/quick` + `QuickCreateProductUseCase` (producto + precio SOLO + opcional CON_ACOMPANANTE en transacción) |
| C1-4 | Sin cambio API (ya existían `GET /rooms/available` y `GET /rooms/cleaning`) |
| C1-5 | `GET /settlements/current-shift/pending-sources` ampliado con `waiters_without_commission` y `girls_without_commission_flag` |

## Endpoints

| Método | Ruta | Permiso |
|--------|------|---------|
| POST | `/api/v1/products/quick` | `products.quick_create` |
| GET | `/api/v1/settlements/current-shift/pending-sources` | `settlements.pending_sources` (campos nuevos) |

### Crear comanda

- Garzón (`WAITER` / rol `waiter`): ignora `waiter_user_id` enviado; asigna usuario de sesión.
- Cajero/admin: sin `waiter_user_id` → `422` — «Debe seleccionar un garzón para abrir la comanda.»

### Producto rápido

Payload mínimo:

```json
{
  "name": "Trago",
  "category_id": 1,
  "solo_price": 30,
  "companion_price": 90,
  "girl_amount": 45,
  "house_amount": 45
}
```

Respuesta: `{ product, prices[] }`.

### Liquidaciones — readiness

Campos adicionales en `pending-sources`:

- `waiters_without_commission`: `[{ id, name }]` — garzones activos sin % o con 0.
- `girls_without_commission_flag`: `[{ id, name }]` — chicas activas sin `can_receive_girl_commissions`.

No bloquean `POST /settlements/generate-current-shift`.

## Permisos (seeder)

- `products.quick_create` → owner, cajero.
- `staff.quick_create_waiter` → cajero (selector + alta rápida en nueva comanda).

## Tests

`tests/Feature\Api/V1/PhaseC1Test.php`:

1. Cajera sin garzón → 422  
2. Garzón usa su propio usuario  
3. Quick waiter + comanda  
4. Quick product con precios  
5. Listado habitaciones en limpieza  
6. Banner garzón sin comisión (API)  
7. Banner chica sin flag (API)  

## Archivos principales

- `app/Application/Order/UseCases/CreateOrderUseCase.php`
- `app/Application/Product/UseCases/QuickCreateProductUseCase.php`
- `app/Application/StaffSettlement/UseCases/GetSettlementPendingSourcesUseCase.php`
- `app/Domain/Order/Exceptions/OrderDomainException.php` (`waiterRequired`)
