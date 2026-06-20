# AUDITORÍA — Liquidaciones y permisos cajera (Backend)

**Fecha:** 2026-06-16  
**Estado:** Diagnóstico — **sin cambios de código**  
**Alcance:** API settlements, scope, permisos, JWT, queries SQL  
**Problemas reportados:** (1) cajera genera pero UI dice vacío, (2) cajera no ve/paga liquidaciones, (3) permisos nuevos no surten efecto

---

## 1. Conclusión ejecutiva

| # | Veredicto | Causa raíz (evidencia) |
|---|-----------|------------------------|
| **1** | **Scope read ≠ scope write** | `GenerateCurrentShiftSettlementsUseCase` escanea **todo el turno** (`generateForShift`) pero `GetCurrentShiftSettlementsUseCase` filtra por **`cash_session_id`** cuando `scope = my_cash_session`. Liquidaciones con `cash_session_id = NULL` (manillas, piezas, shows) **no son visibles** para cajera básica. |
| **2** | **Mismo desalineamiento + `empty_overview`** | Cajera recibe `waiters/girls/cleaning: []` y `summary` en cero aunque existan filas en BD. Admin usa `scope = shift` sin filtro de caja. **Pagar** (`mark-paid`) no está bloqueado por permiso; falla porque **no hay filas en la UI** (o ID no listado). |
| **3** | **Permisos en sesión congelados** | Permisos van en cookie `userData` al login. `POST /auth/refresh` **solo devuelve token**, no permisos. Cambios en rol requieren **logout/login** o `GET /auth/me` (no se invoca al arrancar la app). |

**No es un bug de middleware `settlements.*` en demo:** el seeder asigna los 5 slugs de liquidaciones a `cashier`. El bloqueo es **alcance operativo**, no falta de permiso API.

---

## 2. Parte 1 — Comparación ADMIN vs CAJERA por endpoint

### 2.1 Tabla de rutas y permiso middleware

| Ruta | Método | Middleware permiso | Use case |
|------|--------|-------------------|----------|
| `/settlements/current-shift` | GET | `settlements.access` | `GetCurrentShiftSettlementsUseCase` |
| `/settlements/current-shift/pending-sources` | GET | `settlements.pending_sources` | `GetSettlementPendingSourcesUseCase` |
| `/settlements/generate-current-shift` | POST | `settlements.generate` | `GenerateCurrentShiftSettlementsUseCase` |
| `/settlements/{id}/mark-paid` | POST | `settlements.pay` | `MarkSettlementPaidUseCase` |
| `/settlements/history` | GET | `settlements.history` | `ListSettlementHistoryUseCase` |
| `/cash/session/current/close-check` | GET | `cash.access` | `GetCashSessionCloseCheckUseCase` |

Fuente: `backend/routes/api.php` líneas 342–373.

### 2.2 ¿El backend devuelve lo mismo?

**No.** Misma sucursal/turno, distinto **scope** y payloads.

| Campo / aspecto | Admin (`tenant_owner`) | Cajera básica (`cashier`) |
|-----------------|------------------------|---------------------------|
| `data.context.scope` | `"shift"` | `"my_cash_session"` |
| `data.context.cash_session_id` | puede ser null o ID propio | ID de su caja abierta |
| `data.context.empty_overview` | `false` (salvo edge) | `true` si sin actividad en **su caja** y sin pending/unsettled **filtrados por caja** |
| `data.waiters / girls / cleaning` | Todas las del turno | Solo `cash_session_id = su_caja` **o** limpieza con `cash_session_id IS NULL` |
| `data.summary.total_*` | Agregado turno completo | **Forzado a `0.00`** si `empty_overview = true` |
| `data.settlement_summary` | Filtrado turno | Filtrado por `cash_session_id` cuando scope cajera |
| Generate `created_items` | Cuenta ítems nuevos en **todo el turno** | Idem (misma función `generateForShift`) |
| Generate mensaje si `created_items = 0` | "No hay liquidaciones nuevas…" | Idem, aunque admin ya haya generado para el turno |

