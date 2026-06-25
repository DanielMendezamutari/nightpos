# PRINTING_SYSTEM_UX_REDESIGN_REPORT.md (Backend)

**Tipo:** Auditoría + plan de rediseño UX  
**Fecha:** 2026-06-21  
**Estado:** P1 ✅ (2026-06-21) · P2 ✅ formatos operativos (2026-06-21) · P3 pendiente (plantillas + Ribersoft)

---

## 0. Principio rector

**No romper** CBA, liquidaciones, caja, mesas, kardex, agente Go, pipeline `print_jobs`, contratos API del agente, ni los tipos existentes `ORDER_COMMAND`, `PRECHECK`, `SALE_RECEIPT`.

Todo el rediseño debe **extender** la arquitectura actual, no crear un pipeline paralelo.

---

## 1. Estado real del sistema (2026-06-21)

> **Nota:** `LOCAL_PRINTING_AGENT_AUDIT.md` y secciones de `NIGHTPOS_V1_DEVELOPMENT_MAP.md` dicen “NO IMPLEMENTADO / sin print_jobs”. Eso quedó **obsoleto**. El agente y la cola **sí existen** desde migraciones `2026_06_17_*`.

### 1.1 Pipeline agente (producción térmica)

```
Acción operativa → Use case → print_jobs (PENDING, content_text)
     → Agente Go poll/claim → ESC/POS RAW → printed/failed
```

| Componente | Ubicación |
|------------|-----------|
| Tablas | `print_devices`, `print_jobs` — `2026_06_17_100000_*` |
| Tipos | `PrintJobType`: `ORDER_COMMAND`, `PRECHECK`, `SALE_RECEIPT`, `CASH_CLOSE`, `SETTLEMENT`, `ROOM_SERVICE` |
| Builder | `PrintTicketContentBuilder.php` — único motor de texto agente |
| Agente | `agent/` — Go, WinSpool, polling, `device_key` |
| API agente | `GET /print-jobs/pending`, `POST claim/printed/failed` |
| SSE | `print_job.created`, `print_job.printed`, `print_job.failed` |

### 1.2 Pipeline navegador (fallback V1-97)

Vue blank routes + `window.print()` — **independiente** de `print_jobs`.

| Ruta | Componente |
|------|------------|
| `print/order/:id` | `PrintableOrderTicket.vue` |
| `print/precheck/order/:id` | `PrintablePrecheckTicket.vue` |
| `print/sale/:id` | `PrintableSaleTicket.vue` |

**Riesgo del rediseño:** hoy hay **dos fuentes de verdad** (PHP builder vs Vue). El plan debe unificar criterios de layout, no duplicar reglas.

### 1.3 Configuración sucursal hoy

| Setting | Dónde | Usado |
|---------|-------|-------|
| `branches.auto_print_order_command` | migration `100001` | ✅ Gate `ORDER_COMMAND` |
| `print_devices.*` | registro agente | ✅ Activo/dispositivo |
| `paper_width_mm` en device | DB | ❌ No pasa al builder (siempre 80) |
| `auto_print_order` en device | DB | ❌ No wired |
| Plantillas configurables | — | ❌ No existe |
| Firma Ribersoft | — | ❌ No existe |
| `auto_print_sale_receipt` | migration `100080` | ✅ Gate `SALE_RECEIPT` al cobrar |

---

## 2. Hooks actuales (cuándo se crea un job)

| Evento | Job | Use case |
|--------|-----|----------|
| Crear comanda | ❌ | — |
| Agregar ítems | ❌ | — |
| **Enviar a barra** | ✅ `ORDER_COMMAND` | `SendOrderToBarUseCase` → `CreateOrderCommandPrintJobUseCase` |
| **Precuenta manual** | ✅ `PRECHECK` | `PrintOrderPrecheckUseCase` → `CreatePrecheckPrintJobUseCase` |
| **Reimpresión manual** | ✅ `ORDER_COMMAND` (nuevo idempotency) | `ReprintOrderCommandUseCase` |
| Cobrar comanda | ✅ `SALE_RECEIPT` | `ChargeOrderUseCase` → `CreateSaleReceiptPrintJobUseCase` (P1) |
| Venta directa | ✅ `SALE_RECEIPT` | `CreateDirectSaleUseCase` (P1) |
| **Corrección comanda (SENT_TO_BAR)** | ✅ `ORDER_COMMAND` reimpresión | `DispatchBarCorrectionPrintJobUseCase` (P2) |
| Pieza / habitación auto-comanda | ✅ | `CreateRoomServiceUseCase` → comanda `ROOM_SERVICE` (P1) |

