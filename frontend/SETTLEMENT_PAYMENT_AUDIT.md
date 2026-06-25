# AUDITORÍA — FLUJO DE PAGO DE LIQUIDACIONES (Frontend)

**Fecha:** 2026-06-21  
**Alcance:** Análisis — **implementado 2026-06-21**  
**Backend ref:** `backend/SETTLEMENT_PAYMENT_AUDIT.md`  
**Implementación:** `frontend/SETTLEMENT_PAYMENT_AUDITABLE_IMPLEMENTATION_REPORT.md`

**Objetivo:** Definir la UX de pago **totalmente auditable** (comprobante, historial, reimpresión) antes de Fase 3 descuento manual y ticket definitivo.

---

## 1. Resumen ejecutivo

Fase 2 frontend entregó un **modal de pago maduro**: pay-preview, multas con checkbox, neto dinámico y mark-paid con `applied_fine_ids`.

**Implementado:** ticket navegador, reimpresión, descuento manual, detalle/historial auditable.

El pago termina en **snackbar + reload** — operativamente usable, **no receipt-complete**.

---

## 2. Inventario UI actual

### 2.1 Componentes de pago

| Archivo | Estado | Rol |
|---------|--------|-----|
| `SettlementPayDialog.vue` | ✅ Fase 2 | Preview + multas + método + confirmar |
| `SettlementAdjustmentSummary.vue` | ✅ | Bruto / ajustes / neto |
| `SettlementPayFinesSelector.vue` | ✅ | Checkboxes multas |
| `useSettlementPayment.js` | ✅ | Gate caja + mark-paid |
| `StaffFineDialog.vue` | ✅ Fase 2 | Crear multa |
| `StaffFinesList.vue` | ✅ | Listar / cancelar multas |
| `PrintableSettlementTicket.vue` | ❌ | — |
| `pages/print/settlement/[id].vue` | ❌ | — |

### 2.2 Páginas

| Página | Pago | Ticket | Auditoría |
|--------|------|--------|-----------|
| `settlements/index.vue` | — | — | Multas turno (admin) |
| `settlements/[id].vue` | ✅ | ❌ | Resumen parcial PAID |
| `settlements/waiters.vue` | ✅ | ❌ | Lista `total_amount` |
| `settlements/girls.vue` | ✅ | ❌ | Idem |
| `settlements/cleaning.vue` | ✅ | ❌ | Idem |
| `settlements/history.vue` | — | ❌ | Bruto/neto + chip ajustes |

---

## 3. Flujo actual vs flujo objetivo

### 3.1 Flujo actual

```
Lista / Detalle
  → Abrir caja (si cerrada)
  → SettlementPayDialog
       → GET pay-preview
       → Usuario elige multas + método
       → POST mark-paid
  → Snackbar éxito
  → Reload lista/detalle
  → FIN (sin comprobante)
```

### 3.2 Flujo objetivo (V1 perfecto)

```
Lista / Detalle
  → SettlementPayDialog (preview + confirm)
  → POST mark-paid
  → Response: settlement + cash_movement_id + print_job + ticket_number
  → Success panel:
       · "Liquidación pagada — Ticket 000234"
       · [ Imprimir comprobante ]  → agent print o browser print
       · [ Ver detalle ]
  → Auto-print opcional (config sucursal)
  → Detalle PAID:
       · Resumen pagado congelado
       · Método, pagador, hora, caja, ticket #
       · [ Reimprimir ] → POST print { reprint: true }
       · Link → movimiento caja
  → Historial:
       · Columnas audit completas
       · Acción Reimprimir / Ver ticket
```

---

## 4. Qué muestra hoy cada superficie

### 4.1 Modal de pago (`SettlementPayDialog`)

**Muestra:** persona, tipo, bruto, ajustes (preview), multas ☑, neto, método, notas  
**No muestra:** turno, caja, corte, ticket #, hora estimada  
**Post-confirm:** cierra modal — sin panel éxito ni print

### 4.2 Detalle `[id].vue`

**PENDING:** resumen bruto/limpieza/neto (sin multas en neto), multas pendientes, agregar multa  
**PAID:** resumen con multas aplicadas, paid_by, paid_at  
**Falta:** payment_method, notas de pago, ticket #, botones Imprimir/Reimprimir, link movimiento caja

### 4.3 Historial

| Campo | Visible |
|-------|---------|
| Bruto / Neto | ✅ |
| Chip "Con ajustes" | ✅ |
| Método pago | ❌ |
| Ticket # | ❌ |
| Reimprimir | ❌ |
| Desglose ajustes | ❌ |
| Export | ❌ |

### 4.4 Listas garzones/chicas/limpieza

- Columna **Total** = `total_amount` (pre-multas en preview; post-limpieza en chicas)
- **No** muestran neto a pagar ni chip ajustes
- Botón Pagar sin gate caja previo (falla al confirmar vs detalle que gatea antes)

---

## 5. Referencia: patrón movimiento de caja (reutilizar)

| Pieza | Archivo |
|-------|---------|
| Registro + print job | `CashMovementDialog.vue` |
| API print | `api/cash.js` → `printCashMovement` |
| Browser print | `pages/print/cash-movement/[id].vue` |
| Componente ticket | `PrintableCashMovementTicket.vue` |
| Utilidad | `useNightPosPrint.js` → `openPrintRoute`, `triggerAutoPrint` |

**Settlement debe copiar este patrón**, no inventar flujo paralelo.

---

## 6. Ticket UI objetivo

### 6.1 Pantalla print (`/nightpos/print/settlement/:id`)

Componente `PrintableSettlementTicket.vue`:

