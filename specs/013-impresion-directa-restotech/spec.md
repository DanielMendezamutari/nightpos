# Especificación Funcional: Módulo 013 — Impresión Directa Térmica RestoTech (Cobro y Precuenta)

**Módulo**: `013-impresion-directa-restotech`  
**Estado**: Especificado  
**Enfoque**: Spec-Driven Development (SDD)  
**Fuente de Verdad Legacy**: `ControlConsumoLib.dll` (`ImprimiendoComandas.cs` -> `printCuentaTotalFactura`, `printFacturaNewFormat`, `PrinterClass.cs`, `agent/src/winRawPrint.ps1`).

---

## 1. Declaración del Problema & Objetivos de Negocio

### Problema:
1. **Ancho del Papel Incompleto**: Al imprimir usando el cmdlet `Out-Printer` de PowerShell, Windows envía el texto a través del subsistema GDI aplicando fuentes grandes y márgenes de página predeterminados de casi 1 pulgada, reduciendo el ancho efectivo a ~20 caracteres en un rollo de 80mm y provocando saltos de línea y palabras cortadas a la mitad (`RECIBO DE CA-JA`, `COMPROBAN-TE`, `Silpancho C-ochabambino`).
2. **Falta de Impresión de Precuenta**: El sistema sólo imprimía al momento del cobro final. En la operativa diaria de restaurantes y boliches, es imperativo que el mesero o cajero pueda imprimir la **Precuenta** (comprobante previo de la mesa para que el cliente revise su consumo antes de pagar).
3. **Fidelidad RestoTech**: El usuario exige que el diseño y diagramación del ticket sea idéntico a RestoTech (encabezado centrado, separadores continuos, columnas `DESCRIPCION / CANT. / TOTAL`, total destacado en negrita y corte de papel automático).

### Objetivos:
- **O-1**: Impresión física directa en modo **RAW ESC/POS** a través de la API del Spooler de Windows (`winspool.drv` con `pDataType = "RAW"`), eliminando los márgenes falsos de Windows y aprovechando el 100% del ancho del papel térmico de 80mm (42 columnas nítidas).
- **O-2**: Implementar la impresión directa de **Precuenta** desde la comanda de la mesa (`POST /api/v1/impresion/precuenta/{mesaId}`) con el formato exacto de RestoTech `printCuentaTotalFactura`.
- **O-3**: Formatear el ticket de Cobro (Factura / Nota de Venta) con fidelidad RestoTech `printFacturaNewFormat` a 42 columnas con corte automático de papel.
- **O-4**: Experiencia sin cuadros de diálogo emergentes del navegador: disparo instantáneo al presionar el botón o tecla rápida.

---

## 2. Historias de Usuario & Criterios de Aceptación (Gherkin)

### Historia 1: Impresión de Cobro a Ancho Completo 80mm RAW (P1)
**Como** cajero en la pantalla de cobro,  
**Quiero** que al cobrar una cuenta el ticket se imprima usando todo el ancho del papel de 80mm sin saltos de línea rotos,  
**Para** entregar al cliente un comprobante limpio, profesional y legible idéntico a RestoTech.

- **Escenario 1.1: Recibo de cobro en 42 columnas RAW**
  - **Given** una mesa o venta al paso con productos cargados,
  - **When** se confirma el cobro con método efectivo o mixto,
  - **Then** el backend genera el stream de bytes ESC/POS en 42 columnas,
  - **And** lo envía a la impresora `CAJA` vía `winspool.drv` con tipo de dato `RAW`,
  - **And** la impresora emite el ticket con alineación perfecta y corte automático de papel al final.

---

### Historia 2: Impresión de Precuenta desde la Mesa (P1)
**Como** mesero o cajero en el mapa de mesas o comanda,  
**Quiero** presionar el botón "Precuenta" (o tecla F9),  
**Para** que la impresora `CAJA` imprima de inmediato la cuenta de la mesa antes de cobrar.

- **Escenario 2.1: Precuenta de mesa activa con consumos**
  - **Given** la Mesa 2 con consumos abiertos por Bs. 150.00,
  - **When** el usuario presiona el botón "Precuenta",
  - **Then** el frontend invoca `POST /api/v1/impresion/precuenta/2`,
  - **And** el backend resuelve la visita activa y genera el ticket con encabezado `CUENTA`, `En Mesa`, detalle de productos y `TOTAL: Bs. 150.00`,
  - **And** lo envía de inmediato a la impresora física `CAJA` en modo RAW,
  - **And** la interfaz muestra notificación flotante "Precuenta enviada a impresora CAJA".

---

## 3. Contratos de Datos (API Contracts)

### Endpoint: `POST /api/v1/impresion/precuenta/{mesaId}`
**Request**:
```json
{
  "impresora": "CAJA"
}
```

**Response 200 OK**:
```json
{
  "success": true,
  "message": "Precuenta enviada a impresora CAJA con éxito",
  "data": {
    "mesa_id": 2,
    "mesa_nombre": "MESA 2",
    "total": 150.00,
    "impresion": {
      "success": true,
      "printer": "CAJA",
      "method": "windows_raw_spooler",
      "bytes_sent": 450
    }
  }
}
```

### Endpoint: `POST /api/v1/impresion/ticket/{facturaId}`
**Response 200 OK**:
```json
{
  "success": true,
  "message": "Ticket de cobro enviado a impresora CAJA con éxito",
  "data": {
    "factura_id": 4,
    "nro_comprobante": "REC-000004",
    "impresion": {
      "success": true,
      "printer": "CAJA",
      "method": "windows_raw_spooler"
    }
  }
}
```