**Gates comunes antes de crear job:**

1. Dispositivo activo en sucursal (`hasActiveDevice`)
2. Para `ORDER_COMMAND` no-forzado: `auto_print_order_command = true`
3. Idempotencia en primer envío: `order_command:{order_id}:v1`

---

## 3. Comparativa spec vs código (13 puntos)

| # | Requisito UX | Estado | Gap |
|---|--------------|--------|-----|
| 1 | Comanda barra **solo** al enviar a barra, sin precios | Parcial | Hook ✅; builder **incluye TOTAL** y precios implícitos; Vue muestra columna Total |
| 2 | Botón precuenta elegante | ✅ | `POST /orders/{id}/precheck/print` → `PRECHECK`; falta enriquecer layout |
| 3 | `SALE_RECEIPT` auto al cobrar | ❌ | Enum existe; stub en builder; sin use case ni hook en `ChargeOrderUseCase` |
| 4 | Factura fiscal separada | N/A V1 | No hay facturación electrónica; precuenta ya dice “no fiscal” |
| 5 | Reimpresión por **corrección** con banner | ❌ | Solo reprint manual; sin “REIMPRESIÓN / Corrección #N” |
| 6 | Identificador grande mesa + # | ❌ | `order_number` pequeño en fila texto; sin tipografía destacada |
| 7 | Chicas legibles “María x4” | Parcial | Combos ✅ en builder; acompañante simple usa “Chica:”; incluye “Manillas: 2/2” técnico |
| 8 | Pieza → “PIEZA N”, no Mesa | ❌ | Siempre `Mesa/Amb` / `Mesa`; `service_areas.area_type` (TABLE/BAR/ROOM/VIP) **no usado** en tickets |
| 9 | Observaciones visibles | Parcial | `ORDER_COMMAND`: ítem + orden ✅; `PRECHECK`: **sin notas** |
| 10 | Plantillas configurables por sucursal | ❌ | Textos hardcoded en `PrintTicketContentBuilder` |
| 11 | Firma Ribersoft discreta configurable | ❌ | Footer fijo “NightPOS — comanda barra” en Vue; sin config |
| 12 | Flujo operativo sin pasos extra | Parcial | Barra + precuenta OK; **cobro sin ticket auto** rompe flujo |
| 13 | Agente desconectado no bloquea cobro | ✅ | Jobs fallan/reintentan; cobro no depende de print |

---

## 4. Contenido actual vs spec — comanda barra

### Builder agente (`buildOrderCommand`)

Incluye hoy:

- Header `NIGHTPOS` / `COMANDA BAR`
- Comanda, Mesa/Amb, Garzón, Fecha
- Productos con modo venta, manillas técnicas, chicas en combos
- **TOTAL + moneda** ← **debe eliminarse para barra limpia**
- Notas ítem y orden ✅

### Spec deseado

- Solo producción: productos, cantidades, chicas, observaciones
- Ubicación tipada: SALÓN VIP, Mesa 12, Barra, Habitación 8, Pieza 5
- Sin QR, impuestos, fiscal, precios

---

## 5. PRECHECK (precuenta)

### Implementado

- Job `PRECHECK` con disclaimer fiscal
- Totales por ítem implícitos (solo total final)
- Branch name en header

### Gaps vs spec

- Sin “Gracias por su visita” configurable
- Sin mensaje final elegante
- Sin notas de ítem/orden
- Layout no enfatiza mesa/garzón como spec ejemplo

---

## 6. SALE_RECEIPT (ticket de cobro) — mayor gap operativo

**No implementado.**

`buildForType()` default:

```php
default => "NightPOS — {$type->value}\n",
```

### Diseño recomendado (reutilizando pipeline)

