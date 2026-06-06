# SHIFT_REPORTING_MODE_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend  
**Cambio:** UI alineada a turnos de reporte (no bloquean operación)  
**Fecha:** 2026-06-02  
**Referencias:** `backend/SHIFT_REPORTING_MODE_REPORT.md`, `PHASE_13_FRONTEND_REPORT.md`

---

## 1. Comportamiento esperado

El personal operativo **no** debe ver mensajes del tipo «abrir turno primero». El turno en navbar y en Turnos → Actual refleja la clasificación automática del backend (`auto_created` en API).

La página **Abrir turno** (`/nightpos/shifts/open`) permanece para administradores con `shifts.open` (apertura manual opcional).

---

## 2. Archivos tocados

| Archivo | Cambio |
| ------- | ------ |
| `pages/nightpos/shifts/current.vue` | Alerta informativa si no hay turno; chip «Auto» si `shift.auto_created` |
| `pages/nightpos/settlements/index.vue` | Texto: turno se crea al operar o con «Generar turno actual» |
| `components/nightpos/NightPosNavbarContext.vue` | Chip turno con sufijo `(auto)` cuando aplica |

---

## 3. API

Sin cambios de rutas. `fetchCurrentShift()` devuelve `auto_created` (boolean) desde `ShiftMapper`.

---

## 4. Validación en dev

```bash
cd frontend && pnpm run dev
```

1. Login cajera PIN `1234` (sin menú Abrir turno si no tiene permiso).
2. Caja → abrir → comanda → cobrar (sin abrir turno manual).
3. Navbar: `Día · fecha (auto)` o `Noche · fecha (auto)` según hora.
4. Turnos → Actual: chip «Auto» y datos del turno.
5. Liquidaciones: mensaje suave si aún no hay turno; operar o generar.

---

## 5. Sin cambiar (por diseño)

- Entrada de menú «Abrir turno» en `nightpos-r4.js` (solo visible con `shifts.open`).
- Dashboard: tarjeta «Turno actual» usa API; si no hay OPEN muestra «Sin turno abierto» hasta la primera operación que cree el turno.

---

## 6. Pendiente UI

- Enlace dashboard → `/nightpos/shifts/current`.
- Filtro `current_shift` en listados comandas/ventas.
- Subtítulo dashboard más explícito («Se asigna al operar») si se desea.
