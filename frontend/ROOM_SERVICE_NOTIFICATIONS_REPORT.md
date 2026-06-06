# ROOM_SERVICE_NOTIFICATIONS_REPORT.md (Frontend)

## 1. Pantalla

`/nightpos/services/room-control` — usuario `limpieza.demo` PIN `3333`.

## 2. Polling

Cada 30 s:

- `GET /api/v1/room-services/control`
- `GET /api/v1/notifications/unread-count`

## 3. Sonido

- Archivo: `public/sounds/room-due.mp3` (agregar manualmente).
- Una vez por pieza vencida nueva no revisada.
- No suena si `checked_at` o ya está en `localStorage` (`nightpos_room_due_seen_ids`).
- Botón **Silenciar** → `nightpos_room_due_silent=1`.

## 4. Flujo revisado

1. Pieza vence → comando o tiempo real de espera.
2. UI muestra alerta + badge + sonido (si no silenciado).
3. Usuario pulsa **Revisado** → `POST .../check` + marca ID en localStorage.
4. Misma pieza no vuelve a disparar sonido.

## 5. WhatsApp

No implementado en UI. Solo notificaciones in-app.
