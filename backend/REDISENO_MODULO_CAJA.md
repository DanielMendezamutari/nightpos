# Rediseño conceptual del módulo Caja (Backend / Arquitectura funcional)

**Fecha:** 2026-07-10
**Modo:** diseño conceptual, sin implementación
**Base de partida:** `backend/AUDITORIA_MODULO_CAJA.md`, `frontend/AUDITORIA_MODULO_CAJA.md`, MySQL real `nigtpos`

## 1. Objetivo del rediseño

Convertir Caja en el centro financiero operativo de NightPOS sin cambiar todavía la lógica de negocio existente.

La meta no es recalcular el negocio desde cero, sino **separar correctamente cómo se representan los datos** para que:

- la cajera entienda qué tiene en mano,
- el supervisor entienda qué pasó en la sesión,
- el dueño entienda qué pasó en el negocio,
- y el sistema no mezcle ventas, dinero físico, movimientos, liquidaciones y arqueo en los mismos indicadores.

## 2. Principio rector del nuevo diseño

Caja no debe ser un bloque único de KPIs mezclados.

Debe dividirse en cuatro dominios visuales y semánticos completamente separados:

1. **Ventas**
- Todo lo vendido.
- Fuente de verdad: `sales`, `sale_payments`, `sale_items`.

2. **Caja física / arqueo**
- Solo dinero físicamente esperable y contado.
- Fuente de verdad: `cash_sessions` + movimientos `CASH`.

3. **Movimientos**
- Libro operativo de entradas y salidas.
- Fuente de verdad: `cash_movements`.

4. **Liquidaciones**
- Obligaciones con personal y su estado.
- Fuente de verdad: `staff_settlements` + `staff_settlement_adjustments`.

## 3. Nueva arquitectura conceptual del módulo Caja

## 3.1 Dominio 1 — Ventas

### Qué debe contener

- Venta total del alcance actual
- Cantidad de ventas
- Ticket promedio
- Venta por método de pago
- Venta por hora
- Productos vendidos
- Servicios vendidos
- Habitaciones vendidas

### Qué no debe contener

- Ingresos manuales
- Egresos
- Arqueo
- Diferencia de caja
- Liquidaciones pendientes

### Fuente recomendada

- `sales`
- `sale_payments`
- `sale_items`
- complementos: `room_services`, `shows`, `bracelets` solo si se expresan como venta y no como KPI de caja física

### Regla conceptual

**Ventas no es caja.**

Ventas responde a:
- cuánto se vendió,
- cómo se cobró,
- qué se vendió,
- cuándo se vendió.

No responde a:
- cuánto efectivo físico queda,
- cuánto se pagó,
- si la caja cuadra.

## 3.2 Dominio 2 — Caja física / arqueo

### Qué debe contener

- Fondo inicial
- Ingresos en efectivo
- Egresos en efectivo
- Saldo esperado en efectivo
- Dinero contado
- Diferencia de arqueo
- Saldo actual estimado en efectivo

### Qué no debe contener

- QR como si fuera efectivo físico
- Tarjeta como si fuera efectivo físico
- Venta total del turno
- Liquidaciones mezcladas en el mismo KPI

### Fórmula objetivo

$$
Saldo\ esperado\ de\ caja = Fondo\ inicial + Ingresos\ en\ efectivo - Egresos\ en\ efectivo
$$

### Regla conceptual

Caja física debe responder solo a una pregunta:

**¿Cuánto dinero en efectivo debería haber físicamente en caja?**

QR y tarjeta pueden mostrarse como conciliación de medios no físicos, pero no deben contaminar el KPI principal de arqueo.

## 3.3 Dominio 3 — Movimientos

### Qué debe contener

Libro contable-operativo de movimientos con clasificación obligatoria.

Cada movimiento debe pertenecer a una categoría estructurada.

### Categorías funcionales recomendadas

#### Ingresos
- `SALE_COLLECTION`
- `DIRECT_SALE_COLLECTION`
- `MANUAL_INCOME`
- `OTHER_INCOME`

#### Egresos
- `SETTLEMENT_GIRL_PAYMENT`
- `SETTLEMENT_WAITER_PAYMENT`
- `SETTLEMENT_CLEANING_PAYMENT`
- `OPERATING_EXPENSE`
- `PURCHASE`
- `OTHER_EXPENSE`

