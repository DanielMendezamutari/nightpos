# Auditoría completa del módulo de Caja (Frontend)

**Fecha:** 2026-07-10
**Alcance:** pantalla de Caja, apertura, cierre, dashboard operativo, fiscalización administrativa y experiencia de uso
**Modo:** auditoría solamente, sin implementación

## 1. Pregunta rectora

¿La información que hoy muestra Caja es suficiente, correcta y útil para una operación real de NightPOS?

Respuesta corta: **sirve para operar, pero no para decidir bien**.

La UI actual permite trabajar. Una cajera puede:

- abrir caja,
- cobrar,
- registrar ingresos y egresos manuales,
- ver movimientos,
- intentar cerrar,
- detectar liquidaciones pendientes.

Pero la pantalla no explica bien qué parte de la información es:

- venta,
- movimiento,
- efectivo real,
- verificación de métodos no físicos,
- pago de personal,
- gasto operativo,
- arrastre histórico de una misma caja abierta por varios turnos.

Desde UX y lectura administrativa, el problema no es que falten datos en bruto. El problema es que **la jerarquía visual, los nombres y los agrupamientos no están alineados con cómo una cajera o un dueño piensan la caja real**.

## 2. Superficies auditadas

### Pantalla operativa principal

- `frontend/src/pages/nightpos/cash/index.vue`

### Fiscalización administrativa

- `frontend/src/pages/nightpos/finance/cash-sessions/index.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/summary.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/[id].vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/history.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/by-cashier.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/by-shift.vue`

### Integraciones visibles relacionadas

- `frontend/src/components/nightpos/reports/ProductReconciliationPanel.vue`
- `frontend/src/components/nightpos/reports/ComboBraceletSummaryPanel.vue`
- `frontend/src/api/cash.js`
- `frontend/src/api/adminCashSessions.js`
- `frontend/src/composables/useAdminCashSessionsList.js`

## 3. Qué ve hoy la cajera en Caja

La pantalla principal de Caja muestra, en este orden:

1. Título y acciones rápidas
- Venta directa
- Imprimir arqueo

2. KPI cards
- Estado de caja
- Fondo inicial
- Efectivo (ventas)
- QR (ventas)
- Tarjeta (ventas)
- Ingresos manuales
- Egresos manuales
- Total esperado

3. Resumen por método de pago
- Método
- Inicial
- Ingresos
- Ventas
- Egresos
- Esperado / neto

4. Movimientos de caja
- Tipo
- Monto
- Método
- Descripción
- Fecha

5. Productos vendidos
- Panel de conciliación de productos
- Panel de combos/manillas cuando aplica

6. Acciones principales
- Ingreso / egreso manual
- Cerrar caja

7. Diálogo de cierre
- resumen por método
- efectivo contado
- QR verificado
- tarjeta verificada
- diferencias por método
- notas de cierre

## 4. Lectura UX real de una cajera de 12 horas

Si yo fuera la cajera, lo primero que miraría sería esto:

1. cuánto efectivo debería tener realmente en la mano;
2. cuánto tengo pendiente por pagar en liquidaciones;
3. si hay comandas o piezas que me van a bloquear el cierre;
4. cuánto vendí hoy y por qué medios;
5. si la caja actual corresponde al turno que creo estar trabajando.

La pantalla actual no entrega esa lectura con suficiente claridad.

### Lo que una cajera probablemente miraría primero

- Total esperado
- Liquidaciones pendientes
- Ventas en efectivo
- Botón de cierre
- Bloqueos de cierre

### Lo que probablemente nunca usaría de forma frecuente

- Productos vendidos dentro de la pantalla de Caja, salvo cuando ya exista un problema operativo puntual.
- El detalle de QR y tarjeta como “verificación manual” si ese dato no queda registrado formalmente.
- La distinción entre “Ingresos” y “Ventas” si ambos muestran casi lo mismo.

### Lo que falta para la cajera

- un indicador grande y obvio de “efectivo disponible ahora”;
- un total claro de pagos pendientes al personal;
- una advertencia visible si la caja sigue abierta desde otro turno o desde otro día;
- un resumen simple de “recibido”, “pagado” y “saldo actual”.

## 5. Análisis visual: orden, jerarquía y relevancia

## 5.1 Lo primero que aparece no es lo más importante

La jerarquía actual pone al mismo nivel:

- Estado de caja
- Fondo inicial
- Efectivo
- QR
- Tarjeta
- Ingresos manuales
- Egresos manuales
- Total esperado

