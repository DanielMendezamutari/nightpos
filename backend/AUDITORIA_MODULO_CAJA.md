# Auditoría completa del módulo de Caja (Backend)

**Fecha:** 2026-07-10
**Alcance:** backend, consultas, persistencia, reglas operativas y contraste con MySQL real `nigtpos`
**Modo:** auditoría solamente, sin implementación

## 1. Pregunta rectora

¿La información que hoy muestra Caja es suficiente, correcta y útil para una operación real de NightPOS?

Respuesta corta: **parcialmente**.

El módulo sí resuelve lo mínimo para abrir caja, cobrar, registrar movimientos, pagar liquidaciones y cerrar. Sin embargo, en su estado actual mezcla conceptos de ventas, ingresos y egresos; usa algunas etiquetas que no representan fielmente el cálculo real; y además arrastra un problema estructural: la misma `cash_session` puede acumular actividad de múltiples `official_shift_id`, mientras varias validaciones y mensajes siguen pensando en el turno original de apertura.

Eso no vuelve inútil a Caja, pero sí la vuelve **riesgosa para una operación real prolongada**, especialmente si una caja permanece abierta durante varios cambios de turno.

## 2. Fuentes auditadas

### Backend auditado

- `app/Http/Controllers/Api/V1/CashController.php`
- `app/Application/Cash/UseCases/GetCurrentCashSessionUseCase.php`
- `app/Application/Cash/UseCases/OpenCashSessionUseCase.php`
- `app/Application/Cash/UseCases/CloseCashSessionUseCase.php`
- `app/Application/Cash/UseCases/GetCashSessionCloseCheckUseCase.php`
- `app/Application/Cash/UseCases/RegisterCashMovementUseCase.php`
- `app/Application/Cash/UseCases/ListCashSessionsAdminUseCase.php`
- `app/Application/Cash/UseCases/GetCashSessionAdminUseCase.php`
- `app/Application/Cash/Services/CashSessionFinancialSummaryBuilder.php`
- `app/Application/Cash/Services/CashSessionCloseCheckBuilder.php`
- `app/Application/Reports/Services/CashCloseReportSectionsBuilder.php`
- `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentCashSessionRepository.php`
- `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentSaleRepository.php`
- `app/Application/Order/Support/CashierChargeableOrdersScope.php`
- `app/Application/StaffSettlement/UseCases/MarkSettlementPaidUseCase.php`
- `routes/api.php`

### Base real auditada

Se consultó la base MySQL real importada `nigtpos`.

Hallazgos de volumen observados:

- `cash_sessions`: 12
- `cash_movements`: 131
- `cash_movement_reasons`: 33
- `sales`: 68
- `sale_payments`: 69
- `staff_settlements`: 28
- `staff_settlement_adjustments`: 9
- `orders`: 80
- `room_services`: 1
- `official_shifts`: 33

## 3. Arquitectura real de Caja

Caja no es una sola tabla ni un solo cálculo. Hoy el módulo combina al menos cinco fuentes distintas:

1. `cash_sessions`
- Define apertura, cierre, monto inicial, esperado guardado, contado declarado y diferencia final.

2. `cash_movements`
- Es el libro operativo de ingresos y egresos de la caja.
- Aquí se registran tanto ingresos por ventas como movimientos manuales y pagos de liquidaciones.

3. `sales` + `sale_payments`
- Son la fuente de verdad de ventas cobradas y método de pago de cada cobro.
- También alimentan varios indicadores de Caja.

4. `staff_settlements` + `staff_settlement_adjustments`
- Alimentan pagos al personal, pendientes de pago y bloqueos de cierre.

5. `official_shifts`
- Se usa para contexto de turno, pero en la práctica Caja puede sobrevivir a varios turnos sin cerrarse.

## 4. Hallazgo estructural principal

### 4.1 La caja real puede abarcar múltiples turnos oficiales

En la base real se observó lo siguiente:

- `cash_session_id = 11` sigue abierta.
- Fue abierta con `official_shift_id = 13`.
- El turno abierto actual ya no es 13 sino 33.
- Esa misma caja 11 contiene ventas de los turnos 13, 19, 23 y 28.

Ventas observadas en la misma caja 11:

- turno 13: `340.00`
- turno 19: `1240.00`
- turno 23: `160.00`
- turno 28: `280.00`

