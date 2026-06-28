# PWA / Desktop / Hosting — Regresión (Auditoría Backend)

**Fecha:** 2026-06-27  
**Dominio:** `https://nightpos.ribersoft.com`  
**Alcance:** diagnóstico raíz — **sin implementación**  
**Restricción:** no tocar POS, caja, liquidaciones, impresión operativa

---

## 1. Resumen ejecutivo

### Causa raíz más probable (orden de impacto)

| # | Factor | Peso | Evidencia |
|---|--------|------|-----------|
| 1 | **Hosting LiteSpeed / CloudLinux inestable** | **Alto** | Probe 2026-06-25 y 2026-06-27: **connection reset** en `/`, `/backend/public/health.php`, legacy API y `/api/v1/*` — **sin depender de `.htaccess` raíz** |
| 2 | **Cambio de arquitectura API + deploy incompleto** | **Alto** | Estado estable usaba `VITE_API_BASE_URL=/backend/public/api/v1`; post-PWA/SaaS se migró a `/api/v1` + `.htaccess` raíz que **puede no estar activo** en hosting → 404 HTML |
| 3 | **Deploy frontend mezclado + residuos PWA en hosting** | **Medio** | Commits con `sw.js`, `registerSW.js`, `workbox-*`, `mockServiceWorker.js` en `dist/`; navegador viejo con SW/cookies vs nuevo con `/api/v1` |
| 4 | **PWA activa (periodo Jun 2026)** | **Medio (agravante)** | SW scope `/` interceptaba requests; `navigateFallback` + precache; `sw.js` servido como HTML si fallback SPA |
| 5 | **JWT/config backend** | **Bajo-medio (paralelo)** | `JWT_SECRET` vacío causó login PIN roto — distinto de reset pero confundió diagnóstico |

**Conclusión:** no fue **solo** PWA ni **solo** `.htaccess`. Es **combinación**: hosting inestable + migración `/backend/public/api/v1` → `/api/v1` + deploy parcial + ventana PWA activa.

---

## 2. Línea de tiempo (git)

| Commit / evento | Qué cambió | Estado producción |
|-----------------|------------|-------------------|
| `a7f2578` NightPOS v1 Beta | `VITE_API_BASE_URL=/backend/public/api/v1` | **Funcionaba** — API vía path físico Laravel |
| `794ba65` plataforma SaaS | `vite-plugin-pwa`, manifests, iconos, InstallPwaBanner | PWA implementada |
| `794ba65` + builds | `dist/sw.js`, `registerSW.js`, `workbox-*` | SW registrado en visitas |
| `aff2e55` fix del hosting | Docs connection reset | Diagnóstico hosting |
| `ba2fc8f` fix sin pwa | `VITE_PWA_ENABLED=false`, unregister SW, strip HTML | Rollback **código** PWA |
| Post-ba2fc8f | `.env.production` → `/api/v1` | **Cambio arquitectura** — requiere `.htaccess` raíz |
| `83e72ba`, `507d7a3` | `.htaccess` raíz mínimo | Opción A documentada |
| Repo actual `dist/` | Sin `sw.js`; `index.html` sin `registerSW` | Build PWA-off correcto en repo |

**Gap crítico:** rollback PWA en **código** no garantiza limpieza en **hosting** (archivos SW viejos, SW registrado en browsers, assets viejos).

---

