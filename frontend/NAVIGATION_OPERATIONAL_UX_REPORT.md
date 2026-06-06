# NAVIGATION_OPERATIONAL_UX_REPORT

**Fase:** FASE UX OPERATIVA  
**Fecha:** Jun 2026  
**Alcance:** Menú principal, visibilidad de Venta directa, sidebar estable en desktop, accesos rápidos.

---

## 1. Orden anterior del menú

El menú anterior tenía la siguiente estructura (en `nightpos-r4.js`):

```
Plataforma SaaS       ← encabezado (primera posición, molestaba a cajeras/admin)
  Plataforma SaaS
    Dashboard SaaS
    Setup empresa
    Empresas / Sucursales / Planes / Config SaaS

Operación             ← todo mezclado en un solo grupo grande
  Operación NightPOS
    Dashboard operativo
    Consola de turno
    Comandas
      Cobrar comandas
      Listado
      Nueva comanda
    Caja                 ← duplicado (también aparecía en Finanzas)
      Mi caja
      Venta directa
    Ventas               ← duplicado (también en Finanzas)
    Servicios
    Habitaciones
    Turnos
      Turno actual / Abrir turno / Historial / Cierre

Catálogo
  Productos / Categorías / Vista precios

Personal
  Usuarios / Garzones / Cajeras / Chicas / Roles

Finanzas
  Caja actual           ← duplicado con Operación → Caja
  Ventas                ← duplicado con Operación → Ventas
  Liquidaciones
  Fiscalización de cajas
  Cierre de turno

Configuración
  Sucursal / Pagos / Motivos / Ambientes / Tipos habitación / ...
```

**Problemas:**
- Plataforma SaaS al tope confundía a cajeras y admin operativos.
- "Caja" aparecía dos veces (en Operación y en Finanzas).
- "Ventas" aparecía dos veces.
- Grupo único "Operación NightPOS" con demasiados hijos → difícil de escanear.
- "Venta directa" solo visible al expandir "Caja" dentro de "Operación NightPOS".
- "Turnos" era un sub-grupo dentro de Operación sin relevancia operativa directa.

---

## 2. Orden nuevo del menú

Archivo modificado: `frontend/src/navigation/vertical/nightpos-r4.js`

```
1. Operación
   Operación
     Dashboard operativo
     Consola de turno
     Cobrar comandas        ← primer nivel (alta frecuencia)
     Comandas activas       ← primer nivel (alta frecuencia)
     Servicios
       Manillas / Piezas / Shows / Control piezas
     Habitaciones
       Dashboard / Listado / Disponibles / Limpieza / Mantenimiento

2. Caja                     ← sección propia (ya no está mezclada con Operación)
   Caja
     Mi caja
     Venta directa          ← visible y directo en sección Caja
     Ventas del turno
     Fiscalización de cajas

3. Finanzas
   Finanzas
     Liquidaciones
       Resumen / Garzones / Chicas / Limpieza / Historial
     Cierre de turno

4. Catálogo
   Catálogo
     Productos / Categorías / Vista precios / Config precios

5. Personal
   Personal
     Usuarios / Garzones / Cajeras / Chicas / Roles y permisos

6. Configuración
   Configuración
     Métodos de pago / Motivos de caja / Ambientes / Tipos habitación
     Checklist 1ª noche / Sucursal / Impresoras / Preferencias / Seguridad / Bitácora

7. Plataforma SaaS          ← movida al final (solo superadmin)
   Plataforma SaaS
     Dashboard SaaS / Setup empresa / Empresas / Sucursales / Planes / Config SaaS
```

**Mejoras:**
- Sin duplicados: Caja y Ventas aparecen exactamente una vez.
- Sección "Caja" propia con "Venta directa" visible al primer nivel.
- Sección "Operación" enfocada en comandas, servicios, habitaciones.
- "Plataforma SaaS" al final (solo visible para superadmin).
- Sub-menú "Turnos" eliminado; "Cierre de turno" movido a Finanzas.

---

## 3. Roles y menú visible

### Cajera (`cashier` / `cashier_senior`)
**Permisos relevantes:** `cash.access`, `sales.charge`, `sales.direct_create`, `bracelets.access`, `room_services.access`, `shows.access`, `settlements.access`

Verá:
- **Operación** → Dashboard, Consola de turno, Cobrar comandas, Comandas activas, Servicios, Habitaciones
- **Caja** → Mi caja, Venta directa, Ventas del turno
- **Finanzas** → Liquidaciones

No verá: Personal, Configuración, Catálogo (sin permisos admin), Plataforma SaaS.

### Admin (`tenant_owner` / `branch_manager`)
**Permisos:** todos los de la sucursal.

Verá:
- Todo: Operación, Caja, Finanzas, Catálogo, Personal, Configuración.
- No verá Plataforma SaaS (solo superadmin).

### Superadmin
Verá: todo, incluyendo **Plataforma SaaS** al final.

### Garzón (`waiter`)
- No accede al sidebar administrativo.
- Es redirigido a `/nightpos/waiter` con UI móvil dedicada.
- Guard en router previene acceso al layout vertical.

### Limpieza (`cleaning`)
- No accede al sidebar administrativo.
- Es redirigido a `/nightpos/cleaning` con UI móvil dedicada.