Desde UX operativa, esto tiene dos problemas:

1. El indicador más importante para la cajera, que es el efectivo esperado, aparece demasiado tarde.
2. Se le da el mismo peso visual a métricas secundarias que a métricas críticas.

## 5.2 Hay repetición conceptual

El resumen por método muestra simultáneamente:

- Ingresos
- Ventas

Desde la UI no se explica que en muchos casos ambas columnas representan el mismo dinero por dos rutas distintas del backend.

Eso genera una sensación de doble conteo aunque técnicamente no siempre lo sea.

## 5.3 Hay información irrelevante o prematura para cajera

El panel de “Productos vendidos” y conciliación puede ser útil para auditoría operativa, pero no es de primera prioridad en una pantalla cuyo objetivo primario es cobrar y cerrar.

No sobra funcionalmente, pero sí sobra en prioridad visual dentro de la pantalla principal de Caja.

## 5.4 Falta una separación visual fuerte entre operación y control

La UI mezcla en una sola lectura:

- operación de cobro,
- control de efectivo,
- conciliación por método,
- revisión de productos,
- preparación de cierre.

Eso obliga a la cajera a interpretar demasiado, en vez de leer lo crítico de un vistazo.

## 6. Hallazgos UX específicos por indicador visible

## 6.1 Estado de caja

Está bien. Es claro y útil.

Problema residual:
- no informa si la caja abierta ya quedó desfasada respecto del turno vigente.

## 6.2 Fondo inicial

Está bien. Es un ancla clara para la cajera y para auditoría.

## 6.3 Efectivo / QR / Tarjeta (ventas)

Son útiles.

Problema:
- La UI no explica que estos son cobros de venta y no saldo disponible.

## 6.4 Ingresos manuales

La UI los presenta como si fueran una categoría sólida y confiable.

Problema UX:
- el usuario no tiene ninguna pista de que backend los separa por texto descriptivo y no por categoría contable robusta.

## 6.5 Egresos manuales

La UI induce a error.

Problema:
- el backend suma todos los egresos, no solo los manuales.
- Por tanto la etiqueta es visualmente engañosa.

## 6.6 Total esperado

Es el indicador que más importa a la cajera, pero no está tratado como el dato central de la pantalla.

Problema:
- el nombre puede interpretarse como total general del turno, cuando en la práctica representa efectivo esperado.

## 6.7 Resumen por método

Tiene valor, pero hoy exige demasiada interpretación.

Problemas visuales:
- “Ingresos” vs “Ventas” no es autoexplicativo.
- “Esperado / neto” mezcla dos ideas en un mismo label.
- La tabla es densa para la prioridad operativa que tiene la cajera.

## 6.8 Movimientos

La tabla es útil y necesaria.

Problemas:
- no hay resumen visible de cantidad de ingresos, cantidad de egresos, último movimiento o mayor movimiento;
- la descripción hace de clasificador semántico y eso no siempre es obvio ni confiable para el usuario.

## 6.9 Diálogo de cierre

Tiene una intención buena: preparar el arqueo antes de cerrar.

Hallazgo fuerte:
- el diálogo hace creer que QR y tarjeta están siendo arqueados formalmente,
- pero en realidad esos datos solo terminan en notas de cierre.

Desde UX, esto es delicado. La interfaz promete más control del que realmente persiste el sistema.

## 7. Análisis administrativo: mirada del dueño

Como dueño, al abrir Caja yo querría ver inmediatamente:

- ventas del turno o de la sesión actual;
- efectivo disponible real;
- pagos ya realizados al personal;
- pendientes por pagar al personal;
- gastos operativos del turno;
- diferencia de arqueo;
- cuántas ventas hubo y ticket promedio;
- si esta caja sigue arrastrando datos de otro turno.

La UI administrativa actual ayuda, pero no alcanza.

### Qué está bien para administración

- existe fiscalización por lista, resumen, detalle, historial, por cajera y por turno;
- se puede ver efectivo esperado, QR, tarjeta, ventas, diferencia;
- el detalle de caja muestra movimientos, ventas y liquidaciones pagadas.

### Qué sobra o distrae para administración

- la agregación por cajera y por turno reutiliza indicadores que ya vienen ambiguos de backend;
- “egresos” y “esperado” no siempre están semánticamente limpios para análisis ejecutivo.

### Qué falta para administración

