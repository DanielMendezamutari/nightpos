# AUDITORIA FORENSE FRONTEND - PERSONAL / GARZONES / CAJERAS / CHICAS

## Objetivo
Determinar por que las opciones del menu Personal:
- Garzones
- Cajeras
- Chicas

cambian la URL correctamente, pero dejan la pantalla completamente vacia.

## Conclusión ejecutiva

**Clasificacion final: B**

**Causa exacta:** el componente compartido `UsersListPanel.vue` falla durante `setup()` antes de montar, por una referencia a `load` antes de su inicializacion:

- `defineExpose({ reload: load })`
- `const load = async () => { ... }`

Esto produce el error JavaScript:

```text
ReferenceError: Cannot access 'load' before initialization
```

Como las tres pantallas montan ese mismo componente, las tres quedan en blanco.

**Evidencia adicional:** el fallo fue introducido por un cambio reciente. La linea `defineExpose({ reload: load })` fue agregada en commit `b5633663` el `2026-06-15`.

---

## 1. Router

### 1.1 Estado de las rutas
Las rutas si existen a nivel frontend.

La navegacion referencia correctamente:
- `nightpos-staff-waiters`
- `nightpos-staff-cashiers`
- `nightpos-staff-girls`

Evidencia:
- `frontend/src/composables/useStaffSectionTabs.js`
- `frontend/src/navigation/vertical/nightpos-r4.js`

### 1.2 Como se cargan
El proyecto usa auto-routing:
- `createRouter` desde `vue-router/auto`
- `extendRoutes` con las paginas detectadas automaticamente

Evidencia:
- `frontend/src/plugins/1.router/index.js`

### 1.3 Que componente carga cada ruta
Los archivos de pagina existen y son validos:
- `frontend/src/pages/nightpos/staff/waiters/index.vue`
- `frontend/src/pages/nightpos/staff/cashiers/index.vue`
- `frontend/src/pages/nightpos/staff/girls/index.vue`

Cada una monta `UsersListPanel`:
- Garzones: `staff-role-filter="WAITER"`
- Cajeras: `staff-role-filter="CASHIER"`
- Chicas: `girl-commissions-only="true"`

### 1.4 Si el import dinamico falla
No hay evidencia de fallo de import dinamico.

Indicadores:
- los archivos existen;
- no hay errores estaticos en los archivos de pagina;
- la URL cambia correctamente;
- el router guard no redirige a `not-authorized` ni a login.

**Descartado A:** no hay evidencia de que el router no cargue el componente.

---

## 2. Frontend

### 2.1 Punto comun real de las tres pantallas
Las tres paginas son cascarones minimos. El comportamiento real esta centralizado en:
- `frontend/src/components/nightpos/users/UsersListPanel.vue`

### 2.2 Flujo de setup del componente compartido
Orden real en `UsersListPanel.vue`:

1. define props
2. crea permisos/notificaciones
3. crea refs
4. ejecuta:
   - `defineExpose({ reload: load })`
5. mas abajo declara:
   - `const load = async () => { ... }`
6. al final:
   - `onMounted(load)`

### 2.3 Error exacto
En JavaScript, `const` no puede ser referenciada antes de inicializarse.

Se reprodujo fuera del navegador con una sonda minima equivalente:

```text
ReferenceError: Cannot access 'load' before initialization
```

Evidencia directa en el archivo:
- linea con `defineExpose({ reload: load })`
- linea posterior con `const load = async () => { ... }`

### 2.4 Consecuencia funcional
El componente falla en `setup()` antes de:
- registrarse `onMounted(load)`
- ejecutar `load()`
- cambiar `loading`
- disparar la llamada HTTP a `/admin/users`

Por eso:
- el componente no termina de montar;
- la vista queda vacia;
- no hay tabla, no hay loader, no hay error de API en pantalla.

### 2.5 Si existe error silencioso
No es un error silencioso del componente de datos.

Es un error duro de ejecucion en setup.

No ocurre dentro de `catch`.
No depende de `loading`.
No depende de datos remotos.

### 2.6 Si loading nunca termina
No aplica como causa primaria.

