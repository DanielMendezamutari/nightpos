# Rediseño conceptual del módulo Caja (Frontend / Dashboard / UX)

**Fecha:** 2026-07-10
**Modo:** diseño conceptual, sin implementación
**Base de partida:** `frontend/AUDITORIA_MODULO_CAJA.md`, `backend/AUDITORIA_MODULO_CAJA.md`, `backend/REDISENO_MODULO_CAJA.md`

## 1. Objetivo del rediseño frontend

Rediseñar Caja para que deje de sentirse como una mezcla de tarjetas y tablas heterogéneas y pase a funcionar como un **tablero financiero operativo** con dos cualidades:

1. lectura inmediata para la cajera,
2. lectura confiable para supervisión y dueño.

La UI nueva no debe agregar más ruido. Debe **simplificar la interpretación**.

## 2. Principio de diseño

La pantalla de Caja no debe responder veinte preguntas a la vez.

Debe responder solo estas cuatro, en este orden:

1. ¿Cuánto efectivo debería haber ahora?
2. ¿Qué me falta cobrar o pagar antes de cerrar?
3. ¿Cuánto vendí y cómo se cobró?
4. ¿Qué movimientos importantes ocurrieron?

## 3. Estructura nueva del Dashboard

## 3.1 Dashboard propuesto para cajera

### Bloque 1 — Caja física actual

Bloque grande, superior, dominante.

Debe mostrar:

- Fondo inicial
- Recibido en efectivo
- Pagado en efectivo
- Saldo esperado en efectivo
- Dinero contado, cuando aplique
- Diferencia

### Por qué va primero

Porque esta es la información que más necesita una cajera en operación real.

No necesita empezar mirando QR, tarjeta o conciliación de productos. Necesita saber si su caja física está sana.

## 3.2 Bloque 2 — Pendientes operativos

Bloque grande y muy visible.

Debe mostrar:

- Comandas pendientes de cobro
- Pendiente chicas
- Pendiente garzones
- Pendiente limpieza
- Total pendiente por pagar
- Piezas o bloqueos críticos

### Por qué va segundo

Porque es lo que define si la cajera puede cerrar o no, y qué trabajo urgente le queda.

## 3.3 Bloque 3 — Ventas del alcance actual

Bloque de negocio, no de arqueo.

Debe mostrar:

- Venta total
- Cantidad de ventas
- Ticket promedio
- Venta por método de pago
- opcional resumido: venta por hora

### Regla de diseño

No mezclar “ventas” con “ingresos de caja”.

## 3.4 Bloque 4 — Movimientos relevantes

Bloque de trazabilidad.

Debe mostrar:

- últimos movimientos
- total ingresos manuales
- total gastos operativos
- total pagos a personal
- último movimiento
- movimiento mayor

### Regla de diseño

No mostrar primero todo el libro completo si lo importante es la salud actual de caja. Primero el resumen; después la bitácora.

## 3.5 Bloque 5 — Conciliación y detalle extendido

Zona secundaria, colapsable o inferior.

Aquí sí pueden vivir:

- productos vendidos
- conciliación de productos
- combos/manillas
- detalle fino por método
- histórico reciente

### Regla de prioridad

Esto no debe ocupar el centro visual de la pantalla principal de caja operativa.

## 4. Layout recomendado

## 4.1 Vista cajera

Orden recomendado:

1. Caja física actual
2. Pendientes operativos
3. Ventas del alcance actual
4. Movimientos recientes
5. Conciliación secundaria

## 4.2 Vista supervisor

Orden recomendado:

1. Caja física
2. Pendientes
3. Pagos al personal
4. Ventas
5. Movimientos
6. Conciliación

## 4.3 Vista dueño

Orden recomendado:

1. Ventas
2. Rentabilidad operativa aproximada
3. Pagos al personal
4. Gastos operativos
5. Diferencia de arqueo
6. Alertas y pendientes
7. Caja física al detalle

