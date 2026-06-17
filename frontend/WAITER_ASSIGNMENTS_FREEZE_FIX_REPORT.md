# Fix — UI congelada tras asignar mesas a garzón

**Fecha:** 2026-06-16  
**Severidad:** Urgente (bloqueo navegación)  
**Pantalla:** Personal → Asignar mesas

---

## Síntoma

Tras guardar una asignación garzón ↔ mesa:

- La URL cambia al navegar a otra sección.
- La UI no responde (clicks bloqueados).
- Parece congelamiento de layout/vista.

---

## Causa raíz

### 1. `overlaySafety.js` — `nextTick` sin import (bug crítico)

`setupOverlaySafety()` registraba `router.afterEach` con:

```javascript
nextTick(() => dismissStrayOverlays())
```

`nextTick` **no estaba importado** desde `vue` en un módulo `.js` plano.  
En runtime esto lanza `ReferenceError` en **cada navegación**, impidiendo limpiar overlays.

### 2. Overlays Vuetify huérfanos (VSelect)

En «Asignar mesas» hay dos `VSelect` (garzón, filtro salón). Si el menú/scrim queda activo al guardar o salir de la página, el overlay teletransportado a `<body>` bloquea pointer-events en toda la app.

`dismissStrayOverlays()` original solo:

- Quitaba `.layout-overlay.visible`
- Enviaba `Escape`

No limpiaba:

- `html.v-overlay-scroll-blocked`
- `.v-overlay--active` / scrim
- `pointer-events` / scroll lock en body

---

## Corrección aplicada

### `src/utils/overlaySafety.js`

- Import explícito: `import { nextTick } from 'vue'`
- `clearVuetifyScrollLock()` — remueve clases/estilos de scroll lock Vuetify
- `deactivateVuetifyOverlays()` — desactiva overlays `.v-overlay--active` huérfanos
- `blur()` del elemento activo antes de limpiar
- `countBlockingOverlays()` incluye scroll-blocked

### `staff/waiter-assignments/index.vue`

- Separar `catalogLoading` / `assignmentsLoading` (no `:loading` en VSelect ni VCard)
- `VProgressLinear` lineal en lugar de loader en card
- `closeOpenMenus()` — blur selects + `dismissStrayOverlays()`
- Llamar en: `save` (before/finally), `onBeforeRouteLeave`, `onBeforeUnmount`
- `:menu-props="{ scrollStrategy: 'close' }"` en VSelect
- `watch(selectedWaiterId)` con `nextTick` antes de cargar asignaciones
- VChip `size="large"` (valor válido Vuetify 3.5)

---

## Validación

| Check | Resultado |
|-------|-----------|
| `npm run build` | OK |
| `php artisan test --filter=WaiterTablesPhaseBTest` | OK |
| Backend sin cambios de negocio | ✅ |

### QA manual

1. Login admin / cajera senior  
2. Personal → Asignar mesas  
3. Elegir garzón, marcar mesa, Guardar → snackbar éxito  
4. Navegar Dashboard, Productos, volver a Asignar mesas  
5. Consola sin `ReferenceError: nextTick is not defined`  
6. UI siempre responde  

---

## No tocado

- CBA / combos  
- Liquidaciones  
- Fast Operation / SSE  
- Lógica comandas / API backend asignaciones  
