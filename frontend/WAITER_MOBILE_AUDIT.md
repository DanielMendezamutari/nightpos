# WAITER_MOBILE_AUDIT — Modo garzón móvil

**Fecha:** 2026-06-04  
**Referencias:** `FRONTEND_GUIDELINES.md`, `FRONTEND_AUDIT_REPORT.md`, `NIGHTPOS_OPERATION_AUDIT.md`, `SYSTEM_QUICK_ACTIONS_AUDIT.md`, `PHASE_C4_WAITER_REPORT.md`, `DOMAIN_DESIGN.md`, `BOLICHE_RULES.md`, `ROADMAP.md`.

---

## 1. Flujo actual del garzón

| Paso | Ruta / acción | Estado |
|------|---------------|--------|
| 1 | Login PIN `5678` → redirect `/nightpos/waiter` | OK (post fix home route) |
| 2 | Dashboard KPI + botón nueva comanda | OK funcional, UX mejorable |
| 3 | `/nightpos/waiter/orders/new` — elegir mesa/ambiente | **Falla UX + API cliente** |
| 4 | Detalle comanda — agregar bebida, SOLO / CON_ACOMPANANTE | OK (`OrderAddProductDialog`) |
| 5 | Enviar a barra | OK |
| 6 | Cobro | Cajero (garzón sin `sales.charge`) |

Layout: `blank` + `WaiterBottomNav` (sin sidebar admin). Guard limita rutas a `/nightpos/waiter/*`.

---

## 2. Problema exacto: “Indique mesa o ambiente”

| Aspecto | Detalle |
|---------|---------|
| **Origen del texto** | **Frontend** — `waiter/orders/new.vue`, validación local antes de `POST /orders`. |
| **Condición** | `!service_area_id && !table_label.trim()` |
| **No es** | Mensaje del backend (backend usa: *“Indique mesa, ambiente catalogado o etiqueta de servicio.”*) |

### Escenarios que disparan el mensaje

1. **Usuario no escribe ni elige ambiente** — validación correcta (mensaje debe ser claro, no técnico).
2. **Ambientes no cargan** — `fetchServiceAreas` lee `response.data.service_areas` pero la API NightPOS devuelve `data.service_areas` dentro del wrapper → lista siempre `[]` → solo queda texto libre; si el usuario cree que debe tocar un botón inexistente, no avanza.
3. **Campo texto deshabilitado** — `:disabled="!!form.service_area_id"` impide escribir referencia libre tras tocar un ambiente; si el área quedó seleccionada con `id` inválido o el usuario quiere texto distinto, queda bloqueado visualmente.
4. **Payload mixto vacío** — al enviar, se mandaba `table_label: null` y `service_area_id: null` cuando la validación fallaba por carrera o estado inconsistente.

---

## 3. Qué espera el backend

`CreateOrderRequest` + `CreateOrderUseCase`:

| Campo | Regla |
|-------|--------|
| `service_area_id` | Opcional, entero ≥ 1; si existe y es activo en sucursal, puede rellenar `table_label` con el nombre del ambiente. |
| `table_label` | Opcional en request; **obligatorio efectivo** tras lógica de dominio (texto o nombre de ambiente). |
| `waiter_user_id` | Opcional; garzón → siempre el usuario logueado. |
| `notes` | Opcional. |

**Invariante:** al menos uno de: `service_area_id` válido **o** `table_label` no vacío (tras trim).

Tests existentes: `PhaseC4WaiterTest` con `table_label`; `PhaseC3Test` con `service_area_id` (cajero).

---

## 4. Qué enviaba el frontend (antes)

```json
{
  "table_label": null,
  "service_area_id": null,
  "notes": null
}
```

o mezcla con `service_area_id` numérico + `table_label: null` (válido para backend).

La validación cortaba antes del POST con snackbar técnico.

---

## 5. ¿Usa `service_area_id`?

**Sí**, en UI (`pickArea` → `form.service_area_id`) y en POST, **pero** los ambientes no llegaban al componente por parseo incorrecto del JSON de respuesta.

---

## 6. ¿Usa `table_label`?

**Sí**, campo `VTextField` v-model `form.table_label`, enviado como `table_label` o `null` si vacío.

---

## 7. ¿Se cargan los ambientes?

| Ítem | Estado |
|------|--------|
| Permiso garzón | `settings.service_areas` en seeder — OK |
| Endpoint | `GET /api/v1/service-areas?active_only=true` — OK |
| Seeder | Al menos `M01` → “Mesa 1” en `casa-demo` / `CENTRO` |
| Cliente `fetchServiceAreas` | **Bug:** no usa `unwrapNightPosResponse` → array vacío en UI |

---

## 8. ¿Selector claro en celular?

| Problema | Impacto |
|----------|---------|
| Botones solo si `serviceAreas.length` | Con bug API, nunca se ven |
| Input deshabilitado con ambiente | Confuso en móvil |
| Sin título/subtítulo orientador | Usuario no sabe qué hacer |
| `VAppBar` genérico sin contexto sucursal/garzón | Cabecera poco operativa |

---

## 9. Problemas visuales en cabecera superior

- `VAppBar` primary genérico en todas las pantallas waiter.
- Sin nombre garzón, sucursal, turno, salir, estado conexión.
- Dashboard mezcla 4 KPI + botón duplicado “Nueva comanda”.
- No hay componente header compartido; cada vista repite barra distinta.

`FRONTEND_AUDIT_REPORT.md` ya marca experiencia móvil garzón como **insuficiente (2/5)**.

---

## 10. Propuesta de corrección

| # | Acción | Prioridad |
|---|--------|-----------|
| 1 | Corregir `fetchServiceAreas` con `unwrapNightPosResponse` | P0 |
| 2 | Helper `buildWaiterCreateOrderPayload` — solo `service_area_id` **o** `table_label`, nunca ambos vacíos | P0 |
| 3 | Rediseñar `waiter/orders/new.vue` (título, subtítulo, botones grandes, input libre siempre habilitado, alerta humana) | P0 |
| 4 | Componente `WaiterMobileHeader` (nombre, sucursal, turno/sesión, salir, online) | P1 |
| 5 | Dashboard: cards acción + KPI sin tablas | P1 |
| 6 | Tests backend garzón + `service_area_id` y rechazo sin datos | P1 |
| 7 | Seeder más ambientes demo (Mesa 2, VIP, Barra) — opcional | P2 |

**Backend:** no requiere cambio de validación; comportamiento alineado con `BOLICHE_RULES` y `DOMAIN_DESIGN` (comanda con referencia de servicio).

---

*Auditoría completada — corrección implementada en `WAITER_MOBILE_FIX_REPORT.md`.*
