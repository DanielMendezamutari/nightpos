# CLEANING_MOBILE_MODE_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend  
**Feature:** Modo limpieza móvil  
**Fecha:** 2026-06-08

---

## 1. Ruta

`/nightpos/cleaning` — nombre typed-router: `nightpos-cleaning`

- `layout: blank` (sin sidebar administrativo)
- UI móvil tipo garzón

---

## 2. Redirección login

`src/utils/resolveHomeRoute.js`

| Rol staff | Home |
| --------- | ---- |
| `WAITER` | `/nightpos/waiter` |
| `CLEANING` | `/nightpos/cleaning` |
| `CASHIER` | consola turno / caja |
| Admin | dashboard |

`src/plugins/1.router/guards.js` — sandbox limpieza: solo rutas bajo `/nightpos/cleaning`.

---

## 3. Pantalla principal

`src/pages/nightpos/cleaning/index.vue`

**KPI cards:** activas, tiempo cumplido, en limpieza, terminadas hoy

**Cards por pieza:**

- Habitación, hora inicio, fin estimado, tiempo restante, estado
- **Tocar puerta / Revisado** → `POST .../check`
- **Finalizar servicio** → `POST .../finish` (si permiso)
- **Marcar limpia** → `POST .../mark-clean` (habitación en `CLEANING`)

---

## 4. Componentes y estilos

| Archivo | Descripción |
| ------- | ----------- |
| `components/nightpos/cleaning/CleaningMobileHeader.vue` | Header fijo móvil |
| `assets/styles/cleaning-mobile.scss` | Cards grandes, touch-friendly |
| `api/cleaning.js` | Cliente API `/api/v1/cleaning/*` |

---

## 5. Restricciones UX

Limpieza **no** ve: caja, ventas, liquidaciones, administración.

Solo modo operativo de habitaciones/piezas.

---

## 6. Validación dev

1. Login PIN **3333**
2. Debe entrar a `/nightpos/cleaning`
3. Ver piezas activas/vencidas
4. Finalizar servicio → habitación en limpieza
5. Marcar limpia → `AVAILABLE`
