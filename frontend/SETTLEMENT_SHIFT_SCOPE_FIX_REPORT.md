# SETTLEMENT_SHIFT_SCOPE_FIX_REPORT.md (Frontend)

**Bugfix crítico:** liquidaciones mezclando turnos  
**Fecha:** 2026-06-15  
**Estado:** Completado

---

## 1. Cambios UI — Liquidaciones

**Ruta:** `/nightpos/settlements`

### Banner turno actual

Muestra:

- ID turno
- Tipo (Día/Noche)
- Fecha de negocio
- Sucursal (#branch_id)
- Caja y cajera (desde `context` API)

### Sin liquidaciones

Alerta: *"No hay liquidaciones para este turno"* con conteo de fuentes (`sources_summary`).

### Sin turno abierto

Alerta informativa — no muestra datos de turnos cerrados.

### Historial

Sin cambios de ruta: `/nightpos/settlements/history` con filtro `official_shift_id` para consultas pasadas.

---

## 2. Composables

`useCurrentShiftSettlements.js` expone:

- `context` — tenant, branch, shift, caja, cajera
- `sourcesSummary` — ventas, manillas, piezas, etc.

---

## 3. QA manual

1. Turno A: venta → generar liquidaciones → cerrar turno.
2. Turno B: nueva cajera, caja en 0, sin ventas.
3. Liquidaciones debe mostrar turno B vacío.
4. Historial debe listar turno A.

---

## 4. Referencias

- Backend: `backend/SETTLEMENT_SHIFT_SCOPE_FIX_REPORT.md`