**Evidencia en tests** (`SettlementShiftScopeTest.php`):

```php
// Cajera tras rotación de turno
expect($cashierView->json('data.context.scope'))->toBe('my_cash_session')
    ->and($cashierView->json('data.summary.total_pending'))->toBe('0.00');

// Admin mismo escenario
expect($adminView->json('data.context.scope'))->toBe('shift');
```

Test `stale auto shift with settlements shows zero for new cashier after rotation` (líneas 262–298): admin genera liquidaciones en turno A; cajera en turno B ve `empty_overview: true` y totales en cero — **comportamiento esperado por diseño actual**, no error HTTP.

### 2.3 Ejemplo de divergencia JSON (escenario típico reportado)

**Precondiciones:** Admin generó liquidaciones del turno. Manillas/piezas/shows creadas con `cash_session_id = NULL`. Cajera tiene caja abierta en el mismo turno.

| Response path | Admin | Cajera |
|---------------|-------|--------|
| HTTP status | 200 | 200 |
| `context.scope` | `shift` | `my_cash_session` |
| `summary.total_girls` | `"150.00"` | `"0.00"` |
| `waiters.length` | ≥ 1 | 0 |
| `settlement_summary.generated_pending_count` | ≥ 1 | 0 (si settlements sin su `cash_session_id`) |

Admin y cajera **no reciben el mismo scope**; el middleware de permisos **sí** deja pasar a ambos.

---

## 3. Parte 2 — `SettlementShiftScopeResolver`

Archivo: `backend/app/Application/StaffSettlement/Services/SettlementShiftScopeResolver.php`

### 3.1 Reglas de decisión

| Input | Efecto |
|-------|--------|
| `tenant_id`, `branch_id` | Siempre del contexto HTTP |
| `userId` | Rota turno stale vía `EnsureOperationalShiftUseCase` |
| `cash.access` + rol `cashier`/`CASHIER` **sin** `admin.cash_sessions.view` | **Fuerza** `SCOPE_MY_CASH_SESSION` |
| `admin.cash_sessions.view` o super_admin | Puede usar `SCOPE_SHIFT` (turno OPEN sucursal) |
| Caja cerrada (cajera) | `shift_id: null`, `empty_overview: true` |
| Caja abierta | `shift_id = cashSession.officialShiftId` |
| `empty_overview` | `true` si **no** hay actividad en caja **y** `pendingCount + unsettledCount = 0` **con filtro cash_session** |

### 3.2 ¿Admin y cajera usan el mismo resolver?

**Sí, misma clase.** Cambia el **resultado** por permiso `admin.cash_sessions.view`:

```php
// isMyCashSessionOperator() — líneas 152-164
return $this->staffContext->roleSlug() === 'cashier'
    || $this->staffContext->staffRole() === 'CASHIER';
// AND has cash.access, AND NOT admin.cash_sessions.view
```

| Rol demo | Scope efectivo |
|----------|----------------|
| `tenant_owner` | `shift` |
| `cashier_senior` | `shift` (tiene `admin.cash_sessions.view`) |
| `cashier` | `my_cash_session` |

---

## 4. Parte 3 — Permisos (Policy, middleware, seeder)

### 4.1 No hay Laravel Policy/Gate para settlements

Control vía:
- Middleware `nightpos.permission:{slug}` en rutas
- Checks inline en use cases (`hasPermission(...)`)
- `SettlementAccessPolicy` — **no bloquea cajera**; amplía visión si tiene `settlements.generate|pay|history`

```php
// SettlementAccessPolicy.php — scopedStaffUserId()
if ($this->staff->hasPermission('settlements.generate')
    || $this->staff->hasPermission('settlements.pay')
    || $this->staff->hasPermission('settlements.history')) {
    return null; // ver todo el turno (staff_user_id)
}
```

