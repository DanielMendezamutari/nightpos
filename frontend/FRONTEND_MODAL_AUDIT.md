# FRONTEND_MODAL_AUDIT.md

**Proyecto:** NIGHTPOS — Auditoría de modales (Fase R3)  
**Fecha:** 2026-06-03  
**Criterio:** Modales solo para acciones pequeñas, confirmaciones o vistas rápidas.

---

## NightPOS — Módulo operativo y admin

| Archivo | Propósito | ¿Modal? | Acción R3 | Prioridad |
| ------- | --------- | ------- | --------- | --------- |
| `users/index.vue` | Crear/editar usuario (form grande) | No → convertido | Vistas `create`, `[id]`, `[id]/edit` | Alta |
| `users/index.vue` | Confirmar activar/desactivar | Sí | Mantener modal confirmación | Alta |
| `users/[id]/edit.vue` | Reset PIN | Sí | Mantener | Alta |
| `users/[id]/edit.vue` | Reset contraseña | Sí | Mantener | Alta |
| `platform/tenants/index.vue` | Crear empresa | No → convertido | Vista `tenants/create` | Alta |
| `platform/branches/index.vue` | Crear sucursal | No → convertido | Vista `branches/create` | Alta |
| `categories/index.vue` | Crear categoría | No → convertido | Vista `categories/create` | Alta |
| `products/index.vue` | Crear/editar producto | No → convertido | Vistas `create`, `[id]/edit` | Alta |
| `products/index.vue` | Configurar precios (tabla + form) | No → convertido | Vista `[id]/prices` | Alta |
| `cash/index.vue` | Abrir caja | Sí | Mantener — acción puntual operativa | Media |
| `cash/index.vue` | Movimiento de caja | Sí | Mantener | Media |
| `cash/index.vue` | Cerrar caja | Sí | Mantener (cierre detallado → fase futura vista) | Media |
| `orders/[id].vue` | Cancelar comanda | Sí | Mantener confirmación | Alta |
| `orders/ChargeOrderModal.vue` | Cobro desde comanda | Sí | Mantener — flujo operativo rápido | Alta |
| `orders/AssignGirlModal.vue` | Asignar chica a ítem | Sí | Mantener selección rápida | Media |
| `orders/OrderAddProductDialog.vue` | Agregar producto a comanda | Sí | Mantener — POS en mesa | Media |
| `sales/SaleDetailDialog.vue` | Detalle venta | Sí | Mantener vista rápida | Baja |
| `BranchChangeDialog.vue` | Cambiar sucursal sesión | Sí | Mantener | Media |
| `PlatformContextSelector.vue` | Elegir empresa/sucursal | Sí | Mantener — selector contextual | Alta |

---

## Edición con API pendiente (vista completa, guardar deshabilitado)

| Ruta | Motivo |
| ---- | ------ |
| `platform/tenants/[id]/edit` | Sin `PUT /admin/tenants` en backend |
| `platform/branches/[id]/edit` | Sin `PUT /admin/branches` en backend |
| `categories/[id]/edit` | Sin `PUT /product-categories` en backend |

Formulario Materialize listo; se habilitará guardar cuando exista API.

---

## Plantilla Materialize (demo) — Sin cambios

| Área | Nota |
| ---- | ---- |
| `src/components/dialogs/*` | Demo Materialize — no eliminar |
| `src/views/demos/components/dialog/*` | Demo — oculto del menú operativo |
| `AppBarSearch.vue` | Búsqueda global plantilla |

---

## Resumen R3

| Métrica | Cantidad |
| ------- | -------- |
| Modales convertidos a vistas completas | 8 flujos principales |
| Modales mantenidos (correctos) | 12+ |
| Rutas nuevas | 18 |
| Componentes formulario reutilizables | 5 |

---

*Documento de referencia para PHASE_R3_REPORT.md y siguientes fases.*
