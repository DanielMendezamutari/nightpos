# Plan de Arquitectura e Implementación: Módulo 013 — Impresión Directa estilo RestoTech

**Módulo**: `013-impresion-directa-restotech`  
**Foco**: Backend Printer Service (Windows Spooler) + Kiosk Printing Frontend + Endpoints de Impresión

---

## 1. Componentes a Desarrollar

### Backend (Laravel 12 / PHP 8.2 en Windows XAMPP):
1. **`App\Services\Printing\TicketFormatterService`**:
   - Formatea el ticket en texto plano de 40 columnas exactamente ajustado a impresoras térmicas de 80mm.
   - Manejo de acentos, caracteres especiales, corte de papel y líneas de división limpias.
2. **`App\Services\Printing\WindowsDirectPrinterService`**:
   - Detecta si el sistema operativo es Windows.
   - Detecta la impresora configurada (`CAJA` o la predeterminada de Windows).
   - Envía el ticket formateado directamente al spooler de la impresora mediante PowerShell `Out-Printer -Name "CAJA"` o escritura directa en puerto/spool.
3. **Controlador `App\Http\Controllers\Api\V1\ImpresionController`**:
   - `POST /api/v1/impresion/ticket/{facturaId}`: Recibe el ID de factura o recibo y lo envía a imprimir.
   - `GET /api/v1/impresion/impresoras`: Lista las impresoras disponibles en el sistema y el estado de `CAJA`.
   - `POST /api/v1/impresion/test`: Imprime un ticket de prueba breve para calibración.
4. **Integración en `CajaFacturaController::cobrarYFacturar`**:
   - Disparo automático opcional de la impresión tras guardar la factura en la base de datos.
   - El resultado del cobro devuelve `impresion_directa: true/false`.

### Frontend (Vue 3 / Vite):
1. **Actualizar `CobroFacturacionModal.vue`**:
   - Al completar el cobro, invocar la orden de impresión directa al backend (`impresionService.imprimirTicket(factura.id)`).
   - Si el backend confirma la impresión directa (`printed_via: 'backend_spooler'`), mostrar notificación toast "Ticket impreso en CAJA".
   - Si el backend está en un servidor remoto o no tiene impresora local disponible, ejecutar el fallback del iframe/kiosk printing.
2. **Actualizar `iniciar_pos_kiosk.bat`**:
   - Permitir abrir tanto `http://localhost:5173` (desarrollo Vite) como `http://localhost/nightpos` (XAMPP / Apache) o la URL configurada con el flag `--kiosk-printing`.
   - Crear acceso directo en el escritorio para que el cajero siempre inicie el POS en modo silencioso.
