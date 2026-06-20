# AUDITORÍA — Matriz de permisos NightPOS (Frontend)

**Fecha:** 2026-06-16  
**Estado:** Diagnóstico — sin cambios de código  
**Fuentes:** navegación, guards, composables, menú cajera

---

## 1. Cómo se evalúan permisos en UI

```
Login → user.permissions[] en cookie userData
      → auth.hasPermission(slug)
      → useNightPosPermissions().can(slug)
      → CASL can('access', slug) para nav vertical
      → Router guard meta.permission
      → v-if="can('slug')" en componentes
```

**No hay cache de menú aparte de cookies de sesión.**  
**No hay polling de permisos.**

---

## 2. Matriz menú vs permiso vs rol

### 2.1 Navegación vertical (`nightpos-r4.js`)

Visible solo si **NO** es cajera básica / garzón / chica (`useNightPosNavItems.js`).

| Entrada menú | subject (permiso) | Owner | Sr.Cajera | Cajera | Garzón |
|--------------|-------------------|:-----:|:---------:|:------:|:------:|
| Finanzas → Liquidaciones → Resumen | settlements.access | ✓ | ✓ | ✓* | ✓ |
| → Garzones / Chicas / Limpieza | settlements.access | ✓ | ✓ | ✓* | ✓ |
| → Historial | settlements.history | ✓ | ✓ | ✓* | ✗ |
| Finanzas → Reportes | reports.access | ✓ | ✗ | ✗ | ✗ |
| Finanzas → Fiscalización cajas | admin.cash_sessions.list | ✓ | ✓ | ✗ | ✗ |
| Operación → Consola turno | shift_console.access | ✓ | ✓ | ✓ | ✗ |
| Config → Checklist 1ª noche | settings.checklist | ✓ | ✗ | ✗ | ✗ |
| Staff → Roles | roles.access | ✓ | ✗ | ✗ | ✗ |

\* Cajera básica: **menú vertical vacío** — accede vía tab **Más** si tiene permiso.

### 2.2 Menú «Más» cajera (`useCashierMoreMenu.js`)

| Ítem | Permiso requerido | Cajera demo | Tras agregar permiso sin re-login |
|------|-------------------|:-----------:|:----------------------------------:|
| Liquidaciones | settlements.access | ✓ visible | ✓ si ya lo tenía |
| Ventas del turno | sales.list | ✓ | Necesita re-login |
| Consola de turno | shift_console.access | ✓ | Necesita re-login |
| Manillas / Shows / Piezas / Habitaciones | bracelets.* / shows.* / etc. | ✓ | Necesita re-login |
| Productos / Categorías | products.list / product-categories.list | ✓ | Necesita re-login |
| Motivos caja / Pagos / Ambientes / Mesas | settings.* | parcial | Necesita re-login |
| Asignar mesas | settings.waiter_assignments | ✗ demo | Visible solo tras permiso + re-login |
| Historial liquidaciones | settlements.history | ✓ | Necesita re-login |
| Cierre de turno | shifts.close | ✗ demo | Visible si se agrega + re-login |
| Reportes | reports.access | ✗ | Visible si se agrega + re-login |
| Fiscalización cajas | admin.cash_sessions.list | ✗ | Visible si se agrega + re-login |

---

## 3. Permisos visibles pero “bloqueados” (UX)

| Caso | Permiso UI | Bloqueo real |
|------|------------|--------------|
| Cajera entra a Liquidaciones | settlements.access ✓ | Overview vacío — **scope API** |
| Cajera ve botón Generar | settlements.generate ✓ | Toast “no hay nuevas” — **datos ya generados / scope** |
| Cajera en tab Garzones sin filas | settlements.pay ✓ | Sin botón Pagar — **sin IDs en lista** |
| Entrada menú Reportes | reports.access | Route guard 403 si no tiene permiso |
| Fiscalización cajas en Más | admin.cash_sessions.list | API OK; cajera demo no lo tiene |