## 5. Indicadores que deben conservarse

## 5.1 Para cajera

- Estado de caja
- Fondo inicial
- Saldo esperado en efectivo
- Dinero contado
- Diferencia
- Venta total
- Ventas por método
- Movimientos recientes
- Pendientes de liquidación

## 5.2 Para supervisor / dueño

- Venta total
- Cantidad de ventas
- Ticket promedio
- Pagos al personal
- Gastos operativos
- Diferencia de arqueo
- Acumulados por método

## 6. Indicadores que deben eliminarse del dashboard principal

No necesariamente eliminar del sistema, pero sí del dashboard principal de Caja.

- `Egresos manuales` como KPI unitario
- `Ingresos` y `Ventas` mostrados lado a lado si no están claramente diferenciados
- `Esperado / neto` como etiqueta híbrida
- conciliación de productos en una posición central de la pantalla principal

## 7. Indicadores que deben crearse visualmente

## 7.1 Para cajera

- Saldo esperado en efectivo
- Disponible para liquidaciones
- Disponible para gastos
- Total pendiente por pagar
- Comandas pendientes
- Movimiento más reciente

## 7.2 Para supervisor

- Pagado a chicas hoy
- Pagado a garzones hoy
- Pagado a limpieza hoy
- Gastos operativos hoy
- Total egresado hoy

## 7.3 Para dueño

- Cantidad de ventas
- Ticket promedio
- Venta por hora
- Venta por método
- Venta de servicios
- Pagos al personal
- Diferencia de arqueo

## 8. Qué indicadores sobran por duplicidad

## 8.1 Ingresos vs ventas

Si “ingresos” es simplemente la materialización en `cash_movements` del cobro de ventas, no debe convivir como KPI paralelo en la misma capa visual.

Regla recomendada:

- si es venta, mostrarlo en Ventas;
- si es movimiento no proveniente de venta, mostrarlo en Movimientos;
- si es dinero físico esperado, mostrarlo en Caja.

## 8.2 Egresos manuales

No debe existir como gran KPI porque hoy agrupa cosas incompatibles mentalmente:

- pago a chicas,
- pago a garzones,
- pago limpieza,
- gastos operativos,
- compras,
- otros.

## 9. Nueva navegación conceptual sugerida dentro de Caja

Sin implementar todavía, la UI futura de Caja debería organizarse en secciones lógicas.

### Pestañas o bloques recomendados

- `Resumen`
- `Caja física`
- `Ventas`
- `Movimientos`
- `Liquidaciones`
- `Detalle / conciliación`

### Regla UX

La pantalla de inicio debe ser `Resumen`, pero un resumen limpio, no una mezcla de todo.

## 10. UX del cierre de caja

## 10.1 Estado actual

El diálogo actual mezcla:

- efectivo contado,
- QR verificado,
- tarjeta verificada,
- diferencias por método,
- notas.

## 10.2 Rediseño conceptual recomendado

### Sección A — Arqueo efectivo

- saldo esperado efectivo
- efectivo contado
- diferencia

### Sección B — Conciliación medios no físicos

- QR esperado
- QR verificado
- tarjeta esperada
- tarjeta verificada

### Sección C — Pendientes operativos

- total pendiente de liquidaciones
- comandas/piezas bloqueantes

### Regla UX crítica

Si QR y tarjeta no son todavía parte de un arqueo estructurado en backend, la UI no debe presentarlos como si fueran equivalentes al arqueo de efectivo.

## 11. UX del problema caja vs turno

## 11.1 Problema visual actual

La misma caja puede arrastrar actividad de varios turnos y la pantalla no lo comunica claramente.

## 11.2 Rediseño mínimo obligatorio

La pantalla debe mostrar siempre un bloque de contexto como:

- Sesión de caja actual: `#11`
- Abierta en turno: `#13`
- Turnos con actividad incluidos: `13, 19, 23, 28`
- Turno operativo abierto actual: `#33`

