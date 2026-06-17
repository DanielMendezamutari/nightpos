# Cerrar sesión y cambiar cuenta — Shell cajera

**Fecha:** 2026-06-17

## Problema

Cajera básica con shell **Cobrar | Venta | Caja | Más** no encontraba cómo cerrar sesión ni ver su usuario actual.

## Solución

### Componentes nuevos

| Archivo | Rol |
|---------|-----|
| `useCashierAccount.js` | Nombre, rol, sucursal, logout |
| `CashierAccountSection.vue` | UI panel (móvil) o menú compacto (desktop) |

### Móvil — tab «Más»

Al final, sección **Cuenta**:

- Nombre + rol + sucursal
- **Cambiar cuenta** (equivale a logout → login)
- **Cerrar sesión**

### Desktop — barra superior

En `CashierStatusBar`: botón **Mabel ▾** (nombre del usuario).

Menú desplegable:

- Mi usuario (nombre, rol, sucursal)
- Cambiar cuenta
- Cerrar sesión

## Comportamiento logout

`auth.logout()`:

1. `POST /auth/logout` (invalida JWT)
2. `clearSession()` — token, userData, contexto, abilities
3. SSE se detiene vía watch en `useOperationalEvents`
4. `router.replace({ name: 'login' })`

## Validación manual

1. Login cajera básica → shell.
2. Más → sección Cuenta visible.
3. Cerrar sesión → login.
4. Login otra cuenta → entra correctamente.
5. Desktop → menú usuario en barra superior.

## Sin romper

- Shell tabs principales
- Menú admin para senior/admin
- Login PIN
