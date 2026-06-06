# Operación de caja NightPOS (F1.8)

## Modelo: caja por usuario

Cada cajera abre **su propia sesión** (`findOpenForUser`). Dos cajeras en la misma sucursal tienen **dos sesiones independientes**, no una caja física compartida.

## Flujo recomendado

1. **Apertura:** Cajera inicia sesión PIN → Caja → Abrir (o quick open desde cobro).
2. **Cobros:** Todos los movimientos de venta se asocian a la sesión abierta de quien cobra.
3. **Arqueo:** Antes de cerrar, use **Imprimir arqueo** en Caja o Consola de turno.
4. **Cierre:** Cada cajera cierra su sesión con monto declarado.
5. **Turno:** Admin cierra turno oficial solo cuando **no queden sesiones abiertas** (ver Consola de turno → Cajas abiertas).

## Coordinación en pico

- Una cajera por caja física; la segunda espera cierre o usa otra estación.
- Si una cajera cobra comanda de garzón ajeno, al crear comanda debe elegir **garzón correcto** (comisiones).

## Consola de turno

`GET /shift-console/current` muestra `open_cash_sessions` con nombre y monto esperado por cajera activa.

## Auditoría

Cierres de caja y turno quedan en **Configuración → Bitácora auditoría** (`cash_session.closed`, `official_shift.closed`).