## 11.3 Recomendación UX

Mientras la caja siga siendo independiente del turno, el contexto debe ser visible de manera obligatoria.

No como una nota pequeña. Como una advertencia estructural del módulo.

## 12. Recomendación de producto: caja por turno o caja continua

## Opción A — Caja continua independiente del turno

### UX a favor

- menos fricción para cajera
- continuidad operacional
- no obliga a abrir/cerrar caja por cada rotación

### UX en contra

- lectura difícil
- cortes poco claros
- más confusión administrativa
- más carga mental en cierre

## Opción B — Caja obligatoria por turno

### UX a favor

- lectura simple
- cierre limpio
- menos ambigüedad
- más natural para reportes

### UX en contra

- obliga a disciplina operativa
- puede sentirse más pesada si la rotación es frecuente

## Recomendación UX / producto para NightPOS

**Recomendación final: caja por turno como estado objetivo del producto.**

Razón:

La interfaz se vuelve mucho más comprensible para todos los perfiles cuando cada caja representa un solo corte temporal limpio.

Mientras eso no ocurra, la UI debe explicitar el arrastre multiturbo de la sesión.

## 13. Qué APIs deberían alimentar esta nueva UI

La UI futura no debería consumir un solo `financial_summary` ambiguo.

Necesita bloques separados:

- `sales_summary`
- `cash_summary`
- `movement_summary`
- `settlement_summary`
- `scope_summary`
- `reconciliation_summary`

## 14. Qué tablas deberían cambiar si se aprueba el rediseño

Desde la óptica frontend, las tablas más relevantes a evolucionar serían:

- `cash_movements` para categorías estructuradas
- `cash_sessions` para arqueo no físico y/o política de turno

## 15. Orden recomendado de implementación desde UX

## Fase UX-1

- redefinir taxonomía de datos que llegan a frontend
- eliminar KPIs ambiguos

## Fase UX-2

- nuevo bloque principal de caja física
- nuevo bloque de pendientes

## Fase UX-3

- separar claramente ventas y movimientos
- bajar conciliación a una zona secundaria

## Fase UX-4

- rediseñar cierre de caja
- mostrar contexto real caja/turno

## Fase UX-5

- adaptar vistas administrativas y agrupadas al nuevo modelo

## 16. Conclusión

El rediseño de Caja no necesita más tarjetas. Necesita más claridad conceptual.

La pantalla actual permite trabajar, pero obliga a pensar demasiado.

La pantalla futura debe permitir leer, en segundos:

- cuánto efectivo debería haber,
- cuánto falta cobrar o pagar,
- cuánto se vendió,
- y qué movimientos explican el estado actual.

## 17. Respuesta final sintetizada

### Nueva arquitectura conceptual del módulo Caja
- Ventas
- Caja física
- Movimientos
- Liquidaciones

### Nueva estructura del Dashboard
- 4 bloques grandes: Caja física, Pendientes, Ventas, Movimientos

### Qué indicadores conservar
- estado, fondo inicial, ventas por método, total ventas, contado, diferencia, movimientos

### Qué indicadores eliminar
- egresos manuales como KPI agregado, duplicidad ingresos/ventas, mezcla esperado/neto

### Qué indicadores crear
- ticket promedio, cantidad de ventas, saldo disponible, pagos por rol, pendientes por rol, venta por hora, movimiento mayor

### Qué tablas deberían cambiar
- principalmente `cash_movements` y `cash_sessions`

### Qué APIs deberían modificarse
- resúmenes de caja separados por dominio funcional

### Qué migraciones serían necesarias
- categorías estructuradas, arqueo QR/Tarjeta, política opcional caja-turno

### Orden recomendado de implementación
1. taxonomía backend
2. payloads por dominio
3. dashboard cajera
4. dashboard admin/dueño
5. cierre de caja
6. política final caja vs turno
