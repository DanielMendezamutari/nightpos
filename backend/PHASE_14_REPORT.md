# PHASE_14_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Fase:** 14 — Liquidaciones garzones y chicas  
**Fecha:** 2026-06-02

---

## 1. Tablas creadas

### `staff_settlements`

| Campo | Descripción |
| ----- | ----------- |
| `tenant_id`, `branch_id` | Alcance multi-tenant |
| `official_shift_id` | Turno oficial |
| `cash_session_id` | Nullable, referencia de caja |
| `staff_user_id` | Garzón o chica |
| `staff_role` | `WAITER` / `GIRL` |
| `settlement_type` | `WAITER` / `GIRL` |
| `total_amount` | Suma de ítems |
| `status` | `PENDING` / `PAID` / `CANCELLED` |
| `paid_by_user_id`, `paid_at`, `notes` | Pago |

Índice único: `(official_shift_id, staff_user_id, settlement_type)`.

### `staff_settlement_items`

| Campo | Descripción |
| ----- | ----------- |
| `sale_id`, `sale_item_id`, `order_id` | Trazabilidad |
| `source_type` | `WAITER_COMMISSION`, `GIRL_BRACELET`, `GIRL_ROOM`, `GIRL_SHOW` |
| `description`, `base_amount`, `percent`, `amount` | Detalle |

Índice único: `(sale_item_id, source_type)` — evita duplicar por venta.

**Migración:** `2026_06_03_100015_create_staff_settlements_tables.php`

---

## 2. Endpoints creados

| Método | Ruta | Permiso |
| ------ | ---- | ------- |
| GET | `/api/v1/settlements/current-shift` | `settlements.access` |
| POST | `/api/v1/settlements/generate-current-shift` | `settlements.generate` |
| GET | `/api/v1/settlements/{id}` | `settlements.access` |
| POST | `/api/v1/settlements/{id}/mark-paid` | `settlements.pay` |
| GET | `/api/v1/settlements/history` | `settlements.history` |

---

## 3. Reglas implementadas

1. Liquidaciones solo desde ventas cobradas (`sales` + `sale_items` del turno).
2. Snapshots de comisión y chica — no recalcula precios actuales.
3. No modifica ventas históricas.
4. No duplica ítems (`unique sale_item_id + source_type`).
5. Estados `PENDING` / `PAID`; pago con `settlements.pay`.
6. Filtro por `tenant_id`, `branch_id`, `official_shift_id`.
7. Garzón/chica con solo `settlements.access` ven su liquidación (`SettlementAccessPolicy`).
8. Generación requiere turno oficial **abierto**.

---

## 4. Cálculo garzones

Por cada `sale_item` del turno con `waiter_commission_amount_snapshot > 0`:

- Agrupa por `sales.waiter_user_id`.
- Ítem `WAITER_COMMISSION` con `amount = waiter_commission_amount_snapshot`.
- `base_amount = line_total`, `percent = waiter_commission_percent_snapshot` (snapshot al cobrar).

Fuente: `ChargeOrderUseCase` + `WaiterCommissionResolver` → `staff_profiles.waiter_commission_percent`.

---

## 5. Cálculo chicas

Por cada `sale_item` con `girl_amount_snapshot > 0` y `girl_user_id`:

- Agrupa por chica.
- Ítem `GIRL_BRACELET` con `amount = girl_amount_snapshot` (manilla / CON_ACOMPANANTE).
- No genera liquidación si no hay chica asignada.

---

## 6. Piezas y shows (preparado)

Tipos `GIRL_ROOM` y `GIRL_SHOW` definidos en esquema y mapper; resumen expone `total_pieces` y `total_shows` (hoy `0.00` hasta módulo dedicado).

---

## 7. Permisos

| Slug | Admin | Cajero | Garzón |
| ---- | ----- | ------ | ------ |
| `settlements.access` | ✓ | ✓ | ✓ (solo propia) |
| `settlements.generate` | ✓ | ✓ | — |
| `settlements.pay` | ✓ | ✓ | — |
| `settlements.history` | ✓ | ✓ | — |

Migración incremental: `2026_06_03_100016_assign_settlements_permissions_to_roles.php`

---

## 8. Tests

`tests/Feature/Api/V1/SettlementsPhase14Test.php` — 9 casos OK.

---

## 9. Validación dev

```bash
cd backend
php artisan migrate
php artisan db:seed
php artisan test --filter=SettlementsPhase14
```

Flujo: turno → caja → comanda SOLO_CLIENTE + CON_ACOMPANANTE con chica → cobrar → POST generate → GET current-shift → mark-paid.

---

## 10. Próxima fase recomendada

- Módulo **piezas / shows** con ítems `GIRL_ROOM` / `GIRL_SHOW`.
- Rellenar `shift_closures.total_girl_payouts` / `total_waiter_payouts` al cerrar turno.
- Export PDF / impresión de liquidación.
- Descuentos y bonos (columnas en `FRONTEND_GUIDELINES`).

---

## Archivos principales

- `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentStaffSettlementRepository.php`
- `app/Application/StaffSettlement/UseCases/*`
- `app/Http/Controllers/Api/V1/SettlementController.php`
- `routes/api.php`