- Header LIQUIDACIÓN PAGADA
- Datos operativos (persona, rol, caja, turno, fecha, hora)
- Tabla ajustes (solo los **aplicados al pagar**)
- Neto pagado destacado
- Método + pagador + ticket # + corte
- Footer Ribersoft (usar `PRINT_TICKET_FOOTER` existente)
- Modo reimpresión: banner REIMPRESIÓN N°X + meta reimpresor

### 6.2 Datos API necesarios (backend)

Endpoint sugerido: `GET /settlements/{id}/payment-receipt` o enriquecer `GET /settlements/{id}` cuando PAID:

```json
{
  "settlement": { "...": "..." },
  "payment": {
    "payment_method": "CASH",
    "paid_at": "...",
    "paid_by_name": "...",
    "cash_session_id": 12,
    "cash_movement_id": 456,
    "ticket_number": "000234",
    "print_count": 1,
    "gross_amount": "300.00",
    "net_amount": "240.00",
    "adjustments": [...]
  }
}
```

---

## 7. Historial — diseño objetivo

### Columnas propuestas

| Columna | Contenido |
|---------|-----------|
| Fecha / Turno / Personal | existente |
| Corte | cut_label |
| Bruto | gross_amount |
| Neto pagado | net_amount |
| Estado | PENDING / PAID / CANCELLED chip |
| Ajustes | chip "Con ajustes" / "Sin ajustes" |
| Método | EFECTIVO / QR / TARJETA |
| Ticket | #000234 o "—" |
| Pagado por / Fecha pago | existente |
| Acciones | Detalle · **Reimprimir** (PAID) · Ver ticket |

### Filtros mejorados (V1.1)

- Persona por nombre (autocomplete)
- Con/sin ajustes
- Con ticket impreso

---

## 8. Permisos UX

| Acción | Permiso |
|--------|---------|
| Pagar | `settlements.pay` |
| Crear/cancelar multa | `settlements.fines.manage` |
| Imprimir al pagar | `settlements.pay` |
| Reimprimir | `printing.reprint` (patrón existente) |
| Ver historial | `settlements.history` |

---

## 9. Matriz gaps

### P0 — Receipt loop

| Gap | Acción |
|-----|--------|
| Sin ruta print settlement | Crear `print/settlement/[id].vue` |
| Sin componente ticket | Crear `PrintableSettlementTicket.vue` |
| Sin API print | `fetchSettlementReceipt`, `printSettlement(id, { reprint })` |
| Post-pay sin print | Extender `useSettlementPayment` + dialog success state |
| Sin reprint en detalle/historial | Botones + handler |

### P1 — Auditoría visible

| Gap | Acción |
|-----|--------|
| payment_method no visible PAID | Campo en detalle |
| Sin link movimiento caja | Link a fiscalización / movimiento |
| Listas muestran bruto no neto | Columna neto + chip |
| Notes pago no visibles | Mostrar en detalle PAID |
| cut_label no en detalle API | Backend mapper + UI |

### P2 — Polish (V1.1)

- Export historial CSV
- Timeline eventos (multa creada → aplicada → pagado)
- Hub KPI neto pendiente vs bruto
- Zero-net confirmation dialog

---

## 10. Qué reutilizar / no duplicar

### Reutilizar

- `SettlementPayDialog` + preview (mantener)
- `SettlementAdjustmentSummary` en detalle **y** ticket browser
- `useNightPosPrint` 
- `PrintableTicketShell` si existe wrapper común
- Patrón `CashMovementDialog` post-success

### No duplicar

- Cálculo neto en frontend (siempre API preview / settlement PAID)
- Lógica multas fuera de pay-preview

### Refactorizar (cuando implemente)

- `useSettlementPayment` → retornar `{ ok, data }` con print_job; helper `openSettlementReceipt(settlementId, { reprint })`
- Listas: alinear gate caja con `[id].vue`
- Unificar labels en `constants/settlements.js`

---

## 11. Orden implementación frontend (alineado backend)

| Orden | Entrega frontend | Backend requerido |
|-------|------------------|-------------------|
| 1 | API client receipt + print | Snapshot pago + endpoints |
| 2 | `PrintableSettlementTicket` + ruta print | Payload ticket |
| 3 | Post-pay success + auto-print | print_job en mark-paid |
| 4 | Reimprimir detalle + historial | Reprint endpoint |
| 5 | Detalle audit fields | payment_method, ticket #, movement id |
| 6 | Fase 3 descuento manual UI | MANUAL_DISCOUNT API |
| 7 | Listas neto + chips | — |

---

## 12. Wireframe detalle PAID (objetivo)

```
┌─ Detalle liquidación ──────────────────────────────┐
│ [ Reimprimir ticket ]  [ Ver movimiento caja ]    │
├─ Resumen pagado ───────────────────────────────────┤
│ Bruto 300 · Limpieza -10 · Multas -50 · Neto 240  │
│ Método: EFECTIVO · Ticket: 000234 · Corte #2      │
│ Pagado por María · 21/06/2026 02:15 · Caja #12    │
├─ Multas aplicadas ─────────────────────────────────┤
│ · Vaso roto -30 · Llegada tarde -20                │
├─ Líneas de liquidación ────────────────────────────┤
│ (tabla items)                                       │
└────────────────────────────────────────────────────┘
```

---

## 13. Conclusión

Frontend Fase 2 resolvió **decisión al pagar** (multas opcionales + neto preview).  
Para un sistema **totalmente auditable**, falta la capa **comprobante + reimpresión + metadatos visibles**.

Implementar ticket/reprint **después de aprobar** `backend/SETTLEMENT_PAYMENT_AUDIT.md` §7, reutilizando el pipeline de movimientos de caja.

**No programar hasta aprobar diseño conjunto backend + frontend.**
