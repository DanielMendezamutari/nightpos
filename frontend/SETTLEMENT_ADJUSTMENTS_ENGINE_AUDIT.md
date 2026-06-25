# Auditoría y diseño — Motor de liquidaciones con ajustes (Frontend, V1 simplificado)

**Fecha:** 2026-06-21  
**Revisión Fase 2:** 2026-06-21 — multas independientes, selección al pagar  
**Alcance:** Frontend NightPOS — auditoría + diseño UX  
**Estado:** Fase 1 backend ✅ — Fase 2 **backend + frontend ✅** — Fase 3 descuento manual **✅** — Fase 4 pago auditable **✅**

**Backend:** `backend/SETTLEMENT_ADJUSTMENTS_ENGINE_AUDIT.md`

---

## Resumen ejecutivo

El frontend tiene hub de liquidaciones completo (generar, listar por rol, pagar, detalle, historial). **Faltan:** desglose bruto/neto, multas, descuento manual, ticket al pagar.

**V1 simplificado — responsabilidades UX:**

| Ajuste | UI | Fase |
|--------|-----|------|
| Limpieza | Bloque resumen (solo lectura) | 1 |
| Multas | CRUD independiente + checkboxes al pagar | 2 |
| Descuento manual | Modal en detalle | 3 — **no implementar aún** |
| Ticket | Solo lo pagado | 4 |

---

## Parte A — Estado actual (auditoría)

| Superficie | Archivo | Hoy |
|------------|---------|-----|
| Hub | `settlements/index.vue` | Solo `total_amount` |
| Detalle | `settlements/[id].vue` | Ítems ingreso; sin ajustes |
| Pago | `SettlementPayDialog.vue` | Monto bruto; sin print |
| Multas | — | No existe |
| Descuento | — | No existe |
| Print | — | Solo totales agregados en cierre caja |

API: `src/api/settlements.js` — generate, list, pay, detail. Sin `staff-fines` ni `manual-discount`.

---

## Parte B — Decisiones UX V1

| Decisión | Valor |
|----------|-------|
| Limpieza en resumen | Solo visible en liquidaciones **GIRL** cuando aplique |
| Descuento | Manual por cajera; **no** automático por origen |
| Base preview descuento % | Bruto − limpieza (mostrar explícito) |
| Pago | Botón usa **neto** |
| Ticket | Bruto, limpieza, descuento manual, multas, neto |
| Garzón manilla UI | **Fuera de V1** |

---

## Parte C — Componente central: `SettlementAdjustmentSummary.vue`

Bloque reutilizable en detalle, modal pago y ticket print.

```
┌─ Resumen de liquidación ─────────────────────┐
│ Bruto generado              300,00 BOB       │
│ Limpieza única              − 10,00 BOB      │  ← solo GIRL si ≥100
│ Descuento manual (5%)       − 14,50 BOB      │  ← si existe
│ Multas                      − 50,00 BOB      │
│ ─────────────────────────────────────────── │
│ NETO A PAGAR                225,50 BOB       │
└──────────────────────────────────────────────┘
```

Props (desde API — **no calcular en frontend**):

```js
{
  gross_amount,
  net_amount,
  adjustments: [
    { adjustment_type, amount, discount_mode, discount_value, calculation_base, notes }
  ]
}
```

Labels en `src/constants/settlements.js`:

```js
export const ADJUSTMENT_TYPE_LABELS = {
  CLEANING_DEDUCTION: 'Limpieza única',
  MANUAL_DISCOUNT: 'Descuento manual',
  MANUAL_FINE: 'Multa',
}
```

---

## Parte D — Descuento manual (Fase 3 — NO implementar aún)

Ver diseño en audit backend Parte F. El modal `SettlementManualDiscountDialog` y botón **Agregar descuento** quedan **fuera de Fase 2**.

---

## Parte E — Multas (Fase 2 revisada)

### E.1 Principio

Las multas **no dependen** de que exista una liquidación. Se registran cuando ocurre el incidente (ej. vaso roto). Al pagar, la cajera decide cuáles descontar.

### E.2 Registrar multa — sin settlement

**`StaffFineDialog.vue`** — accesible desde:

- Hub liquidaciones → sección "Multas del turno"
- Detalle liquidación → **[ Agregar multa]** (pre-fill persona)
- Acción independiente (persona + turno + motivo + monto)

No requiere settlement PENDING previo.

### E.3 API client — `src/api/staffFines.js`

```js
fetchStaffFines({ officialShiftId, staffUserId, status })
createStaffFine({ staffUserId, amount, reason, notes })
cancelStaffFine(id, { cancel_reason })
```

```js
// settlements.js — Fase 2
fetchSettlementPayPreview(settlementId, { appliedFineIds })
markSettlementPaid(settlementId, { payment_method, applied_fine_ids, notes })
```

### E.4 Modal de pago — bloque multas con checkboxes

Actualizar **`SettlementPayDialog.vue`**:

```
┌─ Resumen ──────────────────────────────────┐
│ Bruto generado              300,00 BOB     │
│ Limpieza única              − 10,00 BOB     │
├─ Multas pendientes ────────────────────────┤
│ ☑ Vaso roto                 − 30,00 BOB     │
│ ☑ Llegó tarde               − 20,00 BOB     │
│ ☐ Uniforme                    (no aplicar)  │
│ Total multas seleccionadas:   50,00 BOB     │
├────────────────────────────────────────────┤
│ NETO A PAGAR                240,00 BOB     │
└────────────────────────────────────────────┘

Método de pago: [ Efectivo ▼ ]
[ Pagar 240,00 BOB ]
```

**Comportamiento:**

- Al abrir modal: cargar `GET /settlements/{id}/pay-preview`
- Todas las multas PENDING vienen **marcadas por defecto** (☑)
- Cajera puede desmarcar → recalcular neto **en tiempo real** (re-preview o cálculo local con mismos números del API)
- Solo multas marcadas van en `applied_fine_ids` al confirmar
- Multas desmarcadas siguen PENDING

Componente sugerido: **`SettlementPayFinesSelector.vue`**

### E.5 Detalle liquidación — sin multas en generación

En `[id].vue`:

- Resumen Fase 1: bruto + limpieza + neto **sin multas**
- Sección aparte: "Multas pendientes de esta persona" (informativo, no afecta neto hasta pagar)
- Botón **[ Agregar multa ]** — no **[ Agregar descuento ]** hasta Fase 3

### E.6 Lista / hub

**`StaffFinesList.vue`** en index liquidaciones:

| Columna | Contenido |
|---------|-----------|
| Persona | Nombre + rol |
| Motivo | reason |
| Monto | amount |
| Estado | PENDING / APPLIED / CANCELLED |
| Acciones | Cancelar (solo PENDING) |

---

## Parte F — Modal de pago (integración Fase 2)

Actualizar `SettlementPayDialog.vue`:

**Antes de confirmar**, mostrar:

1. `SettlementAdjustmentSummary` — bruto + limpieza (Fase 1)
2. `SettlementPayFinesSelector` — multas con checkboxes (Fase 2)

| Elemento | Cambio |
|----------|--------|
| Monto principal | **Neto a pagar** (grande) — incluye multas seleccionadas |
| Multas | Checkboxes; recalcular al togglear |
| Botón Pagar | `Pagar {neto} BOB` |
| Payload | `applied_fine_ids: number[]` |

`useSettlementPayment.js` — llamar pay-preview al abrir modal; mark-paid con multas seleccionadas.

**Fase 4:** auto-print ticket post-pago.

---

## Parte G — Ticket de liquidación (Fase 4)

Contenido **solo de lo pagado**:

```
LIQUIDACIÓN PAGADA
Persona / Rol / Turno / Caja
Bruto
Limpieza
Multas aplicadas          ← solo las seleccionadas al pagar
  · Vaso roto    -30 Bs
  · Llegó tarde  -20 Bs
Neto pagado
Método / Pagado por / Fecha
```

**No** listar multas PENDING que la cajera dejó sin aplicar.

(Fase 3 agregará línea "Descuento manual" si aplica.)

---

## Parte H — Páginas a modificar