Conclusión:

- La pantalla de Caja no representa un “turno actual” limpio.
- Representa una **sesión de caja viva**, que puede acumular actividad de varios turnos.
- Operativamente eso es válido si el negocio trabaja por caja continua.
- Administrativamente eso es peligroso si la interfaz sigue sugiriendo que todo pertenece a un solo corte temporal.

### 4.2 El close-check mezcla lógica por caja y lógica por turno

`CashSessionCloseCheckBuilder` valida varias cosas por `cash_session_id`, pero otras por `official_shift_id`.

Ejemplos:

- Liquidaciones pendientes: sí usan repositorio híbrido con `cash_session_id`.
- Comandas pendientes de cobro: `CashierChargeableOrdersScope` resuelve por turno abierto de sucursal, no por sesión de caja.
- Piezas activas o vencidas: consulta por `official_shift_id`, no por `cash_session_id`.

Esto significa que una caja que atraviesa turnos puede tener:

- bloqueos correctamente detectados en liquidaciones,
- pero subdetección o desalineación en comandas/piezas si esas fuentes quedaron en otro turno oficial.

## 5. Indicadores actuales y qué significan realmente

## 5.1 Estado de caja

**Qué significa realmente**
- Si la sesión está `OPEN` o `CLOSED`.

**De dónde sale**
- `cash_sessions.status`

**Tabla usada**
- `cash_sessions`

**Cálculo**
- Ninguno, lectura directa.

**Correctitud contable**
- Correcto como estado operativo.

**Riesgo**
- No informa si la caja abierta ya quedó desfasada respecto al turno vigente.

**Usuario que realmente lo necesita**
- Cajera, supervisora, administración.

## 5.2 Fondo inicial

**Qué significa realmente**
- Monto de apertura declarado al abrir la caja.

**De dónde sale**
- `cash_sessions.opening_amount`

**Tabla usada**
- `cash_sessions`

**Cálculo**
- Lectura directa.

**Correctitud contable**
- Correcto. Es el capital inicial de la sesión.

**Riesgo**
- Ninguno relevante; el riesgo está en cómo se combina después con el esperado.

**Usuario**
- Cajera y dueño.

## 5.3 Efectivo (ventas)

**Qué significa realmente**
- Total cobrado en ventas cuyo pago fue `CASH` dentro de la sesión.

**De dónde sale**
- `SaleRepositoryInterface::sumPaymentsByMethodForSession()`

**Tablas usadas**
- `sales`
- `sale_payments`

**Consulta**
- Agrupa `sale_payments.amount` por `payment_method` filtrando `sales.cash_session_id`.

**Cálculo**
- `SUM(sale_payments.amount)` donde `payment_method = CASH`

**Correctitud contable**
- Correcto como total de ventas cobradas en efectivo.

**Riesgo**
- Puede confundirse con dinero disponible en caja. No es lo mismo: luego hay egresos que lo reducen.

**Usuario**
- Cajera y supervisión.

## 5.4 QR (ventas)

Mismo patrón que el punto anterior, pero para `payment_method = QR`.

**Correctitud**
- Correcto como venta cobrada por QR.

**Riesgo**
- No representa efectivo en caja física.

**Usuario**
- Cajera, administración y conciliación.

## 5.5 Tarjeta (ventas)

Mismo patrón que QR, con `payment_method = CARD`.

**Correctitud**
- Correcto como venta cobrada con tarjeta.

**Riesgo**
- No representa efectivo físico.

## 5.6 Ingresos manuales

**Qué significa realmente**
- No son todos los ingresos manuales del sistema.
- Son ingresos `INCOME` en `cash_movements` excluyendo descripciones que empiecen con `Cobro comanda` y `Venta directa`.

**De dónde sale**
- `EloquentCashSessionRepository::sumManualMovements()`

**Tabla usada**
- `cash_movements`

**Consulta**
- Suma `movement_type = INCOME`
- Excluye por texto:
  - `description not like 'Cobro comanda%'`
  - `description not like 'Venta directa%'`

**Correctitud contable**
- Parcial.

