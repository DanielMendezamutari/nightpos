# Cierre de caja y cierre de turno — Rediseño V1 (Frontend)

**Fecha:** 2026-06-21  
**Alcance:** Evolución de vistas imprimibles existentes (navegador 58/80 mm + PDF vía print).

## Componentes actualizados

| Componente | Audiencia | Cambio |
|------------|-----------|--------|
| `PrintableCashSessionReport.vue` | Cajera | Enfoque operativo: arqueo, liquidaciones pagadas con detalle, ajustes, pendientes |
| `PrintableShiftClosureReport.vue` | Admin | Resumen gerencial completo (sin copiar cierre de caja) |

## Páginas imprimibles

| Ruta | Fuente API |
|------|------------|
| `/nightpos/print/cash-session/[id]` | `GET /admin/cash-sessions/{id}` → `operational` |
| `/nightpos/print/my-cash-session/[id]` | `GET /cash/sessions/{id}` → `operational` |
| `/nightpos/print/cash` | `GET /cash/sessions/{id}` (desde sesión actual) |
| `/nightpos/print/shift/[id]` | `GET /shifts/{id}/summary` → `managerial` |

Se eliminaron consultas paralelas en browser (`fetchSettlementsReport`, `fetchProductReconciliation`) para cierre de caja: un solo payload del backend.

## Props nuevas

### PrintableCashSessionReport

- `operational` — bloques operativos del backend
- `tenantName`, `adminName` — cabecera

### PrintableShiftClosureReport

- `managerial` — resumen gerencial del turno
- `width` — 58 mm / 80 mm

## Separación UX

- **Cajera:** no muestra top productos ni ranking gerencial; solo lo necesario para rendir caja.
- **Admin:** no repite arqueo de caja individual; muestra resultado financiero del turno y KPIs.

## Branding

`PRINT_TICKET_FOOTER` desde `@/constants/printTicket` (env `VITE_PRINT_TICKET_FOOTER`).

## Compatibilidad

- `PrintableTicketShell` reutilizado
- Auto-print vía `useNightPosPrint`
- Ancho térmico query `?width=58`

## Bugfix timestamps (2026-06-25)

`PrintableCashSessionReport` usa `operational.general.opened_at` / `closed_at` como fuente principal (fallback a `data.openedAt` / `data.closedAt`). Símbolos Unicode reemplazados por ASCII en movimientos (`-` en lugar de `·`).
