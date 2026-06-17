# CASH_MOVEMENT_REASONS_MANAGEMENT_REPORT.md (Frontend)

**Bugfix / UX:** Motivos de caja obligatorios pero no visibles  
**Fecha:** 2026-06-14  
**Estado:** Completado

---

## 1. Causa raíz

El cliente API en `src/api/cashMovementReasons.js` devolvía siempre un array vacío porque accedía a `response.data.cash_movement_reasons` sin usar `unwrapNightPosResponse()`. La API NightPOS responde:

```json
{ "success": true, "data": { "cash_movement_reasons": [ ... ] } }
```

---

## 2. Rutas y navegación

| Ruta Vue | Nombre de ruta | Pantalla |
|----------|----------------|----------|
| `/nightpos/settings/cash-reasons` | `nightpos-settings-cash-reasons` | Configuración → Motivos de caja |

Equivalente al path sugerido `settings/cash-movement-reasons` (convención del proyecto: `cash-reasons`).

### Menú lateral (`navigation/vertical/nightpos-r4.js`)

```
Configuración
  └── Motivos de caja   [permission: settings.cash_reasons]
```

Visible para admin, superadmin con contexto operativo y cajera (listar).  
Oculto para garzón, limpieza y chica (sin permiso → filtrado por CASL).

Tabs de sección Configuración: `useSettingsSectionTabs.js` incluye "Motivos de caja".

---

## 3. API cliente (`src/api/cashMovementReasons.js`)

| Función | Endpoint | Uso |
|---------|----------|-----|
| `fetchCashMovementReasons` | `GET /cash-movement-reasons` | Pantalla Configuración |
| `fetchCashMovementReasonsForCash` | `GET /cash/movement-reasons` | Mi Caja (`cash.access`) |
| `createCashMovementReason` | `POST /cash-movement-reasons` | Crear (requiere `manage`) |
| `updateCashMovementReason` | `PUT /cash-movement-reasons/{id}` | Editar / activar-desactivar |

Todas las funciones usan `unwrapNightPosResponse()`.

---

## 4. Pantalla Configuración (`settings/cash-reasons/index.vue`)

- **Meta:** `permission: settings.cash_reasons`
- Listado con filtro por tipo (`INCOME`, `EXPENSE`, `BOTH`)
- Crear y editar nombre, tipo y estado (`active` / `inactive`)
- Formulario de edición solo si `can('settings.cash_reasons.manage')`
- Alerta informativa para usuarios de solo lectura (cajera básica)

---

## 5. Mi Caja (`cash/index.vue`)

Al registrar ingreso o egreso manual:

| Comportamiento | Detalle |
|----------------|---------|
| Carga motivos | `fetchCashMovementReasonsForCash({ active_only: true })` |
| Filtro por movimiento | INCOME → motivos `INCOME` + `BOTH`; EXPENSE → `EXPENSE` + `BOTH` |
| Sin motivos | Alerta: *"No hay motivos configurados… Pide al administrador crear motivos en **Configuración → Motivos de caja**."* |
| Con permiso `manage` | Botones **Crear motivo** (inline) y **Gestionar motivos** (link a settings) |
| Validación | Motivo sigue siendo obligatorio (`rules` en `VSelect`) |

Al cambiar tipo de movimiento se limpia el motivo seleccionado.

---

## 6. Permisos en UI

| Permiso | Efecto |
|---------|--------|
| `settings.cash_reasons` | Ver menú Configuración → Motivos de caja; listar en settings |
| `settings.cash_reasons.manage` | Crear/editar en settings; botones en Mi Caja |
| `cash.access` | Listar motivos operativos en Mi Caja |

---

## 7. Build

```bash
cd frontend
pnpm run build
```

Publicar `frontend/dist/` en el servidor según checklist de despliegue.

---

## 8. QA manual sugerido

1. Admin → Configuración → Motivos de caja: ver lista sembrada, crear motivo BOTH.
2. Cajera PIN → Mi Caja → Ingreso: selector con motivos INCOME/BOTH activos.
3. Cajera → Egreso: solo EXPENSE/BOTH.
4. Desactivar un motivo en settings → no aparece en Mi Caja.
5. Cajera básica: no ve botones Crear/Gestionar; garzón no ve ítem en menú Configuración.

---

## 9. Referencias

- Backend: `backend/CASH_MOVEMENT_REASONS_MANAGEMENT_REPORT.md`
- Fase C3 UI original: `PHASE_C3_REPORT.md` (frontend)
- Mapa V1: `NIGHTPOS_V1_DEVELOPMENT_MAP.md`