### Chica (`girl`)
- No accede al sidebar administrativo.
- Es redirigida a `/nightpos/girl` con UI móvil dedicada.

---

## 4. Dónde queda Venta directa

### En el menú principal
```
Sección: CAJA
  → Mi caja           /nightpos/cash
  → Venta directa     /nightpos/cash/direct-sale   ← aquí
  → Ventas del turno  /nightpos/sales
  → Fiscalización     /nightpos/finance/cash-sessions
```

Permiso requerido: `sales.direct_create`  
Roles que lo ven: `admin`, `cajera`, `cajera senior`  
Roles que NO lo ven: `garzón`, `limpieza`, `chica`

### En la página "Mi caja" (`/nightpos/cash`)
Se agregó un botón prominente **"Venta directa"** (color `primary`, tamaño `large`) en el encabezado de la página, visible únicamente cuando la caja está abierta y el usuario tiene el permiso `sales.direct_create`.

### En la "Consola de turno" (`/nightpos/shift-console`)
Se agregó una sección **"Accesos rápidos"** con los siguientes botones:
- **Cobrar comandas** (visible si `canChargeOrders`)
- **Venta directa** (visible si `canDirectSale`, color `success`)
- **Mi caja** (visible si `canAccessCash`)
- **Servicios**
- **Habitaciones**
- **Liquidaciones** (visible si `canAccessSettlements`)

---

## 5. Cómo se corrigió el sidebar colapsable

### Causa raíz
El archivo `frontend/themeConfig.js` tenía:

```js
overlayNavFromBreakpoint: breakpointsVuetifyV3.lg - 1, // 1279px
```

Esto hacía que en pantallas entre 960px y 1279px (laptops comunes), el sidebar funcionara en **modo overlay** (temporal). El componente `VerticalNav.vue` tiene un watcher que llama `toggleIsOverlayNavActive(false)` al cambiar de ruta, lo que cerraba automáticamente el sidebar en pantallas < 1280px.

### Solución aplicada

```js
// Antes (themeConfig.js):
overlayNavFromBreakpoint: breakpointsVuetifyV3.lg - 1,  // 1279px

// Después:
overlayNavFromBreakpoint: breakpointsVuetifyV3.md - 1,  // 959px
```

**Resultado:**
- **>= 960px** (tablets medianos, laptops, desktops): sidebar **permanente**, nunca se cierra al navegar.
- **< 960px** (teléfonos): sidebar **overlay**, se cierra al navegar (correcto para móvil).

### Comportamiento de los grupos (accordion)
El sistema de `VerticalNavGroup.vue` mantiene abierto el grupo activo al navegar dentro de él. Esto ya funcionaba correctamente. Por ejemplo:
- Navegando dentro de `/nightpos/cash/*` → el grupo "Caja" permanece expandido.
- Navegando dentro de `/nightpos/cashier/orders` → el grupo "Operación" permanece expandido.

El acordeón cierra grupos inactivos cuando se abre otro grupo, lo cual es el comportamiento estándar del template Materialize y no fue modificado.

---

## 6. Archivos modificados

| Archivo | Cambio |
|---|---|
| `frontend/themeConfig.js` | `overlayNavFromBreakpoint`: `lg-1` → `md-1` (959px) |
| `frontend/src/navigation/vertical/nightpos-r4.js` | Reorden completo por prioridad operativa; sección "Caja" separada; sin duplicados |
| `frontend/src/pages/nightpos/cash/index.vue` | Botón "Venta directa" (primary/large) en encabezado cuando caja abierta |
| `frontend/src/pages/nightpos/shift-console/index.vue` | Sección "Accesos rápidos" con 6 botones operativos al inicio |

---

## 7. Validación manual

### Cajera
1. Login cajera → PIN 1234 (demo).
2. Sidebar visible a la izquierda con: **Operación**, **Caja**, **Finanzas**.
3. Expandir **Caja** → Ver "Mi caja" y **"Venta directa"** directamente.
4. Click en "Venta directa" → ruta `/nightpos/cash/direct-sale` → sidebar NO se cierra.
5. Grupo "Caja" permanece expandido en el menú.
6. Ir a "Consola de turno" → ver sección "Accesos rápidos" con botones visibles.

### Admin
1. Login admin.
2. Sidebar muestra: Operación, Caja, Finanzas, Catálogo, Personal, Configuración.
3. Click en "Productos" → sidebar NO se colapsa.
4. Click en "Venta directa" → abre `/nightpos/cash/direct-sale` → sidebar estable.
5. Grupo "Caja" activo al estar en direct-sale.

### Garzón
1. Login garzón → PIN 5678 (demo).
2. Redirige a `/nightpos/waiter`.
3. No ve sidebar administrativo.

### Limpieza
1. Login limpieza.
2. Redirige a `/nightpos/cleaning`.
3. No ve sidebar administrativo.

---

## 8. Restricciones respetadas

- ✅ Backend no modificado (solo frontend).
- ✅ No se eliminaron componentes Materialize.
- ✅ Modo garzón y limpieza intactos.
- ✅ No se ocultaron funciones útiles.
- ✅ No se avanzó con nuevas fases hasta limpiar la navegación.
