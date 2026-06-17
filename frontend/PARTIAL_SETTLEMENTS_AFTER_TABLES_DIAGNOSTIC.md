# Diagnóstico frontend — Liquidaciones parciales tras Mis mesas

**Fecha:** 2026-06-16  
**Modo:** Solo auditoría — **sin cambios de código**  
**Par backend:** `backend/PARTIAL_SETTLEMENTS_AFTER_TABLES_DIAGNOSTIC.md`

---

## Resumen ejecutivo

| Pregunta | Respuesta |
|----------|-----------|
| ¿El frontend oculta PENDING si ya hay PAID? | **No.** Render directo de `girls[]` / `waiters[]` sin deduplicación por staff. |
| ¿Mis mesas cambia llamadas a settlements? | **No.** Mismos endpoints; solo cambia cómo se crea la orden (tap mesa → open). |
| ¿Frontend o backend? | Si F5 + Network muestran `girls[]` vacío → **backend o scope**. Si API trae PENDING y no se ve → **refresh/SSE** (secundario). |

---

## Flujo UI de liquidaciones

```
settlements/index.vue (Resumen)
  ├─ useCurrentShiftSettlements() → GET /settlements/current-shift
  ├─ useSettlementPendingSources() → GET /settlements/current-shift/pending-sources
  └─ generate() → POST /settlements/generate-current-shift

settlements/girls.vue (Chicas)
  └─ useCurrentShiftSettlements() → mismo GET, tabla :items="girls"
```

### Sin filtros que oculten PENDING

`useCurrentShiftSettlements.js` asigna respuesta directamente:

```javascript
girls.value = data.girls ?? []
waiters.value = data.waiters ?? []
```

- No hay `.filter(status === 'PENDING')` invertido.
- No hay “si staff ya tiene PAID, omitir PENDING”.
- `girls.vue` muestra **todas** las filas; botón Pagar solo si `status === 'PENDING'`.

**Conclusión:** Una fila PENDING nueva **siempre se renderiza** si viene en la API.

---

## Cuándo el frontend muestra listas vacías (sin ser bug de filtro)

### 1. `context.empty_overview === true`

Backend (`GetCurrentShiftSettlementsUseCase`) devuelve:

```json
{
  "girls": [],
  "waiters": [],
  "context": { "scope": "my_cash_session", "empty_overview": true, "shift_rotated": true }
}
```

**Quién lo sufre:** cajera normal (scope `my_cash_session` forzado).

**Causas:**

- Caja abierta en turno **anterior** tras auto-rotación (`cash_session_shift_id ≠ open_shift_id`)
- Caja sin ventas/actividad en el turno actual

**Síntoma:** “No aparece liquidación” aunque admin con scope `shift` sí la ve en BD/API.

**Banner en UI:** `settlements/index.vue` muestra scope “Mi caja” vs “Turno completo” — puede confundir si `empty_overview` no se explica.

---

### 2. Tab Chicas / Garzones sin SSE

| Página | SSE `settlement.generated` |
|--------|---------------------------|
| `settlements/index.vue` | ✅ Sí (debounced refresh) |
| `settlements/girls.vue` | ❌ No — solo mount + context change |
| `settlements/waiters.vue` | ❌ No |

**Síntoma:** Usuario genera desde Resumen, cambia a Chicas sin F5 → parece que “no apareció”.  
**Mitigación manual:** F5 o volver a Resumen y regenerar navegación.

Tras generate, snackbar muestra `created_items` — si dice **0 líneas nuevas**, el problema es backend, no UI.

---

### 3. Error en load sin limpiar arrays

En catch de `useCurrentShiftSettlements`, se notifica error pero **no se vacían** `girls[]` — muestra datos **viejos**, no vacío. Menos frecuente que empty_overview.

---

## Mis mesas — impacto indirecto en liquidaciones

### Lo que NO cambia

- Endpoints de settlements
- Payload de charge
- Lógica de generate / pay en frontend

### Lo que SÍ cambia (flujo garzón)

| Paso | Comportamiento | Impacto settlement |
|------|----------------|-------------------|
| Tap mesa | `POST /waiter/my-tables/{id}/open` | Orden con `service_table_id`; mismo charge path |
| Add producto | `OrderAddProductDialog` + `waiter/orders/[id].vue` | Envía `girl_user_id` y `allocations[]` igual que antes |
| Chica diferida | Mobile: “asignar al enviar a barra” | Charge **bloqueado** hasta asignar — no debería cobrarse sin chica |
| Combo | `ComboAllocationDialog` → `syncOrderItemAllocations` | Igual que POS; allocations en charge |

