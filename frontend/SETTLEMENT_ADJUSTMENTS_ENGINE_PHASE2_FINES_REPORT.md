# SETTLEMENT_ADJUSTMENTS_ENGINE — Fase 2 Frontend (Multas)

**Fecha:** 2026-06-21  
**Estado:** Completado — Fase 2 frontend  
**Backend:** `backend/SETTLEMENT_ADJUSTMENTS_ENGINE_PHASE2_FINES_REPORT.md`

---

## Resumen

UI conectada a la API de multas independientes. La cajera/admin puede registrar multas, verlas, cancelarlas pendientes y **elegir cuáles aplicar al pagar** con neto recalculado vía `pay-preview`.

**Fuera de alcance:** descuento manual (Fase 3), ticket settlement (Fase 4).

---

## API client

| Archivo | Funciones |
|---------|-----------|
| `src/api/staffFines.js` | `fetchStaffFines`, `createStaffFine`, `cancelStaffFine` |
| `src/api/settlements.js` | `fetchSettlementPayPreview`, `markSettlementPaid` (+ `applied_fine_ids`) |

---

## Constantes

`src/constants/settlements.js`

- Labels ajustes: `CLEANING_DEDUCTION`, `MANUAL_FINE`, fuentes de ingreso
- `formatBob`, `formatSignedBob`, `settlementHasAdjustments`
- Estados multa: `STAFF_FINE_STATUS_LABELS`

---

## Componentes nuevos

| Componente | Rol |
|------------|-----|
| `StaffFineDialog.vue` | Crear multa (persona, rol, monto, motivo) |
| `StaffFinesList.vue` | Tabla multas + cancelar PENDING con motivo |
| `SettlementAdjustmentSummary.vue` | Bruto → ajustes → neto |
| `SettlementPayFinesSelector.vue` | Checkboxes multas pendientes |

---

## Componentes actualizados

### `SettlementPayDialog.vue`

- Al abrir: `GET /settlements/{id}/pay-preview`
- Multas pendientes marcadas por defecto
- Desmarcar → recarga preview → neto actualizado
- Botón: **Pagar {neto} Bs**
- Emite `applied_fine_ids` al confirmar
- Bloquea pago si preview falla

### `useSettlementPayment.js`

- Envía `applied_fine_ids` en `mark-paid`

### Permisos

- `canManageSettlementFines` → `settlements.fines.manage`
- Sin permiso: oculto Agregar multa / Cancelar
- Con `settlements.pay`: puede pagar y ver preview

---

## Páginas

| Página | Cambios |
|--------|---------|
| `settlements/index.vue` | Botón **Registrar multa** + tarjetas rápidas (Chicas / Garzones / Limpieza / Registrar multa) + lista multas PENDING del turno |
| `settlements/[id].vue` | Banda de acciones visible (Agregar multa + Marcar pagado), resumen bruto/limpieza/neto, multas pendientes, pago con preview |
| `settlements/girls.vue` | Botón **Multar** por fila (warning, flat) + pay con fines |
| `settlements/waiters.vue` | Idem |
| `settlements/cleaning.vue` | Idem |
| `settlements/history.vue` | Columnas Bruto / Neto pagado + chip **Con ajustes** |

### UX multas — ajuste operativo (2026-06-25)

| Componente | Rol |
|------------|-----|
| `SettlementListRowActions.vue` | Acciones **Pagar · Multar · Ver detalle** por fila |
| `SettlementHubQuickNav.vue` | Hub: tarjetas Ver chicas / garzones / limpieza + Registrar multa |
| `StaffFineDialog.vue` | Pre-fill persona/rol desde fila; toast «Multa registrada para {nombre}.» |

**Permiso:** `settlements.fines.manage` — asignado a rol **cajera** desde migración `2026_06_25_100001` (antes solo cajera senior/admin en BD demo).

**Flujo cajera (≤2 clics):** Liquidaciones → Chicas → **Multar** en fila → monto + motivo → al pagar, multa seleccionable en preview.

---

## Flujo manual de prueba

1. Generar liquidación chica (≥100 Bs → limpieza −10)
2. Agregar multa «Vaso roto» 30 Bs
3. Abrir pagar → preview muestra bruto, limpieza, multa ☑, neto
4. Desmarcar multa → neto sube
5. Marcar multa → neto baja
6. Pagar → multa APPLIED, egreso por neto
7. Detalle PAID → resumen con multa aplicada

---

## Build

```bash
npm run build
```

OK (2026-06-21).

---

## Próximo paso

**Fase 3 frontend:** `SettlementManualDiscountDialog`  
**Fase 4:** ticket `print/settlement/:id`
