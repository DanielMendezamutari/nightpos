# PHASE_7_5_FRONTEND_ORDERS_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend  
**Fase:** 7.5 — Comandas móvil (Materialize)  
**Fecha:** 2026-06-02  
**Referencias:** `FRONTEND_GUIDELINES.md`, `BOLICHE_RULES.md`, `backend/PHASE_7_REPORT.md`

---

## 1. Pantallas creadas

| Ruta | Archivo | Descripción |
| ---- | ------- | ----------- |
| `/nightpos/orders` | `src/pages/nightpos/orders/index.vue` | Lista de comandas `OPEN`, botón nueva comanda |
| `/nightpos/orders/new` | `src/pages/nightpos/orders/new.vue` | Alta: mesa/ambiente, notas, garzón actual |
| `/nightpos/orders/:id` | `src/pages/nightpos/orders/[id].vue` | Detalle, ítems, total, agregar producto, enviar, cancelar |

**Navegación:** ítems planos en `src/navigation/vertical/nightpos.js` (título de sección + enlaces visibles sin grupo colapsable).  
**Dashboard:** acceso rápido a comandas habilitado.

**Corrección menú lateral:** CASL ocultaba ítems sin `action`/`subject`; se ajustó `can()` en `@layouts/plugins/casl.js` y se mapearon permisos (`orders.access`, `products.list`) en cada entrada.

**Permiso de ruta:** `orders.access` (garzón, cajero, admin demo).

---

## 2. Componentes y piezas técnicas

| Pieza | Ubicación |
| ----- | --------- |
| API comandas | `src/api/orders.js` |
| API usuarios (asignar chica) | `src/api/users.js` |
| Helpers estado/precio | `src/composables/useOrderHelpers.js` |
| Permisos UI | `useNightPosPermissions` → `canAccessOrders`, `canListAdminUsers` |

**Materialize reutilizado:** `VCard`, `VBtn` (size `x-large` en acciones), `VDialog` fullscreen para agregar producto, `VChip`, `VList`, `VSnackbar`, layout `default`.

**Responsive:** botones ancho completo, diálogo fullscreen en móvil para búsqueda de productos, barra de acciones fija al pie en detalle.

---

## 3. Endpoints usados

| Método | Ruta | Uso |
| ------ | ---- | --- |
| GET | `/orders?status=OPEN` | Lista comandas abiertas |
| POST | `/orders` | Nueva comanda |
| GET | `/orders/{id}` | Detalle e ítems |
| POST | `/orders/{id}/items` | Agregar producto (precio resuelto en backend) |
| PATCH | `/orders/{id}/items/{itemId}` | Asignar `girl_user_id` antes de enviar |
| POST | `/orders/{id}/send-to-bar` | Enviar a barra |
| POST | `/orders/{id}/cancel` | Cancelar |
| GET | `/products` | Buscar productos al agregar |
| GET | `/products/{id}/prices` | Vista previa de precio catálogo (sin calcular en front) |
| GET | `/admin/users` | Selector de chica (solo si `admin.users.list`) |

Headers: `Authorization`, `X-Branch-Code`, `X-Tenant-Slug` (cookies / interceptores Axios).

---

## 4. Flujo probado (manual)

1. Login garzón: `casa-demo` / `CENTRO` / PIN `5678`.
2. Menú **Comandas** → lista vacía o comandas `OPEN`.
3. **Nueva comanda** → mesa «Mesa 5» → redirige al detalle.
4. **Agregar producto** → buscar en catálogo → modalidad SOLO → cantidad → precio referencia desde API → agregar → total actualizado por backend.
5. **Enviar a barra** → si hay CON_ACOMPANANTE sin chica, diálogo de asignación → PATCH ítem → POST send-to-bar.
6. **Cancelar** en comanda abierta → vuelve al listado.

Admin (`admin.demo`) puede listar usuarios para elegir chica; garzón usa campo numérico de ID.

---

## 5. Reglas de negocio respetadas en UI

- No se calculan precios ni comisiones en frontend.
- Precio de línea y total vienen del backend tras `POST .../items`.
- Vista previa de precio solo lee `GET .../prices` activos.
- CON_ACOMPANANTE: aviso al agregar; asignación obligatoria antes de enviar.
- Acciones ocultas si la comanda no es modificable (`BILLED` / `CANCELLED`).
- Sin pantallas de caja, cobro, cierre ni liquidaciones.

---

## 6. Extensión backend mínima (Fase 7.5)

Para asignar chica antes de enviar sin duplicar ítems:

- `PATCH /api/v1/orders/{id}/items/{itemId}` con `{ girl_user_id }`
- Caso de uso `AssignOrderItemGirlUseCase`

Documentado aquí porque el flujo de UI lo requiere; no estaba en el reporte Fase 7 original.

---

## 7. Limitaciones

| Tema | Detalle |
| ---- | ------- |
| Historial de estados | El GET de comanda no incluye `order_status_history`; solo se muestran `sent_to_bar_at` / `cancelled_at` si vienen en la orden |
| Lista de chicas | Garzón sin `admin.users.list` debe ingresar ID manual |
| Mesas | `table_label` texto libre; sin mapa de mesas |
| Filtros lista | Solo comandas `OPEN` en el listado principal |
| Editar / quitar ítems | Sin endpoint; no implementado en UI |

---

## 8. Próxima fase recomendada

- **Fase 8 backend:** ventas, cobro (`BILLED`), caja.
- **Frontend:** pantalla caja (solo cuando exista API).
- **Comandas+:** historial de estados en API, listado de personal para asignación de chicas sin permiso admin, módulo mesas/salones.

---

## 9. Comandos

```bash
cd frontend
pnpm run build
```

Backend (si aplica migración / tests):

```bash
cd backend
php artisan test
```

Demo: garzón PIN `5678`, sucursal `CENTRO`, empresa `casa-demo`.
