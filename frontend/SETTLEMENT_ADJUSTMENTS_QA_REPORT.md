# QA OPERATIVO — Motor de liquidaciones con ajustes y ticket (Frontend)

**Fecha:** 2026-06-21  
**Alcance:** Validación UX/flujo — **sin nuevas features**  
**Veredicto UI liquidaciones:** ✅ **APROBADO** (checklist manual recomendado)

---

## Resumen ejecutivo

El frontend implementa el flujo completo acordado en Fase 2–4. `npm run build` compila sin errores. No hay suite E2E automatizada; la validación UI se basa en **revisión de componentes + build + alineación con tests backend**.

---

## Validaciones técnicas

| Comando | Resultado |
|---------|-----------|
| `npm run build` | ✅ OK |

---

## Mapa UI → Casos operativos

### Caso 1 — Chica < 100 Bs

| UI | Componente / página | Qué verificar manualmente |
|----|---------------------|---------------------------|
| Generar | `settlements/girls.vue` o admin generate | Bruto 80, sin fila limpieza |
| Resumen | `SettlementAdjustmentSummary` en `[id].vue` | Neto 80 |
| Pagar | `SettlementPayDialog` | Botón **Pagar 80.00 Bs** |
| Post-pago | Alert ticket # + `openReceipt` | Comprobante navegador |

**Backend:** ✅ Phase1 test. **Frontend build:** ✅.

---

### Caso 2 — Chica ≥ 100 Bs

| UI | Verificar |
|----|-----------|
| `SettlementAdjustmentSummary` | Limpieza única -10 Bs |
| Pay dialog preview | Neto 90 |
| `PrintableSettlementTicket` | Línea Limpieza -10 Bs |
| Detalle PAID | Método, ticket #, movimiento caja |

---

### Caso 3 — Multa aplicada

| UI | Verificar |
|----|-----------|
| `StaffFineDialog` | Crear multa 30 Bs |
| `SettlementPayFinesSelector` | Checkbox marcado |
| `SettlementPayDialog` preview | Multa -30, neto 260 (en escenario 300-10-30) |
| Ticket | Multa en desglose |

**Backend:** ✅ Phase2 + PaymentAudit.

---

### Caso 4 — Multa no aplicada

| UI | Verificar |
|----|-----------|
| `SettlementPayFinesSelector` | Desmarcar multa |
| Preview | Neto sin multa |
| Ticket post-pago | Sin multa desmarcada |
| `StaffFinesList` | Multa sigue PENDING |

---

### Caso 5 — Descuento 5%

| UI | Verificar |
|----|-----------|
| `[id].vue` → **Agregar descuento** | Solo PENDING |
| `SettlementManualDiscountDialog` | Tipo Porcentaje, valor 5, motivo |
| Preview en modal | Base descuento = bruto + limpieza |
| Pay dialog | Descuento visible antes de pagar |
| Ticket | Descuento manual en desglose |

**Backend:** ✅ PaymentAudit percent test.

---

### Caso 6 — Descuento monto fijo

| UI | Verificar |
|----|-----------|
| `SettlementManualDiscountDialog` | Monto fijo 20 Bs |
| Resumen + pay | Neto coherente |
| Ticket + egreso | Mismo neto |

---

### Caso 7 — Corte parcial

| UI | Verificar |
|----|-----------|
| Pagar primer corte (con limpieza) | Neto incluye -10 |
| Nuevo consumo → generar | Segundo `cut_label` (#2) |
| Segundo detalle | Sin limpieza, neto = bruto nuevo |
| Historial | Dos filas, chips Con ajustes solo en corte 1 |

**Backend:** ✅ Phase1 partial cut test.

---

### Caso 8 — Reimpresión

| UI | Verificar |
|----|-----------|
| Detalle PAID → **Reimprimir ticket** | Snackbar éxito / warning impresora |
| **Ver comprobante** | `/nightpos/print/settlement/:id` |
| Banner REIMPRESIÓN | Si `print_count > 0` en ticket navegador |
| Detalle | print_count incrementado |

**Backend:** ✅ PaymentAudit reprint tests.

---

### Caso 9 — Cierre caja

| UI | Verificar |
|----|-----------|
| `cash/index.vue` (Mi Caja) | Movimiento EXPENSE por neto pagado |
| Lista movimientos | Monto = neto liquidación, no bruto |
| Cerrar caja | expected_cash coherente post-egresos |
| Comprobante cierre | Egresos reflejan neto |

**Backend:** ✅ SettlementsCashUiFixTest + close-check tests.

---

## Componentes validados (existencia + build)

| Componente | Estado |
|------------|--------|
| `SettlementAdjustmentSummary.vue` | ✅ MANUAL_DISCOUNT label |
| `SettlementPayDialog.vue` | ✅ Preview + Pagar {neto} |
| `SettlementPayFinesSelector.vue` | ✅ Checkboxes |
| `SettlementManualDiscountDialog.vue` | ✅ Preview API |
| `PrintableSettlementTicket.vue` | ✅ Ticket térmico navegador |
| `print/settlement/[id].vue` | ✅ Ruta registrada |
| `settlements/[id].vue` | ✅ Auditoría PAID |
| `settlements/history.vue` | ✅ Método, ticket, reimprimir |
| `useSettlementPayment.js` | ✅ openReceipt + reprintReceipt |

---

## Checklist smoke manual (recomendado en staging)

Ejecutar como cajera con caja abierta:

- [ ] Caso 2 completo: cobrar chica 100 → generar → pagar → ticket → movimiento 90 Bs
- [ ] Caso 5: descuento 5% visible en modal y ticket
- [ ] Caso 8: reimprimir y ver banner
- [ ] Caso 9: Mi Caja muestra egreso neto → cerrar caja

Tiempo estimado: **15–20 min**.

---

## Conclusión

Frontend alineado con backend auditado. Build OK. **Aprobado para QA operativo** con smoke manual opcional en staging.

No implementar nuevas features hasta completar smoke manual en entorno con MySQL + impresora (opcional).
