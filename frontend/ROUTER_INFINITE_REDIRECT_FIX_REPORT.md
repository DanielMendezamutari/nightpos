# ROUTER_INFINITE_REDIRECT_FIX_REPORT

Corrección del bucle infinito `"/" → "/login"` y pantalla en blanco al abrir `http://localhost:5173`.

---

## 1. Causa exacta

1. **`/login` no era ruta pública**  
   Solo tenía `unauthenticatedOnly`. El guard seguía evaluando sesión y permisos en cada visita a login.

2. **`resolveHomeRoute()` devolvía `{ name: 'login' }` con sesión aún “válida”**  
   Ejemplo: garzón con cookie antigua sin `waiter.dashboard`. En `/login` con `isLoggedIn === true`, el guard hacía `resolveHomeRoute` → otra vez `login` → **bucle infinito** (`/login` → `/login`).

3. **Doble redirección en `/`**  
   El redirect de `additional-routes.js` y el guard enviaban a login por separado; en algunos estados de sesión corrupta se encadenaban navegaciones contradictorias.

4. **Sesión corrupta sin limpieza única**  
   `accessToken` sin `userData` válido (o al revés) dejaba `isLoggedIn` inconsistente entre cookies crudas y Pinia.

---

## 2. Guard corregido

Archivo único: `src/plugins/1.router/guards.js` (registrado solo en `src/plugins/1.router/index.js`).

Orden del `beforeEach`:

1. **Ruta pública** (`isPublicRoute`) → continuar sin exigir token.
2. **`/login` con sesión** → `resolveHomeRoute`; si el home es login → limpiar sesión y **quedarse** en login (sin redirigir).
3. **Sin sesión** → si es `/` o `index`, dejar que el redirect de ruta mande a `/login`; otras rutas → login con `redirectIfDifferent`.
4. **Con sesión** → índice, garzón fuera de waiter, permisos (con `redirectIfDifferent` en todos los retornos).

Helpers: `src/utils/routerGuardHelpers.js`.

---

## 3. Rutas públicas

Lista central (`PUBLIC_ROUTE_PATHS`):

- `/login`
- `/not-authorized`
- `/pages/authentication/login-v1`
- `/pages/authentication/login-v2`
- `/pages/misc/not-authorized`
- `/404`
- `/500`

También se respeta `to.meta.public` (p. ej. `not-authorized.vue`, `login.vue`).

En rutas públicas el guard **no** exige token.

---

## 4. Cómo se evita redirigir a la misma ruta

Función `redirectIfDifferent(router, to, location)`:

- Resuelve el destino con `router.resolve(location)`.
- Si `fullPath` coincide con `to.fullPath` → no redirige (`undefined`).
- Si `path`, `name` y `query` coinciden → no redirige.

Casos especiales:

- Login con home = login → `purgeCorruptSession()` y permitir la vista (sin `return { name: 'login' }`).
- Índice sin sesión → guard no redirige; solo el redirect de ruta va a `/login` (una vez).

Limpieza de sesión corrupta: `clearAuthCookies()` / `authStore.clearSession()` en `loadGuardSession()` y al detectar home inválido en login.

---

## 5. Validación manual

| # | Paso | Esperado |
|---|------|----------|
| 1 | Borrar cookies `accessToken`, `userData`, `tenantSlug`, `branchCode` | — |
| 2 | Abrir `http://localhost:5173` | `/login`, sin loop |
| 3 | Recargar `/login` varias veces | Estable, sin loop |
| 4 | Login garzón PIN `5678` | `/nightpos/waiter` |
| 5 | F5 en waiter | Sigue en waiter o home correcto |
| 6 | Cerrar sesión | `/login` |
| 7 | Login cajero `1234` | Consola o caja |
| 8 | Login superadmin | Plataforma SaaS / tenants |

---

## Archivos tocados

| Archivo | Cambio |
|---------|--------|
| `src/utils/routerGuardHelpers.js` | Nuevo — públicas + `redirectIfDifferent` |
| `src/utils/authSession.js` | Sesión corrupta + `clearAuthCookies` |
| `src/plugins/1.router/guards.js` | Guard unificado sin bucles |
| `src/plugins/1.router/additional-routes.js` | `/` sin sesión → solo `/login` |
| `src/pages/login.vue` | `meta.public: true` |

---

*Un solo `beforeEach`. Backend y Materialize sin cambios.*
