# FRONTEND_AUDIT_REPORT.md

**Proyecto:** NIGHTPOS SaaS  
**Alcance:** Auditoría completa del frontend Vue 3 + Materialize Admin Template  
**Fecha:** 2026-06-03  
**Referencias revisadas:** `FRONTEND_GUIDELINES.md`, `README.md`, `ROADMAP.md`, `PROMPT_CURSOR.md`, `DOMAIN_DESIGN.md`, `SYSTEM_ANALYSIS.md`, `BOLICHE_RULES.md`, `PHASE_6_5_FRONTEND_REPORT.md`, `PHASE_7_5_FRONTEND_ORDERS_REPORT.md`, `PHASE_9_FRONTEND_REPORT.md`

---

## 1. Estado general del frontend

| Dimensión | Veredicto | Nota (1–5) |
| --------- | --------- | ------------ |
| Shell / layout Materialize | Aceptable con reservas | 4 |
| Pantallas operativas NightPOS | Funcionales, no maduras | 2.5 |
| Cumplimiento archivos maestros | Parcial | 2 |
| Experiencia POS / móvil garzón | Insuficiente para producción | 2 |
| Arquitectura frontend (módulos, stores, componentes) | No alineada a guías | 2 |
| Integración API real | Buena en lo implementado | 4 |

**Conclusión honesta:** El proyecto **sí usa** la plantilla Materialize como contenedor (layout vertical, Vuetify, auth v2, menú filtrado por CASL). Las pantallas NightPOS **no están construidas al nivel de composición de la plantilla** (KPI cards, widgets de listado, diálogos reutilizables del demo). Varias piezas del **navbar y perfil siguen siendo demo Materialize**, lo que rompe la percepción de producto POS y confunde al usuario operativo.

No se recomienda seguir parcheando pantallas monolíticas (p. ej. `orders/[id].vue` ~900 líneas). Conviene **reconstruir por módulos** usando patrones existentes en `src/pages/dashboards/*`, `src/pages/apps/ecommerce/*` y `src/views/pages/dialog-examples/*`.

---

## 2. Cumplimiento de Materialize Admin Template

### 2.1 ¿Se usa realmente Materialize?

**Sí**, en el sentido técnico correcto del repo:

| Elemento plantilla | Uso NightPOS | Evidencia |
| ------------------ | ------------ | --------- |
| `VerticalNavLayout` / `default` layout | Sí | `src/layouts/default.vue`, `DefaultLayoutWithVerticalNav.vue` |
| Layout `blank` en login | Sí | `src/pages/login.vue` → `meta.layout: 'blank'` |
| Auth v2 (ilustración, máscara, logo) | Sí | Misma estructura que `login-v2.vue` + `@core/scss/template/pages/page-auth` |
| `VCard`, `VDataTable`, `VDialog`, `VTabs`, `VBtn`, `VChip` | Sí | Todas las páginas `src/pages/nightpos/*` |
| `themeConfig` (título NightPOS) | Sí | `themeConfig.js` → `app.title: 'NightPOS'` |
| Menú demo conservado en archivos | Sí (correcto) | `dashboard.js`, `apps-and-pages.js`, etc. no exportados en `navigation/vertical/index.js` |
| Eliminación de componentes plantilla | No | Cumple regla «no eliminar» |

### 2.2 ¿Se reutilizan componentes originales?

**Parcialmente.**

| Reutilizado | No reutilizado (debería usarse o adaptarse) |
| ----------- | --------------------------------------------- |
| Layout, sidebar plugin, footer | `CardStatisticsVertical` (KPIs) — usado en `dashboards/analytics.vue`, **no** en NightPOS dashboard/caja |
| Ilustraciones auth | `AddPaymentMethodDialog` / patrones en `dialog-examples` — cobro es modal ad hoc |
| `VNodeRenderer` + logo | `BranchSelector`, widgets de ecommerce list (`widgetData` en product list) |
| `VSnackbar` local en cada página | `NavSearchBar`, `NavbarShortcuts`, `NavBarNotifications` — **siguen activos en navbar operativo** |
| | `UserProfile` con enlaces demo (Profile, Billing, Pricing, FAQ) |

### 2.3 ¿Se modificó innecesariamente la plantilla?

- **No** se borraron módulos demo (bien).
- **Sí** hay deuda en **layout operativo**: el navbar no fue «podado» para NightPOS; sigue mostrando búsqueda global, atajos demo, notificaciones demo e i18n EN/FR/AR por defecto — irrelevante para caja/garzón en Bolivia.

### 2.4 ¿Pantallas fuera del diseño oficial?

