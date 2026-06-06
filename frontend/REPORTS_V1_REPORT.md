# Frontend Reports V1 Report

## Fase: V1-96 — Reportes y Cierre de Turno

**Fecha:** 2026-06-06

---

## Módulo de Reportes

### Ruta: `/nightpos/finance/reports`
**Archivo:** `src/pages/nightpos/finance/reports/index.vue`  
**Permiso:** `reports.access`

### Tabs implementadas:

| Tab | Datos mostrados |
|-----|----------------|
| **Resumen diario** | KPIs: total ventas, CASH/QR/CARD, servicios por tipo, liquidaciones, efectivo esperado, habitaciones |
| **Ventas** | Tabla con sale_number, tipo (comanda/directa), cajero, método pago, total, ítems, fecha |
| **Productos** | Conciliación vendidos vs. comandados (ver `PRODUCT_RECONCILIATION_REPORT.md`) |
| **Caja** | Tarjetas por sesión: apertura, ventas por método, ingresos/egresos manuales, diferencia, movimientos |
| **Servicios** | Tres tablas: piezas (con casa/chica/limpieza), manillas, shows |
| **Liquidaciones** | Tabla con personal, rol, tipo, total, estado, fecha pago |
| **Habitaciones** | Tabla con uso, ingresos, duración promedio, limpiezas por habitación |

### Filtros globales:
- Fecha desde / hasta
- ID Turno específico
- ID Caja (`cash_session_id`) e ID Garzón (`waiter_user_id`) — usados por la pestaña Productos
- Botón "Aplicar filtros" + "Limpiar"

### Export CSV:
- Botón por tab: ventas, servicios (combinado), liquidaciones, habitaciones
- Implementación nativa en browser con `Blob` + `URL.createObjectURL`

### API service:
**Archivo:** `src/api/reports.js`  
Funciones: `fetchDailyReport`, `fetchSalesReport`, `fetchCashReport`, `fetchServicesReport`, `fetchSettlementsReport`, `fetchRoomsReport`, `fetchShiftClosureCheck`, `fetchProductReconciliation`

---

## Cierre de Turno mejorado

### Ruta: `/nightpos/shifts/close`
**Archivo:** `src/pages/nightpos/shifts/close.vue`

### Nuevas alertas pre-cierre:

**Bloqueantes** (fondo rojo, impiden cierre):
- Cajas abiertas sin cerrar
- Piezas activas o vencidas

**Advertencias** (fondo amarillo, solo aviso):
- Liquidaciones pendientes de pago
- Sin liquidaciones generadas
- Habitaciones en limpieza
- Diferencia de caja detectada

**Todo en orden** (verde): cuando no hay bloqueantes ni advertencias.

### Bloqueo del botón "Cerrar turno":
- El botón queda deshabilitado (`saveDisabled=true`) cuando existen bloqueantes
- Se muestra mensaje de error en `notify()` si el usuario intenta igual

### Componente NightPosFormActions:
Se añadió prop `saveDisabled: Boolean` para soportar deshabilitar el botón de guardado desde el exterior.

---

## Navegación

Se añadió entrada **"Reportes"** en el menú lateral (`nightpos-r4.js`) bajo **Finanzas**, antes de "Cierre de turno". Requiere permiso `reports.access` (ya asignado a `tenant_owner`).