**Problema de diseño**
- La separación entre ingreso por venta e ingreso manual depende de un texto libre en `description`, no de una clasificación fuerte como `source_type` o `cash_movement_reason_id`.
- Peor aún: los cobros de comandas no guardan `source_type`, mientras venta directa sí guarda `sourceType = 'sale'`.
- Eso vuelve la clasificación frágil.

**Riesgo**
- Si cambia el texto descriptivo de un flujo automático, el ingreso podría reclasificarse incorrectamente como manual.

**Usuario**
- Administración y cajera, pero solo si el indicador fuera confiable por fuente, no por texto.

## 5.7 Egresos manuales

**Qué significa realmente hoy**
- La etiqueta está mal.
- No son egresos manuales: son **todos** los egresos de la sesión.

**De dónde sale**
- También de `sumManualMovements()`.

**Tabla usada**
- `cash_movements`

**Consulta**
- Suma todo `movement_type = EXPENSE`.
- No separa manual vs liquidaciones vs compras vs pagos operativos.

**Correctitud contable**
- Incorrecta la etiqueta.
- Correcta la suma como egreso total de movimientos.

**Riesgo**
- Induce a pensar que se muestra solo gasto manual, cuando en realidad mezcla:
  - pago a chicas,
  - pago a garzones,
  - gastos operativos,
  - compras,
  - pagos por QR o tarjeta,
  - cualquier otro egreso.

**Usuario**
- Ninguno debería consumir este número bajo la etiqueta “manuales”.

## 5.8 Total esperado

**Qué significa realmente**
- Es el efectivo esperado en caja física.
- No es el total económico del turno.
- No incluye QR ni tarjeta en el valor principal mostrado a la cajera.

**De dónde sale**
- `CashSessionFinancialSummaryBuilder::build()`
- Para sesión abierta: cálculo dinámico.
- Para sesión cerrada: usa `cash_sessions.expected_amount` guardado.

**Tablas usadas**
- `cash_sessions`
- `cash_movements`

**Cálculo principal**
- `opening_amount + income_cash - expense_cash`

**Correctitud contable**
- Correcto si la intención es representar **solo efectivo físico esperado**.

**Riesgo**
- El nombre “Total esperado” es ambiguo porque en la misma pantalla también se muestran QR y tarjeta. El usuario puede creer que es el total general esperado y no solo el efectivo.

**Usuario**
- Cajera principalmente.

## 5.9 Resumen por método: Inicial / Ingresos / Ventas / Egresos / Esperado

Este bloque es el más delicado conceptualmente.

### Inicial
- Solo aplica a efectivo.
- Sale de `opening_amount`.
- Correcto.

### Ingresos
- Sale de `cash_movements` por método y `movement_type = INCOME`.
- Incluye cobros por ventas porque los cobros también se registran como `cash_movements`.

### Ventas
- Sale de `sale_payments` por método.
- Representa ventas cobradas.

### Egresos
- Sale de `cash_movements` por método y `movement_type = EXPENSE`.
- Mezcla pagos al personal y gastos manuales.

### Esperado / neto
- Para `CASH`: `apertura + ingresos_cash - egresos_cash`
- Para `QR`: `ingresos_qr - egresos_qr`
- Para `CARD`: `ingresos_card - egresos_card`

**Problema principal**
- “Ingresos” y “Ventas” duplican información en todos los casos donde el ingreso proviene de la venta.
- En la base real de la caja 11:
  - `income_cash = 560.00` y `sales_cash = 560.00`
  - `income_qr = 1460.00` y `sales_qr = 1460.00`
- Por tanto el usuario ve dos columnas distintas que en la práctica significan casi lo mismo.

**Correctitud contable**
- El cálculo es matemáticamente consistente.
- La semántica visual es mala.

**Riesgo**
- Duplicación conceptual.
- Mala lectura de la caja.

**Usuario**
- Administración y cajera avanzada, pero solo si el bloque se reetiqueta o se divide mejor.

## 5.10 Total ventas

**Qué significa realmente**
- Suma de todos los `sale_payments.amount` de la sesión.

**Tablas**
- `sales`
- `sale_payments`

**Cálculo**
- `SUM(sale_payments.amount)` filtrado por `sales.cash_session_id`

**Correctitud contable**
- Correcto como venta cobrada total.

**Riesgo**
- Si se presenta al lado de ingresos sin aclaración, el usuario puede pensar que uno está duplicando al otro, y en efecto hoy esa ambigüedad existe.

