# PHASE_17_FRONTEND_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend  
**Fase:** 17 — Servicios y control de piezas  
**Fecha:** 2026-06-02

> **Nota:** Formularios de pieza y show no incluyen garzón. Solo manillas pueden tener garzón opcional.

> **Actualización 2026-06-08:** Ver `SERVICES_CASH_ACCOUNTING_FIX_REPORT.md` (caja obligatoria) y `CLEANING_MOBILE_MODE_REPORT.md` (ruta `/nightpos/cleaning`, redirect rol CLEANING).

---

## 1. Rutas Servicios

| Subpestaña | Ruta | Permiso |
| ---------- | ---- | ------- |
| Manillas | `/nightpos/services/bracelets` | `bracelets.access` |
| Piezas | `/nightpos/services/room-services` | `room_services.access` |
| Shows | `/nightpos/services/shows` | `shows.access` |
| Control piezas | `/nightpos/services/room-control` | `room_services.cleaning_view` |

---

## 2. Manillas

Sin habitación ni duración. Subtítulo aclara que es consumo asignado a chica.

---

## 3. Piezas

Formulario: chica, habitación, duración, monto total, porcentaje chica (default 50%), cálculo en vivo chica/casa, hora inicio opcional, notas. **Sin garzón.** No se editan montos chica/casa manualmente.

Listado: estado, tiempo restante, fin estimado, botón **Terminar**.

---

## Shows

Formulario: chica, tipo, precio, hora opcional, notas. **Sin garzón.**

---

## 4. Control de piezas (limpieza)

- Cards: activas, vencidas, terminadas hoy
- Tabla vencidas destacada (alerta roja)
- Botones **Revisado** / **Terminar**
- Badge piezas vencidas
- Polling cada **30 s**
- Sonido: `/sounds/room-due.mp3` (fallback beep si falta archivo)
- **Silenciar** → `localStorage`
- IDs revisados en `localStorage` para no repetir sonido

---

## 5. Sonido

Colocar archivo MP3 en `frontend/public/sounds/room-due.mp3`. Sin archivo, se usa beep del navegador.

---

## 6. API cliente

- `api/roomServices.js` — control, due, finish, check
- `api/notifications.js` — listado y unread-count

---

## 7. Validación dev (`pnpm run dev`)

1. Login admin → registrar manilla, pieza (total + % chica, duración corta), show
2. Ver que cada listado es independiente
3. Login limpieza PIN `3333` → Control de piezas
4. Esperar vencimiento o `php artisan room-services:check-due`
5. Ver badge y escuchar alerta (una vez)
6. Marcar revisado → no debe repetir sonido
7. Terminar pieza → generar liquidaciones → pieza como `GIRL_ROOM`

---

## 8. Próxima fase

WebSockets para alertas en tiempo real (opcional).