Las rutas NightPOS viven dentro del layout oficial, pero el **contenido** de muchas páginas es HTML mínimo (título + card + tabla), **sin** la jerarquía visual de las páginas demo (fila de estadísticas, card con toolbar, filtros, acciones alineadas).

**Referencia de diseño no seguida:**

- Listado admin: `src/pages/apps/ecommerce/product/list/index.vue` (widgets + tabla + filtros).
- Dashboard: `src/pages/dashboards/analytics.vue` (`CardStatisticsVertical`, charts).
- Pagos: `src/pages/pages/dialog-examples/index.vue` + `AddPaymentMethodDialog`.

### 2.5 Navegación Materialize

| Aspecto | Estado |
| ------- | ------ |
| Sidebar NightPOS | OK — `src/navigation/vertical/nightpos.js`, permisos CASL |
| Rutas file-based | OK — `typed-router`, prefijo `/nightpos/*` |
| Navbar | **Mal adaptado** — componentes demo visibles |
| Orden menú vs flujo operativo | Mejorable — «Caja» antes que «Comandas» puede confundir al garzón (rol sin caja) |

---

## 3. Cumplimiento de archivos maestros

### 3.1 `FRONTEND_GUIDELINES.md`

| Requisito | Cumple | Observación |
| --------- | ------ | ----------- |
| Vue 3 + Composition API | Sí | |
| Pinia `useAuthStore` | Sí | Falta `useOrderStore`, `useCashStore`, etc. |
| Axios central + JWT | Sí | `src/services/http.js` |
| Interceptor 401 → login | Sí | |
| Interceptor 403 + limpieza stores | **No** | Solo 401; guía pide 403 también |
| Login PIN principal | **Parcial** | Tab PIN por defecto (`loginMode: 'pin'`), pero slug/sucursal manual cada vez |
| Login usuario/contraseña | Sí | Segunda pestaña |
| Menú dinámico por permisos | Sí | CASL + `meta.permission` en rutas |
| Botones según permisos | Sí | `useNightPosPermissions` |
| No calcular precios en front | Sí | Comandas y productos respetan API |
| No cobrar sin caja (UI) | **No** | Botón cobrar no consulta sesión de caja abierta |
| DataTables avanzados / server pagination | **No** | Solo `VDataTable` básico, sin export ni filtros |
| Componentes reutilizables listados | **No** | Ninguno de `PriceTypeSelector`, `PaymentMethodsForm`, `BranchSelector`, etc. |
| Estructura `modules/` | **No** | Todo en `src/pages/nightpos/` plano |
| Dashboard KPIs / charts | **No** | Placeholder de sesión |
| Comanda móvil (mesas, búsqueda, chica) | **Parcial** | Flujo existe; mesas ausentes; chica por ID manual |
| Cierre turno (tarjetas efectivo/QR/tarjeta) | **No** | Caja Fase 8 sin pantalla de cierre rico de guía |
| Experiencia móvil prioritaria | **Parcial** | Botones grandes en comandas; login y admin no optimizados |

### 3.2 `README.md` / `ROADMAP.md` — Primer MVP visual

| MVP | Backend | Frontend |
| --- | ------- | -------- |
| Iniciar sesión | Sí | Sí (con fricción PIN) |
| Abrir caja | Sí | Sí |
| Ver mesas | No | **No** |
| Abrir comanda | Sí | Sí (texto libre) |
| Agregar cerveza SOLO / CON_ACOMPANANTE | Sí | Sí |
| Cobrar | Sí | Sí (modal básico) |
| Ver cierre de caja | Parcial API | **No** UI de cierre según guía |

El frontend **no cierra el MVP visual** del roadmap: faltan **mesas** y **cierre de turno** como experiencia de producto.

### 3.3 `BOLICHE_RULES.md`

- Modalidad SOLO / CON_ACOMPANANTE: respetada en formularios.
- Chica obligatoria antes de enviar: respetada con diálogo (UX mejorable con selector de personal).
- Garzón no calcula montos: respetado.

### 3.4 `PROMPT_CURSOR.md`

- Respeta no extender PHP legacy.
- Consume API `/api/v1` (no `/backend/v1` como texto antiguo en guía — coherente con implementación real).

---

## 4. Análisis por pantalla / área

### 4.1 Login — `src/pages/login.vue`