- separación de egresos por categoría real;
- pagos al personal por rol en KPI claro;
- ticket promedio y cantidad de ventas en los resúmenes superiores;
- aclaración visual de que `expected_cash` es efectivo, no total global;
- relación entre caja viva y turnos involucrados.

## 8. Análisis contable desde la UI

## 8.1 Ventas vs ingresos

La pantalla no separa con suficiente claridad:

- venta cobrada,
- ingreso de caja,
- ingreso manual.

Aunque el backend pueda distinguir parcialmente, la interfaz no lo comunica.

## 8.2 Egresos vs pagos al personal

La pantalla no separa visualmente:

- pago a chicas,
- pago a garzones,
- pago limpieza,
- gastos operativos,
- compras,
- otros egresos.

Eso es una mezcla conceptual relevante para cualquier lectura contable.

## 8.3 Caja física vs métodos no físicos

La UI pone en la misma conversación:

- efectivo esperado,
- QR,
- tarjeta,
- diferencias por método.

Pero solo el efectivo queda registrado como arqueo formal de caja.

Por tanto la interfaz es más ambiciosa que el modelo persistido.

## 9. Información faltante que debería existir o verse mejor

## 9.1 Ventas

Falta mostrar con claridad:

- venta bruta
- venta neta
- cantidad de ventas
- ticket promedio
- venta por hora
- venta por método de pago

## 9.2 Ingresos

Falta separar visualmente:

- ingreso por ventas
- ingreso manual
- multas
- limpieza
- otros ingresos

## 9.3 Egresos

Falta separar:

- pago a chicas
- pago a garzones
- pago limpieza
- gastos operativos
- compras
- otros egresos

## 9.4 Movimientos

Falta resumir:

- cantidad de ingresos
- cantidad de egresos
- último movimiento
- movimiento mayor
- movimientos pendientes de revisión

## 9.5 Caja

Falta lectura directa de:

- dinero inicial
- dinero recibido
- dinero pagado
- dinero esperado
- dinero contado
- diferencia
- saldo actual
- disponible para pagar liquidaciones
- disponible para gastos

## 9.6 Liquidaciones

Falta mostrar de forma prominente:

- pendientes chicas
- pendientes garzones
- pendientes limpieza
- total pendiente
- pagado hoy
- pendiente hoy

## 9.7 Reportes administrativos mínimos dentro de Caja

Faltaría evaluar mostrar:

- ventas del turno
- rentabilidad del turno
- personal pendiente
- habitaciones vendidas
- servicios vendidos
- productos vendidos como KPI resumido, no como panel principal

## 10. Errores de diseño detectados

## 10.1 Venta Total e Ingresos no están suficientemente separados

La UI permite leerlos como conceptos distintos y también como duplicados, dependiendo del contexto. Eso ya es una falla de diseño.

## 10.2 Ingresos incluye ventas cuando la interfaz no lo explica

En el resumen por método, “Ingresos” puede contener cobros por ventas y por eso visualmente compite con “Ventas”.

## 10.3 Egresos manuales está mal nombrado

El usuario recibe una promesa semántica incorrecta.

## 10.4 El cierre parece auditar QR y tarjeta formalmente, pero no es así

Desde UX esto es especialmente riesgoso porque da una falsa sensación de control.

## 10.5 No hay advertencia fuerte de sesión arrastrada entre turnos

Este es uno de los hallazgos más importantes de toda la auditoría.

En la base real auditada, una misma caja abierta acumula ventas de varios official shifts. La UI actual no expone eso de manera frontal.

## 11. Tabla de auditoría visual y funcional

