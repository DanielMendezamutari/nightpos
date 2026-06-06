# V1-97 — TICKETS PDF / VISTA IMPRIMIBLE

**Fecha:** 2026-06-06  
**Estado:** ✅ Completado  
**Cambio de alcance:** V1-97 ya **no** es "impresión automática con agente local". Para V1 se entrega **vista imprimible + `window.print()`** (impresora térmica vía navegador).  
**Impresión automática / agente local / cola de impresoras / térmica directa:** diferido a **V2**.

---

## 1. Objetivo

Generar documentos visuales imprimibles (térmica 58/80 mm) para:

1. Comanda enviada a barra
2. Ticket de venta / cobro (comanda y venta directa)
3. Cierre de caja (Mi Caja y fiscalización)
4. Cierre de turno

El usuario puede: ver el documento, **imprimir** desde el navegador (incl. "Guardar como PDF"), o **volver**.

**CSV ≠ PDF/imprimible.** CSV sigue siendo exportación de reportes; estos tickets son documentos operativos para entregar/imprimir.

---

## 2. Rutas imprimibles (layout `blank`)

| Ruta | Nombre | Documento | Permiso |
|------|--------|-----------|---------|
| `/nightpos/print/order/:id` | `nightpos-print-order-id` | Comanda barra | `orders.access` |
| `/nightpos/print/sale/:id` | `nightpos-print-sale-id` | Ticket venta | `sales.list` |
| `/nightpos/print/cash` | `nightpos-print-cash` | Cierre Mi Caja (sesión actual) | `cash.access` |
| `/nightpos/print/cash-session/:id` | `nightpos-print-cash-session-id` | Cierre caja (fiscalización) | `admin.cash_sessions.view` |
| `/nightpos/print/shift/:id` | `nightpos-print-shift-id` | Cierre turno | `shifts.list` |

Layout limpio: sin sidebar, sin navbar, fondo blanco. Botones **Imprimir** y **Volver** (clase `.no-print`, ocultos al imprimir). Auto-print si la URL trae `?print=1` (lo añade `openPrintRoute`).

Permisos: **reutilizados**, no se crearon nuevos. `sales/{id}` está protegido por `sales.list` en backend, por eso el ticket de venta usa ese permiso.

---

## 3. Componentes

`src/components/nightpos/print/`

| Componente | Uso |
|-----------|-----|
| `PrintableTicketShell.vue` | Base común: ancho térmico (58/80 mm) o A4, encabezado, footer, botones Imprimir/Volver, CSS `@media print` |
| `PrintableOrderTicket.vue` | Comanda: número, mesa/ambiente, garzón, fecha/hora, productos (cant, modalidad, notas, chica), notas, total |
| `PrintableSaleTicket.vue` | Venta: número, tipo (comanda/venta directa), fecha, cajera, garzón, productos, **pagos mixtos**, total |
| `PrintableCashSessionReport.vue` | Caja: apertura/cierre, monto inicial, ventas efectivo/QR/tarjeta, ingresos, egresos, esperado, contado, diferencia |
| `PrintableShiftClosureReport.vue` | Turno: ventas, caja, liquidaciones, servicios, habitaciones, diferencias, resumen |

Ancho por defecto **80 mm** (térmica). El shell soporta `58mm` y `a4` si se requiere.

---

## 4. Botones "Ver imprimible"

| Pantalla | Botón | Destino |
|----------|-------|---------|
| Detalle comanda (`orders/[id]`) | "Imprimir barra" | print/order/:id |
| Detalle venta (`SaleDetailDialog`) | "Ver ticket" | print/sale/:id |
| Mi Caja (`cash/index`) | "Imprimir arqueo" | print/cash |
| Fiscalización caja (`finance/cash-sessions/[id]`) | "Ver cierre imprimible" | print/cash-session/:id |
| Cierre de turno (`shifts/close`) | (existente) | print/shift/:id |
| Historial turnos (`shifts/history`) | "Ver cierre" | print/shift/:id |

---

## 5. Backend

**Sin `print_jobs`, sin agente, sin nuevos endpoints de impresión.** Se reutilizan:

- `GET /orders/{id}`
- `GET /sales/{id}`
- `GET /shifts/{id}/summary`
- `GET /admin/cash-sessions/{id}`
- `GET /cash/session/current`

**Único cambio backend (info faltante para el ticket):** se añadieron nombres legibles a las respuestas de detalle:

- `GetSaleUseCase` → `cashier_name`, `waiter_name`
- `GetOrderUseCase` → `waiter_name`

No se modificaron mappers compartidos (evita romper otras respuestas). Resolución por `UserModel` solo en el detalle.

---

## 6. Tests

- **Backend:** `php artisan test` → **376 passing, 2539 assertions** (los cambios en GetSale/GetOrder no rompen nada).
- **Frontend/manual:** ver checklist §7.

No se añadieron tests backend nuevos (endpoints reutilizados).

---

## 7. Validación manual (checklist)

1. Crear comanda → enviar a barra → "Imprimir barra" → imprimir desde navegador.
2. Cobrar comanda → Ventas → abrir detalle → "Ver ticket" → imprimir.
3. Venta directa → Ventas → abrir detalle → "Ver ticket" (debe decir "Venta directa", mostrar pagos mixtos si aplica).
4. Cerrar caja / Mi Caja → "Imprimir arqueo".
5. Fiscalización de cajas → detalle → "Ver cierre imprimible".
6. Cerrar turno / Historial turnos → "Ver cierre".

Verificar en cada uno: layout limpio (sin nav), botones Imprimir/Volver visibles en pantalla y ocultos al imprimir, ancho térmico, datos correctos.

---

## 8. Diferido a V2

- Impresión automática (sin diálogo del navegador)
- Agente local para impresoras térmicas
- Cola de impresión (`print_jobs`) y reintentos
- WebSocket de impresión
- Facturación electrónica

**La operación NO se bloquea si no se imprime.**

---

*V1-97 entregado como vista imprimible. Detenerse aquí.*
