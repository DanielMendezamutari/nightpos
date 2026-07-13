# IMPLEMENTACION POR ETAPAS - IMPRESION COMANDA, CAMBIO ACOMPANANTE, CIERRE CAJA

## ETAPA 1 - IMPRESION DE COMANDA (COMPLETADA)

Fecha: 2026-07-12
Alcance aplicado: solo backend de impresion de comanda.
No se tocaron: agente Go, cierre de caja, Financial Core, DocumentSequence, PWA, dist.

### Regla de negocio implementada

Se implemento la regla final solicitada:

1. Primera vez que la comanda pasa a SENT_TO_BAR:
- se mantiene la impresion automatica de comanda completa;
- se mantiene idempotencia del job inicial.

2. Despues del primer envio a barra:
- no se genera ninguna impresion automatica por correcciones;
- no se generan tickets cortos de modificacion;
- no se genera reimpresion completa automatica.

3. Impresiones posteriores permitidas solo por accion manual:
- precuenta manual;
- reimpresion manual de comanda.

### Llamadas automaticas eliminadas

Se eliminaron disparos automaticos a DispatchBarCorrectionPrintJobUseCase en:

- AddOrderItemUseCase
- UpdateOrderItemUseCase
- CancelOrderItemUseCase
- SyncOrderItemAllocationsUseCase

Revisado adicionalmente:

- AssignOrderItemGirlUseCase: no dispara impresion automatica (sin cambios).
- No se detectaron listeners/event handlers adicionales que disparen correcciones de impresion en esta etapa.

### Archivos modificados

Codigo:
- backend/app/Application/Order/UseCases/AddOrderItemUseCase.php
- backend/app/Application/Order/UseCases/UpdateOrderItemUseCase.php
- backend/app/Application/Order/UseCases/CancelOrderItemUseCase.php
- backend/app/Application/Order/UseCases/SyncOrderItemAllocationsUseCase.php

Tests actualizados:
- backend/tests/Feature/Api/V1/PrintingP2OperationalFormatsTest.php

Tests nuevos:
- backend/tests/Feature/Api/V1/OrderPostSendPrintingPolicyTest.php

### Tests ejecutados

1) php artisan test tests/Feature/Api/V1/LocalPrintAgentTest.php tests/Feature/Api/V1/PrintingP2OperationalFormatsTest.php tests/Feature/Api/V1/OrderPostSendPrintingPolicyTest.php
- LocalPrintAgentTest: PASS
- PrintingP2OperationalFormatsTest: PASS

2) php artisan test tests/Feature/Api/V1/OrderPostSendPrintingPolicyTest.php
- 11 pruebas PASS, 121 assertions.

### Cobertura funcional validada en ETAPA 1

Validado por pruebas:

- primer send-to-bar crea un unico print job inicial;
- repetir send-to-bar no duplica impresion;
- agregar producto post SENT_TO_BAR no crea print job;
- modificar cantidad post SENT_TO_BAR no crea print job;
- cancelar producto post SENT_TO_BAR no crea print job;
- cambiar chica post SENT_TO_BAR no crea print job;
- sincronizar allocations post SENT_TO_BAR no crea print job;
- precuenta manual refleja estado actualizado;
- reimpresion manual sigue funcionando;
- no se eliminan print_jobs historicos.

Nota sobre fallo de impresion inicial:
- se valido que ausencia de dispositivo/impresora activa no bloquea send-to-bar (operacion continua y orden queda SENT_TO_BAR).

### Confirmaciones solicitadas

- Solo existe una impresion automatica inicial: SI.
- La precuenta refleja cambios posteriores: SI.
- El agente Go no requiere cambios: SI (sin modificaciones en agent/).

### Riesgos pendientes

1. DispatchBarCorrectionPrintJobUseCase queda sin uso operativo en esta etapa.
- Riesgo bajo, pero recomendable decidir en etapa posterior si se elimina o se conserva como reserva historica.

2. Algunas pruebas antiguas de otros modulos podrian asumir correcciones impresas automaticas.
- Mitigacion: ajustar solo los casos impactados por la nueva regla cuando se ejecuten suites globales.

3. El escenario de "falla de impresion inicial" en entorno real depende del flujo de claim/failed del agente.
- Mitigacion: mantener monitoreo de print_jobs FAILED en operacion y fallback manual de precuenta/reimpresion.

### Veredicto ETAPA 1

APROBADO CON OBSERVACIONES

Observacion principal:
- regla solicitada implementada correctamente, pero queda deuda de limpieza de codigo muerto potencial (use case de correccion) para decidir en siguiente etapa.
