# HOME_REDIRECT_GUARD_FIX_REPORT

Corrección urgente: `http://localhost:5173` redirigía a `/not-authorized` (401) en lugar del destino correcto por rol.

---

## 1. Causa exacta

Varios factores se combinaban en la ruta `/`:

1. **Detección incompleta de garzón en cookies**  
   `isWaiterStaff()` solo miraba `staffRole` (camelCase). El usuario en cookie trae `staff_role` (API). Solo coincidía si `role === 'waiter'`. En la práctica funcionaba para el seed, pero la lógica era frágil.

2. **Home único para todos los no-garzones**  
   `defaultHomeRouteName()` enviaba a `nightpos-dashboard` a cajeros, superadmin sin contexto, etc. El dashboard operativo no es el home correcto para todos.

3. **Orden del guard y permisos en rutas “home”**  
   El guard evaluaba `to.meta.permission` **antes** de tratar `/` como resolución de inicio. Un garzón (o sesión antigua sin `waiter.dashboard` en cookie) que caía en `nightpos-waiter` o en otra ruta con permiso faltante recibía `not-authorized` en lugar de re-login o home válido.

4. **Ruta `/` y garzón fuera de `/nightpos/waiter`**  
   Para `to.path === '/'`, el bloque “garzón solo en waiter” podía interactuar mal con el redirect de `index` según el orden de navegación.

5. **`/not-authorized` como efecto colateral**  
   No era un acceso manual sin permiso: era el destino tras fallar el permiso de la primera ruta post-login o post-`/`.

---

## 2. Archivos corregidos

| Archivo | Cambio |
|---------|--------|
| `src/utils/resolveHomeRoute.js` | **Nuevo** — `resolveHomeRoute()`, reglas por rol/contexto/permisos |
| `src/utils/waiterRouting.js` | Reexporta desde `resolveHomeRoute` (compatibilidad) |
| `src/utils/authSession.js` | `readContextFromCookies()` para guards sin Pinia |
| `src/plugins/1.router/guards.js` | `/` → `resolveHomeRoute`; permiso en home → login refresh; garzón después de índice |
| `src/plugins/1.router/additional-routes.js` | Redirect `/` usa `resolveHomeRoute` + contexto cookies |
| `src/pages/login.vue` | Post-login → `resolveHomeRoute` con contexto Pinia |
| `src/components/nightpos/PlatformContextSelector.vue` | Tras cambiar contexto → home según rol |
| `src/layouts/components/UserProfile.vue` | Acción perfil / home → `resolveHomeRoute` |
| `src/pages/not-authorized.vue` | “Back to Home” → home resuelto, no `to="/"` ciego |

---

## 3. Nueva regla de redirección (`resolveHomeRoute`)

| Condición | Destino (nombre de ruta) |
|-----------|---------------------------|
| Sin usuario / sin sesión válida | `login` |
| Garzón (`staff_role` WAITER o `role` waiter) + `waiter.dashboard` | `nightpos-waiter` |
| Garzón sin `waiter.dashboard` | `login` (refrescar sesión) |
| `super_admin` sin `tenantSlug` en contexto | `nightpos-platform-tenants` (o `nightpos-platform-dashboard` si falta listado) |
| `super_admin` con tenant/contexto | `nightpos-dashboard` |
| Cajero (`role` cashier o `staff_role` CASHIER) | `nightpos-shift-console` → si no, `nightpos-cash` → si no, dashboard |
| Admin / manager / owner / cashier_senior | `nightpos-dashboard` |
| Fallback por permisos conocidos | shift-console → cash → waiter → platform tenants → dashboard |
| `/not-authorized` | Solo si el usuario **ya autenticado** entra a una ruta concreta sin permiso y **no** es su home resuelto |

**Uso obligatorio en:** `/`, post-login, cambio de contexto (selector plataforma), botón Home en 401.

---

## 4. Validación manual

Con `pnpm run dev` en `frontend/`:

| # | Paso | Resultado esperado |
|---|------|-------------------|
| 1 | Cerrar sesión, abrir `http://localhost:5173` | `/login` |
| 2 | Login garzón PIN `5678` | `/nightpos/waiter` |
| 3 | Logueado como garzón, abrir `http://localhost:5173` | `/nightpos/waiter`, no `/not-authorized` |
| 4 | Login cajero PIN `1234` | `/nightpos/shift-console` (o caja si no tiene consola) |
| 5 | Login admin | `/nightpos/dashboard` |
| 6 | Login superadmin sin contexto tenant | `/nightpos/platform/tenants` |
| 7 | Entrar manualmente a ruta sin permiso (ej. settings admin como garzón) | `/not-authorized` (correcto) |

**Nota:** Si tras C4 la cookie no incluye `waiter.dashboard`, cerrar sesión y volver a entrar (o borrar cookies `accessToken` / `userData`).

---

## 5. Qué queda pendiente

- Sustituir redirects hardcodeados a `nightpos-dashboard` en `platform/setup.vue`, `platform/branches/*.vue` por `resolveHomeRoute` (mismo patrón que el selector de contexto).
- Mensaje UX en login cuando `reason=session_refresh`.
- Prueba E2E automatizada de la matriz rol × `/`.
- Unificar “Home” del menú vertical NightPOS con `resolveHomeRoute` si se añade enlace explícito a `/`.

---

## 6. Actualización — bucle infinito `/` ↔ `/login` (Jun 2026)

**Síntoma:** pantalla blanca, consola: `Infinite redirect in navigation guard` desde `/` a `/login`.

**Causa:** en `/login` con sesión inválida, `resolveHomeRoute` devolvía otra vez `login`; el guard redirigía sin comprobar si el destino era la ruta actual.

**Corrección:** ver `ROUTER_INFINITE_REDIRECT_FIX_REPORT.md`.

- Rutas públicas (`PUBLIC_ROUTE_PATHS` + `meta.public` en login).
- `redirectIfDifferent()` en todas las redirecciones del guard.
- Sesión corrupta → `clearAuthCookies()` / `clearSession()` una vez.
- En `/` sin sesión, el guard no compite con el redirect de índice (solo `/login` desde `additional-routes.js`).

---

*Backend no modificado. Componentes Materialize intactos.*