**Patrón:** permiso de ruta **aprobado**, datos **denegados por scope backend**.

---

## 4. Permisos en catálogo sin entrada de menú directa

| Permiso | ¿En nav/Más? | Uso |
|---------|:------------:|-----|
| settlements.pending_sources | No (automático) | Composable pending sources |
| settlements.generate | No (botón en página) | index.vue |
| settlements.pay | No (botón en fila) | waiters/girls/cleaning.vue |
| orders.update_items | No menú | Acciones comanda |
| printing.reprint | No menú dedicado | Badge en detalle comanda |
| admin.cash_sessions.view | No | **Cambia scope backend** — no es ítem menú |
| admin.cash_sessions.summary | No | Solo API |
| permissions.access | Solo bajo Roles | Catálogo permisos |

---

## 5. Permisos duplicados / solapados en UI

| UI | Permisos alternativos |
|----|------------------------|
| Productos | `products.list` vs `products.create` para botón crear |
| Categorías | `product-categories.list` — cajera demo tiene list implícito vía products |
| Caja | `cash.access` route + múltiples sub-rutas sin permiso extra |
| Mesas garzón | `waiter.my_tables` vs `settings.service_tables` |

---

## 6. Roles especiales frontend

| Concepto | Implementación |
|----------|----------------|
| Cajera básica | `isBasicCashierStaff()` — oculta nav completo |
| Cajera senior / manager / owner | `FULL_MENU_ROLES` en `cashierRouting.js` — nav completo |
| Garzón / chica / limpieza | Rutas dedicadas + guards `isWaiterStaff` etc. |

**Manager:** listado en `FULL_MENU_ROLES` pero **sin rol backend** → comportamiento indefinido si se crea rol custom `manager`.

**Guardia:** sin rutas ni permisos.

---

## 7. Route guards — settlements

| Ruta | meta.permission | Evaluado en |
|------|-----------------|-------------|
| nightpos-settlements | settlements.access | guards.js |
| nightpos-settlements-waiters/girls/cleaning | settlements.access | guards.js |
| nightpos-settlements-history | settlements.history | guards.js |
| nightpos-settlements-id | settlements.access (hereda) | guards.js |

**Generate y Pay no tienen route guard propio** — solo `v-if` en componente.

---

## 8. Problema 3 — Árbol de decisión

```
¿Agregué permiso al rol en admin?
  └─ ¿Cajera cerró sesión y volvió a entrar?
       ├─ NO → cookie userData sin permiso → menú Más no muestra ítem
       └─ SÍ → ¿El ítem usa ese permiso en useCashierMoreMenu?
            ├─ NO → permiso existe pero sin wiring UI
            └─ SÍ → debería aparecer
```

**fetchMe() no se llama al inicio de app** — confirmado: solo `orders/[id].vue`.

---

## 9. Comparativa rápida roles (visibilidad UI demo)

| Capacidad UI | Admin | Sr.Cajera | Cajera | Garzón |
|--------------|:-----:|:---------:|:------:|:------:|
| Nav lateral completo | ✓ | ✓ | ✗ (shell) | ✗ |
| Liquidaciones en Más | — | — | ✓ | — |
| Botón generar liquidaciones | ✓ | ✓ | ✓ | ✗ |
| Pagar liquidaciones (si hay datos) | ✓ | ✓ | ✓ | ✗ |
| Reportes menú | ✓ | ✗ | ✗ | ✗ |
| Roles y permisos | ✓ | ✗ | ✗ | ✗ |
| Checklist 1ª noche | ✓ | ✗ | ✗ | ✗ |

---

## 10. Referencias

- `SETTLEMENTS_PERMISSION_AUDIT.md` — Diagnóstico liquidaciones UI
- `backend/NIGHTPOS_PERMISSION_MATRIX_AUDIT.md` — Matriz backend por rol
- `stores/auth.js` — Persistencia permisos
- `useCashierMoreMenu.js` — Menú Más filtrado
