# NightPOS - Hexagonal Starter Guide

## 1) Modular structure (Laravel)

Use feature modules inside `app/Modules`:

- `Sales` (POS + orders in real time)
- `Cashier` (open/close shifts, payment control)
- `Inventory` (stock, transfers, anomaly detection)
- `CompanionControl` (girls performance, rotation)
- `AccountingReports` (daily ledger + BI)
- `Shared` (cross-module contracts/events)

Each module should keep this shape:

- `Domain` -> business rules, entities, value objects, ports
- `Application` -> use cases and DTOs
- `Infrastructure` -> Eloquent repos, queues, websocket, printing
- `Interfaces` -> controllers and API resources

## 2) Core rules locked in domain

- Waiter never sets drink price manually.
- Admin defines product prices:
  - `price_solo`
  - `price_with_companion`
- `consumption_type` chooses which configured product price to apply.
- Every order item is registered immediately with timestamp.
- Companion is required when `with_companion`.
- Inventory movement is created for every served drink.

## 3) First use cases to implement

1. `OpenShiftUseCase`
    - Input: cashier, site, opening cash
    - Output: shift id (opened)
2. `AddOrderItemUseCase`
    - Input: order, product, waiter, companion(optional), consumption type, quantity
    - Action: resolve automatic price via `PricingPolicy`
3. `RegisterPaymentUseCase`
    - Input: order, method, amount
    - Action: create payment immediately, no deferred close registration

## 4) Initial database scope

Migration `2026_04_28_220000_create_nightpos_core_tables.php` adds:

- `sites`
- `shift_turns`
- `companions`
- `products`
- `customer_sessions`
- `orders`
- `order_items`
- `payments`
- `inventory_movements`

This is enough to start MVP development for:

- real-time cashier flow
- waiter order capture
- companion attribution
- live inventory discount

## 5) Frontend and mobile strategy

- `frontend` (Vue): admin + cashier dashboard + reports
- waiter mobile app: Vue + Capacitor consuming same API
- real-time updates: Laravel broadcasting + Echo channel subscriptions

## 6) Next implementation milestone

1. Build API endpoints for open shift, add order item, register payment
2. Add Eloquent repositories for module ports
3. Add policy validation for roles (admin/cashier/waiter/manager)
4. Add tests for automatic pricing and immediate registration
