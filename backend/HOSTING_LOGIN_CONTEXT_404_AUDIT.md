# Hosting — Login Context 404 Audit (Backend)

**Fecha:** 2026-06-25  
**Síntoma:** Navegador A login OK con contexto guardado; Navegador B en `/login` → `Request failed with status code 404` sin empresas/sucursales.  
**Alcance:** solo login-context y routing. Sin cambios de código en esta entrega.

---

## 1. Respuestas al diagnóstico (10 puntos)

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Qué URL exacta falla? | En navegador nuevo: **`GET /api/v1/auth/login-context/tenants`** (y también `/api/v1/auth/login-context/branches`). |
| 2 | ¿Por qué responde 404? | **El rewrite Opción A no está activo en hosting.** LiteSpeed devuelve **404 `text/html`** (página genérica), no JSON Laravel. |
| 3 | ¿Es cache viejo del frontend? | **Sí, contribuye.** Hay builds mezclados: algunos chunks usan `/backend/public/api/v1`, otros `/api/v1`. |
| 4 | ¿Es service worker? | **Posible en navegador A** si quedó SW de build PWA anterior; no explica el 404 de `/api/v1` en sí. |
| 5 | ¿Es API base incorrecta? | **Parcialmente.** La base **`/api/v1` es correcta según arquitectura oficial**, pero **hoy no funciona** en el servidor. **`/backend/public/api/v1` sí funciona.** |
| 6 | ¿Es `.htaccess`? | **Sí — causa principal.** Sin regla efectiva `^api/` → `backend/public/index.php`, `/api/v1/*` no llega a Laravel. |
| 7 | ¿Es tenant_slug inválido? | **No en el caso del navegador B inicial.** Ese flujo llama **tenants** (no depende de slug). Slug inválido afectaría **branches** → 404 JSON Laravel `"Empresa no encontrada."` (distinto del error actual). |
| 8 | ¿Backend debería devolver 422 en vez de 404? | **Mejora futura** para tenant inexistente en branches. **No aplica** al 404 HTML actual (routing). |
| 9 | ¿Qué cambios recomienda? | Ver §4 Plan de solución. |
| 10 | ¿Pasos manuales hosting/navegador? | Ver §5. |

---

## 2. Rutas backend (confirmado local)

```bash
php artisan route:list --path=login-context
```

| Método | URI | Acción |
|--------|-----|--------|
| GET | `api/v1/auth/login-context/tenants` | `AuthController@loginContextTenants` |
| GET | `api/v1/auth/login-context/branches` | `AuthController@loginContextBranches` |

Contrato branches: query **`tenant_slug`** requerido (`LoginContextBranchesRequest`).

Tests: `LoginContextSelectionTest.php` — tenant inexistente en branches → **404 JSON** `"Empresa no encontrada."` (Laravel, no HTML).

**Tenants nunca devuelve 404 de negocio** — solo 200 con lista (vacía o con datos).

---

## 3. Pruebas producción (2026-06-25, curl)

| URL | Status | Content-Type | Body |
|-----|--------|--------------|------|
| `/api/v1/auth/login-context/tenants` | **404** | **text/html** | HTML vacío/corto (no Laravel) |
| `/api/v1/auth/login-context/branches?tenant_slug=casa-demo` | **404** | **text/html** | HTML |
| `/backend/public/api/v1/auth/login-context/tenants` | **200** | **application/json** | `{ success, data.tenants }` |
| `/backend/public/api/v1/auth/login-context/branches?tenant_slug=casa-demo` | **200** | **application/json** | `{ success, data.branches }` |
| `/backend/public/api/v1/auth/login-context/branches?tenant_slug=C22` | **200** | JSON | sucursal `EL JEFE` code `1` |

Tenants en producción:

| slug | name |
|------|------|
| `casa-demo` | Casa Demo NightPOS |
| `C22` | CASA22 |

