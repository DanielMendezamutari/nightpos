# Reporte de Implementación: Scope Híbrido de Liquidaciones (Frontend)

**Fecha:** 2026-07-08
**Estado:** Implementado
**Alcance:** Ajuste visual mínimo para reflejar correctamente el nuevo scope híbrido de liquidaciones.

## Objetivo frontend

Alinear la UX de liquidaciones con el modelo híbrido ya implementado en backend:

1. Cajera trabaja por `cash_session_id`.
2. Admin/owner siguen en contexto `official_shift`.
3. La pantalla debe comunicar claramente el contexto activo y, para cajera, los turnos incluidos en su caja.

## Cambios aplicados

## 1) Etiqueta de contexto en resumen de liquidaciones

Archivo:

- `frontend/src/pages/nightpos/settlements/index.vue`

Cambio:

- El `scopeLabel` ahora incluye, en `my_cash_session`, la lista de `settlement_official_shift_ids` cuando exista.
- Ejemplo visual: “Mostrando liquidaciones de mi caja actual (turnos incluidos: 13, 19)”.

## 2) Etiqueta de contexto en listas de garzones

Archivo:

- `frontend/src/pages/nightpos/settlements/waiters.vue`

Cambios:

- Se consume `context` desde `useCurrentShiftSettlements()`.
- Se agrega `scopeLabel` computado con comportamiento híbrido.
- Se muestra `VAlert` informativo con alcance activo:
  - caja actual para cajera,
  - turno oficial para admin/owner.

## 3) Etiqueta de contexto en listas de chicas

Archivo:

- `frontend/src/pages/nightpos/settlements/girls.vue`

Cambios análogos a garzones:

- consumo de `context`,
- `scopeLabel` con turnos incluidos en scope de caja,
- `VAlert` informativo.

## Contrato API consumido

El frontend aprovecha el contexto enriquecido entregado por backend en `current-shift`:

- `context.cash_session_id`
- `context.session_official_shift_id`
- `context.settlement_official_shift_ids`

No se cambió endpoint ni cliente HTTP; se reutilizó el `fetchCurrentShiftSettlements()` existente.

## Qué no se tocó

- No se cambió PWA.
- No se cambió navegación global fuera del módulo de liquidaciones.
- No se alteró lógica de pago ni de impresión en frontend.
- No se tocó `dist`.

## Impacto funcional logrado

- Se elimina ambigüedad visual en cajera cuando su caja contiene liquidaciones de más de un turno oficial.
- Se mantiene lenguaje de turno para administración/owner.
- Se conserva experiencia actual de pagos y tabs, con mínima intrusión UI.

## Conclusión

Frontend quedó alineado con el modelo híbrido aprobado:

- operación de cajera por caja,
- gobierno por turno para perfiles de supervisión,
- transparencia del contexto activo en pantallas de liquidaciones.
