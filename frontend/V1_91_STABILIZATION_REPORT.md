# V1_91_STABILIZATION_REPORT.md — Frontend
# Reporte de Estabilización Pre-SSE — NightPOS V1-91.1

**Fecha:** 2026-06-06  
**Fase:** V1-91.1 — Estabilización Pre-SSE + Pago Limpieza en Caja  
**Estado:** COMPLETADA

---

## Resumen Ejecutivo

Esta fase implementó cambios de UI para reflejar el pago de liquidaciones de limpieza en la caja, corrigió un bug de Temporal Dead Zone en ventas directas, y ocultó estados de comanda no usados en V1 de los KPIs.

---

## Cambios Implementados

### Liquidaciones — Botón Pagar en listas

Se agregó un botón inline "Pagar" en las páginas de lista de liquidaciones, con confirmación que muestra el egreso que se registrará en caja.

| Archivo | Cambio |
|---------|--------|
| `src/pages/nightpos/settlements/cleaning.vue` | Botón "Pagar", dialog confirmación, reload tras pago |
| `src/pages/nightpos/settlements/waiters.vue` | Botón "Pagar", dialog confirmación, reload tras pago |
| `src/pages/nightpos/settlements/girls.vue` | Botón "Pagar", dialog confirmación, reload tras pago |

**Flujo de pago desde lista:**
1. Cajera/admin ve liquidaciones `PENDING` con botón "Pagar".
2. Hace clic → se abre dialog de confirmación con monto.
3. Al confirmar → llama a `markSettlementPaid(id)`.
4. Si OK → la lista se recarga automáticamente.
5. Si no hay caja → muestra error del backend (422).

### Mi Caja — Alerta de liquidaciones pendientes al cerrar

Archivo: `src/pages/nightpos/cash/index.vue`

Antes de mostrar el dialog de cierre de caja, se consultan las liquidaciones pendientes del turno. Si hay pendientes (de cualquier tipo), se muestra:

> "Tienes pagos al personal pendientes: X Bs. Si los pagas antes de cerrar, se descontarán de tu caja."

Con botón "Ir a Liquidaciones".

### Shift Console — KPI "Pendientes cobro" condicional

Archivo: `src/pages/nightpos/shift-console/index.vue`

El KPI "Pendientes cobro" del garzón ahora solo aparece si `pending_charge_orders > 0`. En V1 este valor siempre es 0 (no se usan `IN_PREPARATION`/`READY`), por lo que la tarjeta queda oculta. Se activará automáticamente en V2 cuando el módulo Barra esté operativo.

### ChargeOrderModal — Aviso para comandas OPEN

Archivo: `src/components/nightpos/orders/ChargeOrderModal.vue`

Al cobrar una comanda con estado `OPEN` (no enviada a barra), se muestra alerta:

> "Esta comanda aún no fue enviada a barra. Puede cobrarla de todas formas."

No bloquea el cobro. La comanda pasa directamente a `BILLED`.

### Bug Fix — Ventas Directas (TDZ)

Archivo: `src/pages/nightpos/cash/direct-sale.vue`

Corregido un error de Temporal Dead Zone (`ReferenceError: Cannot access 'loadCash' before initialization`). La función `loadCash` fue reubicada antes de su uso en `onMounted`.

---

## Documentación Creada

| Documento | Descripción |
|-----------|-------------|
| `BAR_MODULE_V1_DECISION.md` | Decisión: no hay módulo Barra en V1 |
| `ORDER_CHARGE_RULES_V1.md` | Reglas de cobro de comandas en V1 |
| `POS_CAT_VALIDATION_REPORT.md` | Validación POS-CAT para 20/100/200 productos |

---

## V1-91.3 — Bugfix: Pieza Aparece Como "Tiempo Cumplido" al Crear

**Fecha:** 2026-06-06

- `create.vue`: helper `localDatetimeString()` → pre-pobla `started_at` con hora local actual.
- `create.vue`: computed `estimatedEndTime` → muestra "Termina aprox: HH:mm" en el hint.
- `create.vue`: campo `Hora de inicio` marcado como obligatorio, sin valor por defecto vacío.
- Backend ahora en `America/La_Paz`: el frontend envía hora local, backend interpreta correctamente.

---

## UX Checklist Post-Estabilización

- [x] Cajera puede ver y pagar liquidaciones de limpieza desde lista.
- [x] Al pagar liquidación → caja refleja egreso automáticamente.
- [x] Cierre de caja advierte si hay liquidaciones pendientes.
- [x] Cierre de turno muestra total pendiente por tipo de personal.
- [x] Cobrar OPEN muestra aviso sin bloquear.
- [x] KPI "Pendientes cobro" oculto en V1 (no hay órdenes `IN_PREPARATION`/`READY`).
- [x] Ventas directas funcionales sin crash.
- [x] Pieza recién creada muestra tiempo restante, no "Tiempo cumplido".
- [x] Formulario pre-pobla hora actual y muestra fin estimado en tiempo real.

---

## Estado Pre-SSE

El frontend está listo para iniciar V1-92 SSE-1.

**Siguiente fase:** V1-92 SSE-1 BASE — implementar conexión EventSource y primer evento de broadcast.
