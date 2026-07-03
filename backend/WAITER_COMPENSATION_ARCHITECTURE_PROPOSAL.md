# WAITER_COMPENSATION_ARCHITECTURE_PROPOSAL.md

Fecha: 2026-07-02  
Estado: Propuesta de arquitectura (sin implementacion)

## 1) Contexto

NightPOS hoy opera liquidaciones de garzon con un unico modelo:

Ventas -> porcentaje -> comision automatica

Esto funciona para perfiles con porcentaje mayor a 0, pero falla funcionalmente para operacion real en tres escenarios:

1. Garzon con porcentaje 0 (debe aparecer y poder pagarse manualmente).
2. Reparto manual entre varios garzones sin depender del porcentaje original.
3. Necesidad de trazabilidad completa sin romper caja, historial, reportes, ni secuencia documental.

## 2) Objetivos funcionales

1. Todo garzon con ventas en el scope debe aparecer en liquidaciones, incluso con comision calculada 0.
2. Nunca ocultar actividad por tener porcentaje 0.
3. Mantener el flujo actual automatico para porcentaje mayor a 0.
4. Permitir monto manual antes del pago cuando porcentaje sea 0.
5. Permitir reparto manual multi-garzon por turno/caja.
6. Mantener auditoria, historial, comprobante, cash movement y trazabilidad extremo a extremo.
7. No romper liquidaciones de chicas.
8. No romper caja.
9. No tocar semantica de DocumentSequence de pago de liquidacion.

## 3) Restricciones de arquitectura

- SaaS multi-tenant y multi-branch.
- Scope operacional actual por official shift y cash session.
- Integracion existente con:
  - pago de liquidaciones
  - ticket de liquidacion
  - movimiento de caja por egreso
  - historial y reportes
- Compatibilidad hacia atras con datos existentes de staff_settlements y staff_settlement_items.

## 4) Modelos de compensacion objetivo

Se propone formalizar tres estrategias de compensacion de garzon:

1. AUTO_PERCENT
- Fuente: porcentaje de perfil.
- Monto: calculado automaticamente por ventas.
- Compatible con el modelo actual.

2. MANUAL_ZERO_PERCENT
- Fuente: ventas reales, porcentaje 0.
- Monto inicial calculado: 0.
- Requiere captura manual de monto antes de pago (o accion explicita de no pagar).

3. MANUAL_DISTRIBUTION
- Fuente: asignacion manual por operador autorizado.
- Puede repartir montos entre varios garzones del mismo scope.
- No depende del porcentaje individual.

## 5) Opciones de diseno evaluadas

### Opcion A: Solo ajustes sobre liquidacion existente

Idea:
- Reusar staff_settlement_adjustments para cargar montos manuales.
- Crear settlement de garzon aunque su comision automatica sea 0.
- Registrar override manual como ajuste positivo.

Ventajas:
- Menor cambio estructural.
- Reusa motor de net_amount y trazabilidad de ajustes.

Desventajas:
- Semantica mezclada: ajustes hoy representan descuentos/multas/deducciones.
- Menos clara para reparto manual multi-garzon como feature de producto.
- Riesgo de deuda semantica en reportes y auditoria a largo plazo.

### Opcion B: Modulo explicito de compensacion de garzon (recomendada)

Idea:
- Mantener staff_settlements como ledger de pago final.
- Introducir capa funcional explicita de compensacion (estrategia + asignacion), que alimenta el settlement.
- Dejar trazabilidad de origen: automatico o manual.

Ventajas:
- Semantica clara y extensible para SaaS.
- Soporta bien crecimiento a nuevos esquemas de pago.
- Facilita auditoria por origen de monto.
- No rompe girls/cash/document sequence.

Desventajas:
- Mayor esfuerzo de diseno y migracion inicial.

### Opcion C: Modificar sale_items para soportar reparto manual directo

Idea:
- Persistir distribucion manual en nivel de venta/item y derivar settlement desde ahi.