### Qué debe responder este bloque

- Cantidad de ingresos
- Cantidad de egresos
- Último movimiento
- Movimiento mayor
- Totales por categoría
- Totales por método de pago

### Regla conceptual

**Ningún movimiento debe clasificarse por texto libre.**

La UI puede seguir mostrando descripciones humanas, pero el agrupamiento funcional debe depender de campos estructurados.

## 3.4 Dominio 4 — Liquidaciones

### Qué debe contener

- Pendiente chicas
- Pendiente garzones
- Pendiente limpieza
- Total pendiente
- Pagado hoy
- Pendiente hoy
- Conteo por tipo

### Qué no debe contener

- Gastos operativos
- Compras
- Ingresos
- Ventas

### Regla conceptual

Liquidaciones son un submódulo financiero propio. No deben diluirse en “egresos manuales”.

## 4. Nueva estructura conceptual del Dashboard

## 4.1 Dashboard operativo de cajera

### Bloque A — Caja física

Grande, prioritario, arriba.

Debe mostrar:

- Fondo inicial
- Recibido en efectivo
- Pagado en efectivo
- Saldo esperado
- Dinero contado cuando aplica
- Diferencia

### Bloque B — Pendientes operativos

Debe mostrar:

- Liquidaciones pendientes por rol
- Comandas pendientes de cobro
- Piezas activas / bloqueantes

### Bloque C — Ventas del alcance actual

Debe mostrar:

- Venta total
- Cantidad de ventas
- Ticket promedio
- Venta por método

### Bloque D — Movimientos recientes

Debe mostrar:

- últimos movimientos,
- filtros por categoría,
- totales rápidos por tipo.

## 4.2 Dashboard de supervisor

Debe heredar la visión de cajera y sumar:

- agregados de pagos al personal,
- egresos por categoría,
- conciliación de medios no físicos,
- cierre parcial de sesión.

## 4.3 Dashboard del dueño

No debe empezar por caja física.

Debe empezar por:

- venta total,
- utilidad operativa aproximada,
- pagos al personal,
- gastos,
- diferencia de arqueo,
- pendientes críticos,
- dispersión por método de pago.

## 5. Indicadores a conservar

## 5.1 Conservar sin cambios conceptuales

- Estado de caja
- Fondo inicial
- Efectivo ventas
- QR ventas
- Tarjeta ventas
- Total ventas
- Movimientos
- Dinero contado
- Diferencia de efectivo

## 5.2 Conservar pero reetiquetar o reubicar

- `Total esperado` → debe pasar a llamarse **Saldo esperado en efectivo**
- `Ingresos manuales` → debe pasar a **Ingresos no provenientes de venta**
- `Egresos manuales` → debe desaparecer como nombre y dividirse por categoría
- `Resumen por método` → debe reestructurarse como conciliación por método, no como mezcla de ventas e ingresos

## 6. Indicadores a eliminar

No necesariamente eliminar del backend, pero sí del dashboard tal como hoy existen.

- `Egresos manuales` como indicador único
- `Ingresos` y `Ventas` simultáneos si representan la misma masa monetaria
- `Esperado / neto` como etiqueta mixta

## 7. Indicadores a crear

## 7.1 Ventas

- Cantidad de ventas
- Ticket promedio
- Venta por hora
- Venta por método de pago
- Productos vendidos
- Servicios vendidos

## 7.2 Caja física

- Recibido en efectivo
- Pagado en efectivo
- Saldo esperado en efectivo
- Saldo contado
- Diferencia de arqueo
- Disponible para liquidaciones
- Disponible para gastos

## 7.3 Movimientos

- Ingresos manuales
- Gastos operativos
- Compras
- Pago chicas
- Pago garzones
- Pago limpieza
- Otros egresos
- Último movimiento
- Movimiento mayor

## 7.4 Liquidaciones

- Pendientes chicas
- Pendientes garzones
- Pendientes limpieza
- Pagado hoy
- Pendiente total

## 8. Tabla resumen de indicadores

