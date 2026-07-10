# Auditoría frontend del cobro de limpieza en liquidaciones de chicas

**Fecha:** 2026-07-09
**Alcance:** visibilidad, edición, impresión y navegación operativa
**Estado:** auditoría realizada, sin cambio de UI

## 1. Resumen ejecutivo

En frontend, el cobro de limpieza en liquidaciones de chicas no se captura hoy como un campo editable propio dentro del formulario de liquidación. La interfaz muestra el resultado del cálculo ya persistido desde backend.

La UX actual expone:

- resumen de bruto, ajustes y neto
- detalle de líneas de liquidación
- ticket y comprobante con limpieza visible si existe ajuste
- acciones de multa y descuento manual, pero no un campo específico para editar la limpieza automática

## 2. Dónde se ve hoy

La evidencia principal está en el detalle de liquidación:

- [frontend/src/pages/nightpos/settlements/[id].vue](frontend/src/pages/nightpos/settlements/[id].vue)

Ahí se muestra:

- `gross_amount`
- `adjustments_total`
- `net_amount`
- el resumen de ajustes con componente `SettlementAdjustmentSummary`

Si la liquidación ya tiene el ajuste de limpieza, el usuario lo ve indirectamente como parte del total de ajustes.

## 3. Dónde no se edita

No existe hoy un campo de edición directa para el cobro de limpieza de chicas en la pantalla de liquidación.

El usuario puede:

- agregar multa
- agregar descuento manual
- pagar la liquidación

Pero no hay un control específico de tipo:

- “limpieza única”
- “monto de limpieza de liquidación”
- “editar deducción automática”

## 4. Diferencia con otros flujos de limpieza

Es importante no mezclar tres superficies distintas:

### Limpieza de piezas / habitaciones

En `frontend/src/pages/nightpos/services/room-services/create.vue` el monto de limpieza puede capturarse al registrar la pieza. Ese flujo sí tiene un input visible para `cleaning_amount`.

### Limpieza de personal operativo

En módulos de usuarios/limpieza hay campos de configuración como `cleaning_base_amount` y `cleaning_room_amount`, pensados para el salario de personal de limpieza.

### Limpieza de liquidación de chicas

La deducción automática de 10 Bs no se introduce en la UI como input propio. La pantalla la recibe ya calculada desde backend.

## 5. Impresión y comprobante

El ticket y la vista imprimible de liquidación leen el ajuste persistido y lo renderizan como limpieza.

Eso significa que la impresión no recalcula el monto; solo refleja lo ya grabado en backend.

## 6. Implicación UX

La UX actual es coherente para operación rápida, pero tiene una limitación clara para auditoría manual:

- el cobro de limpieza existe en datos
- el usuario lo ve en el resumen
- pero no puede revisarlo o corregirlo de forma explícita desde la pantalla de liquidación

En un escenario de operación con control fino, eso obliga a entrar al flujo de descuentos/multas, no al campo específico de limpieza.

## 7. Riesgo operativo

El riesgo principal no es el cálculo, porque ya está centralizado en backend.

El riesgo es de lectura humana:

- una cajera puede ver el neto pero no entender de inmediato por qué se descontaron 10 Bs
- un supervisor puede necesitar revisar el ajuste histórico para confirmar la causa
- si el negocio quiere cambiar a ingreso manual, no hay una interfaz nativa para hacerlo hoy

## 8. Comparación de UX posibles

### UX actual

Solo muestra el ajuste ya aplicado.

Ventaja: simple.

Limitación: poca capacidad de intervención.

### UX recomendada si el negocio quiere manualidad

Agregar un bloque visible dentro de la liquidación pendiente con:

- monto propuesto de limpieza
- base de cálculo
- razón del ajuste
- acción de editar antes de pagar

Ventaja: mantiene trazabilidad y reduce dudas.

### UX no recomendada

Pedir que la cajera capture el cobro de limpieza en un campo genérico de descuento manual.

Problema: mezcla conceptos y hace más difícil auditar después.

## 9. Recomendación frontend

Si el negocio aprueba el cambio a entrada manual, el mejor lugar para implementarlo es el detalle de liquidación pendiente, no el formulario de registro de pieza ni el perfil de la chica.

La UI debería presentar el cobro de limpieza como un ajuste dedicado, no como un descuento genérico.

## 10. Conclusión

Frontend hoy muestra correctamente el resultado del cobro de limpieza, pero no lo trata como una regla editable propia. La interfaz es de consumo y revisión, no de configuración. El cambio futuro razonable sería hacer visible el ajuste como una fila explícita y editable antes del pago.
