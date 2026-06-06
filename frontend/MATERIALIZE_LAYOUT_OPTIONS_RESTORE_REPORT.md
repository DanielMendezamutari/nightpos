# MATERIALIZE_LAYOUT_OPTIONS_RESTORE_REPORT.md

**Proyecto:** NightPOS — Frontend Vue 3 + Materialize Admin Template  
**Fecha:** 2026-06-02  
**Referencias:** `PHASE_R1_REPORT.md`, `PHASE_R4_REPORT.md`, `PHASE_C4_WAITER_REPORT.md`, `FRONTEND_AUDIT_REPORT.md`, `NIGHTPOS_OPERATION_FLOW_AUDIT.md`

---

## 1. Qué se había ocultado

| Elemento | Estado previo | Impacto |
| -------- | --------------- | ------- |
| `TheCustomizer.vue` | Componente intacto en `src/@core/components/` pero **no montado** en `App.vue` | Botón flotante de configuración (ícono engranaje) y panel Theme Customizer invisibles para todos los roles |
| Sidebar colapsado | `DefaultLayoutWithVerticalNav.vue` forzaba `isVerticalNavCollapsed = false` en `onMounted` y con un `watch` | Opción **collapsed** del customizer no tenía efecto en rutas NightPOS |
| Pin/unpin del nav vertical | CSS ocultaba `.nav-pin` y `.nav-unpin` con `display: none !important` | No se podía colapsar el menú lateral desde el header |
| Layout `blank` garzón/limpieza | Sin cambios (correcto) | Rutas `/nightpos/waiter` y `/nightpos/cleaning` siguen sin sidebar administrativo |

El customizer no fue eliminado en R1/R4; dejó de mostrarse al no incluirse en el árbol de render de `App.vue`. R1 documentaba su presencia, pero la integración global nunca quedó cableada en el fork NightPOS.

---

## 2. Qué se restauró

1. **Montaje del Theme Customizer nativo** en `App.vue`, igual que la plantilla Materialize original (`TheCustomizer` + `ScrollToTop`).
2. **Visibilidad condicional por rol** mediante `useShowMaterializeCustomizer()` — solo usuarios no móviles operativos y con layout distinto de `blank`.
3. **Helper `isMobileOperationalRole(user)`** en `src/utils/mobileOperationalRole.js` para centralizar la detección de roles móviles.
4. **Eliminación del bloqueo de colapso** en `DefaultLayoutWithVerticalNav.vue` para que vertical / horizontal / collapsed / theme / skin / content width / direction vuelvan a persistir en cookies/localStorage como define Materialize (`cookieRef`, `useStorage` en `TheCustomizer` y `@layouts/stores/config`).

Persistencia: sin cambios en el mecanismo nativo (`isVerticalNavCollapsed`, `appContentLayoutNav`, `theme`, `skin`, etc. en cookies).

---

## 3. Roles que pueden ver el customizer

| Rol / condición | Slug API típico | `staff_role` |
| --------------- | --------------- | ------------ |
| Superadmin | `super_admin` | — |
| Admin tenant | `admin` | — |
| Dueño tenant | `owner` / `tenant_owner` | — |
| Manager | `manager` | — |
| Cajera | `cashier` | `CASHIER` |
| Cajera senior | `cashier_senior` | — |

Estos usuarios pueden elegir:

- Layout: **vertical**, **horizontal**, **collapsed**
- **Theme** (light / dark / system)
- **Skin** (default / bordered)
- **Primary color**, **semi-dark menu**, **content width**, **direction** (LTR/RTL)

---

## 4. Roles que NO deben ver el customizer

| Rol | `staff_role` | Motivo |
| --- | ------------ | ------ |
| Garzón | `WAITER` | Experiencia móvil `/nightpos/waiter`, `layout: blank` |
| Limpieza | `CLEANING` | Experiencia móvil `/nightpos/cleaning`, `layout: blank` |
| Chica | `GIRL` | Modo móvil compartido con flujo garzón (auditoría B-04) |

Criterio técnico: `isMobileOperationalRole(user) === true` → customizer oculto.

Además se oculta en:

- Rutas con `meta.layout: 'blank'` (login, garzón, limpieza)
- Sesión no autenticada

---

## 5. Archivos modificados

| Archivo | Cambio |
| ------- | ------ |
| `src/utils/mobileOperationalRole.js` | **Nuevo** — `isMobileOperationalRole(user)` |
| `src/composables/useShowMaterializeCustomizer.js` | **Nuevo** — computed de visibilidad del customizer |
| `src/App.vue` | Monta `<TheCustomizer v-if="showMaterializeCustomizer" />` |
| `src/layouts/components/DefaultLayoutWithVerticalNav.vue` | Quita bloqueo de colapso y restaura pin/unpin del nav |
| `src/utils/waiterRouting.js` | Re-exporta `isMobileOperationalRole` |

**Sin modificar (conservados a propósito):**

- `src/@core/components/TheCustomizer.vue` — componente demo nativo intacto
- `src/pages/nightpos/waiter/*` — `layout: blank`
- `src/pages/nightpos/cleaning/*` — `layout: blank`
- `src/plugins/1.router/guards.js` — sandbox garzón/limpieza sin cambios
- Demos Materialize (`/apps/`, `/pages/`, etc.)

---

## 6. Validación manual

Comando: `pnpm run dev` en `frontend/`

| # | Paso | Resultado esperado |
| - | ---- | ------------------ |
| 1 | Login **admin** (`admin.demo`) | Botón engranaje visible borde derecho (viewport ≥ lg) |
| 2 | Abrir Theme Customizer | Panel con Theming + Layout |
| 3 | Cambiar **vertical → horizontal** | Layout cambia; menú horizontal Materialize (demos) |
| 4 | Cambiar **horizontal → collapsed** | Sidebar colapsado; pin/unpin visible en header |
| 5 | Login **cajera** (`1234`) | Customizer visible en caja/dashboard |
| 6 | Login **garzón** (`5678`) | **Sin** botón customizer; vista móvil waiter |
| 7 | Login **limpieza** (`3333`) | **Sin** botón customizer; vista móvil cleaning |
| 8 | Garzón en `/nightpos/waiter` | `layout: blank`, sin sidebar admin |
| 9 | Limpieza en `/nightpos/cleaning` | `layout: blank`, sin sidebar admin |
| 10 | Consola del navegador | Sin errores relacionados con customizer o layout |

**Nota:** En layout **horizontal**, el menú superior usa la navegación demo de Materialize (`navigation/horizontal`), no el menú NightPOS R4. Es el comportamiento nativo de la plantilla al elegir horizontal; el menú NightPOS sigue disponible al volver a **vertical**.

---

## 7. Uso del helper en futuras fases

```js
import { isMobileOperationalRole } from '@/utils/mobileOperationalRole'

if (isMobileOperationalRole(user)) {
  // ocultar customizer, forzar blank, bloquear cambio de layout
}
```

También disponible vía auto-import (`unplugin-auto-import`) y re-export en `@/utils/waiterRouting`.

---

*Implementación acotada a visibilidad y desbloqueo de opciones nativas Materialize; no se eliminaron componentes demo ni se reemplazó el sistema de layout.*