## 5.11 Total income y total expense internos

Existen en el builder aunque no todos se muestran con ese nombre.

- `total_income`: suma de todos los `cash_movements` de tipo `INCOME`
- `total_expense`: suma de todos los `cash_movements` de tipo `EXPENSE`

**Correctitud**
- Correcta como movimiento total de caja.

**Problema**
- No están bien separados visualmente de ventas ni de movimientos manuales.

## 5.12 Contado

**Qué significa realmente**
- Solo existe al cerrar caja.
- Es `declared_closing_amount`.
- Representa el efectivo físico contado.

**Correctitud**
- Correcto para efectivo.

**Riesgo**
- No existe persistencia equivalente para QR y tarjeta, aunque la UI sí pide verificarlos.

## 5.13 Diferencia

**Qué significa realmente**
- `declared_closing_amount - expected_amount`
- Solo sobre efectivo.

**Correctitud contable**
- Correcta si el arqueo auditado es solo de efectivo.

**Riesgo**
- La UI puede hacer creer que también existe una diferencia verificada formalmente para QR y tarjeta, pero eso no se persiste como campo estructurado.

## 5.14 QR verificado / Tarjeta verificada en cierre

**Qué significa realmente**
- Son campos UI en el diálogo de cierre.
- No existen en el request formal ni en columnas propias de `cash_sessions`.
- Se serializan dentro de `closing_notes`.

**Prueba en código**
- `CloseCashSessionRequest` solo valida:
  - `declared_closing_amount`
  - `closing_notes`

**Prueba en base real**
- Sesión cerrada 12 guarda:
  - `closing_notes = "QR verificado: 1189 | Tarjeta verificada: 80 | Diferencia QR: 0.00 | Diferencia tarjeta: 0.00"`

**Correctitud contable**
- Insuficiente.

**Riesgo**
- La verificación de QR y tarjeta no es auditable estructuralmente.
- Es texto, no dato contable.

## 5.15 Movimientos

**Qué significan realmente**
- Libro operativo de entradas/salidas de caja.

**Tablas**
- `cash_movements`
- `cash_movement_reasons` para motivos manuales

**Correctitud**
- Muy útil como bitácora.

**Riesgos**
- Los cobros automáticos de comandas entran con `description = Cobro comanda ...` pero sin `source_type` consistente.
- La venta directa sí guarda `sourceType = sale`.
- Hay inconsistencia de trazabilidad entre tipos de ingresos automáticos.

## 5.16 Liquidaciones y pagos al personal

**Qué significan realmente**
- No son solo egresos genéricos. Son pagos operativos de personal.

**De dónde salen**
- `MarkSettlementPaidUseCase`
- Registra `cash_movements` de tipo `EXPENSE` con `sourceType = STAFF_SETTLEMENT`

**Correctitud**
- Correcto registrar el egreso.

**Riesgo**
- Al no separarse explícitamente en KPI, los pagos al personal se diluyen dentro del mismo bucket de “egresos manuales” o “egresos”.

## 6. Correctitud contable por familias

## 6.1 Ventas

Estado: **bastante correcto**.

- La fuente de verdad está bien definida en `sales` + `sale_payments`.
- La separación por método existe.
- El problema no es el dato, sino cómo se mezcla visualmente con ingresos de caja.

## 6.2 Ingresos

Estado: **conceptualmente mezclado**.

- En Caja, ingresos por venta y movimientos de caja conviven en la misma capa (`cash_movements`).
- El sistema diferencia “manual” por texto, no por clasificación robusta.

## 6.3 Egresos

Estado: **matemáticamente correcto, semánticamente mal separado**.

- Los egresos incluyen pagos de personal y gastos operativos.
- La etiqueta “egresos manuales” es incorrecta para el dato que hoy se calcula.

## 6.4 Caja física

Estado: **parcialmente correcto**.

- `expected_cash` representa bien el efectivo físico esperado.
- `difference` representa bien el faltante/sobrante de efectivo.
- Pero QR y tarjeta no tienen el mismo nivel de persistencia ni formalidad.

## 6.5 Liquidaciones

Estado: **operativamente importante, pero submodelado en KPI de Caja**.

- Los bloqueos de cierre sí consideran pendientes de liquidación.
- La visualización de pagos al personal dentro de Caja es insuficiente para control operativo fino.