| Página | Fase 2 |
|--------|--------|
| `[id].vue` | Resumen bruto/limpieza; lista multas PENDING informativa; botón multa |
| `SettlementPayDialog.vue` | Preview + checkboxes multas + neto dinámico |
| `index.vue` | Card/lista multas del turno |
| `StaffFineDialog.vue` | **Nuevo** — crear/cancelar |
| `SettlementPayFinesSelector.vue` | **Nuevo** — checkboxes en modal pago |

Descuento manual y print route → Fases 3–4.

---

## Parte I — Constantes unificadas

`src/constants/settlements.js`:

```js
export const SETTLEMENT_TYPE_LABELS = {
  WAITER: 'Garzón',
  GIRL: 'Chica',
  CLEANING: 'Limpieza',
}

export const SOURCE_TYPE_LABELS = {
  WAITER_COMMISSION: 'Comisión',
  GIRL_CONSUMPTION: 'Consumo acompañante',
  GIRL_BRACELET: 'Manilla',
  GIRL_BRACELET_ALLOCATION: 'Manilla combo',
  GIRL_ROOM: 'Pieza',
  GIRL_SHOW: 'Show',
  CLEANING_BASE: 'Base turno',
  CLEANING_ROOM: 'Pieza limpiada',
}

export const DISCOUNT_MODE_LABELS = {
  PERCENT: 'Porcentaje',
  AMOUNT: 'Monto fijo',
}
```

---

## Parte J — Plan de fases V1 (frontend)

| Fase | Entrega | Estado |
|------|---------|--------|
| **1** | `SettlementAdjustmentSummary` + constants | Tras backend ✅ |
| **2** | `StaffFineDialog`, `StaffFinesList`, `SettlementPayFinesSelector`, pay-preview | **✅ Implementado** |
| **3** | `SettlementManualDiscountDialog` | ✅ Implementado |
| **4** | Pay dialog final + `print/settlement/:id` | Pendiente |
| **5** | Historial, reports | Pendiente |

---

## Parte K — Wireframe detalle (objetivo V1)

```
[ PENDIENTE | CORTE #2 ]

Información — Persona · Rol · Turno · Caja

Ingresos (bruto)
  [tabla ítems — como hoy]

Resumen
  [SettlementAdjustmentSummary]

[ Pagar ]  [ Agregar multa ]

--- Modal pago (Fase 2) ---
Resumen bruto/limpieza
☑ Multas pendientes (checkboxes)
Neto dinámico
[ Pagar X BOB ]
```

---

## Parte L — Fuera de V1 (documentado)

| Feature | Versión |
|---------|---------|
| ComboAllocation multi-rol / garzón manilla | V1.1 |
| Labels `income_origin` | V1.1 |
| Admin UI reglas descuento automático | V1.1 |
| Descuento % en perfil usuario | V1.1 |
| Editar ajustes en settlement PAID | V2 |

---

## Dependencias backend

1. `GET /settlements/:id` → gross/net/adjustments (limpieza)
2. `GET /settlements/:id/pay-preview` → multas PENDING + neto simulado
3. `POST mark-paid` con `applied_fine_ids`
4. Endpoints `/staff-fines`
5. Fase 3+: descuento manual
6. Fase 4+: print_job

**El frontend no calcula limpieza, descuentos ni multas** — solo muestra y envía acciones.

---

## Checklist entregables

| # | Pregunta | Respuesta V1 |
|---|----------|--------------|
| 1 | Cálculo hoy | Solo `total_amount` bruto |
| 2 | Limpieza única | Bloque resumen; solo chicas; API sync |
| 3 | Multas | `StaffFineDialog` + lista |
| 4 | Descuentos | **Manual Fase 3**; multas al pagar Fase 2 |
| 5 | Garzones manillan | No; fuera V1 |
| 6 | Modelo UI | Consumir API; preview desde backend |
| 7 | Cambios | Summary, descuento modal, multas, print, pay neto |
| 8 | Duplicados | Toasts + estados APPLIED + dedup visual |
| 9 | Ticket | `print/settlement/:id` |
| 10 | V1 scope | Limpieza + multas + descuento manual + ticket |
| 11 | V1.1 | Multi-rol, reglas automáticas |
| 12 | Fases | 5 fases alineadas backend |

---

**Próximo paso:** Fase 3 descuento manual o Fase 4 ticket settlement.
