# AUDITORIA BACKEND - IMPRESION COMANDA, CAMBIO ACOMPANANTE, CIERRE DE CAJA

## 1. Objetivo y alcance

Documento de auditoria tecnica backend para tres mejoras operativas de NightPOS, sin implementar cambios aun:

1. Reimpresion de comandas.
2. Cambio de chica en items CON_ACOMPANANTE.
3. Ticket de cierre de caja corto para operacion de cajera.

Se evalua estado actual, causa raiz, riesgos, opciones de diseno y recomendacion priorizada.

## 2. Evidencia tecnica (backend)

### 2.1 Modulo 1 - Reimpresion de comanda

- La primera impresion se dispara en envio a barra:
  - SendOrderToBarUseCase -> CreateOrderCommandPrintJobUseCase.
- Despues de enviada, varias correcciones de items generan nueva impresion automaticamente:
  - AddOrderItemUseCase.
  - UpdateOrderItemUseCase.
  - CancelOrderItemUseCase.
  - SyncOrderItemAllocationsUseCase.
  - Todas pasan por DispatchBarCorrectionPrintJobUseCase.
- Existe reimpresion manual:
  - endpoint POST orders/{id}/reprint
  - PrintJobController::reprintOrder -> ReprintOrderCommandUseCase.
- Idempotencia observada:
  - orden inicial: order_command:{orderId}
  - correccion: order_command:{orderId}:correction:{correctionNumber}

Conclusion tecnica actual:
- El sistema hoy mezcla dos disparadores para ticket posterior al primer envio:
  - automatico por correccion,
  - manual por boton/endpoint.
- Por eso la percepcion operativa es de "reimpresion frecuente" cuando hay cambios en comanda enviada.

### 2.2 Modulo 2 - Cambio de chica en CON_ACOMPANANTE

- Flujo principal de asignacion:
  - PATCH orders/{orderId}/items/{itemId} con girl_user_id
  - UseCase: AssignOrderItemGirlUseCase.
- Flujo de update mas amplio:
  - PUT orders/{orderId}/items/{itemId}
  - En estado SENT_TO_BAR existen restricciones para evitar cambios no permitidos.
- Persistencia:
  - En order_items se modifica girl_user_id.
  - Al cobrar comanda, ChargeOrderUseCase crea sale_items.
  - Luego snapshotFromOrderItem copia asignaciones a sale_item_allocations (si aplica asignacion por manillas).
- Liquidaciones:
  - GenerateCurrentShiftSettlementsUseCase / EloquentStaffSettlementRepository consumen sale_items y sale_item_allocations.
  - Si se cambia chica antes de cobro, el impacto llega a liquidacion via snapshot de venta.

Conclusion tecnica actual:
- Si el cambio se realiza antes de cobro y bajo reglas de negocio, la trazabilidad funcional existe.
- Falta estandarizar de forma explicita reglas operativas de "hasta cuando se puede cambiar" para eliminar ambiguedad en caja/barra.

### 2.3 Modulo 3 - Ticket de cierre de caja

- Impresion de cierre existe via:
  - PrintCashCloseUseCase
  - CreateCashClosePrintJobUseCase
  - CashClosePrintPayloadEnricher
  - CashPrintPresenter / builders de contenido.
- El payload de cierre agrega resumen amplio, operativos y reconciliacion (incluyendo top_products).
- Reimpresion de cierre utiliza clave tipo:
  - cash_close:{sessionId}:reprint:{timestamp}

Conclusion tecnica actual:
- El backend ya tiene informacion para version "detallada gerencial".
- Para operacion de cajera nocturna, el ticket puede resultar largo y no optimizado para decision rapida en caja.

## 3. Causa raiz por modulo

### Modulo 1
- Causa principal: politica actual que permite autoimpresion de correcciones luego de enviar a barra.
- Efecto: mayor consumo de papel, ruido en barra y confusion con tickets historicos.

### Modulo 2
- Causa principal: reglas funcionales existentes pero no consolidadas como politica operacional unica para caja.
- Efecto: dudas sobre ventana de cambio valida y riesgo de reproceso manual.

### Modulo 3
- Causa principal: un solo enfoque de salida para usos distintos (operativo rapido vs auditoria/gerencial).
- Efecto: ticket operativo con exceso de detalle para cierre rapido.

## 4. Analisis de opciones

### 4.1 Modulo 1 - Modelos A/B/C/D

#### MODELO A - Todo cambio reimprime ticket completo automaticamente
- Pros: barra siempre recibe estado completo.
- Contras: alto papel, duplicacion, ruido operativo.
- Riesgo: saturacion y errores humanos por exceso de tickets.
- Complejidad: baja.
- Recomendacion: no recomendado para NightPOS en operacion nocturna.

