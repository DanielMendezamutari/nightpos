# Checklist de Tareas: Módulo 013 — Impresión RAW y Precuenta RestoTech

- [x] **T-1 (Backend - Spooler RAW `winspool.drv`)**: Actualizado `WindowsDirectPrinterService` y creado `raw_print.ps1` utilizando `OpenPrinter`, `StartDocPrinter` (modo `RAW`), `StartPagePrinter`, `WritePrinter` para bypass completo del GDI de Windows, eliminando márgenes laterales falsos y utilizando el 100% del ancho del papel de 80mm.
- [x] **T-2 (Backend - Formato RestoTech para Tickets)**: Ajustado `TicketFormatterService` a 42 columnas exactas de 80mm con comandos ESC/POS (`\x1B\x40` init, `\x1B\x61` alineación, `\x1B\x45` negrita, `\x1D\x21` doble tamaño, `\x1D\x56\x42\x00` feed & cut) idéntico a RestoTech `printFacturaNewFormat`.
- [x] **T-3 (Backend - Formato y Endpoint de Precuenta)**: Implementado `formatPrecuenta()` en `TicketFormatterService` y endpoints `POST /api/v1/mesas/{id}/precuenta` (en `SalonMesaController`) y `POST /api/v1/impresion/precuenta/{mesaId}` (en `ImpresionController`) con impresión física automática hacia la impresora `CAJA`.
- [x] **T-4 (Backend - Tests Automatizados)**: Actualizada la suite `ImpresionApiTest` y validados los 73 feature tests (554 assertions) pasando al 100% en verde sin fallos ni regresiones.
- [x] **T-5 (Frontend - Botón y Acción de Precuenta)**: Agregado botón visual destacado "Imprimir Pre-cuenta (F9)" con estado de carga y mensaje de confirmación en `index.vue` y conectado a `solicitarPrecuenta`.
- [x] **T-6 (Recompilación Frontend y Sincronización)**: Compilado bundle de producción de Vite (`npm run build`) y sincronizado a la raíz de XAMPP.
- [ ] **T-7 (Validación Física con el Usuario)**: El usuario prueba en su impresora física `CAJA` la emisión de la Precuenta y el cobro final, comprobando el ancho de 80mm y corte de papel.
