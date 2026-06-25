# HOSTING_ADMIN_CASH_SESSION_PERMISSION_FIX_REPORT.md (Frontend)

**Bug:** Error de permiso y botón de cierre administrativo ausente en hosting  
**Fecha:** 2026-06-25  
**Estado:** Fix implementado

---

## Síntoma

En hosting, pantalla **Fiscalización → Cajas**:

- Error visible: `Permiso requerido: admin.cash_sessions.summary`
- Tarjetas de resumen no cargan
- Botón **Cierre administrativo** no aparece (aunque el código frontend ya lo condicionaba bien)

En desktop local funciona porque la BD tiene permisos y la sesión admin incluye los slugs.

---

## Causa raíz

### Backend / BD (principal)

Permisos no migrados o no asignados al rol `tenant_owner` en hosting. Ver `backend/HOSTING_ADMIN_CASH_SESSION_PERMISSION_FIX_REPORT.md`.

### Frontend (contribuyente al error visible)

`useAdminCashSessionsList.js` **siempre** llamaba `GET /admin/cash-sessions/summary` al montar la lista, incluso si el usuario no tenía `admin.cash_sessions.summary`. Eso disparaba el toast de permiso aunque el listado principal (`admin.cash_sessions.list`) sí estuviera autorizado.

El botón de force-close ya usaba el permiso correcto:

```js
can('admin.cash_sessions.force_close')
```

Si `/auth/me` no trae ese slug (sesión vieja o rol sin permiso en BD), el botón no se muestra — comportamiento esperado.

---

## Fix aplicado

### 1. Carga condicional del resumen

`src/composables/useAdminCashSessionsList.js`

- Importa `useNightPosPermissions`
- Expone `canLoadSummary` = `can('admin.cash_sessions.summary')`
- Solo llama `fetchAdminCashSessionsSummary()` cuando `canLoadSummary` es true
- Si no hay permiso, `summary = null` sin request fallido

### 2. UI lista — ocultar KPIs sin permiso

`src/pages/nightpos/finance/cash-sessions/index.vue`

- Fila de tarjetas de resumen envuelta en `v-if="canLoadSummary"`

### 3. Tab Resumen en navegación de sección

`src/composables/useCashSessionSectionTabs.js`

- Tab «Resumen» con `permission: 'admin.cash_sessions.summary'` (consistente con list/view)

---

## Permisos UI (referencia)

| Elemento | Permiso |
|----------|---------|
| Listado de cajas | `admin.cash_sessions.list` |
| Detalle sesión | `admin.cash_sessions.view` |
| Tarjetas KPI + tab Resumen | `admin.cash_sessions.summary` |
| Botón/modal cierre administrativo | `admin.cash_sessions.force_close` |

Archivos con force-close:

- `finance/cash-sessions/index.vue`
- `finance/cash-sessions/[id].vue`

---

## Validación en hosting

1. Ejecutar migraciones backend (ver reporte backend)
2. **Logout + login** admin
3. DevTools → Network → `GET /api/v1/auth/me` debe incluir:
   - `admin.cash_sessions.summary`
   - `admin.cash_sessions.force_close`
4. Abrir Fiscalización → Cajas:
   - Sin error de permiso en toast
   - KPIs visibles si tiene summary
   - Botón cierre administrativo en filas OPEN si tiene force_close

---

## Manual QA

| Paso | Esperado |
|------|----------|
| Admin con permisos completos | KPIs + botón force-close en caja OPEN |
| Rol sin `summary` pero con `list` | Lista OK, sin KPIs, sin error |
| Rol sin `force_close` | Sin botón administrativo |
| Tras editar rol en admin | Logout/login para ver cambios |

---

## Archivos tocados

- `src/composables/useAdminCashSessionsList.js`
- `src/pages/nightpos/finance/cash-sessions/index.vue`
- `src/composables/useCashSessionSectionTabs.js`
