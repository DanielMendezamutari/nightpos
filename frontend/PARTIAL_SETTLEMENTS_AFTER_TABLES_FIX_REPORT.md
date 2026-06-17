# Fix frontend — Liquidaciones parciales (refresh UI)

**Fecha:** 2026-06-16  
**Par backend:** `backend/PARTIAL_SETTLEMENTS_AFTER_TABLES_FIX_REPORT.md`

---

## Resumen

El backend **sí genera** liquidaciones parciales (confirmado por tests Mis mesas). El frontend **no filtraba** PENDING; el problema visible era sobre todo:

1. Tabs **Chicas** / **Garzones** sin refresco SSE tras generate/pay.
2. Usuario en tab secundaria mientras generate ocurre en **Resumen**.

---

## Cambio aplicado

SSE `settlement.generated` + `settlement.paid` en:

- `pages/nightpos/settlements/girls.vue`
- `pages/nightpos/settlements/waiters.vue`

Mismo patrón que `settlements/index.vue` (debounce 600 ms → `reload()`).

**No se añadieron filtros** que oculten PENDING.

---

## Validación

| Check | Resultado |
|-------|-----------|
| `npm run build` | OK |
| Sin filtros client-side por status/staff | ✅ |

---

## Si la UI sigue vacía

1. Network → `GET /settlements/current-shift` → ¿`girls[]` incluye PENDING?
2. Si API vacía → backend/migración/scope (ver backend fix report).
3. Si API OK pero UI vieja → F5; con este fix debería actualizar sola.

---

## Archivos

- `src/pages/nightpos/settlements/girls.vue`
- `src/pages/nightpos/settlements/waiters.vue`
