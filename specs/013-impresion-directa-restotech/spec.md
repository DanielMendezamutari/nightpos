# Especificación Funcional & Técnica: Módulo 013 — Impresión Directa Térmica de Tickets estilo RestoTech

**Módulo**: `013-impresion-directa-restotech`  
**Estado**: Especificado & En Implementación (SDD)  
**Fuente de Verdad Legacy**: `ControlConsumoLib.dll` (`clsImpresoras`, `ctlImpresoras`, `clsPagos`, `ctlFacturas`), `frmFacturacion1`, `frmControlCajaTurno`.

---

## 1. Contexto y Problema

En el sistema tradicional **RestoTech**, al registrar un cobro (sea Factura o Recibo/Nota de venta interna), el ticket se emite **directamente** a la impresora térmica de 80 mm (`CAJA` - Epson TM-T) conectada por USB:
- **Cero clics adicionales**: No muestra ninguna ventana de diálogo de Windows ni del navegador.
- **Corte de papel automático**: Emite el comando de corte al finalizar el ticket.
- **Apertura de gaveta (opcional)**: Envía pulso para abrir la caja registradora conectada a la impresora.

### Situación actual en NightPOS Web:
Actualmente, el modal [`CobroFacturacionModal.vue`](file:///c:/xampp/htdocs/nightpos/frontend/src/components/pos/CobroFacturacionModal.vue) intenta utilizar un `iframe` con `window.print()`. Esto genera dos limitaciones críticas:
1. Si el navegador no se ejecuta con `--kiosk-printing`, aparece la ventana emergente de selección de impresora del navegador, requiriendo pulsar "Imprimir" manualmente.
2. Si el iframe no tiene el foco o el modal se cierra rápidamente con `emit('update:modelValue', false)`, el navegador puede omitir o bloquear la orden de impresión.
3. No hay un canal directo del servidor hacia la impresora física instalada en Windows (`CAJA`).

---

## 2. Solución de Doble Canal (Arquitectura de Alta Disponibilidad)

Para garantizar que la impresión funcione **al 100% directo como RestoTech**:

### Canal A: Impresión Nativa Directa desde Backend (Local Spooler / ESC-POS)
- Cuando el POS corre en entorno local (XAMPP / Windows), el backend en Laravel puede comunicarse directamente con la cola de impresión de Windows (`CAJA`) sin depender de las limitaciones de seguridad del navegador.
- Al confirmar el cobro en `CajaFacturaController::cobrarYFacturar`, o mediante el endpoint dedicado `POST /api/v1/impresion/ticket/{facturaId}`:
  1. Genera el contenido del ticket en formato texto plano térmico (40 columnas, alineaciones, separadores punteados, detalle de ítems, montos, datos fiscales/recibo y CUF/QR).
  2. Envía el trabajo a la impresora Windows `CAJA` de forma asíncrona y silenciosa mediante el Spooler de Windows o comando directo nativo.
  3. Responde al frontend con `{ success: true, printed_via: 'backend_spooler' }`.

### Canal B: Impresión Directa por Frontend (Modo Kiosk Printing)
- Para terminales táctiles o clientes web:
  1. Se configura el acceso directo del POS mediante Chrome/Edge con los flags `--kiosk-printing --app=http://localhost/...`.
  2. En [`CobroFacturacionModal.vue`](file:///c:/xampp/htdocs/nightpos/frontend/src/components/pos/CobroFacturacionModal.vue), tras guardar el cobro exitoso, se ejecuta la impresión directa inmediatamente garantizando persistencia del documento antes de destruir el modal.
  3. Se provee un botón flotante y accesible de "Reimprimir Ticket" en el detalle de la factura y en el historial de caja.

---

## 3. Criterios de Aceptación

1. **CA-1: Impresión al Cobrar**: Al presionar "Procesar Cobro (F12)" o confirmar el pago, la impresora `CAJA` debe imprimir el ticket físico sin requerir confirmar ningún diálogo.
2. **CA-2: Formato Térmico 80mm Idéntico a RestoTech**:
   - Cabecera: Razón social, NIT, teléfono, dirección, número de factura/recibo.
   - Datos del cliente: Razón Social, NIT/CI, fecha/hora, mesero, mesa.
   - Detalle de consumos: Cantidad, Descripción de producto, Subtotal.
   - Resumen financiero: Total a pagar, Monto recibido, Vuelto/Cambio, Método de pago (Efectivo/Tarjeta/QR/Mixto).
   - Pie legal: Ley N° 453, CUF (si es factura), leyenda tributaria o aviso de consumo interno si es recibo.
3. **CA-3: Fallback Inteligente**: Si el servicio de impresión directa del backend reporta éxito, el frontend no necesita abrir ventanas del navegador; si el backend no detecta la impresora local (ej. en un despliegue cloud remoto), el frontend toma el relevo automáticamente con el método de iframe/kiosk.
4. **CA-4: Endpoint de Reimpresión**: `POST /api/v1/impresion/ticket/{id}` permite reimprimir cualquier factura o recibo emitido anteriormente.