**Nota:** En `GetCurrentShiftSettlementsUseCase`, cuando `scope === my_cash_session`, `resolveStaffScopeUserId` **retorna null** de todas formas (líneas 173–176). El filtro real es **`cash_session_id`**, no staff user.

### 4.2 Permisos settlements en seeder demo

`SeedsNightPosFoundation.php`:

| Rol | settlements.access | .generate | .pay | .history | .pending_sources | admin.cash_sessions.view |
|-----|:---:|:---:|:---:|:---:|:---:|:---:|
| tenant_owner | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| cashier_senior | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| **cashier** | ✓ | ✓ | ✓ | ✓ | ✓ | **✗** |
| waiter | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| girl | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |

**Conclusión permisos API:** la cajera demo **no le falta** ningún slug de liquidaciones. Agregar permisos extra (p. ej. `reports.access`) **no cambia** el scope si no incluye `admin.cash_sessions.view`.

### 4.3 Drift provisioner vs demo

`TenantDefaultRolePermissions.php`:
- No define rol `cashier_senior`
- `waiter`/`girl` **sin** `settlements.access` (demo seeder sí se lo da a waiter/girl)

Tenants creados por wizard ≠ datos demo en matriz de roles.

---

## 5. Parte 4 — JWT y caché de permisos

| Mecanismo | Comportamiento |
|-----------|----------------|
| Login | `AuthResponseMapper` incluye `permissions[]` del rol en respuesta |
| JWT | Token de acceso; permisos **no** van en claims JWT para checks — se cargan en request vía `OperationalContextBootstrapper` desde BD |
| `POST /auth/refresh` | Solo `{ token, token_type }` — **no actualiza permisos** |
| `GET /auth/me` | Recarga usuario + permisos desde BD |
| Sesión frontend | Cookie `userData` con array `permissions` |

**Implicación problema 3:** editar permisos del rol en admin **no se refleja** hasta nuevo login o llamada explícita a `/auth/me`.

---

## 6. Parte 6 — Endpoint generar liquidaciones

Archivo: `GenerateCurrentShiftSettlementsUseCase.php`

### 6.1 Flujo cajera

1. Resuelve `shiftId` desde **caja abierta** (`cashSession.officialShiftId`) si es cajera básica.
2. Llama `generateForShift($tenant, $branch, $shiftId)` — **sin filtrar fuentes por cash_session**.
3. Construye `operational` con `scope = my_cash_session` y `cashSessionId` para el **resumen**, no para generación.

### 6.2 Por qué `created_items = 0` con liquidaciones existentes

- Admin (o cajera anterior) ya generó ítems para el turno → `sourceAlreadySettled` / `canAddItemsToSettlement` → **0 líneas nuevas**.
- Mensaje backend: *"No hay liquidaciones nuevas para generar en este turno/caja."*
- **No implica** que no existan liquidaciones en BD — solo que no hubo ítems nuevos en esta llamada.

### 6.3 Por qué `created_items > 0` pero cajera no ve nada

`generateForShift` crea settlements con `cash_session_id`:

| Tipo fuente | cash_session_id al crear |
|-------------|--------------------------|
| Comisiones garzón / consumo chica (venta) | De `sales.cash_session_id` |
| Manillas, piezas, shows | **`NULL`** (líneas 290–384 repository) |
| Limpieza | **`NULL`** |

Filtro lectura cajera (`applyCashSessionSettlementScope`):

```php
$inner->where('cash_session_id', $cashSessionId)
    ->orWhere(function ($cleaning) {
        $cleaning->whereNull('cash_session_id')->where('staff_role', 'CLEANING');
    });
```

**GIRL/WAITER con `cash_session_id = NULL` quedan excluidos** para cajera básica.

---

## 7. Parte 7 — Endpoint pago (`mark-paid`)

Archivo: `MarkSettlementPaidUseCase.php`

| Check | Admin | Cajera |
|-------|-------|--------|
| `settlements.pay` | ✓ middleware | ✓ middleware |
| Caja abierta propia | Requerida | Requerida |
| Settlement pertenece a su `cash_session` | **No validado** | **No validado** |
| Settlement visible en listado | Sí (scope shift) | Solo si pasa filtro caja |

