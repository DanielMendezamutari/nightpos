# Consolidación impresión P2 — Frontend (2026-06-21)

Alineación del fallback navegador con formatos del agente y botón de precuenta para cajera.

## Arquitectura (sin cambios)

- `useNightPosPrint` — rutas blank + `window.print()`
- `Printable*.vue` — diseño browser
- API agente vía `printOrderPrecheck`, `reprintOrderCommand`, cobro con `print_job`

---

## P2.8 — Fallback browser alineado

Componentes actualizados para reflejar `PrintTicketContentBuilder`:

| Componente | Cambios |
|------------|---------|
| `PrintableOrderTicket.vue` | Sin precios/total; `COMANDA #n`; reimpresión; ubicación hero; Creada/Impresa; EN BARRA |
| `PrintablePrecheckTicket.vue` | PRECUENTA #n; PENDIENTE DE COBRO; total grande; mensajes legales; sin columnas precio |
| `PrintableSaleTicket.vue` | PAGO #n; PAGADO; método; ubicación; cajera; desglose mixto; total grande |

**Helper compartido:** `src/composables/usePrintTicketFormat.js`

- `resolvePrintLocationLabel()` — Mesa / Pieza / Habitación / Barra / VIP
- `formatPrintTime()` — HH:mm
- `paymentModeLabel()` — EFECTIVO / QR / TARJETA / MIXTO

Estilos reutilizados de `PrintableTicketShell.vue`: `.nightpos-print-row`, `.nightpos-print-hero`, `.nightpos-print-total`.

---

## P2.9 — Cajera imprime precuenta

| Pantalla | Acción |
|----------|--------|
| `orders/[id].vue` | Botón secundario «Imprimir precuenta» (permiso `sales.charge`) |
| `cashier/orders/index.vue` | Botón en tarjeta de cola «Imprimir precuenta» |

Flujo:

1. `POST /orders/{id}/precheck/print` → job `PRECHECK` al agente
2. Si falla → fallback `openPrintRoute('nightpos-print-precheck-order-id')`
3. **No cobra** — solo precuenta

Patrón idéntico al garzón en `waiter/orders/[id].vue`.

---

## Rutas print (sin cambios de ruta)

| Ruta | Componente |
|------|------------|
| `print/order/:id` | `PrintableOrderTicket.vue` |
| `print/precheck/order/:id` | `PrintablePrecheckTicket.vue` |
| `print/sale/:id` | `PrintableSaleTicket.vue` |

---

## P2.11 — QA manual frontend

1. Detalle comanda cajera → «Imprimir precuenta» encola job
2. Cola cobro → «Imprimir precuenta» en tarjeta
3. `print/order/:id` — sin precios, diseño barra
4. `print/precheck/order/:id` — total grande, sin NIT/QR
5. `print/sale/:id` — PAGADO + método + desglose mixto
6. Comparar ticket browser vs térmica agente — mismos bloques visuales

```bash
npm run build
```

---

## Pendiente P3

- Plantillas configurables en UI
- Footer Ribersoft en tickets
- Preview de plantilla en settings impresoras