| Indicador actual | Significado visible para usuario | ¿Está correcto? | ¿Es útil? | ¿Debe quedarse? | ¿Debe modificarse? | ¿Debe eliminarse? | ¿Debe dividirse? | Prioridad |
|---|---|---:|---:|---:|---:|---:|---:|---|
| Estado de caja | Abierta / cerrada | Sí | Sí | Sí | No | No | No | Alta |
| Fondo inicial | Dinero de apertura | Sí | Sí | Sí | No | No | No | Alta |
| Efectivo (ventas) | Cobros cash | Sí | Sí | Sí | No | No | No | Alta |
| QR (ventas) | Cobros QR | Sí | Sí | Sí | No | No | No | Alta |
| Tarjeta (ventas) | Cobros tarjeta | Sí | Sí | Sí | No | No | No | Alta |
| Ingresos manuales | Entradas no explicadas del todo | Parcial | Parcial | Sí | Sí | No | Sí | Crítica |
| Egresos manuales | En realidad egresos totales | No | Sí | No en esta forma | Sí | No | Sí | Crítica |
| Total esperado | Efectivo esperado | Parcial por ambigüedad de nombre | Sí | Sí | Sí | No | No | Alta |
| Resumen por método | Balance por método | Parcial | Sí | Sí | Sí | No | Sí | Alta |
| Ingresos por método | Puede duplicar ventas | Parcial | Parcial | Sí | Sí | No | Sí | Alta |
| Ventas por método | Ventas cobradas por método | Sí | Sí | Sí | No | No | No | Alta |
| Egresos por método | Egresos mezclados | Parcial | Sí | Sí | Sí | No | Sí | Alta |
| Esperado por método | Neto por método | Sí | Sí | Sí | Sí | No | No | Alta |
| Movimientos | Bitácora operativa | Sí | Sí | Sí | Sí | No | No | Alta |
| Productos vendidos | Panel de conciliación | Sí | Parcial para cajera | Sí | Sí | No | No | Media |
| Contado | Efectivo contado | Sí | Sí | Sí | No | No | No | Alta |
| Diferencia efectivo | Diferencia cash | Sí | Sí | Sí | No | No | No | Alta |
| QR verificado | Verificación visual no persistida estructuralmente | No | Parcial | No en esta forma | Sí | No | Sí | Crítica |
| Tarjeta verificada | Verificación visual no persistida estructuralmente | No | Parcial | No en esta forma | Sí | No | Sí | Crítica |
| Cajas abiertas/cerradas | Conteo admin | Sí | Sí | Sí | No | No | No | Media |
| Diferencia total | Suma de diferencias cash | Sí | Sí | Sí | No | No | No | Media |
| Ventas totales admin | Total ventas acumuladas | Sí | Sí | Sí | Sí | No | No | Media |
| Egresos totales admin | Total de egresos sin separación fuerte | Parcial | Sí | Sí | Sí | No | Sí | Alta |

## 12. Qué está bien

- La pantalla principal sí permite operar la caja de punta a punta.
- Los movimientos están visibles y son legibles.
- El cierre bloquea pendientes críticos antes de cerrar.
- La fiscalización administrativa tiene varias vistas útiles.
- La diferencia de efectivo existe y se muestra de forma comprensible.

## 13. Qué está mal

- La jerarquía visual no prioriza lo más importante para la cajera.
- Hay etiquetas que no representan bien el cálculo real.
- Se mezclan ventas, ingresos y movimientos sin suficiente explicación.
- La UI promete arqueo QR/Tarjeta más formal del que realmente persiste el backend.
- No se hace evidente que una caja abierta puede estar mezclando varios turnos.

## 14. Qué falta

- saldo disponible claro;
- pendientes de liquidación en KPI principal;
- conteo de ventas y ticket promedio;
- separación visual de egresos por categoría;
- lectura ejecutiva de negocio dentro de Caja para dueño/supervisión.

## 15. Qué sobra o puede inducir a errores

- “Ingresos” y “Ventas” en paralelo sin aclaración;
- “Egresos manuales” como etiqueta engañosa;
- el panel de productos vendidos en una posición demasiado protagónica para la operación de caja;
- el pseudo-arqueo QR/Tarjeta sin persistencia estructurada.

## 16. Qué recomendaría mejorar antes de seguir creando más módulos

Sin entrar todavía a implementación, la prioridad debería ser:

1. Reordenar la lectura principal de Caja alrededor de efectivo esperado, pagos pendientes y saldo disponible.
2. Separar visualmente ventas, ingresos manuales, pagos al personal y gastos operativos.
3. Hacer explícito cuándo la caja arrastra actividad de varios turnos.
4. Alinear la UI de cierre con lo que el backend realmente persiste.
5. Reducir duplicación conceptual en el bloque por método de pago.

## 17. Conclusión final

La UI actual de Caja **es operable**, pero todavía no es lo bastante clara ni rigurosa para una operación real larga, una supervisión administrativa exigente o una lectura contable limpia.

Está bien como módulo transaccional básico.
No está bien todavía como tablero confiable de control operativo y administrativo.

El mayor riesgo no es visual solamente. El mayor riesgo es que la interfaz hoy simplifica o mezcla conceptos que en una noche real de operación sí cambian decisiones de pago, cierre y control del negocio.
