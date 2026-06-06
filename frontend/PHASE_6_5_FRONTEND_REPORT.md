# PHASE_6_5_FRONTEND_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend  
**Fase:** 6.5 — Base Materialize + visualización inicial  
**Fecha:** 2026-06-02  
**Referencias:** `FRONTEND_GUIDELINES.md`, `API_DOCUMENTATION.md`, `backend/PHASE_4_REPORT.md` … `PHASE_6_REPORT.md`

---

## 1. Pantallas creadas

| Ruta | Archivo | Descripción |
| ---- | ------- | ----------- |
| `/login` | `src/pages/login.vue` | Login PIN (primario) + usuario/contraseña |
| `/nightpos/dashboard` | `src/pages/nightpos/dashboard.vue` | Dashboard con sesión y accesos rápidos |
| `/nightpos/products` | `src/pages/nightpos/products/index.vue` | Listado, precios, alta producto/precio |
| `/nightpos/categories` | `src/pages/nightpos/categories/index.vue` | Listado y alta de categorías |

El menú lateral muestra solo el bloque **NightPOS**; la navegación demo de Materialize permanece en sus archivos (`dashboard.js`, `apps-and-pages.js`, etc.) sin exportarse en `navigation/vertical/index.js`.

---

## 2. Componentes Materialize reutilizados

| Componente plantilla | Uso NightPOS |
| -------------------- | ------------ |
| `VerticalNavLayout` / `default` layout | Shell autenticado |
| `blank` layout | Login |
| `VCard`, `VCardTitle`, `VCardText` | Dashboard, tablas, formularios |
| `VDataTable` | Productos, precios, categorías |
| `VDialog` | Modales crear producto / precios / categoría |
| `VTabs` / `VWindow` | Login PIN vs contraseña |
| `VTextField`, `VSelect`, `VBtn`, `VChip` | Formularios y estados |
| `VSnackbar` | Feedback de API |
| `VNodeRenderer` + `themeConfig` | Logo y marca |
| Ilustraciones auth v2 | Pantalla login |
| `UserProfile` (adaptado) | Cerrar sesión real |

No se eliminaron componentes demo de la plantilla.

---

## 3. Endpoints usados (backend real)

Base en desarrollo (XAMPP):

- Backend: `http://nightpos.test` (document root = `backend/public`)
- API: `http://nightpos.test/api/v1`
- Frontend dev (`pnpm run dev`): `VITE_API_BASE_URL=/api/v1` + proxy Vite → `http://nightpos.test`

| Método | Ruta |
| ------ | ---- |
| POST | `/auth/login-pin` |
| POST | `/auth/login-password` |
| POST | `/auth/logout` |
| GET | `/auth/me` |
| GET | `/tenant/current` |
| GET | `/branches/current` |
| GET | `/branches/available` |
| GET | `/products` |
| POST | `/products` |
| GET | `/products/{id}/prices` |
| POST | `/products/{id}/prices` |
| GET | `/product-categories` |
| POST | `/product-categories` |

Headers operativos: `Authorization`, `X-Branch-Code`, `X-Tenant-Slug` (cookies).

---

## 4. Configuración técnica

| Pieza | Ubicación |
| ----- | --------- |
| Axios + interceptores | `src/services/http.js` |
| Pinia auth | `src/stores/auth.js` |
| Pinia contexto | `src/stores/operational.js` |
| API productos/categorías | `src/api/products.js`, `src/api/categories.js` |
| Permisos UI | `src/composables/useNightPosPermissions.js` |
| Guards router | `src/plugins/1.router/guards.js` |
| MSW plantilla | Desactivado por defecto (`VITE_USE_MSW=false`) |
| Proxy dev | `vite.config.js` → `/api` → Laravel |

Token y usuario en cookies (`accessToken`, `userData`, `tenantSlug`, `branchCode`).

---

## 5. Qué se puede probar visualmente

1. **Login PIN:** `casa-demo` / `CENTRO` / `1234` (cajero) o `5678` (garzón).
2. **Login admin:** `admin.demo` / `AdminDemo123!` + slug `casa-demo`.
3. **Dashboard:** nombre, rol, empresa, sucursal.
4. **Productos:** listar; como admin crear producto y precios SOLO / CON_ACOMPANANTE con split chica/casa.
5. **Categorías:** listar; crear si rol `products.create`.
6. **Permisos:** cajero ve productos sin botón crear; sin token redirige a login.

Requisito: vhost `nightpos.test` apuntando a `backend/public`, base de datos migrada (`php artisan migrate --seed`). No hace falta `php artisan serve` si usas XAMPP.

```bash
cd frontend
pnpm install
pnpm run dev
```

En desarrollo usar siempre `pnpm run dev` (el proyecto declara `packageManager: pnpm@9.0.6`).

---

## 6. Qué NO se implementó

- Comandas, caja, ventas, turnos, inventario, liquidaciones, reportes operativos.
- Mesas, impresión, admin SaaS global en UI.
- Edición de productos/precios (solo creación y listado).
- DataTables avanzados (se usa `VDataTable` de Vuetify).
- Refresh token.
- i18n específico NightPOS.

---

## 7. Próxima fase recomendada

**Fase 7 — Comandas (frontend + backend)**

1. Pantalla comanda móvil (garzón): mesa, búsqueda producto, modalidad SOLO / CON_ACOMPANANTE.
2. Consumir precios resueltos desde API (sin calcular totales en frontend).
3. Habilitar acceso rápido “Comandas” en dashboard cuando exista el módulo.

---

*Fase 6.5 completada. Plantilla Materialize intacta; menú demo oculto, no eliminado.*