## 7. Hallazgos fuertes detectados

## 7.1 La caja abierta puede arrastrar varios turnos sin que la UI lo explique

Caso real actual:

- sesión abierta: 11
- turno de sesión: 13
- turno abierto actual: 33
- ventas acumuladas en la misma caja desde turnos 13, 19, 23 y 28

Impacto:

- la cajera puede estar mirando acumulados de varios días/cortes sin saberlo claramente;
- el dueño puede interpretar mal el resultado del turno actual;
- varios indicadores dejan de ser “del turno” y pasan a ser “de la caja viva”.

## 7.2 Ingresos manuales depende de texto libre

La exclusión de ventas automáticas se hace por:

- `description not like 'Cobro comanda%'`
- `description not like 'Venta directa%'`

Eso es un criterio débil para contabilidad.

## 7.3 Egresos manuales no es manual

El cálculo suma todos los `EXPENSE`.

La etiqueta actual induce a error.

## 7.4 Cierre con QR/Tarjeta verificados no queda persistido estructuralmente

La UI pide más de lo que el backend realmente guarda.

## 7.5 Ingresos y Ventas duplican concepto en el resumen por método

En la práctica, para la mayoría de sesiones, ambas columnas muestran el mismo dinero con distinto nombre.

## 7.6 El close-check sigue mezclando turno y caja

Aunque las liquidaciones ya están bastante alineadas con `cash_session_id`, otros bloqueos relevantes siguen dependiendo de turno abierto o turno de sesión.

## 8. Información faltante que backend debería exponer con claridad

## 8.1 Ventas

Faltan o no se muestran claramente:

- venta bruta vs venta neta
- cantidad de ventas
- ticket promedio en dashboard principal de caja
- venta por hora
- venta por método en versión ejecutiva más clara

Nota: parte de esto ya se calcula en `CashCloseReportSectionsBuilder`, pero no se usa en la pantalla principal de Caja.

## 8.2 Ingresos

Debe separarse claramente:

- ingreso por ventas
- ingreso manual
- otros ingresos operativos

Hoy “ingresos” e “ingresos manuales” no son una taxonomía contable sólida.

## 8.3 Egresos

Debe separarse al menos en:

- pago a chicas
- pago a garzones
- pago limpieza
- gastos operativos
- compras
- otros egresos

Hoy todo eso puede caer en el mismo bloque de gasto.

## 8.4 Caja

Falta un modelo visible de:

- dinero inicial
- dinero recibido
- dinero pagado
- dinero esperado efectivo
- dinero esperado QR
- dinero esperado tarjeta
- saldo actual disponible
- disponible para liquidaciones
- disponible para gastos

## 8.5 Liquidaciones

Falta una lectura KPI simple de:

- pendientes chicas
- pendientes garzones
- pendientes limpieza
- total pendiente
- pagado hoy
- pendiente hoy

## 9. Tabla de auditoría de indicadores actuales

