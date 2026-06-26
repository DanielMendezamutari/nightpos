# Hosting — ERR_CONNECTION_RESET — Fix Report (Backend)

**Fecha:** 2026-06-25  
**Prioridad:** P0 — estabilizar `https://nightpos.ribersoft.com` antes de SAAS-2 o nuevas features.  
**Síntoma:** Chrome `ERR_CONNECTION_RESET` en homepage y en `/api/v1/auth/login-context/tenants` (sin pasar por Vue).  
**Evidencia hosting:** load average ~24, procesos `lsphp` (LiteSpeed + CloudLinux).

---

## 1. Diagnóstico (respuestas esperadas en hosting)

Ejecutar en este orden y anotar resultado:

| Check | Comando / URL | Si falla → causa probable |
|-------|---------------|---------------------------|
| PHP puro | `https://nightpos.ribersoft.com/health.php` | LiteSpeed/CloudLinux mata PHP (CPU/mem/entry processes), no Laravel |
| Laravel bootstrap | `php artisan about` | Fatal en bootstrap, `.env`, autoload o provider |
| Rutas auth | `php artisan route:list --path=login-context` | Mismo que arriba |
| Migraciones | `php artisan migrate --force` | Tablas SAAS-1.5 parciales o índice largo |
| API mínima | `GET /api/v1/health` | Bootstrap Laravel o DB caída |
| Login context | `GET /api/v1/auth/login-context/tenants` | Auth layer (poco probable si health OK) |
| Control Center | `GET /api/v1/admin/platform/operations/dashboard` | Consultas pesadas SAAS-1.5 (solo superadmin) |

### Interpretación rápida

- **`health.php` resetea** → problema de hosting (LVE, LSAPI killed, mod_security, timeout). Revisar cPanel → Metrics → Errors y `error_log` del dominio.
- **`health.php` OK, Laravel CLI falla** → código/config/migración en repo desplegado.
- **`/api/v1/health` OK, login-context resetea** → revisar `ListLoginContextTenantsUseCase` / DB tenants (debería ser query simple).
- **Todo resetea con load ~24** → saturación de workers PHP; reducir tráfico concurrente y aplicar fixes de abajo antes de abrir Control Center.

---

## 2. Causas probables (prioridad)

1. **Saturación LiteSpeed/CloudLinux** — load ~24 con muchos `lsphp` concurrentes; el servidor cierra sockets antes de responder (reset, no 502 JSON).
2. **Migración SAAS-1.5 incompleta** — índice único con nombre > 64 chars en MySQL (`tenant_operation_checklist_items_tenant_id_branch_id_item_key_unique`).
3. **Consultas pesadas Control Center** — dashboard analiza todos los tenants con agregados sobre sales/orders/audit/events (solo afecta rutas `/admin/platform/operations/*`, no login-context).
4. **Migración parcial** — tablas `tenant_technical_profiles` / `tenant_operation_checklist_items` creadas pero migración no registrada en `migrations`.

`login-context/tenants` **no** usa Control Center: solo `TenantRepository::listAll()` + filtro activo/suscripción.

---

## 3. Fixes aplicados en repo

### 3.1 Migración SAAS-1.5 — índices cortos + idempotente

Archivo: `database/migrations/2026_06_25_150000_saas_15_platform_operations.php`

- `tenant_technical_profiles`: unique `tenant_tech_profile_uq` en `(tenant_id, branch_id)`
- `tenant_operation_checklist_items`: unique `tenant_chk_item_unique` en `(tenant_id, branch_id, item_key)`
- `hasTable` / `hasColumn` para re-ejecutar sin error si quedó a medias

**Si en hosting quedó parcial:**

```sql
-- Solo si migrate falló a medias y la fila NO está en migrations:
DROP TABLE IF EXISTS tenant_operation_checklist_items;
DROP TABLE IF EXISTS tenant_technical_profiles;
```

Luego:

```bash
php artisan migrate --force
```

### 3.2 Health checks

| Archivo | Propósito |
|---------|-----------|
| `public/health.php` | `PHP OK` sin boot Laravel |
| `app/Http/Controllers/Api/V1/HealthController.php` | `GET /api/v1/health` → `{ ok, time, version, db }` |
| `routes/api.php` | Ruta pública al inicio del grupo `v1` |
| `tests/Feature/Api/V1/HealthEndpointTest.php` | Test automatizado |

### 3.3 Índices de rendimiento

Archivo: `database/migrations/2026_06_25_160000_hosting_performance_indexes.php`

Índices con nombre corto (idempotente):

- `sales`: `(tenant_id, paid_at)`
- `orders`: `(tenant_id, updated_at)`, `(tenant_id, created_at)`
- `cash_sessions`: `(tenant_id, status, opened_at)`
- `official_shifts`: `(tenant_id, status, opened_at)`
- `print_devices`: `(tenant_id, enabled, last_seen_at)`
- `audit_logs`: `(tenant_id, created_at)`
- `operational_events`: `(tenant_id, created_at)`

### 3.4 SAAS-1.5 — menos carga por request

| Cambio | Archivo |
|--------|---------|
| Lookback 90 días en MAX/agregados | `PlatformOperationsMetricsReader.php` |
| Cache 60 s dashboard y lista tenants | `PlatformOperationsDashboardBuilder.php` |
| Config | `config/nightpos.php` → `NIGHTPOS_OPS_CACHE_SECONDS`, `NIGHTPOS_OPS_METRICS_LOOKBACK_DAYS` |

**Importante:** no abrir Control Center en producción hasta que `/api/v1/health` y login-context respondan estable con load normal.

---

## 4. Deploy en hosting (orden obligatorio)

```bash
cd ~/public_html/backend   # ajustar ruta real

php artisan optimize:clear
php artisan migrate --force
php artisan about
php artisan route:list --path=health
php artisan route:list --path=login-context
php artisan route:list --path=platform/operations
```

Variables recomendadas en `.env`:

```env
JWT_BLACKLIST_GRACE_PERIOD=30
NIGHTPOS_OPS_CACHE_SECONDS=60
NIGHTPOS_OPS_METRICS_LOOKBACK_DAYS=90
```

Probar en navegador (sin cache):

1. `/health.php`
2. `/api/v1/health`
3. `/api/v1/auth/login-context/tenants`

---

## 5. Logs a revisar (hosting)

Buscar en error_log / LiteSpeed / CloudLinux:

- `LSAPI`, `process killed`, `CPU limit`, `memory limit`, `entry processes`
- `mod_security`, `timeout`, `Premature end of script headers`
- `PHP Fatal error`, `Maximum execution time`
- Migración: `Identifier name ... is too long`

---

## 6. Verificación local (repo)

| Test | Resultado |
|------|-----------|
| `HealthEndpointTest` | ✅ PASS |
| `Saas15PlatformOperationsTest` (16) | ✅ PASS |
| `PlatformOperationsDashboardBuilder` cache | ✅ implementado |
| Índice `tenant_chk_item_unique` | ✅ en migración SAAS-1.5 |

---

## 7. Qué NO hacer hasta estabilizar

- No iniciar SAAS-2 Billing.
- No cargar Control Center con muchos tenants en horario pico si load > 8.
- No mezclar assets frontend viejos/nuevos (ver `frontend/HOSTING_CONNECTION_RESET_FIX_REPORT.md`).

---

## 8. Resultado esperado post-deploy

- Dominio responde sin `ERR_CONNECTION_RESET`.
- Login público lista tenants en `/api/v1/auth/login-context/tenants`.
- Control Center usable solo cuando load y health checks estén OK.

**Estado repo:** fixes listos para deploy. **Confirmación producción:** pendiente ejecutar checklist §4 en hosting y completar tabla §1 con resultados reales.
