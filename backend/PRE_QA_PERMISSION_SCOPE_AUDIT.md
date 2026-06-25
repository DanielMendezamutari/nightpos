# PRE_QA_PERMISSION_SCOPE_AUDIT.md (Backend)

**Producto:** NightPOS V1  
**Editor:** Ribersoft  
**Fecha:** 2026-06-25  
**Alcance:** Permisos, scopes operativos, filtros de comandas garzón

---

## Resumen ejecutivo

Auditoría pre-QA de tres incidentes reportados. La **Parte 1** tenía causa raíz en backend (filtro de turno opcional). Las **Partes 2 y 3** son principalmente frontend/menú; el backend de permisos está alineado con seeders.

| Parte | Problema | Causa raíz | Fix backend |
|-------|----------|------------|-------------|
| 1 | Garzón ve comandas antiguas | Sin filtro obligatorio por `official_shift_id` | ✅ Aplicado |
| 2 | Alta de chicas no en menú | Permiso existe; no hay ruta/menú garzón | N/A (frontend) |
| 3 | Cajera «Más» estático | Catálogo hardcodeado frontend | N/A (frontend) |

---

## PARTE 1 — Garzón ve comandas que ya no debería ver

### Endpoints auditados

| Endpoint | Use case | Scope |
|----------|----------|-------|
| `GET /api/v1/waiter/orders?scope=` | `ListWaiterOrdersUseCase` | Garzón (propio) |
| `GET /api/v1/waiter/dashboard` | `GetWaiterDashboardUseCase` | KPIs garzón |
| `GET /api/v1/waiter/my-tables` | `GetWaiterMyTablesUseCase` | Mesas |
| `GET /api/v1/orders?scope=` | `ListOrdersUseCase` | Admin/cajera (no garzón) |

El garzón **no** usa `GET /orders`. Usa la API dedicada `/waiter/orders`.

### ¿Qué estados veía el garzón?

Scope `active` (default) incluía:

| Estado | ¿Incluido antes? | ¿Incluido después? |
|--------|------------------|---------------------|
| OPEN | Sí | Sí |
| SENT_TO_BAR | Sí | Sí |
| IN_PREPARATION | Sí (scope duplicado) | **No** (alineado a `operational_active`) |
| READY | Sí (scope duplicado) | **No** |
| BILLED | No | No |
| CANCELLED | No (excluido en query) | No |
| CLOSED | No | No |

**ROOM_SERVICE** no es un `status` de comanda — las comandas de pieza usan flujo `room_service` como origen pero status normal OPEN/SENT_TO_BAR/BILLED.

### Causa raíz (primaria)

`ListWaiterOrdersUseCase` y `GetWaiterDashboardUseCase` usaban:

```php
$shiftId = $this->shifts->findOpenForBranch(...)?->id;
// Si null → NO filtra por official_shift_id
```

Cuando no había turno abierto al momento de la consulta, el garzón veía **todas sus comandas activas de todos los turnos históricos**.

La cajera ya tenía guard equivalente en `ListOrdersUseCase` con `cashier_scope=1` → lista vacía si no hay turno.

Documentado previamente en `ORDERS_COMPLETE_AUDIT.md` línea 97.

### Causa raíz (secundaria)

`OrderRepository::findActiveByServiceTable()` **no filtraba por turno**. Una comanda OPEN/SENT_TO_BAR de un turno cerrado podía:

1. Marcar mesa como OCCUPIED en turno nuevo
2. Reabrirse al tocar la mesa

Asimetría: `GetWaiterMyTablesUseCase` ya llamaba `EnsureOperationalShiftUseCase`; el listado de comandas no.

### Fix aplicado (arquitectura existente)

| Archivo | Cambio |
|---------|--------|
| `ListWaiterOrdersUseCase.php` | `EnsureOperationalShiftUseCase` + filtro obligatorio `$shift->id` |
| `GetWaiterDashboardUseCase.php` | Idem |
| `GetWaiterMyTablesUseCase.php` | Pasa `$shift->id` a `findActiveByServiceTable` |
| `OpenWaiterTableUseCase.php` | Idem |
| `OrderRepositoryInterface` + `EloquentOrderRepository` | Parámetro opcional `?int $officialShiftId` en `findActiveByServiceTable` |
| Scope `active` | Usa `OrderListScopeResolver::OPERATIONAL_ACTIVE` (OPEN + SENT_TO_BAR) |

