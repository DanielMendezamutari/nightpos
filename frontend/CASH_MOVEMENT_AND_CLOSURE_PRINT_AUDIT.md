# Auditoría — Impresión movimientos de caja y cierres (frontend)

Fecha: 2026-06-21

## Resumen

El frontend tenía fallback navegador para arqueo de caja abierta (`print/cash.vue`) y reportes admin/historial (`print/cash-session/[id].vue`, `print/shift/[id].vue`), pero **no** integraba el pipeline de agente para movimientos ni cierres.

## Movimientos de caja

| Item | Estado previo |
|------|---------------|
| `CashMovementDialog.vue` mensaje print | ❌ Solo "Movimiento registrado" |
| Botones Ver / Reimprimir | ❌ No |
| `PrintableCashMovementTicket.vue` | ❌ No |
| Ruta `print/cash-movement/[id]` | ❌ No |
| API `printCashMovement` | ❌ No |

## Cierre de caja

| Item | Estado previo |
|------|---------------|
| `submitClose()` maneja `print_job` / `print_warning` | ❌ No |
| Mensaje éxito con impresora | ❌ Solo "Caja cerrada" |
| Botones Ver cierre / Reimprimir | ❌ No |
| Ruta imprimible para cajera post-cierre | ❌ Solo admin (`cash-session/[id]`) |

## Cierre de turno

| Item | Estado previo |
|------|---------------|
| Botón navegador | ✅ "Imprimir PDF" → `print/shift/[id]` |
| Impresión agente (`SHIFT_CLOSE`) | ❌ No |
| API `printShiftClosure` | ❌ No |
| Reimprimir cierre turno | ❌ No |

## Patrón de referencia ya implementado

Piezas y shows (`room-services/create.vue`, `shows/create.vue`):

- Mensajes según `print_job` / `print_warning`
- Ver ticket (browser)
- Reimprimir (POST + agente)
