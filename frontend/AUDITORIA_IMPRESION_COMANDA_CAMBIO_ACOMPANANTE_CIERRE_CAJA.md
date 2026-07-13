# AUDITORIA FRONTEND - IMPRESION COMANDA, CAMBIO ACOMPANANTE, CIERRE DE CAJA

## 1. Objetivo y enfoque

Auditoria funcional frontend de tres mejoras operativas, sin implementar cambios:

1. Reimpresion de comandas.
2. Cambio de chica en items CON_ACOMPANANTE.
3. Ticket corto para cierre de caja/turno.

Enfoque: revisar UX actual, llamadas API, estados visibles, riesgos de uso y propuesta de comportamiento recomendado.

## 2. Estado actual por modulo

### 2.1 Modulo 1 - Reimpresion de comanda

Evidencia funcional:
- Vista de detalle de comanda permite reimpresion manual.
- API frontend expone:
  - fetchOrderPrintStatus(orderId)
  - reprintOrderCommand(orderId)
- Se muestran estados de print job:
  - PENDING, CLAIMED, PRINTED, FAILED.

Comportamiento observado:
- El usuario puede reenviar manualmente desde UI.
- En paralelo, al corregir items en backend para orden ya enviada, pueden existir impresiones automaticas de correccion.

Impacto UX:
- Operativamente puede sentirse como duplicidad de impresiones.
- El estado de impresion existe, pero no siempre explica con claridad si el ticket fue:
  - impresion inicial,
  - correccion automatica,
  - reimpresion manual.

### 2.2 Modulo 2 - Cambio de chica en CON_ACOMPANANTE

Evidencia funcional:
- Frontend usa assignOrderItemGirl(orderId, itemId, girlUserId).
- El flujo de comanda permite asignar/corregir chica en item con modo CON_ACOMPANANTE.
- Existen validaciones de items sin chica y de asignaciones en dialogs/tabla.

Impacto UX:
- La accion esta disponible y comprensible.
- Falta una regla visual unica de negocio para estado SENT_TO_BAR:
  - que se puede cambiar,
  - hasta cuando,
  - y que traza queda en el sistema.

### 2.3 Modulo 3 - Cierre de caja/turno

Evidencia funcional:
- Pantalla de cierre consulta resumen de turno, liquidaciones y reconciliacion.
- Existe boton para imprimir cierre por agente y fallback a vista imprimible web.
- En notificaciones, se distingue warning de impresion y error con fallback.

Impacto UX:
- Flujo es robusto (agente + fallback).
- Para cajera, el contenido impreso puede ser demasiado extenso para uso rapido de cierre.

## 3. Problemas UX-operativos detectados

1. Modulo 1: falta semantica visible del tipo de ticket posterior al primer envio.
2. Modulo 2: regla de cambio de chica no esta expresada como politica unica en la experiencia.
3. Modulo 3: no hay separacion explicita de formato corto operativo vs formato detallado admin desde el punto de vista de producto.

## 4. Evaluacion de opciones (frontend/producto)

### 4.1 Reimpresion post-envio (Modelos A/B/C/D)

MODELO A - Reimpresion completa automatica ante cada cambio
- UX: mucho ruido, baja claridad historica.
- Recomendacion frontend: no.

MODELO B - Primera impresion automatica + ticket corto de modificacion en cambios
- UX: clara narrativa de "alta" y "delta".
- Recomendacion frontend: si, con etiquetas claras en UI y ticket.

MODELO C - Primera impresion automatica + todo cambio posterior solo manual
- UX: simple, pero depende de disciplina operativa.
- Recomendacion frontend: aceptable como fase de transicion.

MODELO D - Hibrido por tipo de cambio
- UX: potente, pero requiere muy buena explicacion en interfaz.
- Recomendacion frontend: segunda etapa, no primera.

Recomendacion frontend modulo 1:
- MODELO B con copy operacional explicito:
  - "Comanda inicial enviada"
  - "Correccion enviada"
  - "Reimpresion manual enviada"

### 4.2 Cambio de chica (Opciones A/B/C)

OPCION A - Cambio libre hasta cobro
- UX flexible, pero requiere avisos fuertes.

OPCION B - Cambio solo antes de send-to-bar
- UX simple, pero poco realista operativamente.

OPCION C - Cambio permitido en SENT_TO_BAR solo para chica, con trazabilidad
- UX recomendada: mensaje de confirmacion con contexto de auditoria.

Recomendacion frontend modulo 2:
- OPCION C.
- Agregar convencion visual de evento: "Chica cambiada: X -> Y" en timeline/chips/bitacora operativa.

### 4.3 Ticket de cierre

Formato A (corto caja):
- Prioridad de lectura en menos de 15 segundos:
  - total ventas por metodo,
  - esperado/declarado/diferencia efectivo,
  - pendientes criticos,
  - firma/confirmacion.

Formato B (detallado admin):
- Mantener para auditoria y analitica.

Recomendacion frontend modulo 3:
- Exponer claramente ambos objetivos:
  - "Imprimir cierre rapido (caja)"
  - "Ver/Imprimir reporte detallado"

## 5. Contratos API frontend a mantener

1. Reimpresion:
- POST /orders/{id}/reprint.
- GET /orders/{id}/print-status.

2. Cambio de chica:
- PATCH /orders/{id}/items/{itemId} con girl_user_id.

3. Cierre:
- POST /shifts/{id}/print-closure.
- fallback a ruta imprimible web.

## 6. Recomendacion priorizada de producto (sin implementar)

1. Primero: definir semantica de impresion posterior (Modulo 1) y mensajes UI asociados.
2. Segundo: formalizar regla de cambio de chica en SENT_TO_BAR con confirmacion y trazabilidad visible (Modulo 2).
3. Tercero: separar UX de cierre rapido vs reporte detallado (Modulo 3).

## 7. Nota

Esta auditoria frontend es solo analisis y recomendacion funcional. No se implementaron cambios de codigo.