**Correlación Mis mesas ↔ liquidaciones:** no hay acoplamiento en código frontend de settlements. El reporte operativo probablemente coincide con:

- Deploy simultáneo de Fase C + fix parciales / scope cajera
- Flujo más lento (mesa → comanda → barra → caja) → más riesgo de rotación de turno

---

## Checklist Network (caso real)

### Paso 9 — Segunda generación

1. **POST** `/api/v1/settlements/generate-current-shift`
   - Status 201?
   - Body: `created_items`, `settlements_touched`, `shift_id`

2. **GET** `/api/v1/settlements/current-shift`
   - `context.scope`, `context.empty_overview`, `context.shift_rotated`
   - `girls[]`: ¿filas con `status: "PENDING"` y `cut_label: "Corte #2"`?
   - ¿`staff_user_id` de la segunda chica presente?

3. Comparar como **admin** vs **cajera** (mismo turno):
   - Si admin ve PENDING y cajera no → **scope**, no Mis mesas.

### Paso 8 — Tras segundo cobro (antes de generate)

4. Confirmar cobro OK (orden pasa a BILLED en UI caja).
5. Opcional: no hay endpoint frontend de sale detail en settlements — ver BD vía backend doc.

---

## Respuestas rápidas (frontend)

| # | Pregunta | Respuesta frontend |
|---|----------|-------------------|
| 5 | ¿Fuente en pending-sources? | Endpoint **no lista** ventas CON_ACOMPANANTE unsettled — no usar como única señal |
| 9 | ¿UI oculta PENDING? | **No**, salvo API devuelva `girls: []` (empty_overview) o tab sin refresh |
| 10 | ¿Mis mesas afectó order creation en settlements? | **No** en capa settlements; sí flujo garzón previo al cobro |

---

## Archivos revisados

| Archivo | Hallazgo |
|---------|----------|
| `pages/nightpos/settlements/index.vue` | Generate + SSE; sin filtro status |
| `pages/nightpos/settlements/girls.vue` | Tabla cruda `girls[]`; sin SSE |
| `composables/useCurrentShiftSettlements.js` | Sin filtros |
| `composables/useSettlementPendingSources.js` | Solo alertas; permiso `settlements.pending_sources` |
| `api/settlements.js` | Sin query params en current-shift |
| `pages/nightpos/waiter/index.vue` | Mis mesas — no toca settlements |
| `composables/useWaiterTables.js` | open → navega a order detail |
| `pages/nightpos/waiter/orders/[id].vue` | add item con `girl_user_id` + allocations |
| `components/nightpos/orders/OrderAddProductDialog.vue` | Chica opcional al add en mobile |
| `components/nightpos/orders/ComboAllocationDialog.vue` | Reparto combo |

---

## Plan de corrección frontend (NO implementado)

| Prioridad | Acción | Cuándo |
|-----------|--------|--------|
| P0 | Confirmar Network antes de tocar UI | Siempre |
| P1 | SSE / reload en `girls.vue` y `waiters.vue` | Si API OK pero UI stale |
| P2 | Banner explícito cuando `empty_overview` + `shift_rotated` | Si cajera no ve liquidaciones de admin |
| P3 | Test E2E: Mis mesas → charge → generate → assert fila en girls | Prevención regresión |

**No tocar** Mis mesas, CBA, ni lógica de generate hasta cerrar diagnóstico backend.

---

## Riesgos

- Añadir filtros frontend “para arreglar” visibilidad → **enmascararía** bugs backend.
- Forzar scope `shift` para cajera → mostrar liquidaciones de otras cajas del turno.

---

*Documento de diagnóstico. Sin cambios de código en diagnóstico inicial.*

---

## Resolución (2026-06-16)

- Backend genera Corte #2 correctamente (tests Mis mesas).
- Frontend: SSE añadido en `girls.vue` / `waiters.vue` para refresh tras generate/pay.
- Ver: `frontend/PARTIAL_SETTLEMENTS_AFTER_TABLES_FIX_REPORT.md`