| Indicador | Conservar | Crear | Eliminar | Observación |
|---|---:|---:|---:|---|
| Estado de caja | Sí | No | No | Operativo |
| Fondo inicial | Sí | No | No | Base de arqueo |
| Venta total | Sí | No | No | Va al bloque Ventas |
| Cantidad de ventas | No | Sí | No | Falta |
| Ticket promedio | No | Sí | No | Falta |
| Efectivo ventas | Sí | No | No | Mantener en Ventas, no en Caja física principal |
| QR ventas | Sí | No | No | Mantener como método de venta |
| Tarjeta ventas | Sí | No | No | Mantener como método de venta |
| Ingresos manuales | Sí, redefinido | No | No | Debe depender de categoría estructurada |
| Egresos manuales | No | No | Sí | Debe dividirse |
| Saldo esperado | Sí, renombrado | No | No | Debe explicitar efectivo |
| Dinero contado | Sí | No | No | Arqueo |
| Diferencia | Sí | No | No | Arqueo |
| Pago chicas | No | Sí | No | Desde movimientos/liquidaciones |
| Pago garzones | No | Sí | No | Desde movimientos/liquidaciones |
| Pago limpieza | No | Sí | No | Desde movimientos/liquidaciones |
| Gastos operativos | No | Sí | No | Desde movimientos |
| Compras | No | Sí | No | Desde movimientos |
| Pendiente chicas | No | Sí | No | Desde liquidaciones |
| Pendiente garzones | No | Sí | No | Desde liquidaciones |
| Pendiente limpieza | No | Sí | No | Desde liquidaciones |
| Pagado hoy | No | Sí | No | Desde liquidaciones |

## 9. Debate estructural: Caja independiente del turno vs caja obligatoria por turno

## Opción A — Caja independiente del turno

### Ventajas

- refleja mejor la operación real cuando la cajera continúa trabajando;
- evita fricción si el turno cambia y la caja sigue abierta;
- es compatible con el estado actual del sistema y la base real;
- simplifica continuidad operativa en locales con rotación horaria automática.

### Desventajas

- mezcla actividad de varios turnos dentro de una sola sesión financiera;
- complica reportes por corte;
- dificulta arqueo por turno;
- obliga a explicar siempre qué pertenece a la caja y qué pertenece al turno.

## Opción B — Una caja nueva por cada turno

### Ventajas

- contabilidad más limpia;
- arqueo más simple;
- reportes por turno más naturales;
- reduce ambigüedad entre caja y turno;
- simplifica supervisión y cierre.

### Desventajas

- mayor fricción operativa;
- requiere disciplina estricta de cierre/apertura en cada rotación;
- puede ser incómodo si la operación real no corta físicamente la caja en cada turno.

## Recomendación para NightPOS

### Recomendación estratégica final

**Objetivo final recomendado: Opción B, una caja por turno.**

Razón principal:

Si Caja va a convertirse en el centro financiero del sistema, su unidad de tiempo debe ser limpia. La relación 1 turno = 1 caja es mucho más sólida para:

- arqueo,
- control gerencial,
- reportes,
- conciliación,
- pagos,
- auditoría.

### Recomendación táctica de transición

Mientras no se cambie la lógica actual, debe asumirse explícitamente un modelo híbrido temporal:

- Caja sigue siendo independiente del turno en backend.
- La UI debe declarar visiblemente:
  - sesión actual,
  - turno de apertura,
  - turnos con actividad acumulada,
  - alcance de datos mostrado.

## 10. Tablas que deberían cambiar

## 10.1 `cash_movements`

Agregar o formalizar clasificación estructurada.

Campos recomendados:

- `movement_category` o equivalente
- opcional: `movement_family` (`SALE`, `MANUAL`, `SETTLEMENT`, `EXPENSE`)
- opcional: `related_entity_type` normalizado
- opcional: `related_entity_id`

## 10.2 `cash_sessions`

Si se adopta caja por turno:

- reforzar relación 1 sesión activa por turno y/o por cajera según política final
- opcional: `session_scope_type` si se decide soportar modos `SHIFT` vs `CONTINUOUS`

## 10.3 `sales`

No requiere cambio estructural urgente para este rediseño conceptual.

## 10.4 `staff_settlements`

