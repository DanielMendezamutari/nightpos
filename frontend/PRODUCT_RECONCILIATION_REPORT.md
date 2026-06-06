# Frontend — Conciliación de Productos (Vendidos vs. Comandados)

## Fase: Control de Cierre — Productos Vendidos vs. Comandados

Visualización de la conciliación de productos comandados vs. vendidos en reportes,
cierre de caja, cierre de turno y tickets/cierres imprimibles.

---

## API

`src/api/reports.js`

```js
fetchProductReconciliation({ dateFrom, dateTo, officialShiftId, cashSessionId, waiterUserId })
// GET /reports/product-reconciliation
```

Se añadió `cash_session_id` al `buildParams` compartido.

---

## Componentes

### `components/nightpos/reports/ProductReconciliationPanel.vue`

Panel reutilizable para la UI de la app. Recibe `data` (respuesta del endpoint) y muestra:

- Alerta "Hay diferencias entre productos comandados y vendidos" si `summary.has_differences`.
- Tarjetas resumen: productos, OK, con diferencias, solo venta directa.
- Tabla de comparación: `Producto | Comandado cobrado | Vendido | Diferencia | Estado`.
- (Opcional, `show-sold`) detalle de venta y bloque de venta directa.

Props: `data`, `loading`, `title`, `showSold`.

### `components/nightpos/print/PrintableReconciliationSection.vue`

Sección compacta para los tickets imprimibles (estilo `nightpos-print-row`):

- **Productos vendidos** (cantidad × producto · total)
- **Conciliación** (OK / total, líneas con diferencias)
- **Venta directa** (productos cobrados directo en caja)

Recibe `data` y se auto-oculta si no hay datos.

---

## Integraciones

| Pantalla | Ruta | Filtro usado |
|----------|------|--------------|
| Reportes → pestaña **Productos** | `/nightpos/finance/reports` | filtros globales (fecha, turno, caja, garzón) |
| **Cierre de caja** | `/nightpos/cash` | `cash_session_id` de la sesión actual |
| **Cierre de turno** | `/nightpos/shifts/close` | `official_shift_id` del turno (solo advierte, no bloquea) |
| Imprimible arqueo de caja | `/nightpos/print/cash` | `cash_session_id` |
| Imprimible caja histórica | `/nightpos/print/cash-session/:id` | `cash_session_id` |
| Imprimible cierre de turno | `/nightpos/print/shift/:id` | `official_shift_id` |

En **Reportes** se agregaron filtros de **ID Caja** e **ID Garzón** y un botón
"Exportar CSV" de la conciliación.

---

## Notas

- El cierre de turno **no se bloquea** por diferencias; solo se muestra advertencia.
- En vistas imprimibles la carga de conciliación usa `.catch(() => null)` para degradar
  con elegancia si el usuario no tiene `reports.access`.
- No incluye stock/Kardex: es solo conciliación de ventas vs. comandas (V2 hará inventario).
