# SETTLEMENT PAYMENT AUDITABLE — Implementation Report (Frontend)

**Fecha:** 2026-06-21  
**Estado:** ✅ Implementado  
**Build:** `npm run build` — OK

---

## Resumen

Frontend cierra el ciclo: descuento manual en PENDING, pago con preview completo, comprobante navegador, reimpresión y historial enriquecido.

---

## API (`settlements.js`)

| Función | Endpoint |
|---------|----------|
| `applySettlementManualDiscount` | POST manual-discount |
| `previewSettlementManualDiscount` | POST manual-discount/preview |
| `cancelSettlementManualDiscount` | DELETE manual-discount |
| `printSettlement` | POST print `{ reprint: true }` |

---

## Componentes nuevos

| Componente | Rol |
|------------|-----|
| `SettlementManualDiscountDialog.vue` | PERCENT/AMOUNT, motivo, preview bruto/base/descuento/neto |
| `PrintableSettlementTicket.vue` | Ticket 80mm estilo térmico |
| `pages/print/settlement/[id].vue` | Ruta `/nightpos/print/settlement/:id` |

---

## Detalle liquidación (`[id].vue`)

**PENDING:** Banda de acciones visible (Agregar multa + Marcar pagado), Agregar descuento, pago con preview  
**PAID:** Ver comprobante, Reimprimir ticket  

Muestra: bruto/neto, ticket #, print count, método, caja, movimiento caja, resumen pagado.

Post-pago: alerta con ticket # + botón ver comprobante.

### UX multas en listas (2026-06-25)

- Filas en Chicas / Garzones / Limpieza: botón **Multar** (`color="warning"`, `variant="flat"`) junto a **Pagar**
- Hub liquidaciones: tarjetas rápidas + **Registrar multa**
- Modal pre-llena persona y rol desde la fila; mensaje «Multa registrada para {nombre}.»
- Permiso UI: `settlements.fines.manage`

---

## Modal pago (`SettlementPayDialog.vue`)

Sin cambios estructurales — pay-preview ya incluye descuento manual vía adjustments backend. Botón **Pagar {neto} Bs**. Botón **Registrar multa** si `settlements.fines.manage`.

---

## Ticket garzón (`PrintableSettlementTicket.vue`)

Solo si `settlement_type === 'WAITER'`:

| Campo | Fuente API |
|-------|------------|
| Venta total | `waiter_sales_total` |
| Porcentaje | `commission_percent` |
| Comisión | `commission_amount` |

Detalle liquidación garzón (`settlements/[id].vue`): tarjeta **Venta garzón** con los mismos campos.

---

## Historial (`history.vue`)

Columnas: bruto, neto, método, ticket #  
Chips: Con ajustes, Con ticket  
Acción Reimprimir si PAID

---

## Composable (`useSettlementPayment.js`)

- Notifica ticket # al pagar
- `openReceipt(id)` → ruta print
- `reprintReceipt(id)` → POST print + fallback navegador

---

## Constants

`DISCOUNT_MODE_LABELS` — PERCENT / AMOUNT

---

## Permisos UI

| Acción | Permiso |
|--------|---------|
| Descuento / multa | `settlements.fines.manage` |
| Pagar / reimprimir | `settlements.pay` |
| Ver comprobante | `settlements.access` |
