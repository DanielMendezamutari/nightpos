# SETTLEMENT_SHIFT_SCOPE_DIAGNOSTIC_REPORT.md (Frontend)

**Fecha:** 2026-06-16  
**Estado:** Diagnóstico — sin cambios de código  
**Pantalla:** `/nightpos/settlements` (y tabs waiters / girls / cleaning)

---

## 1. Conclusión

El frontend **no mezcla datos de otro endpoint ni cachea turnos viejos**. Renderiza fielmente la respuesta de:

```
GET /api/v1/settlements/current-shift  →  data.summary (KPIs)
GET /api/v1/settlements/current-shift/pending-sources  →  alertas
```

Los montos (290 chicas, 40 limpieza, 170 piezas, 40 pendiente) vienen del backend porque **`staff_settlements` del turno OPEN sucursal (ID 3)** ya contiene liquidaciones de isa, carla, etc.

El banner **“Turno actual · Caja #7 · Cajera #14”** combina:

- `data.shift` → turno sucursal
- `data.context` → caja/cajera autenticada

Eso **da la impresión** de que los KPIs son de esa caja, pero el API **no filtra por caja** al calcular `summary`.

---

## 2. Pantallas y fuentes de datos

| Ruta | Composable | API | Qué muestra |
|------|------------|-----|-------------|
| `/nightpos/settlements` | `useCurrentShiftSettlements` + `useSettlementPendingSources` | `current-shift`, `pending-sources` | KPI cards + alertas |
| `/nightpos/settlements/waiters` | `useCurrentShiftSettlements` | `current-shift` → `waiters[]` | Tabla garzones |
| `/nightpos/settlements/girls` | `useCurrentShiftSettlements` | `current-shift` → `girls[]` | Tabla chicas (isa 290…) |
| `/nightpos/settlements/cleaning` | `useCurrentShiftSettlements` | `current-shift` → `cleaning[]` | Tabla limpieza (carla 40…) |
| `/nightpos/settlements/history` | propio | `settlements/history` | Historial (scope distinto) |

**Ninguna pantalla operativa filtra por `cash_session_id` en frontend** — no hay parámetro que enviar; el backend no lo expone como filtro.

---

## 3. Flujo en `/nightpos/settlements/index.vue`

```
onMounted / useOnContextChange
  └─ useCurrentShiftSettlements.load()
       └─ fetchCurrentShiftSettlements()
            └─ GET /settlements/current-shift
                 └─ shift, summary, context, sources_summary

  └─ useSettlementPendingSources.load()
       └─ GET /settlements/current-shift/pending-sources
            └─ waiters_without_commission, active_room_services_count, …
```

### KPI cards

```javascript
// index.vue — summaryCards usa directamente:
s.total_girls, s.total_cleaning, s.total_consumption,
s.total_pieces, s.total_pending, …
```

Sin post-proceso. Si el API devuelve 290, la UI muestra 290.

### Banner turno + caja

```javascript
// shift.id, shift.business_date, shift.shift_type_label  ← turno sucursal
// context.cash_session_id, context.cashier_user_id       ← caja autenticada
```

**Problema UX (no bug de fetch):** el usuario interpreta que los montos son de “su” caja porque el banner lo sugiere.

### Mensaje “No hay liquidaciones para este turno”

```javascript
const summaryHasData = computed(() =>
  total_waiters > 0 || total_girls > 0 || total_cleaning > 0
)
```

Con isa (290 chicas) y carla (40 limpieza), **`summaryHasData = true`** → **no** se muestra el aviso de turno vacío, aunque mabel no haya operado.

Además hay **dos alertas contradictorias** posibles cuando hay KPIs de otros:

- No muestra “No hay liquidaciones…” (porque hay datos del turno)
- Sí podría mostrar “Generar liquidaciones…” si `total_waiters/girls/cleaning` fueran 0 pero otros campos > 0 (no aplica aquí)

---

## 4. Alertas de pending-sources

| Alerta | Origen API | ¿Scope turno? |
|--------|------------|---------------|
| Garzones sin comisión (victor) | `waiters_without_commission` | **No** — todos los garzones mal configurados en sucursal |
| Chicas sin flag comisión | `girls_without_commission_flag` | **No** |
| Piezas activas | `active_room_services_count` | **Sí** — `official_shift_id` turno OPEN |
| Fuentes en context | `sources_summary` | **Sí** — turno OPEN sucursal |

El warning de **victor** en la captura **no prueba** mezcla de turnos; prueba que el backend lista garzones globalmente.

---

## 5. Generate liquidaciones (botón)

```javascript
generateCurrentShiftSettlements()
  └─ POST /settlements/generate-current-shift
```

