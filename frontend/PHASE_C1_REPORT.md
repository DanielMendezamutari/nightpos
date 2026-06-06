# Fase C1 — Correcciones críticas de operación (Frontend)

**Fecha:** 2026-06-02  
**Referencia:** `NIGHTPOS_OPERATION_AUDIT.md` (P1)

## Resumen

UX operativa para garzón en comandas, producto rápido desde agregar ítem, habitaciones en limpieza al registrar pieza, tarjetas de dashboard navegables y advertencias en liquidaciones. Sin reportes, impresión, clientes ni mesas.

## Cambios por ítem

| ID | Pantalla / componente | Comportamiento |
|----|----------------------|----------------|
| C1-1 | `orders/new.vue` | `staffRole === WAITER` → garzón fijo (sesión). Otros roles → autocomplete obligatorio |
| C1-2 | `orders/new.vue` + `QuickWaiterCreateDialog` | Alta de garzón sin salir del flujo (mismo patrón que manillas) |
| C1-3 | `OrderAddProductDialog` + `QuickProductCreateDialog` | Crear producto + precios, refrescar catálogo, preseleccionar y continuar agregar ítem |
| C1-4 | `room-services/create.vue` | Si no hay disponibles: lista códigos en limpieza + botón «Ir a limpieza». Dashboard: cards enlazadas |
| C1-5 | `settlements/index.vue` | Alertas (no bloquean) por garzones sin % y chicas sin flag de comisión |

## Archivos tocados

- `src/pages/nightpos/orders/new.vue`
- `src/pages/nightpos/orders/[id].vue`
- `src/components/nightpos/orders/OrderAddProductDialog.vue`
- `src/components/nightpos/catalog/QuickProductCreateDialog.vue` (nuevo)
- `src/api/products.js` — `quickCreateProduct()`
- `src/pages/nightpos/services/room-services/create.vue`
- `src/pages/nightpos/rooms/dashboard.vue`
- `src/pages/nightpos/settlements/index.vue`

## Permisos UI

| Acción | Permiso |
|--------|---------|
| Selector / quick garzón en nueva comanda | `staff.quick_create_waiter` (lista + diálogo) |
| Crear producto desde comanda | `products.quick_create` |
| Ir a limpieza | `room_services.cleaning_view` |
| Banners liquidación | `settlements.pending_sources` |

## Flujo producto rápido (comanda)

1. Abrir «Agregar producto».
2. Si catálogo vacío o «Crear producto nuevo» → diálogo con nombre, categoría, precio SOLO, opcional CON_ACOMPANANTE + montos chica/casa.
3. Tras guardar: recarga productos, preselecciona el nuevo y muestra precio SOLO_CLIENTE.

## Validación manual sugerida

1. Login cajero (`1234`) → Nueva comanda → exige garzón → quick garzón → abrir comanda.
2. Login garzón (`5678`) → Nueva comanda → garzón bloqueado a su nombre.
3. Comanda abierta → Agregar producto → quick product → agregar línea.
4. Registrar pieza sin habitaciones disponibles → ver alerta + enlace limpieza.
5. Liquidaciones → ver banners de personal antes de «Generar».
