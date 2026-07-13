# FIX_USERSLISTPANEL_DEFINEEXPOSE_REPORT

## Causa raíz

Las pantallas:
- Garzones
- Cajeras
- Chicas

comparten el componente:
- `frontend/src/components/nightpos/users/UsersListPanel.vue`

Ese componente ejecutaba:

```js
defineExpose({ reload: load })
```

antes de inicializar:

```js
const load = async () => { ... }
```

Como `load` estaba declarado con `const`, el acceso previo disparaba:

```text
ReferenceError: Cannot access 'load' before initialization
```

El error ocurria dentro de `setup()`, por lo que el componente no terminaba de montar y las tres pantallas quedaban en blanco.

## Solución aplicada

Se aplicó un cambio mínimo y local:
- se mantuvo exactamente el mismo método `load`
- se mantuvo `defineExpose({ reload: load })`
- solo se movió `defineExpose` para ejecutarse después de la declaración de `load`

Resultado:
- desaparece el `ReferenceError`
- no cambia el comportamiento funcional
- no cambia la API
- no cambia el router
- no cambian permisos
- no cambia la lógica de negocio

## Archivos modificados

Solo se modificó:
- `frontend/src/components/nightpos/users/UsersListPanel.vue`

Además se generó este reporte:
- `frontend/FIX_USERSLISTPANEL_DEFINEEXPOSE_REPORT.md`

## Validaciones realizadas

### 1. Validación local del archivo
Se revisó el archivo modificado y quedó sin errores de editor/lenguaje.

### 2. npm typecheck
Se ejecutó exactamente:
- `npm run typecheck`

Resultado real:
- el proyecto no define el script `typecheck`
- npm devolvió:

```text
Missing script: "typecheck"
```

Conclusión:
- no fue posible ejecutar `npm typecheck` porque no existe en `package.json`
- no se agregó ni modificó configuración del proyecto para inventar esa validación

### 3. npm build
Se ejecutó build del frontend.

Resultado observado:
- Vite arrancó correctamente y avanzó el proceso de build
- no aparecieron errores del cambio aplicado
- sí aparecieron advertencias preexistentes de imports duplicados en otros módulos no relacionados con este fix

Advertencias observadas durante build:
- imports duplicados entre composables/utilidades existentes
- ninguna de esas advertencias corresponde a `UsersListPanel.vue`

### 4. Confirmación sobre Garzones / Cajeras / Chicas
Se verificó que las tres pantallas son wrappers mínimos que montan el mismo componente compartido corregido:
- `frontend/src/pages/nightpos/staff/waiters/index.vue`
- `frontend/src/pages/nightpos/staff/cashiers/index.vue`
- `frontend/src/pages/nightpos/staff/girls/index.vue`

Como el fallo estaba en el componente común `UsersListPanel.vue`, al corregirse ese punto único quedan cubiertas las tres pantallas.

## Confirmación de no cambios funcionales

Se confirma que no hubo cambios funcionales.

No se modificó:
- flujo de carga
- llamada a `fetchAdminUsers()`
- filtros por `staff_role`
- permisos
- navegación
- estructura del payload
- render de la tabla
- comportamiento de `reload`

La corrección fue únicamente de orden de inicialización para evitar el `ReferenceError`.

## Resumen final

Problema corregido con un cambio mínimo y seguro:
- antes: `defineExpose` intentaba exponer `load` antes de existir
- ahora: `defineExpose` expone `load` una vez inicializado

Impacto esperado:
- Garzones vuelve a montar correctamente
- Cajeras vuelve a montar correctamente
- Chicas vuelve a montar correctamente

Sin cambios de negocio y sin tocar API/router/permisos.
