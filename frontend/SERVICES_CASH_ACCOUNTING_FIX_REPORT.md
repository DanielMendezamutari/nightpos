# SERVICES_CASH_ACCOUNTING_FIX_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend  
**Corrección:** Gate de caja en servicios + método de pago  
**Fecha:** 2026-06-08

> **Bugfix 2026-06-08:** Ver `SERVICE_CASH_SESSION_RESOLUTION_FIX_REPORT.md` — `useServiceCashSession` leía `session.status` como `data.session.status` (siempre falso).

---

## 1. Composable

`src/composables/useServiceCashSession.js`

- Consulta `GET /api/v1/cash/session/current`
- Expone `hasOpenCashSession`, `loading`, `refreshCashSession`

---

## 2. Formularios de registro

Archivos actualizados:

- `src/pages/nightpos/services/bracelets/create.vue`
- `src/pages/nightpos/services/room-services/create.vue`
- `src/pages/nightpos/services/shows/create.vue`

**Sin caja abierta:**

- Alerta: *Debe abrir caja para registrar este servicio.*
- Botón **Abrir caja ahora** → `QuickOpenCashDialog`
- Submit deshabilitado hasta abrir caja

**Con caja abierta:**

- Campo obligatorio `payment_method` (métodos habilitados de sucursal)
- Pieza: preview `girl_amount` / `house_amount` por `girl_percent`

---

## 3. Flujo UX esperado

1. Cajera entra a registrar pieza
2. Si no hay caja → dialog rápido de apertura
3. Tras abrir → formulario habilitado
4. Registro exitoso → ingreso reflejado en consola de caja

---

## 4. Archivos relacionados

| Archivo | Rol |
| ------- | --- |
| `QuickOpenCashDialog` | Reutilizado desde módulo caja |
| `api/cash.js` | Sesión actual / apertura |
| Create forms servicios | Gate + `payment_method` |
