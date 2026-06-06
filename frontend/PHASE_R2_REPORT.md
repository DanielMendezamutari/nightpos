# PHASE_R2_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Mejora visual operativa (Materialize)  
**Fase:** R2 — Caja, Ventas, Productos, Categorías  
**Fecha:** 2026-06-02  
**Referencias:** `FRONTEND_GUIDELINES.md`, `FRONTEND_AUDIT_REPORT.md`, `PHASE_R1_REPORT.md`, `backend/PHASE_9_REPORT.md`

---

## 1. Vistas mejoradas

| Vista | Ruta | Cambios principales |
| ----- | ---- | ------------------- |
| **Caja** | `pages/nightpos/cash/index.vue` | 8 KPI `CardStatisticsVertical` (estado, fondo, ventas por método, ingresos/egresos manuales, total esperado). Tabla movimientos con chips, método y fecha formateada. Diálogos abrir / movimiento / cierre con vista previa de **diferencia** (contado − esperado). Botones `x-large` para tablet/móvil cajera. |
| **Ventas** | `pages/nightpos/sales/index.vue` | 4 cards resumen (cantidad, total BOB, desglose por modo). Tabla con comanda, cajera/garzón (nombres vía `GET /admin/users` si permiso), fecha/hora, chips de pago. Modal detalle (`SaleDetailDialog`) con `GET /sales/{id}`: ítems, snapshots, modalidad, chica, comisión garzón, desglose `payments`. |
| **Productos** | `pages/nightpos/products/index.vue` | Fila widgets estilo ecommerce (`apps/ecommerce/product/list`). Filtros estado/categoría. Tabla con avatar, categoría, precios activos SOLO/CON_ACOMPANANTE (API), montos chica/casa. Crear/editar producto (`updateProduct`). Modal precios mejorado. |
| **Categorías** | `pages/nightpos/categories/index.vue` | 3 KPI cards. Tabla con avatar, chips tipo/estado, columna **productos** (conteo local cruzando `GET /products` — el backend no expone `products_count`). Formulario crear con selects Materialize. |

### Nuevo componente

| Archivo | Rol |
| ------- | --- |
| `components/nightpos/sales/SaleDetailDialog.vue` | Diálogo scrollable reutilizable para detalle de venta. |

---

## 2. Componentes Materialize reutilizados

| Componente plantilla | Uso R2 |
| -------------------- | ------ |
| `CardStatisticsVertical` | Caja (8), Ventas (4), Categorías (3) |
| Patrón widgets ecommerce | `VCard` + `VRow` + `VAvatar` + `VDivider` vertical en productos |
| `VDataTable` | Movimientos caja, listas ventas/productos/categorías, ítems en detalle venta |
| `VDialog` / `VCard` | Abrir/cerrar caja, movimientos, crear/editar producto, precios, categoría, detalle venta |
| `VChip` / `VBadge` (chips) | Estado caja, tipo movimiento, método de pago, modalidad, estado producto |
| `VAlert` | Caja cerrada, sin ventas, cierre con monto esperado |
| `VSelect`, `VTextField`, `VBtn` size `large` / `x-large` | Formularios y acciones táctiles |
| `VSnackbar` vía `useNightPosNotify` | Feedback unificado en las 4 vistas |

No se crearon componentes Vue nuevos fuera de `SaleDetailDialog` (requerido por detalle de venta).

---

## 3. Componentes ocultados

Sin cambios respecto a R1 en layout global. En estas pantallas no se añadieron widgets demo de Materialize (`NavSearchBar`, shortcuts, etc.) — ya ocultos en rutas NightPOS según `PHASE_R1_REPORT.md`.

No se eliminó ningún archivo demo del template.

---

## 4. Bugs visuales corregidos

| Problema | Corrección |
| -------- | ---------- |
| Caja mostraba montos sin formato uniforme | `formatMoney` / `fmtBob` en KPI y tabla |
| Cierre sin feedback de diferencia antes de confirmar | Vista previa `declared − expected` en diálogo cerrar |
| Ventas solo mostraban IDs de usuario | Mapa de nombres con `fetchAdminUsers` si `admin.users.list` |
| Ventas sin fecha ni detalle de ítems | Columna `paid_at` + `SaleDetailDialog` con API completa |
| Productos sin precios en listado ni edición | Cache de precios activos por producto; diálogo editar con `PUT /products/{id}` |
| Categorías sin contexto visual ni conteo | KPI + columna productos (conteo frontend) |
| Snackbars duplicados por página | Migración a `useNightPosNotify` en las 4 vistas |

---

## 5. Pendiente (fuera de R2)

| Tema | Nota |
| ---- | ---- |
| Cierre de **turno** (garzones) | No implementado — backend sin API de turno |
| Historial de ventas fuera de sesión actual | Lista usa `current_session=1` |
| `products_count` en categorías desde API | Requiere extensión backend; hoy conteo local |
| Edición de categorías / precios existentes | Solo crear; no hay `PUT` expuesto en frontend API |
| Reportes analytics / CRM Materialize | Fase posterior |
| `npm run build` en CI | Validado en dev; build no bloqueante por instrucción de fase |

---

## 6. Validación con `pnpm run dev`

| Check | Resultado esperado |
| ----- | ------------------ |
| Login PIN + contexto tenant/sucursal | Sin cambios R1 — cookies y formulario |
| Dashboard | KPIs y accesos rápidos operativos |
| Caja | KPIs, movimientos, abrir/cerrar con permiso `cash.access` |
| Ventas | Lista sesión + detalle modal; chips pago |
| Productos | Widgets, filtros, precios desde API, crear/editar según permisos |
| Categorías | Tabla + crear con `products.create` |
| Consola navegador | Sin errores críticos en carga de rutas NightPOS (revisar red si 403 en usuarios sin permiso admin — degradación a `#id`) |
| Responsive | Botones `x-large`/`large` en caja; fila clicable en ventas móvil; tablas con scroll horizontal |

**Credenciales demo (seeder):** tenant `casa-demo`, sucursal `CENTRO`, PIN cajero `1234`, garzón `5678`.

**Comando:** `cd frontend && pnpm run dev`

---

## 7. Próxima fase recomendada

**Fase R3 — Comandas listado + barra + garzón móvil**

1. Mejorar `orders/index.vue` y flujo garzón (cards grandes, filtros por mesa/estado).
2. Pantalla barra/preparación si aplica permisos.
3. Integrar turnos cuando exista API backend.
4. Opcional: `products_count` en `ProductMapper::category` para eliminar conteo N+1 frontend.
5. Ejecutar `npm run build` antes de release o despliegue estático.

---

## 8. Archivos tocados (resumen)

```
frontend/src/pages/nightpos/cash/index.vue
frontend/src/pages/nightpos/sales/index.vue
frontend/src/pages/nightpos/products/index.vue
frontend/src/pages/nightpos/categories/index.vue
frontend/src/components/nightpos/sales/SaleDetailDialog.vue
frontend/PHASE_R2_REPORT.md
```

**Backend:** sin cambios (cumple restricción de fase).

**Permisos / HTTP:** sin cambios — 401 → login; 403 en vista; botones condicionados por `useNightPosPermissions`.