Frontend no envía `official_shift_id` ni `cash_session_id`. El backend decide turno vía `ensureOperationalShift()`.

Tras generar, `refreshAll()` recarga KPIs del mismo endpoint — si el turno sigue siendo ID 3 stale, los montos pueden **aumentar** con fuentes históricas del turno.

---

## 6. Hipótesis E (frontend) — validación

| Afirmación | Resultado |
|------------|-----------|
| Frontend usa endpoint distinto al del banner | **No** — mismo `current-shift` |
| Frontend cachea otro turno | **No** — `useOnContextChange` recarga al cambiar tenant/sucursal |
| Frontend oculta el problema | **Parcial** — banner mezcla turno+caja sin aclarar alcance |
| Historial mezcla por defecto | **Sí (pestaña Historial)** — carga sin filtro salvo fix reciente de turno activo; pantalla principal no es historial |

**Veredicto hipótesis E:** el frontend es **víctima fiel del scope backend**; la confusión es **presentación + expectativa**, no fetch incorrecto.

---

## 7. Qué ver en DevTools (mabel, reproducción)

1. Abrir `/nightpos/settlements`
2. Network → `GET .../settlements/current-shift`
3. Inspeccionar JSON:

```json
{
  "shift": { "id": 3, "business_date": "2026-06-14", "shift_type": "NIGHT" },
  "summary": {
    "total_girls": "290.00",
    "total_cleaning": "40.00",
    "total_pieces": "170.00",
    "total_pending": "40.00"
  },
  "context": {
    "tenant_id": 1,
    "branch_id": 2,
    "current_shift_id": 3,
    "cash_session_id": 7,
    "cashier_user_id": 14
  },
  "sources_summary": { "sales": N, "rooms": M, ... }
}
```

4. Comparar con `GET .../cash/session/current/close-check`:

```json
{
  "official_shift_id": 3,
  "context": { "current_shift_id": 3, "cash_session_id": 7 }
}
```

Si ambos `official_shift_id = 3` y summary > 0 → **backend devuelve liquidaciones del turno 3 compartido**, no bug de render.

5. Opcional: `GET .../settlements/current-shift/pending-sources` → ver `waiters_without_commission` incluye victor sin actividad.

---

## 8. Desalineación UX detectada

| Elemento UI | Qué comunica | Qué hace el API |
|-------------|--------------|-----------------|
| “Turno actual ID 3 · 2026-06-14” | Este turno | Turno OPEN sucursal (puede ser stale) |
| “Caja #7 · Cajera #14” | Mis datos | Solo metadata; KPIs **no** filtrados por caja |
| KPI cards | Totales operativos | Suma `staff_settlements` del turno sucursal |
| Warning victor | Problema del turno | Config global sucursal |

---

## 9. Plan de corrección frontend (prioridad, sin implementar)

### P1 — Claridad (sin ocultar datos)

1. Banner: distinguir **“Turno sucursal”** vs **“Tu caja”** y avisar si `shift.business_date` ≠ fecha operativa actual.
2. Si backend expone `shift_match` / `cash_session_official_shift_id`, mostrarlo en dev/admin.
3. Mensaje vacío: considerar `sources_summary` + settlements de **esta caja** cuando backend lo soporte.

### P2 — Coherencia alertas

4. No mostrar warning garzones/chicas si backend no los limita al turno (depende fix backend).
5. Evitar alertas duplicadas/conflictivas en index.vue.

### P3 — Historial

6. Confirmar que Historial precarga turno activo; documentar que liquidaciones viejas del turno stale aparecen en operativo hasta cerrar turno.

### P4 — Tests E2E / manual

7. Caso: cajera nueva, caja 0, turno stale con settlements → UI debe dejar claro que son del turno sucursal, no de su caja (hasta fix backend).

---

## 10. Archivos frontend auditados

| Archivo | Hallazgo |
|---------|----------|
| `src/pages/nightpos/settlements/index.vue` | Render directo de `summary`; banner mixto |
| `src/composables/useCurrentShiftSettlements.js` | Sin filtros locales |
| `src/composables/useSettlementPendingSources.js` | Sin filtros locales |
| `src/api/settlements.js` | Sin query params en current-shift |
| `src/pages/nightpos/settlements/girls.vue` | Lista `girls[]` del mismo API |
| `src/pages/nightpos/settlements/cleaning.vue` | Lista `cleaning[]` del mismo API |

---

## 11. Referencia backend

Ver `backend/SETTLEMENT_SHIFT_SCOPE_DIAGNOSTIC_REPORT.md` para SQL, use cases y plan P0–P4.