#### MODELO B - Solo primera impresion automatica + cambios generan ticket corto de modificacion
- Pros: conserva trazabilidad y reduce papel.
- Contras: requiere definir formato de delta claro (ADD/EDIT/CANCEL).
- Riesgo: medio-bajo si se estandariza plantilla.
- Complejidad: media.
- Recomendacion: recomendado como equilibrio operativo.

#### MODELO C - Solo primera impresion automatica + cambios sin impresion automatica (solo manual)
- Pros: minimo papel, control total por usuario.
- Contras: depende mucho de disciplina operativa.
- Riesgo: barra no se entera de cambios si no se acciona manual.
- Complejidad: baja.
- Recomendacion: util como fase temporal, no ideal como estado final.

#### MODELO D - Hibrido por tipo de cambio
- Regla ejemplo: CANCEL y ADD importantes imprimen ticket corto; cambios menores no.
- Pros: optimiza signal/noise.
- Contras: regla mas compleja de explicar y mantener.
- Riesgo: inconsistencias si la regla no esta bien documentada.
- Complejidad: media-alta.
- Recomendacion: viable tras estabilizar MODELO B.

Decision sugerida backend modulo 1:
- Adoptar MODELO B como base.
- Mantener reimpresion manual siempre disponible.

### 4.2 Modulo 2 - Opciones A/B/C

#### OPCION A - Cambio libre mientras no se cobre
- Pros: maxima flexibilidad.
- Contras: puede generar incertidumbre en barra si no hay notificacion clara.
- Riesgo: medio.
- Complejidad: baja.

#### OPCION B - Cambio permitido solo antes de SEND_TO_BAR
- Pros: regla simple, evita conflicto con preparacion.
- Contras: rigida para casos reales de correccion tardia.
- Riesgo: medio por bloqueos operativos.
- Complejidad: baja.

#### OPCION C - Cambio permitido en SENT_TO_BAR solo para chica y con traza obligatoria
- Pros: balancea control y operacion real.
- Contras: requiere registrar motivo/auditoria y comunicar cambio.
- Riesgo: bajo si hay registro de evento y actor.
- Complejidad: media.

Decision sugerida backend modulo 2:
- Adoptar OPCION C.
- Regla de oro: permitir cambio de chica en CON_ACOMPANANTE hasta antes del cobro, con auditoria explicita old_girl/new_girl, user_id, timestamp y fuente.

### 4.3 Modulo 3 - Ticket cierre A/B y estrategia de productos

#### Formato A - Ticket corto de cajera (thermal)
- Objetivo: confirmar cierre rapido y discrepancias criticas.
- Debe incluir:
  - encabezado: caja, turno, cajera, fecha/hora.
  - ventas por metodo: CASH, QR, CARD, MIXTO total.
  - movimientos manuales: ingresos/egresos.
  - esperado vs declarado vs diferencia efectivo.
  - estado liquidaciones: pendiente total y conteo.
  - top 5 productos (opcional compacto).

#### Formato B - Ticket/report detallado admin
- Objetivo: auditoria y analitica.
- Incluye detalle extendido de categorias, reconciliacion completa y desglose operativo.

#### Estrategia recomendada de seccion productos
- Ticket corto: Top 5 o Top 10 maximo.
- Detallado: lista completa por categoria y totales.

Decision sugerida backend modulo 3:
- Mantener B para administrativo.
- Introducir A como salida principal de operacion de cierre en caja.

## 5. Riesgos y controles

1. Riesgo de regresion en trazabilidad de cocina/barra.
- Control: mantener print_jobs con source_type/source_id y metadatos de correccion.

2. Riesgo de diferencias en liquidaciones por cambios tardios de chica.
- Control: bloquear cambios post-cobro y guardar auditoria old/new en evento de dominio.

3. Riesgo de interpretacion incompleta en ticket corto.
- Control: definir campos minimos obligatorios y versionar plantilla de impresion.

## 6. Recomendacion final backend (sin implementar)

1. Modulo 1: MODELO B.
2. Modulo 2: OPCION C.
3. Modulo 3: doble formato A (operativo) + B (detallado).

Orden sugerido de implementacion futura:
1. Regla de impresion posterior (Modulo 1), porque reduce ruido operativo inmediato.
2. Regla formal de cambio de chica con auditoria (Modulo 2), para blindar liquidaciones.
3. Ticket corto de cierre (Modulo 3), reutilizando payload existente con plantilla reducida.

## 7. Nota

Esta auditoria es de analisis tecnico. No se realizaron cambios de codigo en backend como parte de este documento.
