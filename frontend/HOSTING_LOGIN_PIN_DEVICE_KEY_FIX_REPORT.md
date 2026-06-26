# Hosting — Login PIN "Key cannot be empty" — Fix Report (Frontend)

**Fecha:** 2026-06-25  
**Síntoma:** Error `Key cannot be empty` al login PIN en hosting.  
**Causa real:** **`JWT_SECRET` vacío en el backend**, no falta de `device_key` en el navegador.

---

## 1. Auditoría frontend

### Payload actual (`stores/auth.js`)

```javascript
api.post('/auth/login-pin', {
  pin,
  tenant_slug: tenantSlug,
  branch_code: branchCode,
})
```

**Correcto** según contrato backend. No falta `device_key` porque el endpoint no lo usa.

### `device_key` en NightPOS

| Uso | Dónde |
|-----|-------|
| Agente de impresión Go/Node | `config.json` → `Authorization: Bearer npd_live_...` |
| Admin UI | Configuración → Impresoras |
| Login PIN / password | **No aplica** |

Confundir `device_key` del print agent con login PIN explica la pista incorrecta en el diagnóstico.

---

## 2. Por qué el usuario ve "Key cannot be empty"

1. PIN y tenant/branch son válidos → backend encuentra usuario.
2. Backend intenta firmar JWT → `JWT_SECRET` vacío en hosting.
3. Librería JWT lanza `Key cannot be empty`.
4. Frontend mostraba el mensaje crudo del servidor.

**No relacionado con:** PWA, service worker, localStorage de device key, ni limpieza de cookies.

---

## 3. Fix frontend (UX)

Archivo: `src/services/http.js` — `classifyApiError()`

Si la API responde:

- `data.code === 'jwt_not_configured'`, o
- mensaje contiene `Key cannot be empty`

→ Mostrar:

> Error de configuración del servidor. Contacte al administrador del sistema.

En lugar del texto crudo de la librería JWT.

---

## 4. Qué debe hacer el administrador del hosting

No es fix de frontend. En el servidor:

```bash
cd backend
php artisan jwt:secret --force
php artisan optimize:clear
```

Verificar:

`GET /backend/public/api/v1/health` → `"jwt":"up"`

---

## 5. DevTools — checklist

1. Network → `POST .../auth/login-pin`
2. Confirmar body: `pin`, `tenant_slug`, `branch_code`
3. Si status **503** + `jwt_not_configured` → configurar `JWT_SECRET` en hosting
4. Si status **401** → PIN incorrecto o usuario inactivo
5. Si status **200** → login OK

---

## 6. Tests manuales

| Escenario | Esperado |
|-----------|----------|
| PIN correcto + JWT OK en servidor | Login OK |
| PIN correcto + JWT vacío | Mensaje claro de config servidor |
| Incógnito | Igual (no depende de localStorage device) |
| PWA off (`VITE_PWA_ENABLED=false`) | Igual |

---

## 7. No implementado (a propósito)

- `getOrCreateDeviceKey()` en login — **no requerido** por la API de auth.
- Enviar `device_key` ficticio en login — añadiría ruido sin beneficio.

Si en el futuro se necesita identificar terminal POS web, usar un campo distinto (`client_session_id`) acordado con backend.

---

**Estado:** UX mejorada + documentación. **Acción hosting:** `jwt:secret` + `optimize:clear`.
