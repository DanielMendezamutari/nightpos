# PHASE_R1_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Reconstrucción frontend (Materialize)  
**Fase:** R1 — Shell operativo, dashboard, login PIN, componentización comandas  
**Fecha:** 2026-06-03  
**Fuente principal:** `frontend/FRONTEND_AUDIT_REPORT.md`

---

## 1. Qué se reconstruyó

| Área | Cambio |
| ---- | ------ |
| Navbar operativa | Contexto NightPOS (tenant, sucursal, caja, turno placeholder, usuario, rol). Demo oculto con `v-show`, no eliminado. |
| User profile | Menú NightPOS: Mi perfil, Cambiar sucursal, Cerrar sesión. Menú demo conservado solo en rutas `/pages`, `/apps`, `/dashboards`, etc. |
| Dashboard | Panel con 8 `CardStatisticsVertical` (2 filas) + accesos rápidos. |
| Login PIN | Cookies 30 días para empresa/sucursal; PIN grande; contexto colapsable si ya recordado. |
| Comanda detalle | Dividida en 7 componentes + página contenedora ~280 líneas. |
| HTTP | 403 → `/not-authorized` (sin cerrar sesión); 401 → login + limpieza. |

---

## 2. Qué se mantuvo

- Layout `VerticalNavLayout`, sidebar NightPOS, footer, customizer, loading indicator.
- Archivos demo Materialize intactos (`dashboard.js`, `apps/*`, `pages/*`, componentes navbar demo).
- Pantallas productos, categorías, caja, ventas, listado comandas (lógica API sin cambios de negocio).
- `src/services/http.js`, stores `auth` / `operational`, guards router, CASL.
- Auth v2 login (estructura e ilustraciones).

---

## 3. Componentes Materialize reutilizados

| Componente plantilla | Uso R1 |
| -------------------- | ------ |
| `CardStatisticsVertical` | Dashboard filas 1 y 2 |
| `VerticalNavLayout` / `IconBtn` / `VChip` | Navbar y layout |
| `VCard`, `VDialog`, `VToolbar`, `VList`, `VMenu`, `PerfectScrollbar` | Perfil, modales comanda, login |
| `VTabs` / `VWindow` | Login PIN / contraseña |
| Auth v2 (`page-auth` scss, ilustraciones) | Login |
| `VSnackbar`, `VAlert`, `VBtn` size `x-large` | Operación móvil |

---

## 4. Componentes ocultados (no eliminados)

En rutas NightPOS (`showNightPosChrome`):

| Componente | Archivo |
| ---------- | ------- |
| `NavSearchBar` | `layouts/components/NavSearchBar.vue` |
| `NavbarShortcuts` | `layouts/components/NavbarShortcuts.vue` |
| `NavBarNotifications` | `layouts/components/NavBarNotifications.vue` |
| `NavbarThemeSwitcher` | `layouts/components/NavbarThemeSwitcher.vue` |
| `NavBarI18n` | `@core/components/I18n.vue` |

En menú perfil demo (solo rutas Materialize demo): Profile, Settings, Billing, Pricing, FAQ.

---

## 5. Componentes disponibles para futuras fases

Sin cambios — siguen en repo para reportes, analytics, CRM, liquidaciones, etc.:

- Dashboards `analytics`, `crm`, `ecommerce`
- Charts, forms, wizard-examples
- `AddPaymentMethodDialog` y resto de `dialog-examples`
- DataTables demo, notificaciones, i18n

---

## 6. Vistas / archivos refactorizados

### Nuevos (`src/components/nightpos/`)

| Archivo | Rol |
| ------- | --- |
| `NightPosNavbarContext.vue` | Chips contexto operativo en navbar |
| `BranchChangeDialog.vue` | Cambio sucursal (autenticado) |
| `orders/OrderHeader.vue` | Cabecera comanda |
| `orders/OrderItemsTable.vue` | Líneas de producto |
| `orders/OrderTotals.vue` | Total destacado |
| `orders/OrderActionsBar.vue` | Barra acciones táctil fija |
| `orders/OrderAddProductDialog.vue` | Fullscreen agregar producto |
| `orders/AssignGirlModal.vue` | Asignar chicas |
| `orders/ChargeOrderModal.vue` | Cobro + aviso caja cerrada |

