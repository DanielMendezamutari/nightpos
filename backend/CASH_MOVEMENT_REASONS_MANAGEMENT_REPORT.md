# CASH_MOVEMENT_REASONS_MANAGEMENT_REPORT.md (Backend)

**Bugfix / UX:** Motivos de caja obligatorios pero no visibles  
**Fecha:** 2026-06-14  
**Estado:** Completado

---

## 1. Problema detectado

En **Mi Caja**, el campo `cash_movement_reason_id` es obligatorio al registrar ingreso o egreso manual. El catálogo existía en BD y API, pero en producción el selector aparecía vacío porque el frontend leía `response.data.cash_movement_reasons` en lugar de desenrollar el envelope `{ success, data: { ... } }`.

---

## 2. Modelo de datos (existente — Fase C3)

Tabla `cash_movement_reasons`:

| Campo | Tipo | Notas |
|-------|------|-------|
| id | PK | |
| tenant_id | FK | Aislamiento multi-tenant |
| branch_id | FK nullable | Motivo global del tenant o por sucursal |
| name | string | |
| type | enum | `INCOME`, `EXPENSE`, `BOTH` |
| status | enum | `active`, `inactive` |
| timestamps | | |

**No implementado en V1:** columna `description` (el spec la menciona; se deja para ampliación futura). La desactivación reemplaza al DELETE físico.

Migración base: `2026_06_07_100029_phase_c3_master_data.php`.

---

## 3. API

### Gestión (Configuración)

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/v1/cash-movement-reasons` | `settings.cash_reasons` |
| POST | `/api/v1/cash-movement-reasons` | `settings.cash_reasons.manage` |
| PUT | `/api/v1/cash-movement-reasons/{id}` | `settings.cash_reasons.manage` |

Query params: `active_only`, `type` (`INCOME` / `EXPENSE` / `BOTH`).

### Operativo (Mi Caja)

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/v1/cash/movement-reasons` | `cash.access` |

Permite a la cajera listar motivos activos sin exponer el módulo de configuración completo.

### Uso en movimientos

`POST /api/v1/cash/movements` exige `cash_movement_reason_id`.  
`RegisterCashMovementUseCase` valida:

- Motivo activo del tenant/sucursal.
- Tipo compatible: `INCOME` acepta motivos `INCOME` o `BOTH`; `EXPENSE` acepta `EXPENSE` o `BOTH`.

---

## 4. Permisos

Se mantienen los slugs de Fase C3 (no se renombraron a `cash_movement_reasons.*`):

| Slug | Descripción | Roles |
|------|-------------|-------|
| `settings.cash_reasons` | Ver/listar motivos | `tenant_owner`, `cashier`, `cashier_senior` |
| `settings.cash_reasons.manage` | Crear/editar/activar | `tenant_owner`, `cashier_senior` |

**Cajera básica:** puede listar y usar motivos; no puede administrarlos.  
**Garzón / limpieza / chica:** sin permisos de configuración.

Migración de asignación: `2026_06_07_100030_assign_phase_c3_permissions.php` (actualizada: `cashier_senior` con `manage`).

---

## 5. Cambios aplicados en este bugfix

| Área | Cambio |
|------|--------|
| `RegisterCashMovementUseCase` | Acepta motivos tipo `BOTH` |
| `EloquentCashMovementReasonRepository` | Filtro `type` incluye `BOTH` cuando corresponde |
| `CreateCashMovementReasonRequest` | Valida `BOTH` como tipo válido |
| `routes/api.php` | Nuevo `GET /cash/movement-reasons` bajo `cash.access` |
| `NightPosSeeder` | Motivos demo ampliados (ver §6) |
| `SeedsNightPosFoundation` | `cashier_senior` recibe `settings.cash_reasons.manage` |

---

## 6. Seeder — motivos por tenant/sucursal

| Tipo | Nombres |
|------|---------|
| INCOME | Ingreso manual, Ajuste positivo, Otro ingreso |
| EXPENSE | Compra insumos, Pago taxi, Limpieza, Pago personal, Ajuste negativo, Otro egreso (+ legacy: Compra hielo, Compra comida, Multa) |
| BOTH | Corrección caja |

---

## 7. Tests

Archivo: `tests/Feature/Api/V1/CashMovementReasonsManagementTest.php`

| # | Caso |
|---|------|
| 1 | Admin lista motivos |
| 2 | Admin crea motivo INCOME |
| 3 | Admin crea motivo EXPENSE |
| 4 | Cajera lista motivos activos vía `/cash/movement-reasons` |
| 5 | Cajera sin `manage` no puede crear motivos |
| 6 | Movimiento INCOME solo acepta INCOME/BOTH |
| 7 | Movimiento EXPENSE solo acepta EXPENSE/BOTH |
| 8 | Motivo inactivo excluido del listado activo |
| 9 | Aislamiento por tenant |
| 10 | Aislamiento por sucursal (branch-scoped) |

Suite relacionada `PhaseC3Test` actualizada (nombre "Pago taxi").  
**Resultado:** 20 tests / 94 assertions — OK.

---

## 8. Despliegue

Tras pull:

```bash
cd backend
php artisan migrate --force
php artisan db:seed --class=NightPosSeeder   # solo entornos demo; prod según política
php artisan test --filter="CashMovementReasonsManagement|PhaseC3"
```

Frontend: rebuild y publicar `dist/` según `backend/database/pasos-para-modificaciones.md`.

---

## 9. Referencias

- Fase C3 original: `PHASE_C3_REPORT.md`
- Frontend: `frontend/CASH_MOVEMENT_REASONS_MANAGEMENT_REPORT.md`
- Mapa V1: `NIGHTPOS_V1_DEVELOPMENT_MAP.md` (sección BUGFIX Motivos de caja)
