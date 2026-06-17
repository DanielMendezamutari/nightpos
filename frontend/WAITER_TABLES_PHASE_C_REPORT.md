# Fase C — Mis mesas garzón + config admin (Frontend)

**Fecha:** 2026-06-16  
**Estado:** ✅ Completado  
**Par backend:** `backend/WAITER_TABLES_PHASE_B_REPORT.md`

---

## Objetivo UX

El garzón **no escribe mesa**. Abre la app → ve sus mesas → **un toque** abre o continúa la comanda.

---

## Experiencia garzón

### Home (`/nightpos/waiter`) — «Mis mesas»

- Reemplaza el dashboard KPI + «Nueva comanda» como flujo principal.
- **Grid táctil** 2–3 columnas, mínimo 112px por celda.
- Estados claros:
  - **Libre** — borde/ fondo verde suave, hint «Tocar para abrir»
  - **Ocupada** — borde primario, total de comanda visible
- Resumen chips: `X libres` / `Y ocupadas`
- Copy: *«Toca una mesa… No necesitas escribir nada.»*
- **Feedback al tocar:** overlay loading en la celda; toast «Comanda abierta» o «Continuando Mesa N»
- **SSE + polling 30s** — mesa vuelve a Libre al cobrar en caja
- **Refresh** al volver desde detalle de comanda (`onActivated`)
- **Empty state** amigable si no hay mesas asignadas — sin opción de comanda manual
- **SSE + polling 30s** — mesa nueva aparece al asignarla; mesa libre al cobrar en caja

### Bottom nav

| Tab | Ruta | Rol |
|-----|------|-----|
| Mesas | `nightpos-waiter` | Flujo principal |
| Comandas | `nightpos-waiter-orders` | Lista secundaria |

### Sin mesas asignadas

- Mensaje: *«No tienes mesas asignadas. Pide a la cajera o administradora…»*
- Botón **Actualizar** (SSE/polling también refresca)
- **No** hay botón «Otra mesa» ni comanda manual

### Mesa extra no asignada

La cajera/admin asigna en **Personal → Asignar mesas**. El garzón la ve en Mis mesas vía SSE/polling sin cerrar sesión.

### Ruta legacy `waiter/orders/new.vue`

Conservada como fallback admin/dev (table_label). Garzones redirigidos a Mis mesas (router guard + redirect en página).

---

## Componentes nuevos

| Archivo | Rol |
|---------|-----|
| `WaiterTableTile.vue` | Celda LIBRE/OCUPIADA, área táctil grande, aria-label |
| `WaiterTablesGrid.vue` | Agrupación por salón |
| `useWaiterTables.js` | API + tap + SSE + polling + navegación |

---

## Admin / cajera senior

| Ruta | Función |
|------|---------|
| `settings/service-tables` | CRUD mesas por salón (Configuración → Mesas) |
| `staff/waiter-assignments` | Garzón + chips multiselect mesas (Personal → Asignar mesas) |

Navegación R4 y section tabs actualizados.

---

## API client

- `api/serviceTables.js`
- `api/waiter.js` — `fetchWaiterMyTables`, `openWaiterTable`

---

## QA manual sugerido

1. Admin: salón VIP → Mesa 1, Mesa 2
2. Admin: asignar ambas a `garzon.demo`
3. Garzón login → ve grid → toca libre → comanda + add producto
4. Volver a Mesas → ocupada con total
5. Tocar ocupada → misma comanda
6. Cajera cobra → SSE/polling → mesa libre
7. Garzón sin mesas → mensaje claro + Actualizar; no puede abrir comanda manual
8. Admin asigna mesa con garzón logueado → aparece en Mis mesas

---

## Bugfix — UI congelada tras guardar asignación (2026-06-16)

Tras guardar en Personal → Asignar mesas, la URL cambiaba pero la UI quedaba bloqueada.

**Causa:** `overlaySafety.js` usaba `nextTick` sin import → `ReferenceError` en cada navegación; overlays de `VSelect` (scrim + scroll lock) no se limpiaban.

**Fix:** import de `nextTick`, limpieza Vuetify scroll/overlays en `dismissStrayOverlays()`, cleanup en página de asignaciones (`closeOpenMenus`, `scrollStrategy: 'close'`, loading sin overlay de card).

Doc: `frontend/WAITER_ASSIGNMENTS_FREEZE_FIX_REPORT.md`

---

## Pulido UX — solo mesas asignadas (2026-06-16)

Eliminado flujo «Otra mesa» / comanda manual para garzones:

- Sin tab ni botón «Otra mesa» en home y bottom nav
- Empty state mejorado + botón Actualizar
- `nightpos-waiter-orders-new` bloqueada para staff_role WAITER (redirect a Mis mesas)
- Mesa extra: cajera asigna en Personal → Asignar mesas; SSE/polling la muestra al garzón

---

## Pendiente (Fase D)

Unificar copy manillas; mover manillas manuales a Configuración / Avanzado.
