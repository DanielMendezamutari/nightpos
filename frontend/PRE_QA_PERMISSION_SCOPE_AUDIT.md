# PRE_QA_PERMISSION_SCOPE_AUDIT.md (Frontend)

**Producto:** NightPOS V1  
**Editor:** Ribersoft  
**Fecha:** 2026-06-25  
**Alcance:** Menús operativos, permisos UI, scopes garzón/cajera

---

## Resumen ejecutivo

| Parte | Problema | Causa raíz | Fix aplicado |
|-------|----------|------------|--------------|
| 1 | Comandas antiguas visibles | Backend sin filtro turno + UI sin tabs claros | Backend fix + scopes documentados |
| 2 | «Alta de chicas» no aparece | Expectativa de menú; permiso solo inline en comanda | **Sin menú garzón** — flujo inline confirmado (decisión UX) |
| 3 | «Más» cajera fijo | Catálogo hardcodeado en composable | Catálogo único + filtro dinámico `can()` |

---

## PARTE 1 — Garzón comandas antiguas

### Flujo UI

| Pantalla | Ruta | API |
|----------|------|-----|
| Mis mesas | `/nightpos/waiter` | `GET /waiter/my-tables` |
| Comandas | `/nightpos/waiter/orders` | `GET /waiter/orders?scope=` |
| Detalle | `/nightpos/waiter/orders/:id` | `GET /orders/:id` (policy garzón) |

Default scope: `active` (`waiter.js` → `fetchWaiterOrders(scope)`).

### Scopes disponibles (query `?scope=`)

| Scope | Título UI | Estados (post-fix backend) |
|-------|-----------|----------------------------|
| `active` | Activas | OPEN, SENT_TO_BAR |
| `open` | Abiertas | OPEN |
| `sent_to_bar` | En barra | SENT_TO_BAR |
| `pending_charge` | Pendientes cobro | IN_PREPARATION, READY (barra futura) |

**No hay pestañas visibles** en `waiter/orders/index.vue` — solo cambio vía URL. Mejora UX opcional post-QA: chips VTabs.

### Causa raíz

1. **Backend:** listado sin turno obligatorio → ver informe backend.
2. **UX:** una sola lista «Activas» mezcla conceptos; garzón interpreta comandas viejas del mismo turno largo o de turnos previos como trabajo pendiente.

### Fix backend (consumido por frontend)

Tras fix, `GET /waiter/orders?scope=active` solo devuelve comandas del **turno actual**.

SSE (`useOrderOperationalEvents`) sigue refrescando la lista — sin cambios.

---

## PARTE 2 — Garzón «Alta de chicas»

### Expectativa vs realidad

| Expectativa usuario | Realidad operativa |
|---------------------|--------------------|
| Opción en menú «Alta de chicas» | **Descartado por UX** — no hay menú «Más» garzón |
| Permiso en rol garzón | ✅ `staff.quick_create_girl` |
| Label admin | «Alta rápida de chica» (no «Alta de chicas») |

### Flujo correcto (sin abandonar la comanda)

```
Mesa → Agregar producto → Con compañía → Buscar chica
  → Si no existe → botón «+ Nueva chica» (solo con permiso)
  → Crear → selección automática → continuar agregando
```

### Dónde funciona el permiso

| Ubicación | Condición |
|-----------|-----------|
| `waiter/orders/[id].vue` | `can('staff.quick_create_girl')` → props a diálogos |
| `OrderAddProductDialog.vue` | Prop `canQuickCreateGirl` → «Nueva chica» |
| `GirlQuickPicker.vue` | Invocado desde flujo de agregar producto |
| `AssignGirlModal.vue` | CTA «+ Nueva chica» con `can()` |

### Causa raíz del reporte original

1. **Drift de naming** — permiso «Alta rápida de chica» vs expectativa «Alta de chicas».
2. **Superficie UI** — el permiso nunca tuvo entrada de menú garzón; solo inline en comanda.
3. **Permisos stale** — cookie `userData.permissions` sin refresh tras cambio de rol (afecta botón inline, no menú).

### Decisión UX (2026-06-25) — revertir menú «Más» garzón

Tras revisión operativa, el tab **Más** para garzón **no mejora la experiencia** y agrega complejidad innecesaria. Se retiró:

| Eliminado | Motivo |
|-----------|--------|
| Tab «Más» en `WaiterBottomNav` | Garzón enfocado en Mesas + Comandas |
| `waiter/more.vue` | Shell secundario innecesario |
| Entrada garzón en `nightposSecondaryNavCatalog.js` | Catálogo solo para cajera |
| `useWaiterMoreMenu()` | Sin consumidores |

**Mantenido:** `fetchMe()` en `cashier/more.vue` (permisos actualizados al abrir «Más» cajera).

---

## PARTE 3 — Cajera menú «Más» dinámico

### Arquitectura antes del fix

```
useCashierMoreMenu.js
  └── sections[] HARDCODED (20 ítems)
        └── filter: can(item.permission)  ← dinámico solo aquí
```

| Capa | Hardcoded | Dinámico |
|------|-----------|----------|
| Qué ítems existen | ✅ Lista fija en composable | — |
| Visibilidad por usuario | — | ✅ `can()` |
| Shell tabs | ✅ `cashierShellNav.js` | Parcial (permisos por tab) |
| Nav admin senior | ✅ `nightpos-r4.js` | CASL + permissions |

**Problema:** nuevo permiso asignado en admin **no aparece** hasta agregar fila manual en `useCashierMoreMenu.js`.

### `FULL_MENU_ROLES` vs cajera básica

```javascript
// cashierRouting.js
FULL_MENU_ROLES = ['admin', 'manager', 'owner', 'cashier_senior', 'super_admin']
```

| Rol | Experiencia |
|-----|-------------|
| `cashier` básica | Shell + tab «Más» |
| `cashier_senior`, owner | Sidebar completo `nightpos-r4.js` |
| `manager` | Solo frontend — **sin rol backend** (legacy) |

### Ítems que faltaban en «Más» (ahora en catálogo)

- `settings.printers` → Impresoras
- `settings.checklist` → Checklist 1ª noche

### Fix aplicado — catálogo único

```
nightposSecondaryNavCatalog.js   ← fuente única (solo shell cajera)
        ↓
buildSecondaryNavSections('cashier', can)
        ↓
useSecondaryNavMenu('cashier')
        ↓
cashier/more.vue
```

**Regla para nuevos permisos:** agregar **una fila** al catálogo con `permission`, `shells`, `to` — aparece automáticamente si el usuario tiene permiso.

### Permisos stale

| Evento | permissions[] actualiza? |
|--------|---------------------------|
| Login | ✅ |
| `fetchMe()` | ✅ |
| JWT refresh | ❌ |
| Admin edita rol | ❌ hasta re-login |

**Fix:** `authStore.fetchMe()` al abrir «Más» (cajera).

---

## Matriz de verificación manual pre-QA

### Garzón

- [ ] Comandas solo del turno actual
- [ ] No ve comandas BILLED en listado
- [ ] Bottom nav: solo **Mesas** y **Comandas** (sin tab «Más»)
- [ ] Con compañía → «+ Nueva chica» visible solo con `staff.quick_create_girl`
- [ ] Alta rápida ocurre dentro del flujo de comanda (sin cambiar de pantalla)

### Cajera básica

- [ ] Tab «Más» muestra solo secciones con al menos 1 ítem permitido
- [ ] Nuevo permiso en catálogo + rol → visible sin cambiar composable (solo catálogo)
- [ ] Impresoras visible con `settings.printers`

---

## Archivos modificados

| Archivo | Parte |
|---------|-------|
| `navigation/nightposSecondaryNavCatalog.js` | 3 (catálogo cajera) |
| `composables/useCashierMoreMenu.js` | 3 |
| `pages/nightpos/cashier/more.vue` | 3 (`fetchMe`) |
| `components/nightpos/waiter/WaiterBottomNav.vue` | UX revert — sin tab «Más» |

---

## Referencias

- `backend/PRE_QA_PERMISSION_SCOPE_AUDIT.md`
- `ORDERS_COMPLETE_AUDIT.md`
- `frontend/NIGHTPOS_PERMISSION_MATRIX_AUDIT.md`
- `frontend/CASHIER_MORE_MENU_IMPLEMENTATION_REPORT.md`

---

**Ribersoft — NightPOS V1**