| | |
| - | - |
| **Bien** | Basado en auth v2 Materialize; PIN en primer tab; integración `useAuthStore` + contexto operativo; errores visibles. |
| **Mal** | Garzón debe escribir `tenant_slug` y `branch_code` en cada login — inviable en celular en piso. Valores demo precargados (`casa-demo`) parecen producción. PIN en `type="password"` sin teclado numérico dedicado. |
| **Rehacer** | Flujo PIN operativo: sucursal/empresa persistida en cookie tras primer setup; teclado PIN grande; opcional selector de sucursal si el usuario tiene varias (`operational.branches`). |
| **Prioridad** | **Alta** |

### 4.2 Dashboard — `src/pages/nightpos/dashboard.vue`

| | |
| - | - |
| **Bien** | Muestra sesión tenant/sucursal; accesos rápidos a módulos reales. |
| **Mal** | No es un dashboard según guía: sin ventas del día, caja abierta/cerrada, KPIs, alertas, charts. No usa `CardStatisticsVertical`. Atajo «Ventas» ausente. Texto «vista inicial para validar auth» — nivel prototipo. |
| **Rehacer** | Dashboard operativo copiando composición de `dashboards/analytics.vue` o `crm.vue` con datos API (caja, ventas turno, comandas abiertas). |
| **Prioridad** | **Alta** |

### 4.3 Productos — `src/pages/nightpos/products/index.vue`

| | |
| - | - |
| **Bien** | API real; modales crear producto/precio; split CON_ACOMPANANTE; permisos; `VDataTable`. |
| **Mal** | Sin fila de widgets; sin edición; sin búsqueda/filtros; patrón alejado de `apps/ecommerce/product/list`. Pantalla de administración en desktop, no problema grave si rol es admin. |
| **Optimizar** | Alinear layout con listado ecommerce + componentes `ProductForm` / `PriceTypeSelector`. |
| **Prioridad** | **Media** |

### 4.4 Categorías — `src/pages/nightpos/categories/index.vue`

| | |
| - | - |
| **Bien** | Simple, clara, cumple Fase 6.5; poco código. |
| **Mal** | Sin filtros ni paginación server-side (aceptable por volumen bajo). |
| **Mantener** | Sí, con mejoras menores de cabecera/toolbar. |
| **Prioridad** | **Baja** |

### 4.5 Comandas — `orders/index.vue`, `orders/new.vue`, `orders/[id].vue`

| | |
| - | - |
| **Bien** | Lista en cards táctiles; botones `x-large`; fullscreen para agregar producto; barra fija de acciones; cobro integrado; no calcula totales; asignación chica. |
| **Mal** | `[id].vue` monolítico (~900 líneas) mezcla cobro, catálogo, envío, cancelar — anti-guía de componentes. Sin mapa de mesas. Lista solo `OPEN`. Garzón ingresa ID de chica sin lista. Modal cobro no valida caja abierta. No usa `ProductSearchModal` ni `PaymentMethodsForm`. |
| **Rehacer** | Módulo comandas: páginas delgadas + `components/orders/*` basados en plantilla. Flujo móvil garzón como app dentro del layout (menos navbar demo). |
| **Prioridad** | **Alta** (detalle y cobro) / **Alta** (mesas cuando exista API) |

### 4.6 Caja — `src/pages/nightpos/cash/index.vue`

| | |
| - | - |
| **Bien** | Apertura, movimientos, cierre; `sales_by_method`; modales Materialize; API correcta. |
| **Mal** | No implementa pantalla de **cierre de turno** de `FRONTEND_GUIDELINES.md` (tarjetas separadas efectivo/QR/tarjeta, diferencia, liquidaciones futuras). Tabla movimientos sin método de pago visible en columna. Acciones en bloque estrecho `max-width: 480px` en desktop. |
| **Rehacer** | Vista cierre usando cards estadísticas Materialize + resumen ventas + arqueo. |
| **Prioridad** | **Alta** |

### 4.7 Ventas — `src/pages/nightpos/sales/index.vue`

| | |
| - | - |
| **Bien** | Lista turno actual; chips de pago; permiso `sales.list`. |
| **Mal** | Muestra `cashier_user_id` / `waiter_user_id` (IDs crudos) — no es POS profesional. Sin detalle de venta, sin filtros, sin enlace a comanda. |
| **Optimizar** | Rehacer listado con nombres desde API o join; fila clic → detalle; patrón tabla ecommerce. |
| **Prioridad** | **Media** |

### 4.8 Layout general, sidebar, navbar

| Componente | Estado |
| ---------- | ------ |
| **Sidebar** | Correcto para NightPOS |
| **Layout vertical** | Correcto |
| **Navbar** | **Incorrecto para producto** — búsqueda, shortcuts, notificaciones, i18n y theme switcher son ruido operativo |
| **UserProfile** | **Incorrecto** — menú apunta a rutas demo (`apps-user-view-id`, `pages-account-settings`, `pages-pricing`) |
| **Footer** | Plantilla por defecto (aceptable) |
| **Branch / tenant en UI** | Solo cookies + login; **sin** `BranchSelector` en navbar |

