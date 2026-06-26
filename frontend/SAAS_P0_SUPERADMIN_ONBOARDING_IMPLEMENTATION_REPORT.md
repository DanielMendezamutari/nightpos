# SAAS P0 — SUPERADMIN + ONBOARDING — REPORTE FRONTEND

**Fecha:** 2026-06-25  
**Estado:** Implementado (P0)

---

## Resumen

Perfil real de usuario, enlaces de menú corregidos y checklist post-wizard para dejar tenant operativo.

---

## Parte 6 — Mi perfil

**Ruta:** `/nightpos/account/profile` (`nightpos-account-profile`)

Permite:

- Ver datos de usuario (nombre, username, email, rol, contexto)
- Cambiar contraseña (`PATCH /auth/me/password`)
- Cambiar PIN (`PATCH /auth/me/pin`)
- Cerrar sesión

Tras cambio de contraseña: logout automático y redirect a login.

**Archivos:**

- `src/pages/nightpos/account/profile.vue`
- `src/api/account.js`
- `src/layouts/components/UserProfile.vue` — "Mi perfil" → perfil real (ya no `resolveHomeRoute`)

---

## Parte 7 — Post-wizard checklist

En `platform/setup.vue` paso 4 (Confirmación):

- Checklist visual con ítems completados (empresa, sucursal, admin, permisos, pagos, caja, impresoras)
- Links: Configurar impresoras, Crear usuarios, Cargar productos
- Usa `data.bootstrap` y `data.roles` de respuesta API

---

## Verificación manual

1. Login superadmin → Setup → crear tenant
2. Paso confirmación muestra checklist
3. Operar en empresa → menú Configuración → Impresoras visible
4. Menú usuario → Mi perfil → cambiar PIN/contraseña

---

## Archivos

| Archivo | Cambio |
|---------|--------|
| `pages/nightpos/account/profile.vue` | Nuevo |
| `api/account.js` | Nuevo |
| `layouts/components/UserProfile.vue` | Fix navegación perfil |
| `pages/nightpos/platform/setup.vue` | Checklist post-alta |
