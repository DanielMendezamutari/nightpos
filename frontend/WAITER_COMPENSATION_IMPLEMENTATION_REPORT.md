# WAITER COMPENSATION IMPLEMENTATION REPORT

Fecha: 2026-07-03
Estado: Implementado (Fase 1 y Fase 2)
Alcance: UI de liquidaciones de garzones

## Objetivo implementado

Se implemento soporte visual y operativo para liquidaciones de garzones en modo MANUAL y AUTO_PERCENT, con bloqueo preventivo de pago cuando falta monto manual.

## Cambios realizados

## 1) API settlements

Archivo: src/api/settlements.js

Nuevo metodo:

- updateSettlementManualCompensation(id, payload)

Request:

- PATCH /settlements/{id}/manual-compensation
- body: { amount, notes }

## 2) Acciones por fila

Archivo: src/components/nightpos/settlements/SettlementListRowActions.vue

Nuevas capacidades:

- Boton Asignar monto (cuando aplica)
- Deshabilitar boton Pagar con mensaje contextual

Nuevos props:

- canAssignManual
- payDisabled
- payDisabledReason

Nuevo evento emitido:

- assign-manual

## 3) Pantalla Garzones

Archivo: src/pages/nightpos/settlements/waiters.vue

Mejoras:

- Nuevas columnas:
  - Modo
  - Monto manual
- Etiqueta de modo:
  - AUTO_PERCENT -> Auto %
  - MANUAL -> Manual
- Regla de bloqueo de pago por fila:
  - requires_manual_amount = true -> Pagar deshabilitado
- Mensaje de usuario:
  - Asigne monto manual antes de pagar.
- Dialogo de asignacion manual:
  - monto (>= 0)
  - notas
  - guardado via PATCH
  - recarga de tabla tras guardar

## 4) Integracion UX con flujo existente

Se mantiene:

- Dialogo de pago existente
- Dialogo de multas existente
- SSE refresh de liquidaciones
- Banner de caja y flujo de apertura rapida

## Validacion

- Sin errores de analisis en archivos frontend modificados.
- Compatibilidad con estructura actual de tabla y composables de liquidaciones.

## Resultado funcional

- Garzones con ventas quedan visibles aunque comision automatica sea 0.
- Si la liquidacion es MANUAL sin monto, el usuario no puede pagar desde la UI.
- El usuario puede asignar monto manual en contexto y luego completar el pago.
