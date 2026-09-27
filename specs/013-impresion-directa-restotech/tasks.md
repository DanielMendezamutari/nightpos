# Tareas de Implementación: Módulo 013 — Impresión Directa estilo RestoTech

- [x] **Tarea 1 (Backend - Servicios)**: Crear `TicketFormatterService` y `WindowsDirectPrinterService` en `backend/app/Services/Printing/`.
- [x] **Tarea 2 (Backend - Controlador & Rutas)**: Crear `ImpresionController` y registrar rutas en `backend/routes/api.php` (`POST /api/v1/impresion/ticket/{id}`, `GET /api/v1/impresion/impresoras`, `POST /api/v1/impresion/test`).
- [x] **Tarea 3 (Backend - Auto-print en Cobro)**: Conectar `WindowsDirectPrinterService` en `CajaFacturaController::cobrarYFacturar` para enviar automáticamente a la impresora `CAJA`.
- [x] **Tarea 4 (Backend - Tests)**: Crear prueba de integración `ImpresionApiTest.php` verificando formateo de ticket, endpoints y manejo de errores (4/4 tests pasados).
- [x] **Tarea 5 (Frontend - Modal de Cobro)**: Integrar la llamada de impresión directa en `CobroFacturacionModal.vue` y afinar el fallback de impresión si no es servidor local.
- [x] **Tarea 6 (Script & Kiosk)**: Optimizar `iniciar_pos_kiosk.bat` para soportar tanto desarrollo como XAMPP/Apache local.
- [x] **Tarea 7 (Verificación Final)**: Ejecutar suite de pruebas completa (`72 tests passed, 539 assertions`) y compilar frontend para producción.