`loading` no queda colgado: `load()` nunca llega a ejecutarse.

### 2.7 Si hay errores en composables
No se encontro evidencia de que los composables sean la causa principal.

Revisados:
- `useNightPosPermissions`
- `useUserAdminForm`
- `useAuthStore`

No presentan error estatico y no explican por si solos que las tres pantallas queden completamente vacias.

---

## 3. API

### 3.1 Endpoint usado por las pantallas
`UsersListPanel` llama un unico endpoint:

- `GET /api/v1/admin/users`

Evidencia:
- `frontend/src/api/users.js`

### 3.2 Respuesta esperada por frontend
Frontend espera:

```json
{
  "success": true,
  "message": "Listado de usuarios.",
  "data": {
    "users": []
  }
}
```

### 3.3 Reproduccion real del endpoint
Se reprodujo con usuario administrador activo real `LAURA`:

Contexto:
- `user_id`: `10`
- `username`: `LAURA`
- `role`: `tenant_owner`
- `tenant_slug`: `C22`
- `branch_code`: `1`

Resultado:
- `HTTP 200 OK`
- payload valido con lista real de usuarios
- incluye personal `WAITER`, `CASHIER`, `GIRL`, `MANAGER`

### 3.4 Lectura tecnica
La API responde correctamente cuando se la invoca desde un admin activo.

**Descartado C:** la API no es la causa del blanco.

---

## 4. Console

### 4.1 Error JavaScript identificado
El error de consola esperado y tecnicamente consistente es:

```text
ReferenceError: Cannot access 'load' before initialization
```

Se demostro con reproduccion de la misma semantica JavaScript usada en `UsersListPanel`.

### 4.2 Implicacion
Ese error ocurre durante `setup()` del componente compartido y bloquea completamente el render.

---

## 5. Network

### 5.1 Llamadas que deberian ocurrir
Si el componente montara correctamente, la llamada seria:
- `GET /api/v1/admin/users`

### 5.2 Llamadas que realmente pueden ocurrir con el fallo actual
Debido al `ReferenceError` en setup:
- `onMounted(load)` no llega a registrarse efectivamente;
- `load()` no se ejecuta;
- la pantalla no alcanza a disparar su request propia.

### 5.3 Conclusión de network
La red no es el origen del problema. El problema ocurre antes de la fase de carga remota.

**Descartado D:** el componente no esta esperando datos que nunca llegan; falla antes de pedirlos.

---

## 6. Cambio reciente

### 6.1 Evidencia de regresion reciente
`git blame` sobre `UsersListPanel.vue` muestra:
- la linea `defineExpose({ reload: load })` fue introducida en commit `b5633663`
- fecha: `2026-06-15 11:05:20 -0400`

### 6.2 Interpretacion
Si antes no existia esa linea y ahora si, el cambio reciente puede explicar la regresion compartida.

**E es verdadera como antecedente historico**, pero la clasificacion tecnica principal sigue siendo **B**, porque el efecto actual observable es fallo del componente al montar.

---

## 7. Determinacion final A-F

### A) El router no carga el componente
Descartado.

### B) El componente falla al montar
**Confirmado.**

### C) La API devuelve error
Descartado como causa primaria.
`GET /api/v1/admin/users` respondio `200 OK` con payload valido para admin activo.

### D) El componente espera datos que nunca llegan
Descartado.
La llamada ni siquiera llega a dispararse por el error de setup.

### E) Algun cambio reciente rompio esas pantallas
Verdadero como factor historico de regresion.
La linea conflictiva fue agregada en un commit reciente.

### F) Otra causa
No necesaria.
La causa ya quedo explicada por B.

---

## Conclusión final
La URL cambia porque el router si resuelve la ruta, pero las pantallas quedan completamente vacias porque las tres montan el mismo componente compartido `UsersListPanel.vue`, y ese componente lanza un `ReferenceError` en `setup()` al intentar exponer `load` antes de declararlo.

La causa operativa exacta es **B**.
La evidencia de regresion reciente existe y corresponde a **E** como antecedente, no como clasificacion principal.