### Nuevos composables

| Archivo | Rol |
| ------- | --- |
| `useNightPosShell.js` | Distingue rutas demo vs operativas |
| `useNightPosNotify.js` | Snackbar reutilizable |
| `useDashboardOperationalStats.js` | KPIs desde API existente |

### Modificados

| Archivo | Antes → Después |
| ------- | ---------------- |
| `layouts/components/DefaultLayoutWithVerticalNav.vue` | Navbar demo + contexto NightPOS condicional |
| `layouts/components/UserProfile.vue` | Dual menú demo / NightPOS |
| `pages/nightpos/dashboard.vue` | Placeholder → KPI cards Materialize |
| `pages/login.vue` | Contexto recordado + PIN móvil |
| `pages/nightpos/orders/[id].vue` | ~919 líneas → composición modular |
| `services/http.js` | Manejo 403 |

### Eliminaciones

**Ningún archivo Vue eliminado** (cumple regla de seguridad R1).

---

## 7. Datos del dashboard (sin inventar)

| KPI | Fuente |
| --- | ------ |
| Caja abierta/cerrada | `GET /cash/session/current` |
| Comandas abiertas | `GET /orders?status=OPEN` |
| Efectivo / QR / Tarjeta / Total sesión | `session.sales_by_method` + suma (sesión abierta) |
| Turno actual | **Placeholder** — sin API turnos |
| Ventas del día | **Placeholder** — sin endpoint reporte diario |

Subtítulos de cards indican explícitamente «Placeholder» o «sesión» donde aplica.

---

## 8. Experiencia garzón (móvil)

- Botones acción `min-block-size: 3.25rem`, bloque fijo al pie en detalle comanda.
- Diálogo agregar producto fullscreen.
- PIN con `inputmode="numeric"` y fuente ampliada.
- Navbar con chips scroll horizontal en pantallas pequeñas.
- Cobro: aviso si no hay caja abierta; botón cobrar visible con hint en barra de acciones.

**Pendiente validación física** en dispositivos Android/iPhone por el equipo (no automatizada en R1).

---

## 9. Vistas que requieren mejoras futuras (R2+)

| Vista | Fase sugerida |
| ----- | ------------- |
| Caja — cierre de turno con cards FRONTEND_GUIDELINES | R2 |
| Ventas — nombres cajero/garzón, detalle | R2 |
| Productos — layout tipo ecommerce list | R2 |
| Mesas / salas | Backend + UI |
| `PaymentMethodsForm` basado en `AddPaymentMethodDialog` | R2 |
| Pinia `useCashStore` / `useOrderStore` | R2 |
| Selector chicas sin permiso admin | API personal |

---

## 10. Referencias visuales (rutas plantilla)

- KPI: `src/pages/dashboards/analytics.vue` + `src/@core/components/cards/CardStatisticsVertical.vue`
- Listado admin: `src/pages/apps/ecommerce/product/list/index.vue`
- Pagos dialog: `src/pages/pages/dialog-examples/index.vue`
- Login base: `src/pages/pages/authentication/login-v2.vue`

---

## 11. Validación en modo dev

Durante el desarrollo se prioriza:

```bash
cd frontend
pnpm run dev
```

Checklist por fase visual:

- Rutas y navegación (`/login`, `/nightpos/*`)
- Consola del navegador (Vue / Pinia / Axios)
- Permisos CASL y guards
- Llamadas API con cookies `accessToken`, `tenantSlug`, `branchCode`
- Vista móvil (comandas, login PIN)

El build de producción (`npm run build`) se ejecuta cuando cierren varias fases funcionales, no como bloqueante de cada iteración.

---

## 12. Próxima fase recomendada

**Fase R2** (según auditoría):

1. Pantalla cierre de caja con grid de tarjetas (efectivo, QR, tarjeta, diferencia).
2. Re-skin productos con toolbar/filtros estilo ecommerce.
3. Ventas con nombres y detalle.
4. `useCashStore` compartido entre dashboard, navbar y cobro.

---

*Fase R1 completada. Materialize intacto; chrome operativo NightPOS alineado a auditoría. Sin eliminación de archivos de plantilla.*
