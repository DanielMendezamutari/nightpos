# Auditoría completa del cobro de limpieza en liquidaciones de chicas

**Fecha:** 2026-07-09
**Alcance:** backend, persistencia, caja, reportes e impresión
**Estado:** auditoría realizada, sin cambio de lógica de negocio

## 1. Resumen ejecutivo

El cobro de limpieza en liquidaciones de chicas no está hardcodeado en la UI ni en la liquidación como tal. Hoy se genera de forma automática desde el motor de ajustes de liquidaciones cuando la liquidación de tipo `GIRL` supera el umbral configurado.

En la implementación actual:

- el umbral es `100 Bs`
- el monto aplicado es `10 Bs`
- el ajuste se guarda como histórico en `staff_settlement_adjustments`
- el total de la liquidación se recalcula desde bruto + ajustes

La evidencia de base real confirma liquidaciones con `adjustments_total = -10.00` y su ajuste correspondiente con `adjustment_type = CLEANING_DEDUCTION`.

## 2. Hallazgo principal

La lógica central está en el motor de ajustes de liquidación:

- `SettlementAdjustmentEngine::syncCleaningDeduction(...)`
- `SettlementTotalsCalculator::recalculate(...)`

El flujo es este:

1. Se calcula el bruto de la liquidación desde sus ítems.
2. Si la liquidación está en estado `PENDING`, se invoca el sincronizador de limpieza.
3. Si el bruto supera el umbral configurado, se crea o actualiza un ajuste negativo de limpieza.
4. Luego se vuelve a sumar bruto + ajustes y se persiste el total final.

## 3. De dónde sale el 10 Bs

El monto no sale de un valor fijo en la vista. Proviene de configuración:

- `backend/config/nightpos.php`
- `nightpos.girl_unique_cleaning.threshold = 100`
- `nightpos.girl_unique_cleaning.amount = 10`

Por tanto:

- no es un valor embebido en el frontend
- no es un valor tomado del perfil de la chica
- no es un monto calculado por pieza o por habitación

Es una regla automática global del sistema.

## 4. Cuándo se aplica

La limpieza única se aplica solo cuando se cumplen estas condiciones:

- la liquidación existe
- la liquidación está en estado `PENDING`
- el tipo de liquidación es `GIRL`
- el bruto supera el umbral de `100 Bs`

El ajuste generado queda marcado con una llave de deduplicación basada en:

- `official_shift_id`
- `cash_session_id`
- `staff_user_id`

Eso evita duplicados si el cálculo se repite varias veces.

## 5. Persistencia histórica

Sí, queda persistido históricamente. La fuente de verdad del cobro no está en un cálculo efímero de UI, sino en la tabla de ajustes:

- `staff_settlement_adjustments.adjustment_type = CLEANING_DEDUCTION`
- `staff_settlement_adjustments.amount = -10.00`
- `staff_settlement_adjustments.calculation_base`
- `staff_settlement_adjustments.dedup_key`
- `staff_settlement_adjustments.notes`

Además, la liquidación principal almacena los totales ya recalculados:

- `staff_settlements.gross_amount`
- `staff_settlements.adjustments_total`
- `staff_settlements.net_amount`
- `staff_settlements.total_amount`

## 6. Tablas y columnas involucradas

### Tabla `staff_settlements`

Campos relevantes:

- `settlement_type`
- `official_shift_id`
- `cash_session_id`
- `gross_amount`
- `adjustments_total`
- `net_amount`
- `total_amount`
- `status`

### Tabla `staff_settlement_adjustments`

Campos relevantes:

- `adjustment_type`
- `amount`
- `discount_mode`
- `discount_value`
- `calculation_base`
- `notes`
- `dedup_key`

### Tabla `room_services`

Importante para no mezclar conceptos:

- `gross_girl_amount`
- `girl_amount`
- `house_amount`
- `cleaning_amount`

Ese `cleaning_amount` pertenece a la pieza/servicio, no a la deducción automática de liquidación de chicas.

