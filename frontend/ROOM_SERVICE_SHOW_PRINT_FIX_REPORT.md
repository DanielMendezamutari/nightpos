# Room Service & Show Print Fix — Frontend (2026-06-21)

UI alineada con impresión automática de piezas y shows.

## Cambios

| Pantalla | Comportamiento |
|----------|----------------|
| `services/room-services/create.vue` | Tras registrar: mensaje + Ver ticket + Reimprimir |
| `services/shows/create.vue` | Igual para shows |
| Default % chica pieza | 60 (UI) |

## Fallback browser

| Ruta | Componente |
|------|------------|
| `print/room-service/:id` | `PrintableRoomServiceTicket.vue` |
| `print/show/:id` | `PrintableShowTicket.vue` |

## API

- `printRoomService(id, { reprint })` → `POST /room-services/{id}/print`
- `printShow(id, { reprint })` → `POST /shows/{id}/print`

## Mensajes

- Éxito con impresora: «Pieza registrada y ticket enviado a limpieza.»
- Sin impresora: toast warning desde `print_warning` del backend
- Reimpresión fallida: abre vista imprimible como fallback

```bash
npm run build
```