1. Migración: `branches.auto_print_sale_receipt BOOLEAN DEFAULT true`
2. `CreateSaleReceiptPrintJobUseCase` — espejo de `CreateOrderCommandPrintJobUseCase`
3. `PrintTicketContentBuilder::buildSaleReceipt()` — payload desde venta post-cobro
4. Hook en `ChargeOrderUseCase` y `CreateDirectSaleUseCase` **después** de persistir venta
5. Idempotencia: `sale_receipt:{sale_id}:v1`
6. **Nunca** revertir cobro si falla impresión — solo log + SSE + toast frontend
7. Contenido: PAGADO, método (EFECTIVO/QR/TARJETA/MIXTO), mesa, total, hora, cajera

Datos ya disponibles en `ChargeOrderUseCase` / `SaleMapper` / pagos mixtos.

---

## 7. Reimpresión y correcciones

| Actual | Spec |
|--------|------|
| `POST /orders/{id}/reprint` manual | Auto al corregir si `status >= SENT_TO_BAR` |
| Mismo template que comanda original | Banner `REIMPRESIÓN`, `Corrección #N`, hora |
| Sin metadata de corrección | Contador en audit o payload job |

**Hooks candidatos (sin tocar lógica de corrección):**

- `UpdateOrderItemUseCase`
- `CancelOrderItemUseCase`
- `UpdateOrderItemProductUseCase` (si existe)

Patrón: si orden `SENT_TO_BAR` o `BILLED` según regla → `CreateOrderCommandPrintJobUseCase` con `force: true` + flag `is_reprint` / `correction_number` en payload.

---

## 8. Etiquetas de ubicación (mesa / barra / pieza / habitación)

**Datos existentes:**

- `orders.table_label`
- `orders.service_area_id` → `service_areas.area_type` (`TABLE`, `VIP`, `BAR`, `ROOM`, `OTHER`)
- `service_areas.name` (ej. “Salón VIP”, “Barra”)

**Propuesta:** `OrderLocationLabelResolver` (nuevo servicio pequeño)

| `area_type` | Etiqueta ticket |
|-------------|-----------------|
| `TABLE`, `VIP` | `Mesa {table_label}` |
| `BAR` | `Barra` (+ label opcional) |
| `ROOM` | `Habitación {n}` |
| Pieza (orden desde room service) | `Pieza {n}` — requiere detectar origen |

Usar en builder + enriquecer `OrderPresentationService` para incluir `location_display` en payload.

---

## 9. Sistema de plantillas (sin tocar código por sucursal)

### Estado

Cero configuración. Todo en PHP strings.

### Diseño recomendado V1

Tabla o JSON en sucursal:

```text
branch_print_settings
  - paper_width_mm (58|80)
  - template_order_command (json flags)
  - template_precheck (json)
  - template_sale_receipt (json)
  - show_logo, show_branch_address, show_waiter, show_girls, ...
  - footer_marketing_enabled
  - footer_marketing_lines (max 3)
  - footer_developer_name, footer_whatsapp, footer_tagline
```

**Refactor mínimo:**

- `PrintTemplateRenderer` lee config + delega a secciones
- `PrintTicketContentBuilder` se convierte en layout engine o wrapper
- Admin API extiende `GET/PATCH /print-settings` (ya existe shell)

**Compatibilidad:** defaults = comportamiento actual migrado, para no romper sucursales existentes.

---

## 10. Marketing Ribersoft

| Regla spec | Implementación sugerida |
|------------|-------------------------|
| Máx. 3 líneas | Validación en PATCH settings |
| Solo al final, separador | Método `appendMarketingFooter($lines, $config)` |
| No en documentos fiscales | Excluir tipo `INVOICE` futuro; en V1 solo en operativos |
| Configurable ON/OFF | `footer_marketing_enabled` |
| Campos: empresa, WhatsApp, tagline opcional | Seed default Ribersoft / 67369293 |

**No reemplazar** disclaimer fiscal de precuenta.

---

## 11. Qué reutilizar (no reescribir)

| Reutilizar tal cual | Extender |
|---------------------|----------|
| `print_jobs` + estados + claim | Contenido `content_text` |
| Agente Go + WinSpool | Nada en agente (sigue imprimiendo texto) |
| `CreateOrderCommandPrintJobUseCase` | Variante reprint/correction metadata |
| `CreatePrecheckPrintJobUseCase` | Layout + notas |
| `OrderPresentationService` | Location + girl aggregation |
| `BranchPrintSettingsReader` | Más flags |
| Permisos `settings.printers`, `printing.reprint` | Opcional `printing.precheck` |
| Tests `LocalPrintAgentTest`, `WaiterPrecheckPrintTest` | Ampliar casos |