**Conclusión:** Laravel y datos están bien. Falla el **enrutamiento `/api/v1`** en la raíz del dominio.

---

## 4. Por qué un navegador funciona y otro no

### Flujo frontend (`login.vue` → `hydratePinStep`)

| Situación | Llamadas API al abrir `/login` |
|-----------|--------------------------------|
| **Sin cookies** tenant+branch (Navegador B) | `GET .../login-context/tenants` primero |
| **Con cookies** tenant+branch (Navegador A) | **Salta tenants** → solo `GET .../login-context/branches?tenant_slug={cookie}` |

Navegador A (contexto CASA22 / EL JEFE):

- Cookies: `tenantSlug=C22`, `branchCode=1` (slug real en BD, no `CASA22`)
- Si JS cacheado usa **`/backend/public/api/v1`** → branches **200** → pantalla PIN OK
- **No ejecuta** `loadTenants()` al inicio

Navegador B (limpio):

- Ejecuta **`loadTenants()`** → **`/api/v1/.../tenants`** → **404 HTML** → axios muestra *"Request failed with status code 404"*

### Señal de que el 404 es routing, no negocio

- `Content-Type: text/html` (no `application/json`)
- Mensaje axios genérico (no `"Empresa no encontrada."` del backend)
- Misma falla en **tenants** y **branches** vía `/api/v1`

---

## 5. Plan de solución (sin implementar aún)

### Fase 1 — Estabilizar ya (mínimo riesgo)

**Opción 1a — Temporal:** rebuild frontend con  
`VITE_API_BASE_URL=/backend/public/api/v1`  
hasta que `.htaccess` Opción A esté verificado.

**Opción 1b — Definitiva:** desplegar `.htaccess` raíz desde `frontend/public/.htaccess` y verificar:

```bash
curl -i https://nightpos.ribersoft.com/api/v1/auth/login-context/tenants
# Debe: HTTP 200, Content-Type: application/json
```

No pasar a `/api/v1` en frontend hasta que ese curl sea 200.

### Fase 2 — Deploy limpio

1. Borrar **todos** los assets viejos en raíz hosting (conservar `backend/`)
2. Un solo `npm run build` + copiar `dist/` completo
3. Verificar un solo hash en `index.html` (no mezclar `index-*.js` viejos)

### Fase 3 — Navegadores

En **cada** estación:

1. DevTools → Application → Service Workers → Unregister (si existe)
2. Clear site data
3. Hard reload / incógnito de prueba

### Fase 4 — Mejoras UX (después de routing OK)

- `classifyApiError`: 404 en login-context → *"No se pudo cargar empresas o sucursales..."*
- Backend: valorar **422** en tenant inexistente (branches) en lugar de 404

---

## 6. Pasos manuales hosting (checklist)

```bash
cd /home/vnplktsg/nightpos.ribersoft.com

# ¿Existe .htaccess con rewrite api?
head -30 .htaccess

# Debe contener:
# RewriteRule ^api/ backend/public/index.php [L,QSA]

# Smoke — DEBE ser JSON 200 antes de usar /api/v1 en frontend
curl -i https://nightpos.ribersoft.com/api/v1/auth/login-context/tenants
curl -i https://nightpos.ribersoft.com/api/v1/health
```

Si `/api/v1` sigue 404 HTML:

- Confirmar `mod_rewrite` activo (LiteSpeed suele heredarlo)
- Confirmar `.htaccess` no sobrescrito por cPanel
- Confirmar `AllowOverride All` en vhost (soporte hosting)

---

## 7. Variables backend

| Variable | Valor recomendado |
|----------|-------------------|
| `APP_URL` | `https://nightpos.ribersoft.com` |
| `JWT_SECRET` | configurado (`jwt:secret`) |

No afectan directamente al 404 de routing.

---

**Estado:** diagnóstico cerrado. **Bloqueo:** rewrite `/api/` en raíz hosting. **No requiere cambios Laravel** para el 404 HTML actual.
