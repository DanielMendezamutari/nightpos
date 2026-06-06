# Fase C2 — Consola operativa de turno (Frontend)

**Fecha:** 2026-06-02  
**Referencias:** `PHASE_C1_REPORT.md`, `SYSTEM_QUICK_ACTIONS_AUDIT.md`

## Resumen

Pantalla única de operación del turno, mejoras en agregar producto a comanda y navegación. Sin reportes avanzados, impresión, clientes ni mesas.

## C2-1 Consola de turno

**Ruta:** `/nightpos/shift-console` (`nightpos-shift-console`)  
**Menú:** Operación → Consola de turno  
**Permiso:** `shift_console.access`

### Cards principales (8)

Turno, caja, comandas abiertas, pendientes de cobro, habitaciones ocupadas, limpieza, piezas vencidas (badge), liquidaciones pendientes.

### Secciones

| Sección | Contenido |
|---------|-----------|
| Caja | Cajera, apertura, totales por método, esperado |
| Habitaciones | Chips por estado |
| Alertas | Mensajes del API + enlace a control de piezas |
| Servicios | Manillas, piezas activas, shows |
| Comandas | Tabs: abiertas, barra, pend. cobro, cobradas (tabla clicable) |

- Auto-refresh cada 30 s.
- Sonido en piezas vencidas nuevas (`useRoomDueAlerts`).
- Layout responsive (tablet cajera / escritorio admin).

## C2-2 Agregar producto más rápido

`OrderAddProductDialog` + `useOrderProductShortcuts` (localStorage):

- Favoritos (estrella en lista).
- Últimos usados (botones grandes).
- Buscador más visible (`solo-filled`).
- Orden: recientes → favoritos → catálogo.

## C2-3 Limpieza / piezas vencidas

- Card «Piezas vencidas» con badge y enlace a `nightpos-services-room-control`.
- Alertas de limpieza y piezas en consola.

## C2-4 Cajera senior

Rol demo `cashier_senior` en seeder (backend). Cajero normal sin cambios. Documentación de permisos en `backend/PHASE_C2_REPORT.md`.

## Archivos

- `src/pages/nightpos/shift-console/index.vue`
- `src/api/shiftConsole.js`
- `src/composables/useOrderProductShortcuts.js`
- `src/components/nightpos/orders/OrderAddProductDialog.vue`
- `src/pages/nightpos/orders/[id].vue`
- `src/navigation/vertical/nightpos-r4.js`

## Validación manual (`pnpm run dev`)

1. Login admin (`admin.demo` / `AdminDemo123!`) o cajera PIN `1234`, sucursal CENTRO.
2. Operación → **Consola de turno**.
3. Ver caja, comandas, habitaciones (P3 en limpieza), servicios y alertas.
4. Abrir comanda → agregar producto con favoritos/últimos.
5. Registrar pieza / marcar habitación limpia → Actualizar consola.
6. Consola del navegador sin errores críticos.

## Próxima fase recomendada

Dashboard de cierre nocturno o notificaciones push para piezas vencidas (fuera de alcance C2).
