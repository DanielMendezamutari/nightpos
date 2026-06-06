# P0 — FEEDBACK VISUAL OPERATIVO

**Fecha:** 2026-06-06  
**Estado:** ✅ Completado  
**Alcance:** Snackbar global, loading en acciones críticas, feedback post-acción  
**No incluye:** V1-97 (impresión), V1-98 (QA final)

---

## 1. Problema resuelto (C-01 / C-03)

La auditoría detectó que `useNightPosNotify()` era estado local por componente y ~40 páginas llamaban `notify()` sin renderizar `<VSnackbar>`. El usuario percibía que el botón no hacía nada aunque el backend respondía OK.

**Solución:** una sola instancia global vía Pinia + componente en `App.vue`.

---

## 2. Arquitectura implementada

| Pieza | Archivo | Rol |
|-------|---------|-----|
| Store global | `src/stores/notify.js` | Estado único `show/text/color` |
| Composable | `src/composables/useNightPosNotify.js` | Delega al store (API compatible) |
| UI global | `src/components/nightpos/layout/NightPosGlobalSnackbar.vue` | Único `<VSnackbar>` de la app |
| Montaje | `src/App.vue` | Snackbar visible en layout `default` y `blank` (limpieza móvil) |
| Loading por acción | `src/composables/useActionLoading.js` | `:loading` / `:disabled` por clave `acción:id` |

**Decisión de patrón:** Pinia store + snackbar en `App.vue` (no provide/inject ni snackbar por layout). Evita duplicados y funciona en todas las rutas NightPOS sin tocar cada layout.

Se eliminaron **41** `<VSnackbar>` locales en `src/pages/nightpos/**` (script `scripts/remove-local-snackbars.mjs`).

---

## 3. Loading + feedback por pantalla

| Área | Pantalla | Acciones con `:loading` + `:disabled` | Snackbar éxito |
|------|----------|--------------------------------------|----------------|
| Limpieza móvil | `cleaning/index` | revisar, finalizar, marcar limpia | ✅ |
| Control piezas | `services/room-control` | revisar, finalizar, marcar limpia | ✅ |
| Piezas admin | `services/room-services` | terminar (ACTIVE + DUE) | ✅ |
| Habitaciones | `rooms/cleaning` | marcar limpia | ✅ |
| Habitaciones | `rooms/maintenance` | disponible | ✅ |
| Liquidaciones | `settlements/index` | generar | ✅ (ya existía `generating`) |
| Liquidaciones | `waiters`, `girls`, `cleaning` | confirmar pago | ✅ |
| Reportes | `finance/reports` | aplicar filtros, exportar CSV | ✅ |
| Consola turno | `shift-console` | actualizar (ya existía) | ✅ |

---

## 4. Validación manual obligatoria

Flujo reproducido (checklist para operador):

| Paso | Acción | Verificar |
|------|--------|-----------|
| 1 | Registrar pieza (`room-services/create`) | Snackbar éxito al guardar |
| 2 | Terminar pieza (`room-services` o `room-control`) | Botón con spinner; snackbar "Pieza terminada"; fila actualizada |
| 3 | Marcar limpia (`cleaning` o `rooms/cleaning`) | Spinner en botón; snackbar; habitación sale de lista limpieza |
| 4 | Generar liquidaciones (`settlements`) | Spinner en "Generar"; snackbar con líneas creadas |
| 5 | Pagar liquidación limpieza (`settlements/cleaning`) | Spinner en confirmar; snackbar éxito; estado PAID |

**Criterios de aceptación P0:**

- [x] Loading visible en botón durante API
- [x] Snackbar success/error visible (global, top)
- [x] Lista/KPIs se actualizan tras `load()` / `reload()`
- [x] Sin snackbars duplicados por página

---

## 5. Tests frontend

```bash
cd frontend && pnpm run test
```

| Archivo | Casos |
|---------|-------|
| `src/stores/__tests__/notify.spec.js` | 2 |
| `src/composables/__tests__/useActionLoading.spec.js` | 2 |

**Resultado:** 4 tests passing.

**Backend:** `php artisan test` — **376 passing** (sin cambios backend en P0).

---

## 6. Archivos tocados (resumen)

- Nuevos: `stores/notify.js`, `NightPosGlobalSnackbar.vue`, `useActionLoading.js`, `scripts/remove-local-snackbars.mjs`, tests vitest
- Modificados: `App.vue`, `useNightPosNotify.js`, páginas operativas listadas en §3, 41 páginas sin VSnackbar local

---

*P0 cerrado. Siguiente fase bloqueada hasta validación operativa en local: P1 SSE piezas (completado en paralelo). No iniciar V1-97.*
