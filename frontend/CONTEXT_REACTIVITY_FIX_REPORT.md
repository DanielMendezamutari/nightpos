# CONTEXT_REACTIVITY_FIX_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend  
**Bug:** Menú lateral y módulos operativos no se actualizan al elegir empresa/sucursal (requería F5)  
**Fecha:** 2026-06-02

---

## 1. Causa exacta

`useCookie()` en `@core/composable/useCookie.js` **creaba una ref nueva en cada llamada** al mismo nombre. Al guardar contexto en `usePlatformContext`, otras partes (`useNightPosNavItems`, `operational.refreshContext`, dashboard) leían **otra ref** inicializada solo al montar, sin sincronización.

Efectos:

- Menú lateral seguía con `hasOperationalContext = false` tras elegir empresa.
- Navbar no reflejaba tenant/branch hasta recargar.
- `BranchChangeDialog` forzaba `window.location.reload()`.
- Axios podía enviar headers desactualizados si el store Pinia no estaba alineado.

---

## 2. Archivos modificados

| Archivo | Cambio |
| ------- | ------ |
| `src/stores/context.js` | **Nuevo** — Pinia fuente de verdad (`tenantSlug`, `branchCode`, `version`, getters, `applyContext`, `clearContext`) |
| `src/composables/useOnContextChange.js` | **Nuevo** — watch de `context.version` para recargar módulos |
| `src/composables/usePlatformContext.js` | Delega al context store (`storeToRefs`) |
| `src/composables/useNightPosNavItems.js` | Usa context store + `navMenuKey` para remontar menú |
| `src/@core/composable/useCookie.js` | Caché singleton por nombre de cookie |
| `src/stores/operational.js` | `refreshContext` lee `context.tenantSlug` |
| `src/stores/auth.js` | Sincroniza context en login/logout |
| `src/services/http.js` | Headers `X-Tenant-Slug` / `X-Branch-Code` desde context store |
| `src/plugins/0.pinia.js` | `hydrateFromCookies()` al iniciar |
| `src/plugins/1.router/guards.js` | Hidrata context en cada navegación |
| `src/layouts/components/DefaultLayoutWithVerticalNav.vue` | `:key="navMenuKey"` en layout vertical |
| `src/components/nightpos/BranchChangeDialog.vue` | Sin `reload`; usa `applyContext` |
| `src/components/nightpos/NightPosNavbarContext.vue` | `useOnContextChange` + aviso sucursal |
| `src/components/nightpos/PlatformContextSelector.vue` | Redirige a dashboard si hay sucursal |
| `src/components/nightpos/layout/NightPosContextCards.vue` | Alerta «Seleccione sucursal» |
| `src/pages/nightpos/dashboard.vue` | Reactivo a cambio de contexto |
| `src/pages/nightpos/cash/index.vue` | Recarga sesión de caja al cambiar contexto |
| `src/pages/nightpos/orders/index.vue` | Recarga comandas |
| `src/pages/nightpos/sales/index.vue` | Recarga ventas |
| `src/pages/nightpos/shifts/current.vue` | Recarga turno |
| `src/pages/nightpos/platform/tenants/index.vue` | «Operar» sin F5 |
| `src/pages/nightpos/platform/branches/index.vue` | «Operar aquí» sin F5 |

---

## 3. Cómo se resolvió la reactividad

1. **Pinia `nightposContext`** como única fuente reactiva de `tenantSlug` / `branchCode`.
2. **Cookies** solo persisten vía `persistToCookies()` (y refs singleton de `useCookie` alineadas).
3. **`context.version`** incrementa en cada cambio; `useOnContextChange` dispara recargas de datos.
4. **Menú:** `computed` sobre getters del store + `:key="navMenuKey"` fuerza re-render del `VerticalNavLayout`.
5. **HTTP:** interceptor lee siempre el context store cuando Pinia está activo.

---

## 4. Datos que se limpian / recargan al cambiar contexto

| Área | Comportamiento |
| ---- | -------------- |
| `operational` store | `refreshContext()` — tenant, branch, branches |
| Navbar | Caja y turno vuelven a consultarse |
| Dashboard | KPIs y atajos se recalculan |
| Caja / Comandas / Ventas / Turno actual | `useOnContextChange` → nueva petición API |
| Menú lateral | Filtrado por `hasOperationalContext` sin F5 |

No se usa `window.location.reload` ni `setTimeout`.

---

## 5. Validación manual

Con `pnpm run dev`:

1. Login `superadmin` / `SuperAdmin123!` sin empresa → solo **Plataforma SaaS** en menú.
2. **Elegir empresa** `casa-demo` (navbar o listado empresas) → sin F5: menú muestra secciones operativas; navbar muestra empresa; aviso **«Seleccione sucursal»** si no hay branch.
3. **Operar aquí** en sucursal `CENTRO` → sin F5: chips sucursal, módulos Caja/Comandas/Ventas/Turnos visibles.
4. Entrar a **Caja** sin F5 → datos de la sucursal correcta.
5. Cambiar sucursal (diálogo o plataforma) → Caja/Comandas se actualizan sin recargar navegador.
6. **Cerrar sesión** → contexto y cookies limpios.

---

## 6. Tests

No hay Vitest configurado en el frontend. Validación documentada arriba.

Prueba conceptual del store (consola dev):

```js
const ctx = useContextStore()
await ctx.applyContext({ tenantSlug: 'casa-demo', branchCode: 'CENTRO' })
// navItems y operational.tenant deben actualizarse sin reload
```

---

## 7. Pendientes

- Extender `useOnContextChange` a más páginas (detalle de comanda, edición producto) si quedan datos cacheados en refs locales.
- Tests unitarios del context store cuando se añada Vitest.
- Opcional: evento global `nightpos:context-changed` para plugins de terceros.
