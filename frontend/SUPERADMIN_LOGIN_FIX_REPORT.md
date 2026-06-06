# SUPERADMIN_LOGIN_FIX_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend  
**Bug:** Login superadmin muestra «Credenciales inválidas»  
**Fecha:** 2026-06-02

---

## 1. Causa exacta

El mensaje **«Credenciales inválidas.»** proviene del API **401** del backend. En XAMPP la causa habitual fue **no ejecutar `php artisan db:seed`** después de migrar: no existía el usuario `superadmin`.

El frontend ya enviaba el payload correcto para plataforma (sin `tenant_slug` cuando el usuario es `superadmin`). Ajustes aplicados para evitar regresiones:

| Punto | Corrección |
| ----- | ---------- |
| Username | `trim()` + `toLowerCase()` antes del POST |
| `tenant_slug` en body | No se envía si el usuario es `superadmin` |
| Cookies PIN previas | Se limpian `tenantSlug` / `branchCode` al detectar login plataforma |
| Post-login | `refreshContext()` ya omite tenant/branch si `role === 'super_admin'` sin cookie empresa |

Si el login fallaba con credenciales correctas, revisar primero **backend seed**, no solo el UI.

---

## 2. Archivos corregidos

| Archivo | Cambio |
| ------- | ------ |
| `src/stores/auth.js` | Username normalizado; body solo `username` + `password` para superadmin |
| `src/pages/login.vue` | `trim()` en username al enviar; alerta plataforma global; campo empresa oculto para superadmin |
| `src/stores/operational.js` | (previo) superadmin global sin llamar tenant/current con error |

---

## 3. Payload esperado

**Superadmin:**

```json
{
  "username": "superadmin",
  "password": "SuperAdmin123!"
}
```

No incluir `tenant_slug` ni `branch_code`.

**Admin tenant:**

```json
{
  "username": "admin.demo",
  "password": "AdminDemo123!",
  "tenant_slug": "casa-demo"
}
```

---

## 4. Cómo probar

```bash
# Terminal 1 — backend
cd backend
php artisan db:seed
php scripts/verify-superadmin.php

# Terminal 2 — frontend
cd frontend
pnpm run dev
```

1. Abrir `/login`  
2. Pestaña **Usuario / contraseña**  
3. Usuario: `superadmin`, contraseña: `SuperAdmin123!`  
4. Debe mostrarse alerta «Acceso plataforma global»  
5. Entrar al dashboard sin error en consola  

**PIN (regresión):** pestaña PIN, `casa-demo` + `CENTRO` + PIN `1234` (cajero).

---

## 5. Comandos / verificación

No hay cambios de dependencias. Verificar API en DevTools → Network → `login-password` → status **200** y `data.token` presente.

---

## 6. Pendiente

- Mensaje más claro si el backend responde 401 por «usuario no existe» vs «password incorrecto» (hoy mismo texto genérico por seguridad).
- Recordatorio en pantalla login: ejecutar `php artisan db:seed` en instalaciones nuevas.
