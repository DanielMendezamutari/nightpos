# PHASE_15_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Fase:** 15 — Manillas, piezas y shows  
**Fecha:** 2026-06-04  
**Referencias:** `DOMAIN_DESIGN.md` (§3.13), `DATABASE_GUIDELINES.md`, `SHIFT_REPORTING_MODE_REPORT.md`

---

## 1. Tablas creadas

| Tabla | Descripción |
| ----- | ----------- |
| `bracelets` | Manillas por chica, cantidad y precio |
| `room_services` | Piezas / habitaciones |
| `shows` | Shows por tipo (`PRIVATE`, `STAGE`, `SPECIAL`) |

Migraciones: `100017_create_girl_income_services_tables`, `100018_assign_girl_income_permissions_to_roles`.

Todas incluyen: `tenant_id`, `branch_id`, `official_shift_id`, `girl_user_id`, `waiter_user_id` (nullable), montos, `registered_by_user_id`, `registered_at`, `notes`, timestamps.

---

## 2. Reglas implementadas

| # | Regla |
| - | ----- |
| 1–4 | Tenant, sucursal, turno (`EnsureOperationalShiftUseCase`) y chica obligatoria (`GirlStaffValidator`, `staff_role=GIRL`) |
| 5–6 | Registros inmutables; no se recalculan liquidaciones históricas |
| 7 | Auditoría: quién registró y cuándo |
| 8–9 | Cajera y admin con permisos `*.create`; garzón solo con permiso explícito |
| 10 | Campo `settlement_source_type` en API (`GIRL_BRACELET`, `GIRL_ROOM`, `GIRL_SHOW`) para liquidaciones futuras |

**Manillas:** `total_amount = unit_price × quantity` (persistido, no recalculado).

**Piezas / shows:** `total_amount = unit_price` (un registro = un servicio).

---

## 3. Endpoints creados

| Método | Ruta | Permiso |
| ------ | ---- | ------- |
| GET | `/api/v1/bracelets` | `bracelets.access` |
| POST | `/api/v1/bracelets` | `bracelets.create` |
| GET | `/api/v1/bracelets/{id}` | `bracelets.access` |
| GET | `/api/v1/room-services` | `room_services.access` |
| POST | `/api/v1/room-services` | `room_services.create` |
| GET | `/api/v1/room-services/{id}` | `room_services.access` |
| GET | `/api/v1/shows` | `shows.access` |
| POST | `/api/v1/shows` | `shows.create` |
| GET | `/api/v1/shows/{id}` | `shows.access` |

Listados devuelven `shift`, `summary` e `items` del turno OPEN actual.

---

## 4. Capas

- **Domain:** `GirlIncome` — repositorios, excepciones.
- **Application:** use cases create/list/get, `GirlStaffValidator`, `GirlIncomeMapper`.
- **Infrastructure:** modelos Eloquent, repositorios, controllers, FormRequests.

---

## 5. Permisos y seeder

Slugs: `bracelets.access|create`, `room_services.access|create`, `shows.access|create`.

Asignados a `tenant_owner`, `cashier` y `super_admin` (vía migración + `NightPosSeeder`).

Usuario demo chica: `chica.centro` / PIN `9012`.

---

## 6. Tests

`tests/Feature/Api/V1/GirlIncomePhase15Test.php` — 9 casos (registro, listado, chica obligatoria, permisos, tenant, cajera).

**Suite:** 112 tests OK.

---

## 7. Preparación para liquidaciones

- Tipos alineados con `staff_settlement_items.source_type`: `GIRL_BRACELET`, `GIRL_ROOM`, `GIRL_SHOW`.
- `GenerateCurrentShiftSettlementsUseCase` **no** incluye aún piezas/shows manuales (solo `GIRL_BRACELET` desde ventas CON_ACOMPANANTE).
- Próximo paso: extender generador para leer `bracelets`, `room_services`, `shows` del turno sin alterar ítems ya pagados.

---

## 8. Validación manual

```bash
cd backend
php artisan migrate
php artisan test --filter=GirlIncomePhase15
```

1. Login `admin.demo` / `AdminDemo123!`, sucursal CENTRO.
2. `POST /api/v1/bracelets` con `girl_user_id` de `chica.centro`.
3. `GET /api/v1/bracelets` → resumen y turno.
4. Repetir `room-services` y `shows`.

---

## 9. Próxima fase recomendada

**Fase 16 — Liquidaciones v2:** incorporar manillas/piezas/shows registrados en `staff_settlement_items`, reportes por fuente y cierre de turno con totales reales.

Detener implementación hasta nuevas instrucciones.