**Conclusión:** admin puede pagar porque **ve** los IDs en overview. Cajera no paga porque **listado vacío**, no porque API rechace el permiso (salvo caja cerrada → `SettlementCashSessionRequiredException`).

---

## 8. Parte 8 — Consistencia de criterio entre operaciones

| Operación | Turno usado | Filtro cash_session en datos |
|-----------|-------------|------------------------------|
| **GET current-shift** | `scopeResolver` → caja o sucursal | **Sí** si `my_cash_session` |
| **GET pending-sources** | Idem | **Sí** en queries de fuentes |
| **POST generate** | Turno caja (cajera) o `ensureOperationalShift` (admin) | **No** en generación; **Sí** en `settlement_summary` del response |
| **POST mark-paid** | N/A (por ID) | No en autorización; asigna `cash_session_id` al pagar |
| **GET close-check** | `session.officialShiftId` | **Sí** (`build(..., $session->id)`) |

**Desalineación principal:** generate escanea turno completo; read filtra por caja. Close-check y pending **sí** alinean con caja.

---

## 9. Parte 9 — Evidencia de logs / tests

| Fuente | Evidencia |
|--------|-----------|
| `SettlementShiftScopeTest.php` | 7 tests documentan divergencia cashier/admin scope |
| `MarkSettlementPaidUseCase` | `Log::warning('mark-paid: caja abierta no resuelta...')` si no hay caja |
| `SETTLEMENT_SHIFT_SCOPE_DIAGNOSTIC_REPORT.md` | Caso producción mabel/turno stale (parcialmente superseded por resolver actual) |

**Queries clave lectura cajera:**

```sql
SELECT * FROM staff_settlements
WHERE tenant_id = ? AND branch_id = ? AND official_shift_id = ?
  AND (
    cash_session_id = :cash_session_id
    OR (cash_session_id IS NULL AND staff_role = 'CLEANING')
  )
```

---

## 10. Recomendaciones (solo diagnóstico — no implementadas)

1. **Alinear generate con read:** filtrar fuentes por `cash_session_id` al generar como cajera, **o** asignar siempre `cash_session_id` al crear settlements (manillas/piezas/shows).
2. **Ampliar `applyCashSessionSettlementScope`** si negocio exige ver GIRL/WAITER sin caja asignada.
3. **Unificar mensaje generate:** distinguir "ya generado en turno" vs "nada que generar en tu caja".
4. **Permisos UI:** documentar que cambios de rol requieren re-login; opcionalmente refrescar permisos en `/auth/me` al bootstrap.
5. **Cajera senior:** asignar `admin.cash_sessions.view` solo si debe ver turno completo (comportamiento actual del resolver).

---

## 11. Archivos revisados

| Archivo | Rol en auditoría |
|---------|------------------|
| `SettlementShiftScopeResolver.php` | Scope cajera vs admin |
| `GetCurrentShiftSettlementsUseCase.php` | empty_overview, listado vacío |
| `GenerateCurrentShiftSettlementsUseCase.php` | Desalineación generate/read |
| `GetSettlementPendingSourcesUseCase.php` | Pending filtrado por caja |
| `MarkSettlementPaidUseCase.php` | Pago sin check de scope |
| `GetCashSessionCloseCheckUseCase.php` | Criterio caja |
| `EloquentStaffSettlementRepository.php` | generateForShift, applyCashSessionSettlementScope |
| `SettlementOperationalContextBuilder.php` | settlement_summary scoped |
| `SettlementAccessPolicy.php` | Visión por staff (secundario vs cash_session) |
| `SeedsNightPosFoundation.php` | Permisos demo por rol |
| `TenantDefaultRolePermissions.php` | Drift wizard |
| `routes/api.php` | Middleware settlements |
| `SettlementShiftScopeTest.php` | Evidencia automatizada |
