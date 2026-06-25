# Fix — Impresión movimientos de caja y cierres (frontend)

Fecha: 2026-06-21

## API (`src/api/`)

- `cash.js`: `registerCashMovement` devuelve payload completo; `fetchCashMovement`, `fetchCashSession`, `printCashMovement`, `printCashClose`
- `shifts.js`: `printShiftClosure`

## Componentes / rutas

| Archivo | Rol |
|---------|-----|
| `PrintableCashMovementTicket.vue` | Ticket browser movimiento |
| `print/cash-movement/[id].vue` | Ruta imprimible movimiento |
| `print/my-cash-session/[id].vue` | Cierre caja cajera (`cash.access`) |
| `CashMovementDialog.vue` | Mensajes print + Ver/Reimprimir post-registro |
| `cash/index.vue` | Cierre caja con print + alert post-cierre |
| `shifts/close.vue` | Imprimir/Reimprimir cierre turno (agente) + vista navegador |

## UX

### Movimiento

- Éxito: "Movimiento registrado y comprobante enviado a impresora."
- Warning: "Movimiento registrado, pero no se pudo imprimir."
- Botones: Ver comprobante, Reimprimir comprobante

### Cierre caja

- Éxito: "Caja cerrada y comprobante enviado a impresora."
- Warning: "Caja cerrada, pero no se pudo imprimir."
- Alert persistente con Ver cierre / Reimprimir cierre

### Cierre turno

- Tras cerrar: alert con Imprimir cierre, Reimprimir cierre, Vista imprimible, Ir al historial
- Header: Vista imprimible (navegador) + botones térmicos post-cierre

## Pipeline

Mismo que piezas/shows: `useNightPosPrint` + POST print endpoints + fallback `Printable*.vue`