### Tabla `staff_profiles`

También existe otra limpieza distinta para personal de limpieza:

- `cleaning_base_amount`
- `cleaning_room_amount`

Eso corresponde al pago de limpieza operativa, no a la deducción en liquidación de chicas.

## 7. Evidencia en base real MySQL

En la base real `nigtpos` se observan ejemplos como estos:

- liquidación `id = 22`, tipo `GIRL`, `gross_amount = 160.00`, `adjustments_total = -10.00`, `net_amount = 150.00`
- liquidación `id = 20`, tipo `GIRL`, `gross_amount = 200.00`, `adjustments_total = -10.00`, `net_amount = 190.00`
- liquidación `id = 17`, tipo `GIRL`, `gross_amount = 720.00`, `adjustments_total = -10.00`, `net_amount = 710.00`

Y en `staff_settlement_adjustments` aparecen filas con:

- `adjustment_type = CLEANING_DEDUCTION`
- `amount = -10.00`
- `calculation_base = 160.00`, `200.00`, `720.00`
- `dedup_key = cleaning:<official_shift_id>:<cash_session_id>:<staff_user_id>`
- `notes = Limpieza única turno (≥ 100.00 Bs)`

Eso confirma que el monto se guarda como ajuste histórico y no como simple fórmula visual.

## 8. Impacto funcional

### Caja

La deducción afecta el `net_amount` de la liquidación, por lo tanto el egreso de caja se registra con el monto neto final, no con el bruto.

### Reportes

Los reportes de cierre y resumen gerencial agregan el campo `cleaning` desde `staff_settlement_adjustments` con tipo `CLEANING_DEDUCTION`.

### Cierre de turno

El cierre de turno muestra limpieza como categoría separada dentro de liquidaciones. El valor entra al total de liquidaciones pendientes/pagadas y al resumen de ajustes.

### Impresión

El ticket de liquidación imprime el campo de limpieza a partir de los ajustes persistidos. No depende de rederivar la regla en el momento de imprimir.

### Auditoría

Como el ajuste queda en una tabla histórica, se puede rastrear:

- quién fue la chica afectada
- en qué corte se aplicó
- cuál fue la base de cálculo
- qué llave evitó duplicados

## 9. Comparación de modelos

### Modelo A: cobro fijo automático e invisible

La app descuenta 10 Bs de forma automática y el usuario no puede intervenir.

Ventaja: simple.

Riesgo: poca trazabilidad operativa para correcciones manuales.

### Modelo B: cobro manual total

La cajera o admin ingresa el monto de limpieza en cada liquidación.

Ventaja: control explícito.

Riesgo: más fricción, más error humano y más variación operativa.

### Modelo C: automático con ajuste editable previo al pago

El sistema propone el cobro automático y lo deja visible para revisión/corrección antes del pago.

Ventaja: conserva consistencia histórica y añade control.

Riesgo: requiere una UI clara para no duplicar ajustes.

### Modelo D: cobro fuera de la liquidación

La limpieza se cobra como egreso/ajuste aparte, desconectado de la liquidación de la chica.

Ventaja: separa conceptos contables.

Riesgo: rompe la lectura operativa unificada de la liquidación.

## 10. Recomendación

La recomendación técnica es mantener el modelo actual como base contable hasta que el negocio apruebe un rediseño. Si se quiere pasar a entrada manual, el mejor camino no es mover el monto a la pieza ni al perfil, sino convertir el ajuste de limpieza en una fila editable en el flujo de liquidación pendiente.

Eso preserva:

- historial
- deduplicación
- consistencia de reportes
- trazabilidad de caja

## 11. Conclusión

Hoy el cobro de limpieza en liquidaciones de chicas es:

- automático
- config-driven
- persistido históricamente
- visible en reportes, caja e impresión

No hay evidencia de que el monto salga de la interfaz o de una cifra hardcodeada en la vista. La fuente real está en configuración y en el motor de ajustes.
