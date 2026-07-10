# Implementacion — Limpieza Manual para Chicas (Frontend)

Fecha: 2026-07-10
Estado: Implementado y validado

## Objetivo funcional

Cambiar el flujo de limpieza de liquidaciones GIRL desde deduccion automatica a ajuste manual dedicado, manteniendo compatibilidad operativa y visual.

## Cambios de UI implementados

### 1) Seccion dedicada en detalle de liquidacion

Pantalla: settlements/[id]

Se agrega bloque "Cobro de limpieza" visible cuando:

- settlement_type = GIRL
- status = PENDING
- usuario con permiso operativo de pago (settlements.pay)

Incluye:

- Input de monto de limpieza (>= 0)
- Boton guardar
- Nota explicita: monto 0 elimina el cobro de limpieza
- Recarga de settlement y pay preview tras guardar

### 2) Resumen claro previo al pago

Se crea componente dedicado para desglose de pago:

- Pago bruto
- Limpieza
- Otros ajustes
- Total a pagar

Se usa en:

- Dialogo de pago de liquidacion
- Contexto de detalle de liquidacion

### 3) Etiquetado semantico

Se actualiza label de CLEANING_DEDUCTION a:

- "Cobro de limpieza"

## Integracion API

Se agrega cliente para endpoint manual:

- updateSettlementCleaningDeduction(settlementId, amount)
- Metodo PATCH
- Ruta /api/v1/settlements/{id}/cleaning-deduction

## Compatibilidad UX y operativa

- No se mezclo el input dedicado con "otros ajustes" para evitar confusiones.
- Se mantiene visualizacion consistente en resumenes y dialogos de pago.
- Al conservar CLEANING_DEDUCTION, los datos historicos y reportes no se rompen.

## Archivos frontend modificados

- src/components/nightpos/settlements/SettlementPaymentBreakdown.vue (nuevo)
- src/api/settlements.js
- src/constants/settlements.js
- src/components/nightpos/settlements/SettlementPayDialog.vue
- src/pages/nightpos/settlements/[id].vue

## Escenarios validados

- Liquidacion GIRL PENDING muestra control dedicado de limpieza.
- Guardar monto > 0 actualiza neto y resumen pre-pago.
- Guardar 0 elimina limpieza y no deja cobro residual.
- Dialogo de pago refleja bruto, limpieza, otros ajustes y total final con datos actualizados.

## Resultado

El frontend queda alineado al modelo manual dedicado, con UX clara para cajera/operacion y sin dependencia de reglas automaticas ocultas.