**Test:** `PhaseC4WaiterTest` → `waiter active scope excludes orders from previous closed shifts` — **PASS**.

### Respuestas directas del brief

| Pregunta | Respuesta |
|----------|-----------|
| ¿Otro turno? | Sí — comandas de turnos cerrados aparecían si no había filtro de shift |
| ¿BILLED? | No en listado garzón |
| ¿CANCELLED? | No (whereNotIn) |
| ¿CLOSED? | No como status order |
| ¿Históricas? | Sí — cuando `$shiftId === null` |

### Regla operativa resultante

El garzón ve solo comandas **propias** (`waiter_user_id`) del **turno oficial actual** (`EnsureOperationalShiftUseCase`) en estados **operativos** (OPEN, SENT_TO_BAR).

Separación UI (frontend): scopes `open`, `sent_to_bar`, `pending_charge`, `active` vía query param — ver informe frontend.

---

## PARTE 2 — Garzón permiso «Alta de chicas»

### Permiso canonical

| Campo | Valor |
|-------|-------|
| Slug | `staff.quick_create_girl` |
| Nombre en catálogo | **Alta rápida de chica** |
| Seeder | `SeedsNightPosFoundation.php`, rol `waiter` |
| API | `POST /staff/quick-girls` middleware `staff.quick_create_girl` |

**No existe** slug `alta_chicas` ni permiso de menú separado.

### Backend — verificación

| Check | Resultado |
|-------|-----------|
| Permiso en BD (seeder) | ✅ Asignado a waiter demo |
| `/auth/me` incluye permissions[] | ✅ |
| Middleware API | ✅ |
| Drift seeder vs `ManageablePermissionCatalog` | ✅ Alineado |

### Conclusión backend

El permiso **llega correctamente** al frontend tras login. La superficie UI correcta es **inline en el flujo de comanda** (Con compañía → «+ Nueva chica»), no un menú garzón separado. El tab «Más» garzón fue descartado por decisión UX — ver `frontend/PRE_QA_PERMISSION_SCOPE_AUDIT.md`.

---

## PARTE 3 — Cajera menú «Más»

### Backend

Los permisos evaluados en «Más» son slugs estándar del RBAC (`settlements.access`, `products.list`, etc.). No hay endpoint específico de menú.

Permisos **no** en catálogo admin de roles (no asignables vía UI de roles):

- `settings.printers` / `settings.printers.manage`
- `printing.reprint`
- `settings.checklist`

Estos existen en seeders pero faltaban en `ManageablePermissionCatalog` — considerar ampliación en fase posterior.

### Permisos stale tras editar rol

`POST /auth/refresh` devuelve **solo token**, no permissions. Si admin cambia permisos de rol, usuario activo conserva array en cookie hasta re-login o `GET /auth/me`.

**Mitigación frontend:** `fetchMe()` al montar pantallas «Más» (aplicado en frontend).

---

## Archivos clave

| Área | Path |
|------|------|
| Scope resolver | `app/Application/Order/Support/OrderListScopeResolver.php` |
| Lista garzón | `app/Application/Waiter/UseCases/ListWaiterOrdersUseCase.php` |
| Dashboard garzón | `app/Application/Waiter/UseCases/GetWaiterDashboardUseCase.php` |
| Turno operativo | `app/Application/Shift/UseCases/EnsureOperationalShiftUseCase.php` |
| Repo orders | `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentOrderRepository.php` |
| Permisos catálogo | `app/Application/Role/Support/ManageablePermissionCatalog.php` |
| Tests | `tests/Feature/Api/V1/PhaseC4WaiterTest.php` |

---

## No modificado (por diseño)

- Reglas de comanda (`SendOrderToBarUseCase`, cobro, CBA)
- Liquidaciones, caja, SSE
- `GET /orders` scopes cajera (`cashier_chargeable`, `cashier_scope=1`)

---

**Ribersoft — NightPOS V1**  
*Auditoría pre-QA. Base para QA integral V1.*
