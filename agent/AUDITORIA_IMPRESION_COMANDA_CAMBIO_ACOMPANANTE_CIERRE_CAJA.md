# AUDITORIA AGENTE - IMPRESION COMANDA, CAMBIO ACOMPANANTE, CIERRE DE CAJA

## 1. Objetivo

Auditoria tecnica del agente Windows de impresion en relacion con tres mejoras operativas de NightPOS, sin implementacion en esta fase:

1. Reimpresion de comanda.
2. Cambio de chica (impacto indirecto por nuevos tickets de correccion).
3. Ticket de cierre de caja corto.

## 2. Estado actual del agente

Arquitectura observada:
- Polling periodico a backend.
- Heartbeat de dispositivo.
- Ciclo de trabajo por job:
  1) Pending
  2) Claim
  3) Print
  4) Printed o Failed

Endpoints usados por agente:
- POST /print-devices/heartbeat
- GET /print-jobs/pending?limit=n
- POST /print-jobs/{id}/claim
- POST /print-jobs/{id}/printed
- POST /print-jobs/{id}/failed

Comportamiento de resiliencia:
- Deteccion de errores de red y estado no internet.
- Backoff progresivo ante fallos de conectividad.
- Estados de salud y ultimo job en snapshot.

Conclusion:
- El agente es generico por tipo de print_job y no decide reglas de negocio.
- La politica de reimpresion/correccion se decide en backend/frontend.

## 3. Impacto por modulo

### 3.1 Modulo 1 - Reimpresion de comanda

Observacion clave:
- Si backend encola mas jobs (correcciones automaticas o reimpresion manual), el agente los imprime de forma secuencial.

Implicacion:
- El agente no es la causa raiz de sobreimpresion.
- El agente es el ejecutor de la politica definida aguas arriba.

Riesgo operativo:
- Cuando existe alto volumen de jobs por correcciones, aumenta cola y tiempo de drenaje.

Control recomendado:
- Etiquetar claramente en payload/content_text el tipo de ticket para facilitar lectura humana en barra:
  - INICIAL,
  - CORRECCION,
  - REIMPRESION MANUAL.

### 3.2 Modulo 2 - Cambio de chica

Observacion clave:
- El cambio de chica no se resuelve en agente.
- Solo impacta indirectamente si backend decide emitir ticket de correccion por ese cambio.

Implicacion:
- Cualquier nueva regla de cambio de chica debe traducirse en tipo de print_job consistente.

Control recomendado:
- Si se adopta ticket corto de modificacion, incluir linea sintetica del cambio para barra:
  - Item
  - Chica anterior
  - Chica nueva
  - Hora

### 3.3 Modulo 3 - Ticket cierre de caja corto

Observacion clave:
- El agente imprime content_text ESC/POS recibido.
- Puede imprimir tanto formato corto como detallado sin cambio estructural del runtime.

Implicacion:
- La separacion A (corto) / B (detallado) puede implementarse solo en backend/frontend, manteniendo agente estable.

Control recomendado:
- Mantener mensajes de error claros cuando content_text llegue vacio o mal formado.
- Mantener fallback web en frontend cuando falla impresora/agente.

## 4. Analisis operativo para modelos recomendados

### Modulo 1 (A/B/C/D) visto desde agente

- MODELO A: mas volumen de jobs, mayor ruido y cola.
- MODELO B: volumen moderado, mejor relacion informacion/papel.
- MODELO C: volumen minimo, depende de accion humana.
- MODELO D: volumen optimizado, mayor complejidad de reglas.

Recomendacion agente:
- MODELO B como base por balance entre trazabilidad y carga de cola.

### Modulo 2 (A/B/C) visto desde agente

- A: flexible, puede generar mas correcciones impresas.
- B: menos correcciones, pero rigido para operacion real.
- C: controlado y trazable, adecuado para ticket de modificacion.

Recomendacion agente:
- OPCION C.

### Modulo 3 (A/B)

- A corto: menor bytes por ticket, lectura rapida.
- B detallado: mas bytes, uso auditor.

Recomendacion agente:
- Soportar ambos; usar A por defecto operativo en cierre y B para consulta/admin.

## 5. Riesgos y mitigaciones

1. Riesgo: crecimiento de cola por exceso de correcciones impresas.
- Mitigacion: regla backend MODELO B y formato delta corto.

2. Riesgo: confusion del personal al recibir tickets similares.
- Mitigacion: cabeceras estandar en ticket impreso con tipo de evento.

3. Riesgo: fallas de red/impresora en momento de cierre.
- Mitigacion: mantener fallback web y reintento manual controlado.

## 6. Recomendacion final del frente agente (sin implementar)

1. Confirmar que el agente permanece generico y estable, sin logica de negocio adicional.
2. Estandarizar tipado semantico del contenido impreso desde backend.
3. Priorizar MODELO B en reimpresion de comanda para reducir cola y ruido.
4. Mantener doble salida de cierre: corto operativo y detallado administrativo.

## 7. Nota

Esta auditoria del agente es de analisis tecnico y operacion. No se realizaron cambios de codigo en el agente.