| Indicador actual | Significado real | ¿Está correcto? | ¿Es útil? | ¿Debe quedarse? | ¿Debe modificarse? | ¿Debe eliminarse? | ¿Debe dividirse? | Prioridad |
|---|---|---:|---:|---:|---:|---:|---:|---|
| Estado de caja | Estado OPEN/CLOSED de la sesión | Sí | Sí | Sí | No | No | No | Alta |
| Fondo inicial | Monto de apertura | Sí | Sí | Sí | No | No | No | Alta |
| Efectivo (ventas) | Ventas cobradas en cash | Sí | Sí | Sí | No | No | No | Alta |
| QR (ventas) | Ventas cobradas por QR | Sí | Sí | Sí | No | No | No | Alta |
| Tarjeta (ventas) | Ventas cobradas por tarjeta | Sí | Sí | Sí | No | No | No | Alta |
| Ingresos manuales | Ingresos no excluidos por texto de venta | Parcial | Parcial | Sí | Sí | No | Sí | Crítica |
| Egresos manuales | En realidad todos los egresos | No | Sí, pero mal nombrado | No en esta forma | Sí | No | Sí | Crítica |
| Total esperado | Efectivo esperado físico | Sí, pero ambiguo | Sí | Sí | Sí | No | No | Alta |
| Inicial por método | Apertura solo cash | Sí | Sí | Sí | No | No | No | Media |
| Ingresos por método | Todo INCOME por método, incluidas ventas | Parcial | Parcial | Sí | Sí | No | Sí | Alta |
| Ventas por método | Sale payments por método | Sí | Sí | Sí | No | No | No | Alta |
| Egresos por método | Todos los egresos por método | Sí | Sí | Sí | Sí | No | Sí | Alta |
| Esperado por método | Neto por método | Sí | Sí | Sí | Sí | No | No | Alta |
| Movimientos | Libro operativo de caja | Sí | Sí | Sí | Sí | No | No | Alta |
| Contado | Efectivo contado al cierre | Sí | Sí | Sí | No | No | No | Alta |
| Diferencia | Diferencia efectivo contado vs esperado | Sí | Sí | Sí | No | No | No | Alta |
| QR verificado | Verificación textual de QR | No estructuralmente | Parcial | No en su forma actual | Sí | No | Sí | Crítica |
| Tarjeta verificada | Verificación textual de tarjeta | No estructuralmente | Parcial | No en su forma actual | Sí | No | Sí | Crítica |
| Ventas totales | Suma de sale_payments de la sesión | Sí | Sí | Sí | No | No | No | Alta |
| Total expenses admin | Suma de egresos bajo etiqueta ambigua | Parcial | Sí | Sí | Sí | No | Sí | Alta |
| Cajas abiertas/cerradas | Conteo de sesiones | Sí | Sí | Sí | No | No | No | Media |
| Diferencia total admin | Suma de diferencias cash | Sí | Sí | Sí | No | No | No | Media |

## 10. Qué está bien

- La base de ventas y cobros está bien modelada en `sales` y `sale_payments`.
- La caja sí tiene bitácora de movimientos (`cash_movements`).
- El efectivo esperado se calcula con una lógica consistente para caja física.
- El cierre de caja bloquea pendientes importantes de liquidaciones.
- El detalle administrativo de sesión muestra ventas, movimientos y liquidaciones pagadas.

## 11. Qué está mal

- Se usan etiquetas que no representan el cálculo real (`egresos manuales`).
- La separación entre venta e ingreso manual depende de texto descriptivo.
- El arqueo de QR y tarjeta no se persiste estructuralmente.
- La sesión de caja puede arrastrar múltiples turnos sin señalización fuerte.
- El módulo mezcla lectura por caja con reglas parciales por turno.

## 12. Qué falta

- Conteo de ventas, ticket promedio y venta por hora en dashboard principal.
- Separación explícita de pagos al personal por rol.
- Saldo disponible para liquidaciones y saldo disponible para gasto.
- Pendientes operativos en formato KPI simple.
- Indicadores ejecutivos compactos para owner.

## 13. Qué sobra o puede inducir a error

- La coexistencia de “Ingresos” y “Ventas” cuando ambos muestran el mismo dinero.
- La etiqueta “manual” aplicada a egresos que no son manuales.
- La verificación QR/Tarjeta como si fuera un arqueo formal cuando en realidad termina en texto.

## 14. Recomendación de auditoría antes de seguir con módulos nuevos

Antes de seguir ampliando NightPOS, Caja necesita una corrección conceptual, no solo visual.

Prioridades más urgentes:

1. Separar formalmente ventas, ingresos manuales y otros ingresos.
2. Separar pagos al personal de gastos operativos.
3. Dejar explícito que `expected_cash` es efectivo físico, no total económico del turno.
4. Persistir arqueo QR/Tarjeta como estructura real si se quiere auditar esos métodos.
5. Normalizar la relación entre `cash_session` y `official_shift` para que los indicadores no mezclen caja viva con turno histórico sin advertencia.

## 15. Conclusión final

La información actual de Caja **no es suficiente ni completamente correcta para una operación real prolongada** si se la toma como verdad administrativa o contable integral.

Sí es suficiente para operar un flujo básico de cobro y cierre.
No es suficiente para una lectura robusta de negocio, fiscalización fina ni control contable limpio.

La principal causa no es la ausencia total de datos. La causa es que el módulo ya tiene muchos datos, pero hoy los presenta con **mezclas conceptuales, etiquetas ambiguas y un modelo híbrido caja/turno todavía no resuelto del todo**.