No requiere cambio urgente, pero su lectura debe exponerse mejor por rol y estado.

## 10.5 Persistencia de arqueo no físico

Si se quiere auditar QR/Tarjeta formalmente, harían falta campos estructurados en `cash_sessions`, por ejemplo:

- `declared_qr_amount`
- `declared_card_amount`
- `difference_qr_amount`
- `difference_card_amount`

## 11. APIs que deberían modificarse

## 11.1 `GET /cash/session/current`

Debe devolver bloques separados:

- `sales_summary`
- `cash_summary`
- `movement_summary`
- `settlement_summary`
- `scope_summary`

No un solo `financial_summary` mezclado.

## 11.2 `GET /cash/session/current/close-check`

Debe aclarar explícitamente si cada bloqueo se evalúa por:

- caja,
- turno,
- ambos.

## 11.3 `GET /admin/cash-sessions/{id}`

Debe devolver secciones financieras separadas y no solo resúmenes reusados de la vista operativa.

## 11.4 APIs admin resumen

- `/admin/cash-sessions/summary`
- agrupados por cajera
- agrupados por turno

Deben agregarse sobre la nueva taxonomía y no sobre KPIs ambiguos.

## 11.5 API de registro de movimiento

Debe aceptar y persistir una categoría estructurada, no solo tipo + razón + descripción.

## 12. Migraciones necesarias

Si se aprobara el rediseño:

1. Migración para categorías estructuradas de movimientos
2. Migración para arqueo estructurado de QR/Tarjeta
3. Migración opcional de política de sesión por turno
4. Backfill de movimientos históricos según `source_type`, `cash_movement_reason_id`, `settlement_type` y reglas de negocio

## 13. Orden recomendado de implementación

## Fase 1 — Taxonomía financiera

- Definir categorías oficiales de movimientos
- Separar semánticamente ventas, caja, movimientos y liquidaciones
- Ajustar payloads backend sin romper compatibilidad

## Fase 2 — Nuevo backend summary

- Exponer nuevos bloques:
  - `sales_summary`
  - `cash_summary`
  - `movement_summary`
  - `settlement_summary`
  - `scope_summary`

## Fase 3 — Nuevo dashboard frontend

- Reordenar la UI en bloques grandes
- Eliminar duplicados conceptuales
- Priorizar visión cajera

## Fase 4 — Fiscalización administrativa

- Rehacer vistas admin sobre nueva taxonomía
- separar arqueo, ventas y liquidaciones

## Fase 5 — Política caja vs turno

- Decidir y aplicar modelo final
- ideal recomendado: una caja por turno

## 14. Conclusión

La arquitectura actual de Caja no está totalmente rota, pero sí está semánticamente mezclada.

El rediseño recomendado no debe empezar por la UI. Debe empezar por una **nueva arquitectura conceptual de datos financieros**:

- Ventas
- Caja física
- Movimientos
- Liquidaciones

Con esa separación, recién después el dashboard puede volverse claro, confiable y útil.

## 15. Respuesta final sintetizada

### Nueva arquitectura conceptual del módulo Caja
- separar Ventas, Caja física, Movimientos y Liquidaciones

### Nueva estructura del Dashboard
- pocos bloques grandes: Caja física, Pendientes, Ventas, Movimientos

### Qué indicadores conservar
- estado, apertura, ventas por método, total ventas, contado, diferencia, movimientos

### Qué indicadores eliminar
- egresos manuales como KPI único
- duplicidad ingresos/ventas

### Qué indicadores crear
- ticket promedio, cantidad de ventas, pagos por rol, pendientes por rol, saldo disponible, movimiento mayor, venta por hora

### Qué tablas deberían cambiar
- principalmente `cash_movements` y posiblemente `cash_sessions`

### Qué APIs deberían modificarse
- `cash/session/current`, `close-check`, `admin/cash-sessions/*`, registro de movimientos

### Qué migraciones serían necesarias
- categorías estructuradas, arqueo QR/Tarjeta, backfill histórico, eventual política caja-turno

### Orden recomendado de implementación
1. taxonomía financiera
2. payloads backend
3. dashboard frontend
4. fiscalización admin
5. política final caja vs turno
