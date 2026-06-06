# OPERATIONAL_ROLE_FLOW_FIX_REPORT.md

**Proyecto:** NightPOS — Frontend Vue 3 + Materialize  
**Fecha:** 2026-06-02  
**Referencias:** `NIGHTPOS_OPERATION_FLOW_AUDIT.md`, `PHASE_C4_WAITER_REPORT.md`, `WAITER_MOBILE_FIX_REPORT.md`

---

## 1. Problemas corregidos

| Área | Antes | Después |
|------|-------|---------|
| Cajera | Comandas solo OPEN en listado genérico | Vista dedicada **Cobrar comandas** con cola de cobro |
| Garzón OPEN | Botón «Ver» ambiguo | **Gestionar** + + Producto + Enviar barra |
| Garzón SENT_TO_BAR | + Bebida deshabilitado | **Agregar extra** habilitado (alineado con API) |
| Garzón pendiente cobro | Sin mensaje claro | Alerta «Pendiente de cobro por caja» |
| Chica GIRL | Entraba a modo garzón (`role=waiter`) | Pantalla propia `/nightpos/girl` solo lectura |
| Finanzas | Placeholders Movimientos/Reportes/Cierre caja | Ocultos del menú (funcionalidad real en Caja / Cierre turno) |
| Nav garzón/chica | `isWaiterStaff(auth)` incorrecto | `isWaiterStaff(auth.user)` / `isGirlStaff(auth.user)` |

---

## 2. Nuevo flujo cajera

**Ruta:** `/nightpos/cashier/orders`  
**Menú:** Operación NightPOS → Comandas → **Cobrar comandas**  
**Permiso:** `sales.charge`

La página:
- Carga `fetchCashierChargeableOrders()` → `scope=cashier_chargeable`
- Muestra cards con mesa, garzón, hora, estado, total
- Chip caja abierta/cerrada
- Botón **Cobrar** (o **Abrir caja ahora**)
- Navega a detalle con `?charge=1` para abrir modal de cobro

---

## 3. Nuevo flujo garzón

Componente `WaiterOrderActions.vue` — acciones por `order.status`:

```
OPEN          → Gestionar | + Producto | Enviar barra
SENT_TO_BAR   → Ver | Agregar extra
IN_PREP/READY → Ver | mensaje pendiente cobro
BILLED        → Ver historial
```

Detalle `waiter/orders/[id].vue`:
- Botón agregar habilitado en OPEN y SENT_TO_BAR
- Alertas contextuales por estado
- Solo lectura en estados terminales

---

## 4. Nuevo flujo chica

**Ruta:** `/nightpos/girl` (`layout: blank`)  
**Permiso:** `girl.dashboard`

Helpers y guards:
- `isGirlStaff(user)` — `staff_role === 'GIRL'`
- `isGirlOnlyRoute(path)` — sandbox `/nightpos/girl`
- Home route prioriza chica antes que garzón
- `isWaiterStaff` ya **no** usa `role === 'waiter'` (evita capturar GIRL)

Pantalla muestra (solo lectura):
- Consumos con acompañante
- Manillas, piezas, shows
- Total pendiente / total pagado  
API: `GET /girl/shift-earnings`

---

## 5. Finanzas — placeholders ocultos

Removidos del menú R4 (sin eliminar rutas demo):
- Movimientos (`/nightpos/finance/movements`)
- Cierre de caja (`/nightpos/finance/cash-close`)
- Reportes (`/nightpos/finance/reports`)

Se mantienen: Caja actual, Ventas, Liquidaciones, Fiscalización de cajas, Cierre de turno.

---

## 6. Archivos modificados

| Archivo |
|---------|
| `src/pages/nightpos/cashier/orders/index.vue` **(nuevo)** |
| `src/pages/nightpos/girl/index.vue` **(nuevo)** |
| `src/api/orders.js` |
| `src/api/girl.js` **(nuevo)** |
| `src/components/nightpos/waiter/WaiterOrderActions.vue` |
| `src/pages/nightpos/waiter/index.vue` |
| `src/pages/nightpos/waiter/orders/index.vue` |
| `src/pages/nightpos/waiter/orders/[id].vue` |
| `src/pages/nightpos/orders/[id].vue` |
| `src/utils/resolveHomeRoute.js` |
| `src/utils/waiterRouting.js` |
| `src/plugins/1.router/guards.js` |
| `src/composables/useNightPosNavItems.js` |
| `src/navigation/vertical/nightpos-r4.js` |

---

## 7. Validación manual (`pnpm run dev`)

| Rol | PIN | Verificar |
|-----|-----|-----------|
| Cajera | 1234 | Menú Cobrar comandas, cobro con/sin caja |
| Garzón | 5678 | OPEN = Gestionar, SENT_TO_BAR = Agregar extra |
| Chica | 9012 | Va a `/nightpos/girl`, no a waiter |
| Superadmin | — | Wizard empresa nueva → permisos operativos |

**No se modificó:** modo limpieza, Materialize customizer, demos.
