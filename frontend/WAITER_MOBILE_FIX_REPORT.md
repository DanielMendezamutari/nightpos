# WAITER_MOBILE_FIX_REPORT

Corrección del modo garzón móvil tras auditoría `WAITER_MOBILE_AUDIT.md`.

---

## 1. Causa exacta

| # | Causa |
|---|--------|
| 1 | **`fetchServiceAreas`** no usaba `unwrapNightPosResponse` → la lista de ambientes llegaba vacía siempre. |
| 2 | Validación frontend con mensaje técnico **“Indique mesa o ambiente”** antes del POST. |
| 3 | Campo texto **deshabilitado** al elegir ambiente → confusión en móvil. |
| 4 | Payload podía enviar `service_area_id: null` y `table_label: null` juntos. |
| 5 | Cabecera `VAppBar` genérica sin contexto operativo (garzón / sucursal / salir). |

El backend **no** requería cambios: ya acepta `service_area_id` o `table_label` válido.

---

## 2. Corrección aplicada

| Archivo | Cambio |
|---------|--------|
| `src/api/serviceAreas.js` | `unwrapNightPosResponse` en list/create/update |
| `src/utils/waiterOrderPayload.js` | Payload exclusivo ambiente **o** texto libre |
| `src/pages/nightpos/waiter/orders/new.vue` | UX móvil completa + alerta clara |
| `src/components/nightpos/waiter/WaiterMobileHeader.vue` | Cabecera compacta garzón |
| `src/pages/nightpos/waiter/index.vue` | Cards acción + KPI, sin tablas |
| `src/pages/nightpos/waiter/orders/index.vue` | Header móvil |
| `src/pages/nightpos/waiter/orders/[id].vue` | Header móvil, `mobile-waiter` en diálogo, estados en español |
| `src/api/waiter.js` | `fetchWaiterServiceAreas`, `fetchWaiterGirls` |
| `src/composables/useOperationalGirls.js` | `waiterMode` sin fallback admin |
| `src/composables/useOrderProductShortcuts.js` | Null-safe + `repairStorage()` |
| `backend/database/seeders/NightPosSeeder.php` | Ambientes demo: Mesa 1/2, VIP, Barra |

**Refinamiento UX (Jun 2026):** ver `WAITER_MOBILE_UX_REFINEMENT_REPORT.md` — KPI cards, cards de comanda, cabecera mínima, sin 403 innecesarios.

---

## 3. Nueva UX móvil

### Nueva comanda (`/nightpos/waiter/orders/new`)

- Título: **Nueva comanda**
- Subtítulo: **Elige una mesa o escribe una referencia**
- Botones grandes de ambientes (si existen en API)
- Input siempre habilitado: **O escribe mesa / ambiente**
- Botón **Abrir comanda** tamaño `x-large`
- Alerta: *“Escribe la mesa o ambiente para abrir la comanda.”*

### Inicio garzón

- Cards: Nueva comanda, Mis comandas abiertas, En barra, Pendientes de cobro
- Lista de últimas comandas (cards, no tablas)
- Bottom navigation fija

### Cabecera (`WaiterMobileHeader`)

- Nombre garzón, sucursal, turno activo, estado en línea, cerrar sesión
- Sin navbar admin ni elementos demo

---

## 4. Payload correcto

**Ambiente:**

```json
{
  "service_area_id": 1,
  "notes": "opcional"
}
```

**Texto libre:**

```json
{
  "table_label": "Mesa 1",
  "notes": "opcional"
}
```

**No se envía** `{ "service_area_id": null, "table_label": "" }` — `buildWaiterCreateOrderPayload` omite campos vacíos y aborta antes del POST si falta referencia.

---

## 5. Validación manual

| # | Paso | Esperado |
|---|------|----------|
| 1 | Login garzón PIN `5678` | `/nightpos/waiter` |
| 2 | Nueva comanda → botón **Mesa 1** (o VIP) → Abrir | Comanda creada |
| 3 | Nueva comanda → escribir “Cliente Juan” → Abrir | Comanda creada, sin error técnico |
| 4 | Sin selección ni texto → Abrir | Alerta clara en español |
| 5 | Vista móvil DevTools | Botones grandes, header compacto |
| 6 | Agregar bebida SOLO / CON_ACOMPANANTE | Cards de producto, sin “beverage”, chips categoría — ver `WAITER_PRODUCT_SELECTOR_UX_REPORT.md` |
| 7 | Enviar a barra | Chip “En barra”, sin código técnico |
| 8 | Consola | Sin 403 a `/admin/users` ni `/staff/girls` en flujo garzón |

---

## 6. Selector de productos (Jun 2026)

- `OrderAddProductDialog` rediseñado para `mobile-waiter`: cards, chips categoría, textos en español.
- `useProductLabels.js` — sin slugs técnicos (`beverage`, `SOLO_CLIENTE`, etc.) visibles al garzón.
- Detalle: `WAITER_PRODUCT_SELECTOR_UX_REPORT.md`.

## 7. Pendientes

- Nombre de turno oficial en header (API dedicada).
- PWA / vibración en cambio de estado.
- Tests E2E frontend del flujo garzón.

---

*Ver también `WAITER_MOBILE_AUDIT.md` y `PHASE_C4_WAITER_REPORT.md`.*
