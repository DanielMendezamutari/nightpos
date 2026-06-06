# NAVIGATION_UX_FINAL_REPORT

**Fase:** FASE UX FINAL — NAVEGACIÓN OPERATIVA  
**Fecha:** 2026-06-05  
**Origen:** Hallazgos de `OPERATION_CASH_FINANCE_AUDIT.md`

---

## Cambios aplicados

### P0 — Venta directa en Operación (primario)

**Archivo:** `frontend/src/navigation/vertical/nightpos-r4.js`

**Antes:**
```
Operación → [Consola, Cobrar, Comandas activas, Servicios, Habitaciones]
Caja      → [Mi caja, Venta directa, Ventas, Fiscalización]
```

**Después:**
```
Operación → [Dashboard, Consola, Cobrar comandas, VENTA DIRECTA, Comandas activas, Servicios, Habitaciones]
Caja      → [Mi caja, Ventas del turno, Fiscalización]
```

La entrada "Venta directa" en Operación usa la misma ruta (`nightpos-cash-direct-sale`) y el mismo permiso (`sales.direct_create`). El acceso secundario existe en:
- Botón en Mi caja (header, siempre visible con permiso)
- Accesos rápidos en Consola de turno

Venta directa fue **quitada del menú Caja** para no duplicar la entrada de menú y mantener Caja limpia con sus tres funciones reales.

---

### P0 — Accesos rápidos dashboard alineados con consola

**Archivo:** `frontend/src/pages/nightpos/dashboard.vue`

**Antes:**
```
[Comandas] [Caja] [Ventas] [Productos]
```

**Después:**
```
[Cobrar comandas ★] [Venta directa ★] [Mi caja] [Servicios] [Habitaciones] [Liquidaciones]
```

Los colores `primary`/`success` distinguen visualmente las acciones de mayor frecuencia. Los botones se deshabilitan automáticamente si el usuario no tiene el permiso correspondiente.

---

### P1 — Botón Venta directa en Mi caja: visible aunque la caja esté cerrada

**Archivo:** `frontend/src/pages/nightpos/cash/index.vue`

**Antes:** `v-if="session && canDirectSale"` — el botón desaparecía si la caja estaba cerrada.

**Después:** `v-if="canDirectSale"` — el botón siempre aparece cuando el usuario tiene el permiso; la página destino (`direct-sale.vue`) ya gestiona internamente el caso de caja cerrada con `QuickOpenCashDialog`.

---

### P1 — Rutas placeholder convertidas en redirects

**Archivos modificados:**

| Archivo | Antes | Después |
|---------|-------|---------|
| `finance/movements/index.vue` | Placeholder con texto | Redirect a `nightpos-cash` |
| `finance/cash-close/index.vue` | Placeholder con texto | Redirect a `nightpos-cash` |
| `finance/reports/index.vue` | Placeholder con texto | Redirect a `nightpos-dashboard` |
| `finance/shift-close/index.vue` | Ya era redirect a `nightpos-shifts-close` | Sin cambios |

Las páginas siguen existiendo (las rutas no se borran) pero ya no muestran contenido confuso; redirigen al módulo funcional correspondiente.

---

### P1 — Volver en detalle comanda cajera

**Estado:** Ya estaba resuelto antes de esta fase.

`orders/[id].vue` lee `?from=cashier` en el query string. Cuando la cajera llega desde `cashier/orders` (vía `goCorrect` o `goCharge` en `cashier/orders/index.vue`), el parámetro se pasa y el botón «Volver» apunta a `nightpos-cashier-orders`. No se requirió cambio.

---

### P2 — Submenú Turnos en Finanzas (admin/senior)

**Archivo:** `frontend/src/navigation/vertical/nightpos-r4.js`

Agregado al grupo Finanzas:

