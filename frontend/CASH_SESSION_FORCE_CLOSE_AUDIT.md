# CASH_SESSION_FORCE_CLOSE_AUDIT.md (Frontend)

**Feature:** Cierre administrativo de caja  
**Fecha auditoría:** 2026-06-21  
**Estado:** Implementado (2026-06-21). Ver `CASH_SESSION_FORCE_CLOSE_IMPLEMENTATION_REPORT.md`.

---

## 1. Veredicto

El frontend tiene:

- ✅ Flujo completo de **cierre de cajera** (close-check, blockers, arqueo multi-método)
- ✅ Módulo **Fiscalización de cajas** read-only (listado, historial, detalle, resumen, impresión)

No tiene **ningún** elemento de force-close:

- ❌ Botón “Cerrar administrativamente”
- ❌ Permiso en guards/composables
- ❌ Cliente API
- ❌ Modal de confirmación
- ❌ Badges / metadata de cierre forzado
- ❌ Preview de blockers para sesión ajena

---

## 2. Pantallas existentes (reutilizables)

### Fiscalización de cajas

| Ruta | Archivo | Uso para force-close |
|------|---------|----------------------|
| `nightpos-finance-cash-sessions` | `pages/nightpos/finance/cash-sessions/index.vue` | Botón en filas `OPEN` |
| `nightpos-finance-cash-sessions-id` | `pages/nightpos/finance/cash-sessions/[id].vue` | Botón en header + panel post-cierre |
| `nightpos-finance-cash-sessions-history` | `history.vue` | Badge en historial |
| Impresión | `pages/nightpos/print/cash-session/[id].vue` | Badge en ticket |

Nav: `navigation/vertical/nightpos-r4.js` → “Fiscalización de cajas”.

### Cierre cajera (referencia UX)

| Archivo | Patrón reusable |
|---------|-------------------|
| `pages/nightpos/cash/index.vue` | Dialog blockers, acciones con rutas |
| `pages/nightpos/shifts/close.vue` | Layout blockers + warnings + confirmación |

---

## 3. API client actual

### `src/api/cash.js` (cajera)

| Función | Endpoint |
|---------|----------|
| `fetchCashSessionCloseCheck()` | `GET /cash/session/current/close-check` |
| `closeCashSession()` | `POST /cash/session/close` |

Solo sesión **actual** del usuario — no sirve para admin sobre otra caja.

### `src/api/adminCashSessions.js` (admin)

| Función | Endpoint |
|---------|----------|
| `fetchAdminCashSessions()` | `GET /admin/cash-sessions` |
| `fetchAdminCashSession()` | `GET /admin/cash-sessions/{id}` |
| `fetchAdminCashSessionsSummary()` | `GET /admin/cash-sessions/summary` |

**Faltan:**

```js
fetchAdminCashSessionCloseCheck(id)   // GET .../close-check
forceCloseAdminCashSession(id, body)  // POST .../force-close
```

---

## 4. Permisos frontend

| Permiso | Uso actual |
|---------|------------|
| `cash.access` | Mi Caja, cierre cajera |
| `admin.cash_sessions.list` | Listados fiscalización |
| `admin.cash_sessions.view` | Detalle + print admin |
| `admin.cash_sessions.summary` | Resumen |

`definePage({ meta: { permission } })` en `plugins/1.router/guards.js`.

**No existe** `admin.cash_sessions.force_close` (ni `cash_sessions.force_close`).

Implementación sugerida:

- Route meta opcional en detalle/index
- `can('admin.cash_sessions.force_close')` en botón y modal
- Registrar en seed backend + documentación de roles

---

## 5. UI propuesta (checklist)

### Botón

- Ubicación: `cash-sessions/index.vue` (acción en fila OPEN) y `[id].vue` (header)
- Visible solo si: `session.status === 'OPEN'` && permiso force-close

### Modal obligatorio

**Motivo** (select):

- Cajera se retiró → `cashier_left`
- Error operativo → `operational_error`
- Caja no pudo cerrar por pendientes → `blockers_unresolved`
- Cambio de turno → `shift_change`
- Otro → `other`

**Notas:** textarea requerida.

**Preview pendientes** (antes de confirmar):

Cargar `GET /admin/cash-sessions/{id}/close-check` y listar:

- liquidaciones pendientes
- comandas activas
- piezas activas
- diferencia estimada (desde summary)
- movimientos sin revisar (si aplica)

**Checkbox confirmación:**

> “Entiendo que este cierre no paga pendientes ni corrige diferencias.”

### Post éxito

- Toast de confirmación
- Badge en detalle/historial
- SSE `cash.session.closed` ya escuchado en shift-console — verificar refresh de listado abierto

---

## 6. Visualización post force-close

### Detalle `[id].vue` — campos nuevos a mostrar

| Campo API | UI |
|-----------|-----|
| `is_forced_close` | Badge “Cierre administrativo” |
| `forced_closed_by` | Admin que cerró |
| `opened_by_user_id` / cashier | Cajera original |
| `forced_close_reason` | Motivo traducido |
| `forced_close_notes` | Notas |
| `forced_closed_at` | Fecha/hora |
| `close_blockers_snapshot` | Panel “Pendientes al cierre” |
| `financial_summary_snapshot` | Resumen congelado |

Estado compuesto sugerido: **“Cerrada con observaciones”** cuando `is_forced_close && blockers.length > 0`.

### Reportes

| Pantalla | Cambio |
|----------|--------|
| `finance/reports/index.vue` (tab Caja) | Badge en cards de sesión |
| `PrintableCashSessionReport.vue` | Línea “CIERRE ADMINISTRATIVO” |
| `cash-sessions/history.vue` | Columna o chip en status |

---

## 7. SSE

Listeners existentes de `cash.session.closed`:

- `pages/nightpos/cash/index.vue`
- `pages/nightpos/cash/direct-sale.vue`
- `pages/nightpos/shift-console/index.vue`
- `composables/useCashierShell.js`

Acción sugerida en fiscalización: suscribir en `cash-sessions/index.vue` o recargar listado tras force-close local + SSE.

---

## 8. Gaps menores existentes (no bloquean diseño)

- Tab “Resumen” visible sin permiso `summary` en tabs (guard bloquea ruta)
- Close-check cajera no muestra `summary` numérico (solo mensajes de blockers)
- Detalle admin solo lista settlements **pagados**, no pendientes vivos

---

## 9. Plan de implementación frontend (futuro)

1. Agregar funciones en `adminCashSessions.js`
2. Permiso en guards + botones condicionados
3. Componente `AdminForceCloseCashSessionDialog.vue` (modal reusable)
4. Integrar en `index.vue` y `[id].vue`
5. Extender mappers/types si hay TS
6. Badges en history, reports, print
7. Manual QA según checklist del spec (13 pasos)

---

## 10. Conclusión

La shell de fiscalización es el lugar natural del feature. El dialog de blockers de Mi Caja y la página de cierre de turno son los mejores templates de UX. **No hay código previo** que contradiga el diseño; todo es additive con un permiso y endpoint backend nuevos.