Ventajas:
- Gran detalle de origen por venta.

Desventajas:
- Alto acoplamiento con flujo de cobro.
- Mayor complejidad transaccional y de UI operativa.
- Riesgo innecesario para release incremental.

## 6) Decision de arquitectura recomendada

Recomendada: Opcion B, con rollout incremental.

Principio clave:
- Staff settlement sigue siendo el documento de pago.
- La compensacion define como se calcula o asigna el monto de ese documento.

## 7) Modelo de datos propuesto (conceptual)

### 7.1 Extensiones en staff_settlements

Agregar metadatos de compensacion:

- compensation_mode: AUTO_PERCENT | MANUAL_ZERO_PERCENT | MANUAL_DISTRIBUTION
- compensation_source: PROFILE_PERCENT | MANUAL_INPUT | MANUAL_DISTRIBUTION
- manual_amount_input: decimal nullable
- compensation_locked_at: datetime nullable
- compensation_locked_by_user_id: bigint nullable

Uso:
- total/gross/net siguen siendo fuente contable de pago.
- Estos campos explican origen y gobernanza del monto.

### 7.2 Itemizacion de evidencia de garzon

Agregar/usar source_type para base de visibilidad de garzon:

- WAITER_SALES_BASE
  - base_amount: ventas del garzon en scope
  - percent: porcentaje aplicado (puede ser 0)
  - amount: comision automatica calculada (puede ser 0)

Con esto el garzon siempre aparece por actividad, no por monto.

### 7.3 Entidad de distribucion manual (nueva)

Nueva tabla conceptual: waiter_compensation_allocations

Campos sugeridos:
- tenant_id, branch_id, official_shift_id, cash_session_id nullable
- staff_settlement_id
- allocation_mode: MANUAL_DISTRIBUTION | MANUAL_ZERO_PERCENT
- base_sales_amount (snapshot)
- assigned_amount
- notes
- created_by_user_id
- created_at

Objetivo:
- Registrar explicitamente asignaciones manuales como evento de negocio.
- Mantener separacion entre calculo/decision y pago final.

Nota:
- Tambien puede resolverse con staff_settlement_adjustments + ajuste tipado, pero la tabla dedicada escala mejor para analytics y compliance.

## 8) Casos de uso target

### 8.1 Generar liquidaciones de turno/caja

- Detectar garzones con ventas en scope aunque su comision sea 0.
- Crear settlement WAITER siempre que exista actividad de ventas.
- Crear item WAITER_SALES_BASE con percent y amount calculado (incluso en 0).
- compensation_mode inicial:
  - AUTO_PERCENT si percent mayor a 0
  - MANUAL_ZERO_PERCENT si percent igual a 0

### 8.2 Capturar monto manual (percent 0)

- Actor: cajera autorizada, senior, administrador.
- Precondicion: settlement PENDING.
- Accion: registrar monto manual.
- Resultado:
  - net_amount actualizado.
  - origen marcado como MANUAL_INPUT.
  - evento auditado con usuario, fecha, valor anterior/nuevo, motivo.

### 8.3 Reparto manual multi-garzon

- Actor: administrador/senior con permiso dedicado.
- Entrada: lista de garzones y montos.
- Resultado:
  - cada garzon conserva su settlement individual.
  - se registra asignacion por cada destino.
  - cada settlement queda listo para flujo normal de pay.

### 8.4 Pago

No cambia la semantica de pago:
- mark-paid genera cash movement de egreso.
- se emite ticket con DocumentSequenceType SettlementPayment.
- se registra historial y auditoria de pago.

## 9) Compatibilidad con datos existentes

Backwards compatible por capas:

1. Settlements historicos sin compensation_mode:
- interpretar como AUTO_PERCENT legacy.

2. Reportes legacy:
- siguen leyendo total_amount/net_amount.
- se pueden enriquecer luego con dimension compensation_mode.

3. Girls:
- no se tocan settlement_type GIRL ni su pipeline.

