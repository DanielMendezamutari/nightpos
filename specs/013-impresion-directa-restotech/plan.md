# Plan de Arquitectura e Implementación: Módulo 013 — Impresión RAW y Precuenta RestoTech

**Objetivo**: Migrar el canal de impresión de texto GDI a **RAW ESC/POS Spooler (`winspool.drv`)** para ocupar el ancho completo de 80mm e implementar la **Precuenta** en backend y frontend.

---

## 1. Arquitectura Técnica

### 1.1 Impresión RAW vía Spooler de Windows (`winspool.drv`)
- En lugar de invocar `Out-Printer` (que fuerza renderizado GDI con márgenes de Windows), invocamos un helper en C# o PowerShell que llama a las APIs nativas de Windows:
  - `OpenPrinterA`
  - `StartDocPrinterA` con `pDataType = "RAW"`
  - `StartPagePrinter`
  - `WritePrinter`
  - `EndPagePrinter`
  - `EndDocPrinter`
  - `ClosePrinter`
- Esto ya está probado y disponible en `agent/src/winRawPrint.ps1` o mediante un ejecutable/script directo invocado desde `WindowsDirectPrinterService`.
- Al enviar datos en modo RAW:
  - Los comandos ESC/POS como corte de papel (`\x1D\x56\x01` o `\x1D\x56\x42\x00`), inicialización (`\x1B\x40`), negrita (`\x1B\x45\x01`) y tamaño de fuente son interpretados por el firmware de la impresora EPSON TM-T.
  - La línea usa los 42 caracteres completos de la fuente Font A sin cortes de palabras.

### 1.2 Formateador de Tickets RestoTech (`TicketFormatterService`)
1. **Cobro / Factura / Recibo (`formatTicket`)**:
   - Cabecera de comercio.
   - `RECIBO DE CAJA / NOTA DE VENTA`
   - Metadatos (Nro, Fecha, Cliente, NIT/CI, Mesa, Cajero, Método).
   - Separador `------------------------------------------` (42 caracteres).
   - Tabla: `CANT.  DESCRIPCION                 SUBTOTAL`
   - Lista de productos con formato alineado.
   - Totales y formas de pago.
   - Pie y corte de papel ESC/POS.
2. **Precuenta (`formatPrecuenta`)**:
   - Basado exactamente en `ImprimiendoComandas.cs` -> `printCuentaTotalFactura`.
   - `RIBERESTO POS`
   - `CUENTA`
   - `En Mesa`
   - `Fecha: YYYY-MM-DD HH:mm:ss`
   - `Mesa: X  |  Mesero: Nombre`
   - Separador `------------------------------------------`
   - `DESCRIPCION             CANT.        TOTAL`
   - Separador `------------------------------------------`
   - Ítems de la visita activa.
   - Separador `------------------------------------------`
   - `TOTAL:                     Bs.      XX.XX`
   - `Gracias por su preferencia!`
   - `Sistema Restotech by Ribersoft`
   - 4 líneas en blanco + Corte de papel ESC/POS.

### 1.3 Endpoints Backend
- `POST /api/v1/impresion/precuenta/{mesaId}`: Obtiene los consumos de la mesa activa, genera el ticket de precuenta y lo envía a la impresora `CAJA`.
- `POST /api/v1/impresion/ticket/{facturaId}`: Envía el ticket de cobro a `CAJA` en modo RAW.

### 1.4 Frontend Integration
- En `ComandaModal.vue` y `index.vue`:
  - Botón "Precuenta (F9)" al lado de "Cobrar (F12)".
  - Al presionar, realiza `cajaStore.imprimirPrecuenta(mesaId)`.
  - Muestra toast / feedback inmediato sin recargas ni diálogos.