### 4.9 Responsive móvil

| Pantalla | ¿Utilizable en celular? |
| -------- | ------------------------ |
| Login | Regular — muchos campos |
| Comandas lista/detalle | **Buena** — mejor módulo móvil |
| Caja | Regular |
| Productos / categorías | Desktop-first |
| Ventas | Regular |
| Navbar demo | **Mala** — iconos pequeños, distracción |

### 4.10 Componentes Materialize utilizados vs duplicados

| Patrón | Instancias |
| ------ | ---------- |
| `VSnackbar` + `notify()` | Duplicado en **cada** página (8+ copias) — extraer `useNightPosNotify` o componente layout |
| Lógica de carga `loading` + `VProgressLinear` | Repetida |
| Modales de formulario | Repetidos inline; no carpeta `components/nightpos/` |
| Helpers negocio | Bien centralizados: `useOrderHelpers`, `useNightPosPermissions` |

---

## 5. Problemas encontrados (consolidado)

### Críticos (rompen experiencia o guías)

1. **Navbar y UserProfile sin adaptar** — usuario operativo ve enlaces a Billing, FAQ, Pricing de la plantilla demo.
2. **Dashboard no operativo** — no cumple definición de dashboard en guías ni MVP roadmap.
3. **Sin módulo mesas** — comanda por texto; no hay vista visual de mesas/estados.
4. **Detalle comanda monolítico** — deuda alta; cobro acoplado sin componente de pagos reutilizable.
5. **Cobro sin guard de caja en UI** — permite intentar cobrar sin sesión (error backend, mala UX POS).
6. **Login PIN con slug/sucursal manual** — no escala para garzones.

### Importantes

7. Sin componentes compartidos documentados en `FRONTEND_GUIDELINES.md`.
8. Sin stores Pinia por dominio (order, cash, sale) — estado disperso en páginas.
9. Ventas muestran IDs en lugar de nombres.
10. Interceptor HTTP incompleto (403).
11. Estructura de carpetas `modules/*` no aplicada.
12. Caja sin UI de cierre de turno según especificación (tarjetas por método).
13. i18n por defecto en inglés en plantilla — no localizado a operación BO.

### Menores

14. Categorías sin toolbar avanzada.
15. Productos sin edición (alcance fase anterior, pero gap vs guía CRUD).
16. `canListSales` importado en ventas pero sin uso defensivo extra.
17. Dashboard shortcut «Turnos» deshabilitado sin explicación en UI.

---

## 6. Referencias de componentes plantilla (para reconstrucción)

| Objetivo NightPOS | Referencia en repo |
| ----------------- | ------------------ |
| KPI / resumen caja | `src/pages/dashboards/analytics.vue`, `CardStatisticsVertical` |
| Listado catálogo admin | `src/pages/apps/ecommerce/product/list/index.vue` |
| Diálogo métodos de pago | `src/views/pages/dialog-examples/`, `AddPaymentMethodDialog` |
| Login | `src/pages/login.vue` (base actual OK), comparar `login-v2.vue` |
| Perfil usuario limpio | Reescribir `src/layouts/components/UserProfile.vue` (solo logout + sucursal) |
| Navbar operativo | Podar `DefaultLayoutWithVerticalNav.vue` (quitar search, shortcuts, notifications para rol operativo) |

---

## 7. Pantallas que deben rehacerse (completo o casi completo)

| Pantalla | Motivo | Enfoque reconstrucción |
| -------- | ------ | ---------------------- |
| **Layout navbar + UserProfile** | Demo Materialize visible | Podar navbar; perfil mínimo NightPOS |
| **Dashboard** | No cumple guía ni MVP | KPI cards + API caja/ventas/comandas |
| **Comandas detalle** `orders/[id].vue` | Monolito; cobro mezclado | Dividir en 4–6 componentes + página contenedor |
| **Modal / flujo cobro** | Ad hoc vs `PaymentMethodsForm` | Componente dedicado + validación caja |
| **Caja (cierre y resumen)** | Falta cierre de turno rico | Cards por método + arqueo Materialize |
| **Login PIN operativo** | Fricción tenant/sucursal | Persistencia + teclado PIN |

## 8. Pantallas que pueden mantenerse (con ajustes)

