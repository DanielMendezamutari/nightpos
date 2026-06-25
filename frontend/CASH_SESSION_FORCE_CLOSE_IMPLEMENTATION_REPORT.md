# CASH_SESSION_FORCE_CLOSE_IMPLEMENTATION_REPORT.md (Frontend)

**Feature:** Cierre administrativo de caja  
**Fecha:** 2026-06-21  
**Estado:** Implementado

---

## Resumen

UI en Fiscalización de cajas para cerrar administrativamente sesiones `OPEN`, con modal de confirmación, preview de blockers y badges post-cierre.

---

## API client

`src/api/adminCashSessions.js`

- `fetchAdminCashSessionCloseCheck(id)`
- `forceCloseAdminCashSession(id, payload)`
- `FORCE_CLOSE_REASONS`, `forceCloseReasonLabel()`

---

## Componentes

| Archivo | Rol |
|---------|-----|
| `components/nightpos/finance/AdminForceCloseCashSessionDialog.vue` | Modal confirmación |
| `components/nightpos/finance/AdminForcedCloseSessionPanel.vue` | Panel metadata post-cierre |

---

## Pantallas

| Ruta | Cambios |
|------|---------|
| `finance/cash-sessions/index.vue` | Botón + modal en cajas abiertas |
| `finance/cash-sessions/[id].vue` | Botón header, panel, badge |
| `finance/cash-sessions/history.vue` | Chip “Cierre administrativo” |
| `print/cash-session/[id].vue` | Datos force-close al ticket |
| `components/nightpos/print/PrintableCashSessionReport.vue` | Sección CIERRE ADMINISTRATIVO |

Permiso UI: `admin.cash_sessions.force_close`

---

## Manual QA

Ver checklist en spec (14 pasos): cajera bloqueada → admin force-close → badge → cajera B abre caja → pendientes visibles en fiscalización e impresión.
