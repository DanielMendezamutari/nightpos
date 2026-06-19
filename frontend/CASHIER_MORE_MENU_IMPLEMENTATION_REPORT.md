# Implementación — Tab «Más» cajera por permisos



**Fecha:** 2026-06-17  

**Actualizado:** 2026-06-17 — Piezas movido a tab principal



## Cambios



### `useCashierMoreMenu.js` (nuevo)



Composable con 4 secciones filtradas por `can(permission)`:



1. **Operación** — liquidaciones, ventas, consola, servicios (sin Piezas)

2. **Catálogo** — productos, categorías, vista precios

3. **Configuración** — motivos caja, métodos pago, ambientes, mesas, asignar mesas, tipos habitación

4. **Finanzas y turno** — historial liquidaciones, cierre turno, turno actual, reportes, fiscalización



### `cashier/more.vue`



- Usa `useCashierMoreMenu` con secciones agrupadas.

- Empty state si ningún ítem visible.

- Sección **Cuenta** al final (logout / cambiar cuenta).



### `cashierRouting.js`



Ampliado `CASHIER_SHELL_SECONDARY_PREFIXES`:



```

/nightpos/products, /categories, /catalog, /services, /rooms,

/settings, /shift-console, /staff

```



Redirect cajera básica: `nightpos-services-room-services` → `nightpos-cashier-piezas`.



Guards de permiso por ruta evitan acceso a pantallas no autorizadas.



## Shell principal (actualizado)



```

Cobrar | Piezas | Venta | Caja | Más

```



**Piezas** ya no aparece en «Más» (evita duplicado). Sigue en tab principal si `room_services.access` o `rooms.access`.



Config tabs: `cashierShellNav.js`.



## Cajera básica — ítems esperados en «Más»



**Operación:** Liquidaciones, Ventas del turno, Consola de turno, Manillas, Shows, Control piezas, Habitaciones (dashboard).



**Catálogo:** Productos, Categorías, Vista precios.



**Configuración:** Motivos de caja, Métodos de pago, Ambientes, Mesas, Tipos de habitación.



**No visible:** Piezas (tab principal), Cierre turno, Reportes, Asignar mesas, Fiscalización cajas.



## Cajera senior — adicional



- Asignar mesas

- Cierre de turno

- Fiscalización de cajas



## Sin cambios



- Módulo de piezas (`services/room-services`)

- Menú admin completo para senior con `FULL_MENU_ROLES`

- Cobro inline, liquidaciones, movimientos, permisos seeders