| Pantalla | Condición |
| -------- | --------- |
| **Login** (estructura auth v2) | Mantener shell; rehacer solo flujo PIN/branch |
| **Comandas lista** `orders/index.vue` | Mantener patrón cards; añadir filtros de estado |
| **Comandas nueva** `orders/new.vue` | Mantener; mejorar cuando existan mesas |
| **Categorías** | Mantener; pulir toolbar |
| **Productos** | Mantener lógica API; re-skin con layout ecommerce |
| **Ventas** | Mantener ruta; rehacer columnas y detalle |
| **Caja apertura/movimientos** | Mantener flujos; integrar en vista cierre unificada |
| **Infra** `http.js`, `auth.js`, `operational.js`, guards, `nightpos.js` nav | Mantener y extender |

---

## 9. Plan de reconstrucción (sin parches)

### Fase R1 — Shell operativo (1 sprint corto)

1. Crear `layouts/NightPosNavbar.vue` o condicionar `DefaultLayoutWithVerticalNav` por ruta `/nightpos/*`.
2. Reescribir `UserProfile.vue` para NightPOS: nombre, rol, sucursal, cerrar sesión.
3. Añadir `BranchSelector` en navbar (cookie `branchCode` + `operational.branches`).
4. Completar interceptor 403 → `not-authorized`.
5. Extraer `useNightPosNotify()` para snackbars.

**No tocar lógica de negocio backend.**

### Fase R2 — Dashboard y caja (1 sprint)

1. Reconstruir `dashboard.vue` con `CardStatisticsVertical` y datos reales: sesión caja, total ventas turno, comandas abiertas.
2. Reconstruir sección **cierre** en caja con grid de cards (efectivo, QR, tarjeta, esperado, contado, diferencia) según `FRONTEND_GUIDELINES.md`.
3. Enlazar dashboard → caja / comandas / ventas según permisos.

### Fase R3 — Módulo comandas POS (1–2 sprints)

1. Carpeta `src/modules/orders/` (o `src/components/nightpos/orders/`):
   - `OrderProductPickerDialog.vue` (fullscreen móvil, búsqueda).
   - `OrderLineList.vue`
   - `OrderGirlAssignDialog.vue`
   - `OrderChargeDialog.vue` (basado en patrón pagos plantilla).
   - `PriceTypeSelector.vue`
2. Reducir `[id].vue` a composición (<150 líneas).
3. Validar `fetchCurrentCashSession()` antes de mostrar cobrar.
4. Cuando API mesas exista: `OrderTableCard.vue` + ruta `/nightpos/mesas`.

### Fase R4 — Admin catálogo y ventas (1 sprint)

1. Productos: layout tipo ecommerce list + componentes formulario.
2. Ventas: nombres cajero/garzón, detalle venta, enlace comanda.
3. Opcional: `useCashStore` / `useOrderStore` para estado compartido dashboard–caja–cobro.

### Fase R5 — Login móvil garzón (paralelizable)

1. Pantalla PIN dedicada post-selección sucursal.
2. Recordar `tenantSlug` / `branchCode` en cookies.
3. `inputmode="numeric"` y botones grandes.

---

## 10. Prioridad por módulo

| Módulo | Prioridad | Acción recomendada |
| ------ | --------- | ------------------ |
| Layout / navbar / perfil | **Alta** | Reconstruir adaptación NightPOS |
| Dashboard | **Alta** | Reconstruir con KPI Materialize |
| Comandas (detalle + cobro) | **Alta** | Reconstruir con componentes |
| Caja (cierre turno) | **Alta** | Reconstruir vista cierre |
| Login PIN | **Alta** | Reconstruir flujo operativo |
| Mesas / salas | **Alta** (bloqueado API) | Nueva pantalla cuando backend exista |
| Ventas | **Media** | Rehacer listado + detalle |
| Productos | **Media** | Re-skin + componentes |
| Categorías | **Baja** | Mantener + polish |
| i18n / theme demo navbar | **Baja** | Ocultar en rutas operativas |

---

## 11. Veredicto final

| Pregunta | Respuesta |
| -------- | --------- |
| ¿Materialize como base? | **Sí** |
| ¿Implementación NightPOS al nivel de la plantilla? | **No aún** |
| ¿Listo para operación real en boliche? | **No** — garzón/cajero necesitan shell limpio, dashboard útil, cierre de caja claro y menos fricción en login/cobro |
| ¿Seguir parcheando vistas actuales? | **No recomendado** en comandas detalle, dashboard, navbar y caja cierre |
| ¿Siguiente paso sugerido? | Ejecutar **Fase R1 + R2** antes de nuevas features backend |

---

*Auditoría completada. Sin cambios de código en el repositorio salvo este informe. Esperar instrucciones para iniciar reconstrucción por fase.*