| No tocar | Motivo |
|----------|--------|
| Contrato API agente | Compatibilidad EXE instalados |
| `ChargeOrderUseCase` lógica de cobro | Solo **añadir** hook post-success |
| Settlement / cash session close | Fuera de alcance |
| Kardex / CBA | Explícito en spec |

---

## 12. Plan de implementación por fases (propuesto)

### Fase A — Layout barra limpio (sin DB nueva)

- Quitar TOTAL/precios de `buildOrderCommand`
- Quitar columnas precio en agente; alinear Vue fallback
- `OrderLocationLabelResolver`
- Identificadores grandes (líneas dedicadas `#258`, `Mesa 12`)
- Chicas: solo `Nombre xN`, ocultar “Manillas: x/y” en barra

### Fase B — SALE_RECEIPT auto cobro

- Migración `auto_print_sale_receipt`
- `CreateSaleReceiptPrintJobUseCase` + builder
- Hook charge + direct sale
- Frontend: toast si falla, no bloquea

### Fase C — Reimpresión por corrección

- Hooks en use cases de corrección
- Template `REIMPRESIÓN` + correction number
- Tests regresión corrección + print

### Fase D — Plantillas + Ribersoft

- Migración settings JSON
- UI admin impresoras
- Footer marketing configurable
- Unificar criterios Vue ↔ PHP (mismos flags)

### Fase E — Piezas / habitaciones / factura

- Etiquetas pieza en órdenes auto-generadas
- `ROOM_SERVICE` print si aplica operación
- Factura fiscal cuando exista módulo FE (V2)

---

## 13. Tests backend (P2 completado)

Suite: `tests/Feature/Api/V1/PrintingP2OperationalFormatsTest.php` — 11 tests ✅

Ver detalle en `PRINTING_P2_OPERATIONAL_FORMATS_REPORT.md`.

---

## 14. Documentación relacionada

| Doc | Nota |
|-----|------|
| `LOCAL_PRINTING_AGENT_AUDIT.md` | Diseño original — **actualizar banner “implementado”** |
| `PRINTING_P2_OPERATIONAL_FORMATS_REPORT.md` | ✅ P2 formatos + reimpresión corrección |
| `ROOM_SERVICE_SHOW_PRINT_FIX_REPORT.md` | ✅ Pieza/show auto print |
| `CASH_MOVEMENT_AND_CLOSURE_PRINT_AUDIT.md` | ✅ Auditoría caja/cierres |
| `CASH_MOVEMENT_AND_CLOSURE_PRINT_FIX_REPORT.md` | ✅ Movimientos + cierres print |
| `OPERATIONAL_CONSOLIDATION_P1_REPORT.md` | ✅ P1 cobro, cola, pieza |
| `PRINTABLE_TICKETS_V1_REPORT.md` | Browser path |
| `agent/README.md` | Agente Go |

---

## 15. Conclusión

NightPOS **ya tiene** infraestructura de impresión profesional (cola, agente, idempotencia, precuenta, comanda al enviar barra). El rediseño UX **no requiere** nuevo pipeline.

**Prioridades:**

1. ~~Comanda barra limpia~~ ✅ P2
2. ~~`SALE_RECEIPT` automático al cobrar~~ ✅ P1
3. ~~Reimpresión automática en correcciones~~ ✅ P2
4. ~~Pieza/show ticket operativo~~ ✅ 2026-06-21
5. ~~Movimientos de caja + cierres (CASH_MOVEMENT, CASH_CLOSE, SHIFT_CLOSE)~~ ✅ 2026-06-21
6. Servicio Windows agente + admin test-print + debug ORDER_COMMAND — ✅ 2026-06-25
7. Plantillas configurables + Ribersoft — P3
8. ~~Unificar agente ↔ browser~~ ✅ P2 (criterios alineados)

Todo extensible sobre `PrintTicketContentBuilder` y los use cases `Create*PrintJobUseCase` existentes.
