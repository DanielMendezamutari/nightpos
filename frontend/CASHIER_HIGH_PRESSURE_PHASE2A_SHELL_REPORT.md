# Cajera alta presión — Fase 2A Shell (Frontend)

**Fecha:** 2026-06-16  
**Estado:** ✅ Implementado  
**Depende de:** Fase 0 + Fase 1 (`CASHIER_HIGH_PRESSURE_PHASE0_REPORT.md`, `CASHIER_HIGH_PRESSURE_PHASE1_REPORT.md`)

---

## Objetivo

Shell cajera simplificado para **cajera básica** (`role=cashier` / `staff_role=CASHIER` sin `cashier_senior` ni admin).

Filosofía (como garzón):

```
Cobrar | Piezas | Venta | Caja | Más
```

Sin menú lateral administrativo. Solo UX/navegación — **sin cambios de backend ni reglas de negocio**.

---

## Usuarios afectados

| Rol | Shell |
|-----|-------|
| Cajera básica | Shell cajera (`layout: blank`) |
| `cashier_senior`, admin, manager, owner | Menú completo (sin cambios) |
| Garzón, limpieza, chica | Sin cambios |

---

## Rutas shell

| Tab | Ruta | Nombre ruta |
|-----|------|-------------|
| Cobrar (home) | `/nightpos/cashier/orders` | `nightpos-cashier-orders` |
| Piezas | `/nightpos/cashier/piezas` | `nightpos-cashier-piezas` |
| Venta directa | `/nightpos/cashier/venta` | `nightpos-cashier-venta` |
| Mi caja | `/nightpos/cashier/caja` | `nightpos-cashier-caja` |
| Más | `/nightpos/cashier/more` | `nightpos-cashier-more` |
| Redirect | `/nightpos/cashier` | → `nightpos-cashier-orders` |

La ruta admin `/nightpos/services/room-services` redirige a `nightpos-cashier-piezas` para cajera básica.

Las rutas admin originales (`/nightpos/cash`, `/nightpos/cash/direct-sale`, etc.) **siguen existiendo** para senior/admin. Guards redirigen cajera básica a equivalentes shell.

---

## Navegación

### Desktop

Barra superior fija (`CashierDesktopNav`): Cobrar · Piezas · Venta directa · Mi caja · Más.

### Móvil

Bottom nav fijo (`CashierBottomNav`): Cobrar · Piezas · Venta · Caja · Más — botones grandes, safe-area.

Tabs visibles según permisos (`sales.charge`, `room_services.access`/`rooms.access`, `sales.direct_create`, `cash.access`). «Más» siempre visible.

Configuración centralizada en `cashierShellNav.js`.

---

## Barra de estado (`CashierStatusBar`)

Indicadores compactos arriba del shell:

- Caja abierta / cerrada
- Comandas pendientes + total BOB (tab Cobrar)
- SSE: En vivo / Reconectando / Sin tiempo real

---

## Banner caja cerrada

Si no hay sesión de caja abierta: alerta persistente con botón **Abrir caja** (`QuickOpenCashDialog`) visible en **todos** los tabs del shell.

---

## Tab «Más»

Enlaces secundarios filtrados por permiso:

| Opción | Ruta | Permiso |
|--------|------|---------|
| Liquidaciones | `nightpos-settlements` | `settlements.access` |
| Ventas del turno | `nightpos-sales` | `sales.list` |
| Cierre de turno | `nightpos-shifts-close` | `shifts.close` |
| Reportes | `nightpos-finance-reports` | `reports.access` |

---

## Guards y home

**`cashierRouting.js`**

- `isBasicCashierStaff(user)` — excluye `cashier_senior`, admin, manager, owner, super_admin
- `isCashierShellAllowedPath(path)` — shell + secundarias + `/nightpos/orders/*` (corrección)
- `resolveCashierShellRedirect(routeName)` — admin → shell

**`guards.js`**

- Cajera básica: redirige rutas admin conocidas; bloquea rutas fuera del allowlist → `nightpos-cashier-orders`
- Permisos existentes siguen aplicando (`not-authorized`)

**`resolveHomeRoute.js`**

- Home cajera básica → `nightpos-cashier-orders` (o venta/caja/más según permisos)

**`useNightPosNavItems.js`**

- Menú vertical vacío para cajera básica (como garzón)

**`mobileOperationalRole.js`**

- Cajera básica oculta Materialize Customizer en móvil

---

## Páginas envueltas

| Página shell | Contenido reutilizado |
|--------------|----------------------|
| `cashier/orders/index.vue` | Cola Fase 1 + `CashierShell` |
| `cashier/venta.vue` | `cash/direct-sale.vue` |
| `cashier/caja.vue` | `cash/index.vue` |
| `cashier/piezas.vue` | `services/room-services/index.vue` |
| `cashier/more.vue` | Lista enlaces secundarios |

`layout: blank` en todas — sin sidebar admin.

---

## Archivos nuevos

| Archivo |
|---------|
| `src/utils/cashierRouting.js` |
| `src/composables/useCashierShell.js` |
| `src/assets/styles/cashier-shell.scss` |
| `src/components/nightpos/cashier/CashierShell.vue` |
| `src/components/nightpos/cashier/CashierStatusBar.vue` |
| `src/components/nightpos/cashier/CashierBottomNav.vue` |
| `src/components/nightpos/cashier/CashierDesktopNav.vue` |
| `src/pages/nightpos/cashier/index.vue` |
| `src/pages/nightpos/cashier/more.vue` |
| `src/pages/nightpos/cashier/caja.vue` |
| `src/pages/nightpos/cashier/venta.vue` |
| `src/pages/nightpos/cashier/piezas.vue` |
| `src/utils/cashierShellNav.js` |

## Archivos modificados

| Archivo |
|---------|
| `src/pages/nightpos/cashier/orders/index.vue` |
| `src/plugins/1.router/guards.js` |
| `src/utils/resolveHomeRoute.js` |
| `src/composables/useNightPosNavItems.js` |
| `src/utils/mobileOperationalRole.js` |

**Backend:** sin cambios.

---

## Checklist QA manual

| # | Escenario | Esperado |
|---|-----------|----------|
| 1 | Login cajera básica | Entra a Cobrar (`/cashier/orders`) |
| 2 | Navegación | Tabs Cobrar, Piezas, Venta, Caja, Más — sin menú admin |
| 3 | Piezas | Módulo room-services en tab principal |
| 4 | Cobrar | Igual Fase 1 (modal inline) |
| 5 | Venta directa | Funciona en tab Venta |
| 6 | Mi caja | Funciona en tab Caja |
| 7 | Más | Sin duplicado de Piezas |
| 7 | Caja cerrada | Banner + Abrir caja en cualquier tab |
| 8 | Corregir comanda | Navega a detalle; Volver → cola |
| 9 | Admin / senior | Menú completo sin shell forzado |
| 10 | Ruta admin manual | Guard redirige o `not-authorized` |

---

## Build

`npm run build` — OK.

---

## No incluido (Fase 2B+)

- Cobro express / doble clic en card
- Cambios en pago mixto
- Toggle shell ↔ menú completo para senior
- Backend

---

## Próximo paso sugerido

Fase 2B: cobro «Todo efectivo» en un clic desde card, o sticky footer venta directa.
