# Frontend — Selección de contexto en login PIN

## Cambios UX

### Paso PIN (contexto guardado)

- Muestra **Empresa** y **Sucursal** con nombres legibles (no solo slugs).
- Campo PIN grande.
- Botón **Cambiar empresa / sucursal**.

### Paso selección (primera vez o cambio)

- `VSelect` empresa (desde API).
- `VSelect` sucursal (carga al elegir empresa).
- Botón **Continuar** → guarda selección y vuelve al PIN.
- Botón **Volver al PIN** si había contexto previo.

### Errores de contexto

Si el backend responde 403/404/422 o mensaje relacionado con empresa/sucursal:

> No se pudo ingresar con esta empresa/sucursal. Cambia la empresa o sucursal.

Con botón directo para cambiar contexto. No queda en loading infinito.

### Contexto inválido guardado

Al abrir login, valida empresa/sucursal contra la API. Si ya no existen, fuerza re-selección con mensaje claro.

## Persistencia (cookies, 30 días)

| Cookie | Contenido |
|--------|-----------|
| `tenantSlug` | Slug técnico |
| `branchCode` | Código sucursal |
| `tenantName` | Nombre legible empresa |
| `branchName` | Nombre legible sucursal |

**No se guarda PIN.**

## Limpieza de estado

- `auth.clearAuthOnly()` al entrar a login y al cambiar contexto (token/user sin borrar favoritos).
- `clearSavedContext()` al cambiar empresa/sucursal.
- Cookies de contexto solo se guardan al **confirmar selección** o **login exitoso** (no antes del intento).

## Superadmin

Login usuario/contraseña sin cambios: `superadmin` no requiere empresa/sucursal.

## Archivos

| Archivo |
|---------|
| `src/pages/login.vue` |
| `src/api/loginContext.js` |
| `src/stores/context.js` — `tenantName`, `branchName` |
| `src/stores/auth.js` — `clearAuthOnly`, nombres en sesión |

## Validación manual

1. Sin cookies → selectores empresa/sucursal.
2. Elegir y continuar → PIN.
3. Login cajera `1234`.
4. Cerrar sesión → login muestra nombres guardados.
5. Entrar solo con PIN.
6. Cambiar empresa/sucursal → nueva selección.
7. Contexto inválido → error + botón cambiar.
