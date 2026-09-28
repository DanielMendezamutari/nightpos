# Plan de Arquitectura e Implementación: Módulo 014 — Impresión de Comandas por Estación

**Objetivo**: Separar e imprimir tickets de comanda en impresoras físicas remotas (`COCINA`, `BARRA`, etc.) en el momento en que se confirman los pedidos.

---

## 1. Diseño Arquitectónico

### 1.1 Modelo de Datos & Enrutamiento
- En la tabla `categorias_productos`:
  - Campo `estacion` (`COCINA`, `BARRA`, `PARRILLA`, `NINGUNA`).
  - Campo `impresora_nombre` (nullable string, default: null).
- En la tabla `visitas_detalles`:
  - Campo `impreso_comanda` (boolean, default: false).
  - Permite saber qué ítems son nuevos y evitar reimprimir los platos ya enviados anteriormente.

### 1.2 Formateador de Comandas (`TicketFormatterService::formatComanda`)
- Formato a 42 columnas (80mm):
  - Encabezado: `*** ORDEN DE PRODUCCION: COCINA ***`
  - Mesa / Pedido / Garzón / Hora
  - Separador continuo
  - Ítems: `[CANTIDAD]x [NOMBRE PRODUCTO]` (en tamaño normal o doble)
  - Modificador: `>> NOTA: [OBSERVACIONES]` (en negrita)
  - Separador continuo y corte de papel automático.

### 1.3 Despachador de Impresión (`ComandaPrintDispatcherService`)
- Al invocar `POST /api/v1/comandas/enviar` o `ComandaController::enviarComanda`:
  1. Identifica los ítems recién agregados (`impreso_comanda = false`).
  2. Los agrupa por estación / impresora asignada.
  3. Para cada grupo, genera el payload binario ESC/POS y lo envía vía `WindowsDirectPrinterService::imprimirRaw($bytes, $impresora)`.
  4. Marca los ítems procesados como `impreso_comanda = true`.
  5. Si la impresora no existe o falla, atrapa la excepción y la registra en logs sin interrumpir la experiencia del usuario ni cancelar la comanda.
