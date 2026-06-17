# Liquidaciones parciales / múltiples cortes — Implementación (Frontend)

**Fecha:** 2026-06-16  
**Estado:** Completado

---

## Resumen

La UI de liquidaciones ahora muestra **varias filas por persona** (cortes), no una sola fila que se sobrescribe.

Ejemplo:

| Chica | Corte | Total | Estado |
|-------|-------|-------|--------|
| María | Corte #1 | 150 Bs | Pagado |
| María | Corte #2 | 80 Bs | Pendiente |

---

## Cambios

### Tablas de turno actual

- `girls.vue` — columnas: Corte, Generado, Pagado
- `waiters.vue` — idem
- `cleaning.vue` — idem

Campos API usados: `cut_label`, `created_at`, `paid_at`, `status`.

### Detalle

- `[id].vue` — subtítulo incluye `cut_label`.

### Historial

- `history.vue` — columna **Corte** (`cut_label`).

### Sin cambios de API client

Los campos `cut_number` / `cut_label` vienen del backend en respuestas existentes (`current-shift`, `history`, `show`).

---

## Validación manual

1. Consumo + pieza para María → Generar → Pagar corte #1.
2. Nuevos consumos mismo turno → Generar.
3. Verificar **Corte #2 PENDING** en Chicas.
4. Pagar corte #2.
5. Confirmar corte #1 sigue PAID sin cambios.
6. Confirmar dos egresos en caja (pagos separados).

---

## Archivos

- `src/pages/nightpos/settlements/girls.vue`
- `src/pages/nightpos/settlements/waiters.vue`
- `src/pages/nightpos/settlements/cleaning.vue`
- `src/pages/nightpos/settlements/history.vue`
- `src/pages/nightpos/settlements/[id].vue`
