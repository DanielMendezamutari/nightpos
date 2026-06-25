# Room Service & Show Print Fix — Backend (2026-06-21)

Corrección operativa: piezas y shows generan ticket térmico automático para limpieza/control.

## Problema

P1 creó `room_services.order_id` y comanda OPEN, pero **no** encolaba impresión. Shows tampoco imprimían. En operación real el personal de limpieza depende del papel.

## Solución

Pipeline existente (`print_jobs` + agente + `PrintTicketContentBuilder`). Sin segundo pipeline.

| Tipo | Enum | Disparador |
|------|------|------------|
| Pieza | `ROOM_SERVICE` | Auto al registrar pieza |
| Show | `SHOW_TICKET` | Auto al registrar show |

## Use cases

| Archivo | Rol |
|---------|-----|
| `CreateRoomServicePrintJobUseCase` | Job pieza + content |
| `CreateShowPrintJobUseCase` | Job show + content |
| `PrintRoomServiceUseCase` | Reimpresión manual `POST /room-services/{id}/print` |
| `PrintShowUseCase` | Reimpresión manual `POST /shows/{id}/print` |

Hooks en `CreateRoomServiceUseCase` y `CreateShowUseCase` (post-commit, no revierte registro).

Respuesta API: `print_job`, `print_warning` (sin impresora activa).

## Ticket pieza (`buildRoomService`)

- PIEZA, habitación/pieza, chica, inicio, duración
- Total, chica X%, casa, limpieza si aplica
- Estado ACTIVA, registrado por, observaciones
- Sin NIT/QR/fiscal

## Ticket show (`buildShowTicket`)

- SHOW, tipo, chica, hora, total
- Chica 100% / Casa 0 (liquidación show existente)
- Estado REGISTRADO

## Split 60/40 pieza

- Default operativo: `config('nightpos.room_service.default_girl_percent')` = **60**
- Snapshot en DB: `girl_percent`, `gross_girl_amount`, `girl_amount`, `house_amount`, `cash_session_id`
- Liquidación `GIRL_ROOM` usa `girl_amount` (neto tras limpieza)

## Tests

`tests/Feature/Api/V1/RoomServiceShowPrintFixTest.php` — 13 tests ✅

```bash
php artisan test tests/Feature/Api/V1/RoomServiceShowPrintFixTest.php
```

## QA manual

1. Cajera abre caja
2. Registra pieza → ticket ROOM_SERVICE en agente
3. Registra show → ticket SHOW_TICKET
4. Sin impresora → registro OK + `print_warning`
5. Liquidaciones tras finalizar pieza → 60% chica
