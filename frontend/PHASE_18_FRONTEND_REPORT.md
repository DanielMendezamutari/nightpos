# Fase 18 — Frontend habitaciones

**Fecha:** 2026-06-02

> **Actualización 2026-06-08:** Formularios de habitación sin precio/duración obligatorios; registro de pieza con total + split chica/casa. Ver `ROOM_SERVICE_PRICING_MODEL_FIX_REPORT.md`.

---

## 1. Módulo Operación → Habitaciones

Rutas (`src/pages/nightpos/rooms/`):

| Ruta | Nombre | Permiso |
|------|--------|---------|
| `/nightpos/rooms` | redirect → dashboard | `rooms.access` |
| `/nightpos/rooms/dashboard` | `nightpos-rooms-dashboard` | `rooms.access` |
| `/nightpos/rooms/list` | `nightpos-rooms-list` | `rooms.access` |
| `/nightpos/rooms/available` | `nightpos-rooms-available` | `rooms.access` |
| `/nightpos/rooms/cleaning` | `nightpos-rooms-cleaning` | `rooms.access` |
| `/nightpos/rooms/maintenance` | `nightpos-rooms-maintenance` | `rooms.access` |
| `/nightpos/rooms/create` | `nightpos-rooms-create` | `rooms.create` |
| `/nightpos/rooms/:id/edit` | `nightpos-rooms-id-edit` | `rooms.update` |

Navegación: `src/navigation/vertical/nightpos-r4.js`.

Pestañas: `useRoomsSectionTabs.js` + `NightPosSectionTabs`.

---

## 2. Componentes Materialize

- `CardStatisticsVertical` — dashboard KPIs
- `VDataTable` — listados
- `VTabs` / `NightPosSectionTabs` — subpestañas
- `VChip` / `VBadge` — estados
- `VCard`, `VForm`, `VSelect`, `VTextField`, `VTextarea`
- `NightPosPageHeader` — breadcrumbs
- `NightPosFormActions` — crear/editar

---

## 3. Registrar pieza

`services/room-services/create.vue`:

- Selector de habitación desde `GET /rooms/available`
- Precio y duración se precargan al elegir habitación
- Payload con `room_id` (sin texto libre obligatorio)

---

## 4. API cliente

`src/api/rooms.js` — CRUD y acciones de estado.

---

## 5. Validación manual

1. `pnpm run dev`
2. Login admin → Habitaciones → dashboard (5 cards)
3. Registrar pieza → solo habitaciones `AVAILABLE`
4. Finalizar pieza → Limpieza → marcar limpia (PIN `3333`)
5. Consola sin errores críticos

---

## 6. Alta rápida de chica (registrar pieza)

- `QuickGirlCreateDialog.vue` — modal reutilizable
- `VAutocomplete` + “+ Nueva chica” en `room-services/create.vue`
- Ver `frontend/QUICK_GIRL_CREATE_REPORT.md`

---

## 7. Próxima fase

Pantallas de reportes por habitación cuando el backend exponga agregados (Fase 19).
