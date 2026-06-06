# ROOM_SERVICE_NOTIFICATIONS_REPORT.md (Backend)

## 1. Notificación interna

1. Comando `room-services:check-due` busca piezas `ACTIVE` con `expected_ends_at <= now()` y `alert_sent_at` null.
2. Crea fila en `notifications` con `role_target=CLEANING`, `type=ROOM_SERVICE_DUE`.
3. Marca `alert_sent_at` en `room_services`.
4. API expone listado y conteo para la UI de limpieza.

La operación crítica **no depende de WhatsApp**.

## 2. Evitar duplicados

- Índice lógico: no crear si existe notificación UNREAD/READ para mismo `source_id` + `ROOM_SERVICE_DUE`.
- `alert_sent_at` evita reprocesar la misma pieza en el comando.

## 3. Ejecutar comando

```bash
cd backend
php artisan room-services:check-due
```

Salida: `Notificaciones creadas: N`

## 4. Cron en producción

```
* * * * * php /var/www/nightpos/backend/artisan room-services:check-due
```

## 5. WhatsApp preparado

| Env | Uso |
| --- | --- |
| `NIGHTPOS_WHATSAPP_ENABLED` | false por defecto |
| `NIGHTPOS_WHATSAPP_PROVIDER` | nombre proveedor |
| `NIGHTPOS_WHATSAPP_PHONE_CLEANING` | destino |
| `NIGHTPOS_WHATSAPP_TEMPLATE_ROOM_DUE` | plantilla |

`WhatsAppNotificationChannel` solo registra log hasta integrar API oficial.

## 6. Activar WhatsApp real (pendiente)

1. Contratar proveedor con API REST oficial.
2. Implementar envío en `WhatsAppNotificationChannel::send()`.
3. Activar `NIGHTPOS_WHATSAPP_ENABLED=true`.
4. Probar en staging — nunca scraping ni WhatsApp Web automatizado.
