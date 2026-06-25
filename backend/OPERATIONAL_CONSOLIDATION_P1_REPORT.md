# Consolidación operativa P1 — Backend (2026-06-21)

Evolución de la arquitectura existente. Sin segunda cola ni duplicación de lógica.

## 1. SALE_RECEIPT automático al cobrar

| Pieza | Archivo |
|-------|---------|
| Migración `auto_print_sale_receipt` | `database/migrations/2026_06_21_100080_add_auto_print_sale_receipt_to_branches.php` |
| Settings reader | `BranchPrintSettingsReader::isAutoPrintSaleReceiptEnabled()` |
| Use case | `CreateSaleReceiptPrintJobUseCase` |
| Builder | `PrintTicketContentBuilder::buildSaleReceipt()` |
| Hook comanda | `ChargeOrderUseCase` (post-commit, nunca revierte cobro) |
| Hook venta directa | `CreateDirectSaleUseCase` |
| API settings | `GET/PATCH /print-settings` → `auto_print_sale_receipt` |

**Respuesta cobro:** `print_job`, `print_warning` (ej. sin impresora activa).

**Idempotencia:** `sale_receipt:{sale_id}:v1`.

**Tests:** `LocalPrintAgentTest` — crea job `SALE_RECEIPT` al cobrar.

## 2. Pieza → comanda editable

| Pieza | Detalle |
|-------|---------|
| Migración | `orders.source_type/source_id`, `room_services.order_id` |
| Origen | `ROOM_SERVICE` en `orders.source_type` |
| Flujo | `CreateRoomServiceUseCase` crea comanda OPEN `Pieza {label}` y enlaza `order_id` |
| SSE | `order.created` tras registrar pieza |

## 3. Cola caja solo SENT_TO_BAR

`OrderListScopeResolver::CASHIER_CHARGEABLE = ['SENT_TO_BAR']` — excluye borradores OPEN.

## 5. Comanda barra y formatos P2 (completado)

Ver `PRINTING_P2_OPERATIONAL_FORMATS_REPORT.md`:

- Reimpresión automática en correcciones (`bar_correction_count`, `DispatchBarCorrectionPrintJobUseCase`)
- Numeración unificada COMANDA/PRECUENTA/PAGO
- Fechas Creada/Impresa, estados EN BARRA / PENDIENTE DE COBRO / PAGADO
- Precheck y sale receipt rediseñados en builder
- Cajera puede imprimir precuenta

## 6. Impresión pieza/show (2026-06-21)

Ver `ROOM_SERVICE_SHOW_PRINT_FIX_REPORT.md`:

- Auto `ROOM_SERVICE` al registrar pieza
- Auto `SHOW_TICKET` al registrar show
- Default split pieza 60/40 (configurable por registro)
- Reimpresión: `POST /room-services/{id}/print`, `POST /shows/{id}/print`

## 7. Pendiente P3

- Plantillas JSON por sucursal
- Firma Ribersoft configurable

## Migraciones requeridas

```bash
php artisan migrate
```

Nuevas: `2026_06_21_100080_*`, `2026_06_21_100081_*`, `2026_06_21_100090_*`.