4. Caja:
- egreso sigue naciendo en pago, no en asignacion.

5. DocumentSequence:
- permanece en evento de pago, sin cambio de tipo documental.

## 10) UI y experiencia operativa

### 10.1 Tabla Garzones

Agregar columnas:
- Ventas (base)
- Porcentaje
- Comision automatica
- Monto manual asignado (si aplica)
- Modo de compensacion

Estados recomendados:
- PENDING_AUTO
- PENDING_MANUAL_REQUIRED
- PENDING_READY_TO_PAY
- PAID

### 10.2 Dialogo de pago

Regla UX:
- Si percent mayor a 0 y sin override, permitir pago directo.
- Si percent igual a 0 y sin monto manual, bloquear pagar y guiar a Asignar monto.
- Mostrar trazabilidad de quien asigno y cuando.

### 10.3 Reparto manual

Pantalla/modal dedicada:
- seleccionar scope (turno/caja)
- lista de garzones con ventas base
- captura de monto por garzon
- validaciones de negocio configurables

## 11) Auditoria y trazabilidad

Registrar eventos de dominio dedicados:

- waiter_compensation.generated
- waiter_compensation.manual_amount_set
- waiter_compensation.manual_distribution_applied
- waiter_compensation.manual_amount_updated
- waiter_compensation.manual_amount_cleared
- settlement.paid

Cada evento debe incluir:
- tenant_id, branch_id, official_shift_id, cash_session_id
- settlement_id, staff_user_id
- before/after de campos monetarios
- actor user_id y timestamp
- motivo/nota operacional

## 12) Reportes e historial

Historial de liquidaciones:
- conservar vista actual.
- agregar filtros por compensation_mode y compensation_source.

Reportes de settlements:
- dimensionar totales por:
  - AUTO_PERCENT
  - MANUAL_ZERO_PERCENT
  - MANUAL_DISTRIBUTION
- KPI sugeridos:
  - porcentaje de pagos manuales
  - variacion entre comision automatica y monto final pagado

Auditoria operativa:
- listado de override manual por periodo, actor, sucursal.

## 13) Escalabilidad SaaS

La capa de compensacion permite crecer a:

- politicas por tenant/branch
- reglas por rol o categoria de garzon
- ventanas de bloqueo (cutoff) para no editar manual despues de cierre
- aprobacion en dos pasos para montos manuales altos
- analitica multi-sucursal sin reinterpretar ajustes legacy

## 14) Riesgos y mitigaciones

Riesgo 1: Inflar pendientes con settlements en 0.
- Mitigacion: estado PENDING_MANUAL_REQUIRED y reglas claras de cierre.

Riesgo 2: Uso indebido de montos manuales.
- Mitigacion: permiso dedicado, motivo obligatorio, auditoria before/after.

Riesgo 3: Inconsistencia entre asignacion manual y caja.
- Mitigacion: caja se afecta solo en pay; asignacion no crea movimiento.

Riesgo 4: Confusion en reportes historicos.
- Mitigacion: versionado de metricas y columnas nuevas no destructivas.

## 15) Plan de evolucion recomendado (sin implementar aun)

Fase 1 - Visibilidad garantizada
- Garzones con ventas siempre aparecen.
- Crear WAITER_SALES_BASE aun con monto 0.

Fase 2 - Manual zero-percent
- Captura de monto manual para percent 0 con auditoria.

Fase 3 - Reparto multi-garzon
- Modulo de distribucion manual y reportes por modo.

Fase 4 - Gobernanza SaaS
- politicas avanzadas, aprobaciones, analytics de compensacion.

## 16) Decision propuesta para aprobar

Decision sugerida:
- Aprobar arquitectura Opcion B.
- Implementar por fases, preservando compatibilidad contable y documental.
- Mantener pago, caja y ticketing sin cambios de semantica.

Con esto NightPOS soporta los tres modelos operativos sin romper el flujo actual ni comprometer trazabilidad para SaaS.
