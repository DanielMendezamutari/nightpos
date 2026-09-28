# Especificación Funcional: Módulo 014 — Impresión Directa de Comandas a Cocina y Barra (RestoTech Faithful)

**Módulo**: `014-impresion-comandas-cocina-barra`  
**Estado**: Especificado  
**Enfoque**: Spec-Driven Development (SDD)  
**Fuente de Verdad Legacy**: `ControlConsumoLib.dll` (`clsCategorias_Impresoras.cs`, `ImprimiendoComandas.cs` -> `printComandas`, `clsImpresoras.cs`).

---

## 1. Declaración del Problema & Objetivos de Negocio

### Problema:
1. En restaurantes y boliches, la preparación de alimentos y bebidas ocurre en estaciones físicas separadas (Cocina Caliente, Parrilla, Barra / Bartender).
2. Actualmente el sistema cuenta con el monitor digital KDS en pantalla, pero muchos locales gastronómicos tradicionales operan exclusivamente con **impresoras térmicas de tickets colgadas en cocina y barra**.
3. Al enviar una comanda desde el mapa de mesas o desde el celular del garzón, los cocineros y bármanes deben recibir su ticket físico impreso de inmediato con las notas de preparación destacadas (`* SIN CEBOLLA`, `* CON HIELO`).

### Objetivos:
- **O-1**: Mapear categorías de productos o estaciones a impresoras de destino de Windows (`COCINA`, `BARRA`, o impresora predeterminada).
- **O-2**: Al enviar una comanda (`POST /api/v1/comandas/enviar`), separar automáticamente los ítems nuevos:
  - Los productos con destino `COCINA` se envían en un ticket a la impresora física de Cocina.
  - Los productos con destino `BARRA` se envían en un ticket a la impresora física de Barra.
- **O-3**: Formato de comanda térmico idéntico a RestoTech:
  - Encabezado `*** COMANDA COCINA ***` o `*** COMANDA BARRA ***`.
  - Número de Mesa, Nombre del Garzón, Hora y Número de Pedido.
  - Detalle: Cantidad destacada, Nombre del Producto y Notas de Cocina en negrita.
  - Corte de papel automático.
- **O-4**: Impresión silenciosa vía Spooler RAW Win32 (`winspool.drv`), sin bloquear la experiencia táctil si una impresora remota está apagada o sin papel.

---

## 2. Historias de Usuario & Criterios de Aceptación (Gherkin)

### Historia 1: Enrutamiento y Despacho de Comandas por Estación (P1)
**Como** mesero o cajero,  
**Quiero** confirmar una comanda de comida y bebidas en la Mesa 2,  
**Para** que la cocina reciba el pedido de platos y el barman reciba el pedido de tragos sin confusiones.

- **Escenario 1.1: Comanda mixta con platos y bebidas**
  - **Given** una comanda con `1x Pique Macho` (Categoría Comida -> Impresora `COCINA`) y `2x Fernet Branca` (Categoría Bebidas -> Impresora `BARRA`),
  - **When** el usuario presiona "Enviar Comanda",
  - **Then** el backend genera dos trabajos de impresión independientes:
    - Ticket 1 enviado a la impresora `COCINA` con el Pique Macho y sus notas.
    - Ticket 2 enviado a la impresora `BARRA` con los Fernets.
  - **And** ambos tickets se cortan automáticamente en sus respectivas impresoras.

---

## 3. Contratos de Datos (API Contracts)

### Endpoint: `POST /api/v1/impresion/comanda/{visitaId}`
**Request**:
```json
{
  "detalles_ids": [12, 13],
  "forzar_todas": false
}
```

**Response 200 OK**:
```json
{
  "success": true,
  "message": "Comandas enviadas a impresoras de producción",
  "data": {
    "estaciones": [
      { "estacion": "COCINA", "impresora": "COCINA", "items": 1, "status": "IMPRESO" },
      { "estacion": "BARRA", "impresora": "BARRA", "items": 2, "status": "IMPRESO" }
    ]
  }
}
```