## 3. Respuestas a las 18 preguntas

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Qué cambios PWA rompieron producción? | `vite-plugin-pwa` + SW scope `/` + precache + `registerSW.js` en build; `mockServiceWorker.js` en `dist/`; fallback SPA devolviendo HTML en `/sw.js` |
| 2 | ¿SW registrado en producción hoy? | **Repo:** no (`VITE_PWA_ENABLED=false`, sin `sw.js` en dist actual). **Hosting:** **desconocido** — puede quedar SW viejo + registro en browser |
| 3 | ¿sw.js/registerSW/workbox viejos en hosting? | **Muy probable** si no se ejecutó `rm -f sw.js registerSW.js workbox-*.js` tras rollback |
| 4 | ¿mockServiceWorker.js en producción? | Estuvo en commits PWA; MSW no arranca con `VITE_USE_MSW=false`, pero **el archivo no debe estar** en docroot |
| 5 | ¿API interceptada por index.html? | **Sí, si** falta rewrite `^api/` **o** SPA fallback sin excluir `/api/` **o** SW viejo con navigateFallback |
| 6 | ¿Frontend usa /api/v1 o legacy? | **Repo actual:** `/api/v1`. **Estado estable anterior:** `/backend/public/api/v1` |
| 7 | ¿Agente misma URL que frontend? | **Docs:** `https://nightpos.ribersoft.com/api/v1`. **config.json local dev:** `nightpos.test` — producción depende config en PC |
| 8 | ¿`.htaccess` raíz correcto? | **En repo:** sí (mínimo, probado lógicamente). **En hosting:** **no confirmado** — probes fallan antes de validar |
| 9 | ¿`backend/public/.htaccess` correcto? | **Sí** — Laravel estándar; no usar en raíz |
| 10 | ¿Document root correcto? | Asumido `/home/vnplktsg/nightpos.ribersoft.com/` — **verificar en cPanel** que no apunte a subcarpeta |
| 11 | ¿Assets mezclados? | **Sí en repo git:** decenas de `index-*.js` huérfanos en `dist/assets/`; mismo riesgo en hosting con deploy parcial |
| 12 | ¿PWA cacheando chunks viejos? | **Durante periodo PWA:** sí (precache). **Hoy repo PWA-off:** riesgo solo si SW legacy sigue activo en browser |
| 13 | ¿Por qué navegador viejo OK y nuevo no? | Viejo: cookies tenant/branch → **no llama tenants**; cache/SW/URL legacy. Nuevo: **siempre** `GET /api/v1/auth/login-context/tenants` → falla si rewrite roto |
| 14 | ¿Sin `.htaccess` sigue reset? | **Sí** (probe externo) → **hosting**, no solo rewrite |
| 15 | ¿Problema real hosting o deploy? | **Ambos** — hosting impide probar; deploy/architecture explica 404 HTML y agente con HTML |
| 16 | ¿Qué revertir? | Ver §5 — no revertir SaaS backend; revertir **estrategia URL** temporalmente + limpiar PWA en hosting |
| 17 | ¿PWA pausado hasta V1.1? | **Sí** — ya en repo; mantener |
| 18 | ¿Solución segura hoy? | Estabilizar hosting → deploy limpio → legacy API URL **o** htaccess validado por etapas |

---

## 4. Backend — archivos revisados

| Archivo | Hallazgo |
|---------|----------|
| `bootstrap/app.php` | `apiPrefix: 'api'` → rutas `/api/v1/...` |
| `routes/api.php` | `health`, `login-context/tenants`, `login-context/branches` públicos |
| `backend/public/.htaccess` | Front controller OK — **no modificar** |
| `backend/public/health.php` | Diagnóstico PHP puro — reset en probe = servidor caído |
| `.env.example` | `APP_URL=https://nightpos.ribersoft.com` (sin `/backend/public`) |
| JWT | `JWT_SECRET` vacío → error PIN (fix aparte, no PWA) |

### Login-context backend

- **1 query**, ~117 ms local (auditoría previa)
- SAAS-1.5 **no** en ruta
- No es causa de lentitud; sí de 404 si URL/routing mal

---

## 5. Pruebas obligatorias — resultados probe 2026-06-27

Todas devolvieron **`curl: (35) Connection reset`** (~300 ms):

| URL | Resultado |
|-----|-----------|
| `/` | reset |
| `/login` | reset |
| `/backend/public/health.php` | reset |
| `/backend/public/api/v1/auth/login-context/tenants` | reset |
| `/api/v1/health` | reset |
| `/sw.js` | reset |

**Implicación Etapa 1:** no se puede validar `.htaccess` hasta que `health.php` responda estable.

---

## 6. Recomendación — sin programar aún

### Opción A — Estabilizar rápido (recomendada)

1. Escalar hosting: entry processes, PHP memory, error_log LiteSpeed
2. Confirmar `health.php` → `PHP OK` 10 veces seguidas
3. En hosting: `rm -f sw.js registerSW.js workbox-*.js mockServiceWorker.js`
4. Deploy **limpio** `dist/*` (borrar raíz excepto `backend/`)
5. **Temporal:** `VITE_API_BASE_URL=/backend/public/api/v1` + rebuild — **sin depender** de rewrite `/api/`
6. Agente: `backend_url=.../backend/public/api/v1`
7. Navegadores: unregister SW + clear site data
8. **PWA off** hasta V1.1 (ya en repo)

### Opción B — Solución definitiva (después de A)

1. Aplicar `.htaccess` raíz por etapas (`frontend/CPANEL_HTACCESS_DEPLOY_FIX_REPORT.md`)
2. Smoke: `/api/v1/health`, tenants, `/login` reload
3. Unificar frontend + agente en `/api/v1`
4. Mantener PWA off hasta 48 h estables

---

## 7. Qué NO revertir

- SaaS Control Center / backend operativo
- Fix JWT (`jwt:secret`)
- Fix login-context P1 (timeout, mensajes)
- Agente Go / impresión

---

## 8. Relacionados

- `frontend/PWA_DESKTOP_HOSTING_REGRESSION_AUDIT.md`
- `agent/PWA_DESKTOP_HOSTING_REGRESSION_AUDIT.md`
- `frontend/PWA_HOSTING_ROLLBACK_FIX_REPORT.md`
- `backend/HOSTING_DEPLOY_ARCHITECTURE_AUDIT.md`
- `backend/CPANEL_HTACCESS_DEPLOY_FIX_REPORT.md`