```javascript
{
  title: 'Turnos',
  children: [
    { title: 'Turno actual',    to: 'nightpos-shifts-current', subject: 'shifts.access' },
    { title: 'Abrir turno',     to: 'nightpos-shifts-open',    subject: 'shifts.open' },
    { title: 'Historial turnos',to: 'nightpos-shifts-history', subject: 'shifts.list' },
  ],
}
```

La cajera básica no tiene `shifts.open` ni `shifts.list`, por lo que no ve esos ítems. Solo ve «Turno actual» si tiene `shifts.access`. El admin/senior ve los tres.

---

## Estado final del menú

```
OPERACIÓN (cajera, admin, senior)
  Dashboard operativo
  Consola de turno            [shift_console.access]
  Cobrar comandas             [sales.charge]
  Venta directa               [sales.direct_create]  ← PRIMARIO
  Comandas activas            [orders.access]
  Servicios
    Manillas / Piezas / Shows / Control piezas
  Habitaciones
    Dashboard / Listado / Disponibles / Limpieza / Mantenimiento

CAJA (cajera, admin, senior)
  Mi caja                     [cash.access]
    └ botón "Venta directa"   ← acceso secundario (siempre visible con permiso)
  Ventas del turno            [sales.list]
  Fiscalización de cajas      [admin.cash_sessions.list]

FINANZAS (cajera, admin, senior)
  Liquidaciones               [settlements.access]
    Resumen / Garzones / Chicas / Limpieza / Historial
  Cierre de turno             [shifts.close]
  Turnos                      [shifts.access]
    Turno actual / Abrir turno / Historial

CATÁLOGO / PERSONAL / CONFIGURACIÓN / PLATAFORMA SAAS  (sin cambios)
```

---

## Rutas placeholder

| Ruta | Antes | Ahora |
|------|-------|-------|
| `/nightpos/finance/movements` | Texto placeholder | Redirect → Mi caja |
| `/nightpos/finance/cash-close` | Texto placeholder | Redirect → Mi caja |
| `/nightpos/finance/reports` | Texto placeholder | Redirect → Dashboard |
| `/nightpos/finance/shift-close` | Redirect → shifts/close | Sin cambios |

---

## Validación manual

### Cajera (PIN 1234)

1. Login → llega a Consola de turno.
2. Menú Operación expandido → ver: Consola, **Cobrar comandas**, **Venta directa**, Comandas activas, Servicios, Habitaciones.
3. Click «Venta directa» → abre `/nightpos/cash/direct-sale`. Sidebar no se cierra. Grupo Operación activo.
4. Ir a «Mi caja» → botón **Venta directa** visible aunque la caja esté cerrada.
5. Dashboard → ver 6 accesos rápidos: Cobrar, Venta directa, Mi caja, Servicios, Habitaciones, Liquidaciones.
6. Click cualquier acceso → navega correctamente.
7. Caja en menú → solo Mi caja, Ventas del turno (sin Fiscalización).
8. Finanzas → Liquidaciones + Cierre de turno + Turnos (solo turno actual para cajera básica).

### Admin (PIN o password)

1. Login → Dashboard operativo.
2. Operación → igual que cajera + todos los módulos.
3. Caja → Mi caja + Ventas + Fiscalización de cajas.
4. Finanzas → Liquidaciones + Cierre + Turnos (abrir/historial visibles).
5. Catálogo, Personal, Configuración visibles.

### Garzón / Limpieza / Chica

- No ven sidebar administrativo.
- Guard redirige a `/waiter`, `/cleaning`, `/girl` respectivamente.
- Sin cambios en estos flujos.

---

## Restricciones respetadas

- ✅ No se modificó backend.
- ✅ No se eliminaron componentes Materialize.
- ✅ Modo garzón/limpieza/chica intacto.
- ✅ No se ocultaron funciones útiles del menú.
- ✅ Fiscalización sigue accesible para admin y cajera senior.
- ✅ Venta directa tiene **dos puntos de acceso** (menú Operación + botón Mi caja + consola turno).
