# Consolidación impresión P2 — Backend (2026-06-21)

Cierre operativo de formatos y reimpresión por corrección. **Sin** plantillas configurables (P3) ni footer Ribersoft (P3).

## Arquitectura (sin cambios)

Pipeline único: acción operativa → use case → `print_jobs` → agente Go → `PrintTicketContentBuilder`.

Tipos: `ORDER_COMMAND`, `PRECHECK`, `SALE_RECEIPT`.

---

## P2.1 — Reimpresión automática en correcciones

| Regla | Detalle |
|-------|---------|
| Disparador | Comanda en `SENT_TO_BAR` y corrección que afecta producción |
| Job | Nuevo `ORDER_COMMAND` con `is_reprint=true`, `correction_number=N` |
| Idempotencia | `order_command:{order_id}:correction:{n}` |
| Contador | `orders.bar_correction_count` (migración `2026_06_21_100090_*`) |

**Use case:** `DispatchBarCorrectionPrintJobUseCase`

**Hooks:**

- `AddOrderItemUseCase` (si ya `SENT_TO_BAR`)
- `UpdateOrderItemUseCase` (cantidad, producto, chica)
- `CancelOrderItemUseCase`
- `SyncOrderItemAllocationsUseCase`

**No imprime si:** comanda `OPEN`, sin envío a barra, o cambio sin efecto en producción.

**Ticket:**

```
REIMPRESION
COMANDA #258-2
Correccion #2
Creada: HH:mm
Impresa: HH:mm
Estado: EN BARRA
```

**Reimpresión manual** (`ReprintOrderCommandUseCase`): marca `REIMPRESION` sin incrementar contador de corrección.

---

## P2.2 — Numeración unificada

| Documento | Encabezado |
|-----------|------------|
| Comanda | `COMANDA #258` |
| Precuenta | `PRECUENTA #258` |
| Cobro | `PAGO #258` |
| Reimpresión corrección | `COMANDA #258-2` + `Correccion #2` |

Base: `order_number` de la comanda.

---

## P2.3 — Fecha/hora creación e impresión

Todos los documentos internos incluyen:

- `Creada: HH:mm` (apertura comanda)
- `Impresa: HH:mm` (momento del job o `sent_to_bar_at` en comanda)

---

## P2.4 — Estado visual

| Tipo | Estado en ticket |
|------|------------------|
| `ORDER_COMMAND` | `EN BARRA` |
| `PRECHECK` | `PENDIENTE DE COBRO` |
| `SALE_RECEIPT` | `PAGADO` + `Metodo: EFECTIVO/QR/TARJETA/MIXTO` |

---

## P2.5 — PRECHECK elegante

`PrintTicketContentBuilder::buildPrecheck`:

- Mesa / Pieza / Habitación / Barra tipada
- Garzón, productos, cantidades, modalidad, manilla, combo
- Total centrado grande
- `Gracias por su preferencia.`
- `No tiene validez fiscal.`
- Sin QR, NIT ni impuestos

---

## P2.6 — SALE_RECEIPT claro

`PrintTicketContentBuilder::buildSaleReceipt`:

- `PAGO #n`, `PAGADO`, método principal
- Ubicación, cajera, hora de cobro
- Total grande
- Pago mixto: desglose Efectivo / QR / Tarjeta

Auto al cobrar (P1): `CreateSaleReceiptPrintJobUseCase` en `ChargeOrderUseCase` / `CreateDirectSaleUseCase`.

---

## P2.7 — Comanda barra limpia

`PrintTicketContentBuilder::buildOrderCommand`:

- Sin precios, sin total, sin impuestos
- Número y ubicación destacados
- Garzón, hora, productos, observaciones, chicas resumidas
- `EN BARRA`

---

## P2.9 — Cajera puede imprimir precuenta

`PrintOrderPrecheckUseCase`: permite `sales.charge` además de acceso garzón.

Ruta: `POST /api/v1/orders/{id}/precheck/print` → job `PRECHECK` (no cobra).

---

## P2.10 — Tests

Archivo: `tests/Feature/Api/V1/PrintingP2OperationalFormatsTest.php`

| # | Test | Estado |
|---|------|--------|
| 1 | Corrección tras `SENT_TO_BAR` crea reimpresión | ✅ |
| 2 | Corrección en `OPEN` no imprime | ✅ |
| 3 | Reimpresión contiene `REIMPRESION` | ✅ |
| 4 | Reimpresión contiene `Correccion #N` | ✅ |
| 5 | PRECHECK sin datos fiscales | ✅ |
| 6 | PRECHECK total legible | ✅ |
| 7 | SALE_RECEIPT al cobrar | ✅ |
| 8 | SALE_RECEIPT método de pago | ✅ |
| 9 | Pago mixto con desglose | ✅ |
| 10 | ORDER_COMMAND sin total ni precios | ✅ |
| 11 | Cajera puede imprimir precuenta | ✅ |

```bash
php artisan test tests/Feature/Api/V1/PrintingP2OperationalFormatsTest.php
```

---

## Migraciones P2

```bash
php artisan migrate
```

- `2026_06_21_100090_add_bar_correction_count_to_orders.php`

---

## P2.11 — QA manual (checklist)

1. Garzón envía comanda → imprime comanda barra
2. Cajera corrige después de enviada → imprime REIMPRESIÓN
3. Garzón imprime precuenta
4. Cajera imprime precuenta (cola o detalle)
5. Cobro efectivo / QR / mixto → ticket PAGADO con método y desglose si aplica
6. Fallback navegador alineado con agente (ver reporte frontend)

---

## Pendiente P3

- Plantillas configurables por sucursal
- Footer Ribersoft configurable
- Unificación formal PHP ↔ Vue vía schema compartido (opcional)
