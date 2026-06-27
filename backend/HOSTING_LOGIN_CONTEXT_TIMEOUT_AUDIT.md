# Hosting — Login Context Timeout Audit (Backend)

**Fecha:** 2026-06-25  
**Síntoma:** `/login` muestra *"El servidor tardó demasiado en responder"* al cargar empresas.  
**Endpoint:** `GET /api/v1/auth/login-context/tenants`  
**Alcance:** diagnóstico only — sin cambios de código en esta entrega.

---

## 1. Respuestas al diagnóstico (10 puntos)

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Cuánto tarda `/api/v1/health`? | **Producción (probe 2026-06-25):** ~600–700 ms → **connection reset** (sin body). **Local:** ~similar orden con bootstrap. |
| 2 | ¿Cuánto tarda login-context tenants? | **Producción:** reset ~700 ms (ahora) o **200 JSON ~200 ms** (probe anterior estable). **Local:** **117 ms** total. |
| 3 | ¿Cuántas queries? | **1 query** (`SELECT * FROM tenants ORDER BY name`). |
| 4 | ¿Query lenta? | **No.** ~27 ms SQL local. Tabla pequeña (2 tenants prod). |
| 5 | ¿Qué clase causa lentitud? | **Ninguna en código.** `ListLoginContextTenantsUseCase` → `EloquentTenantRepository::listAll()` — liviano. |
| 6 | ¿SAAS-1.5 involucrado? | **No.** PlatformOperations solo registrado en DI; `NightPosServiceProvider::boot()` vacío. No middleware global. |
| 7 | ¿Es hosting/LiteSpeed? | **Sí — causa principal en producción.** Resets intermitentes + cola PHP bajo load. |
| 8 | ¿Es frontend timeout? | **Contribuye.** Default **15 s** (`VITE_API_TIMEOUT_MS` no set en `.env.production`). Si PHP tarda >15 s en cola → mensaje timeout. |
| 9 | ¿Fix recomendado? | Ver §5. |
| 10 | ¿Qué implementar para login-context liviano? | Query SQL mínima con `WHERE status='active'` (optimización menor); **prioridad real = estabilizar hosting**. |

---

## 2. Pruebas curl producción

```text
=== Probe A (sesión anterior, servidor respondía) ===
/api/v1/auth/login-context/tenants          → 404 text/html
/backend/public/api/v1/.../tenants          → 200 application/json (~instant)

=== Probe B (2026-06-25, servidor inestable) ===
/api/v1/auth/login-context/tenants          → ERR connection reset ~708 ms
/api/v1/health                              → ERR connection reset ~599 ms
/backend/public/api/v1/.../tenants          → ERR connection reset ~608 ms
```

**Conclusión:** cuando el servidor responde, el endpoint es **rápido** (JSON inmediato). Cuando falla, **no es lentitud de query** sino **caída/reset del worker** o saturación LiteSpeed.

El mensaje frontend *"tardó demasiado"* aparece cuando axios agota **15 s** esperando respuesta (cola PHP) — distinto del reset rápido en curl, pero **misma raíz: hosting**.

---

## 3. Auditoría de código

### Ruta

| Método | URI | Middleware auth |
|--------|-----|---------------|
| GET | `api/v1/auth/login-context/tenants` | **Ninguno** (público) |
| GET | `api/v1/auth/login-context/branches` | **Ninguno** |

Confirmado: `php artisan route:list --path=login-context`

### Cadena de ejecución

```
AuthController::loginContextTenants()
  → ListLoginContextTenantsUseCase::execute()
      → TenantRepository::listAll()
          → TenantModel::query()->orderBy('name')->get()
      → array_filter(isActive && hasValidSubscription)  // PHP, no SQL extra
      → map id, name, slug
```

### Lo que **NO** ejecuta

- PlatformOperationsDashboardBuilder  
- PlatformOperationsMetricsReader  
- PlatformOperationsTenantAnalyzer  
- Health score  
- Plan usage calculator  
- print_jobs / audit_logs / operational_events  
- Relaciones Eloquent (`plan`, `branches`) — **no cargadas**

### TenantModel

- Sin global scopes  
- Sin `$appends`  
- Sin accessors pesados  

### Mejora menor (futuro, no urgente)

`listAll()` trae **todos** los tenants y filtra en PHP. Con 2–10 tenants irrelevante. Recomendado a futuro:

```sql
SELECT id, name, slug FROM tenants
WHERE status = 'active'
  AND (subscription_ends_at IS NULL OR subscription_ends_at >= NOW())
ORDER BY name
```

---

## 4. Benchmark local (HTTP kernel)

| Métrica | Valor |
|---------|-------|
| HTTP status | 200 |
| Tiempo total | **117 ms** |
| Queries | **1** |
| SQL tenants | **26.6 ms** |

**Esperado en hosting sano:** < 300 ms con pocos tenants.

---

## 5. Plan de solución recomendado

### P0 — Hosting (obligatorio)

1. Revisar load / entry processes CloudLinux  
2. `php artisan optimize:clear` — evitar config cache rota  
3. Confirmar `JWT_SECRET` + DB OK (`/api/v1/health` → json `"db":"up"`)  
4. Desplegar `.htaccess` Opción A **o** usar temporalmente `/backend/public/api/v1` en frontend hasta rewrite OK  
5. No abrir Control Center bajo carga (SAAS-1.5 dashboard es pesado — **otro endpoint**, pero compite por mismos workers PHP)

### P1 — Frontend (sin tocar aún)

1. `VITE_API_TIMEOUT_MS=30000` en `.env.production`  
2. Excluir `/auth/login-context/` de refresh proactivo JWT en interceptor (si hay cookie `accessToken` stale)  
3. Mensajes de error por tipo (timeout vs reset vs 404 HTML)

### P2 — Backend (opcional)

1. Query dedicada `listActiveForLogin()` con filtros SQL  
2. Log temporal `login_context.tenants` con ms + query count (solo debug hosting)

---

## 6. SQL tablas grandes (ejecutar en hosting)

```sql
SELECT COUNT(*) FROM tenants;
SELECT COUNT(*) FROM branches;
SELECT COUNT(*) FROM print_jobs;
SELECT COUNT(*) FROM audit_logs;
SELECT COUNT(*) FROM operational_events;
```

Login-context **solo toca `tenants`**. Conteos altos en otras tablas indican carga general del servidor, no este endpoint.

---

## 7. Health vs login-context

| Endpoint | Trabajo |
|----------|---------|
| `/api/v1/health` | PDO + `SELECT 1` + check JWT config |
| `/api/v1/auth/login-context/tenants` | 1× SELECT tenants |

Ambos deben ser livianos. Si ambos fallan/reset → **infra**, no lógica login-context.

---

**Estado:** código backend **no es la causa** de lentitud. **Bloqueo: estabilidad hosting** + URL API consistente + timeout frontend.
