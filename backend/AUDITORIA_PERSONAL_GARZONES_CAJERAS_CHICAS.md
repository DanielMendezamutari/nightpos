# AUDITORIA FORENSE BACKEND - PERSONAL / GARZONES / CAJERAS / CHICAS

## Objetivo
Verificar si el backend participa en el problema donde las vistas:
- Garzones
- Cajeras
- Chicas

quedan en blanco, y documentar la cadena API real con evidencia.

## Conclusión ejecutiva
El backend **no aparece como causa primaria** de la pantalla en blanco.

La API compartida por esas vistas existe, esta correctamente rutada y responde con `200 OK` para un administrador activo real.

Por tanto:
- no se confirma problema de router backend;
- no se confirma error de payload;
- no se confirma espera infinita de datos.

El blanco se explica en frontend antes de que la llamada llegue a ejecutarse desde la pantalla.

---

## 1. Endpoint real usado por las tres pantallas

Las tres vistas usan `UsersListPanel.vue`, y este panel llama:

- `GET /api/v1/admin/users`

Evidencia frontend:
- `frontend/src/api/users.js`

---

## 2. Ruta backend

En `backend/routes/api.php` la ruta existe y esta protegida por permiso:

```php
Route::middleware('nightpos.permission:admin.users.list')->group(function () {
    Route::get('users', [AdminUserController::class, 'index']);
    Route::get('users/{id}', [AdminUserController::class, 'show'])->whereNumber('id');
});
```

La ruta esta dentro del bloque admin API y no presenta inconsistencia de wiring.

---

## 3. Controller que atiende la ruta

Archivo:
- `backend/app/Http/Controllers/Api/V1/Admin/AdminUserController.php`

Metodo relevante:
- `index()`

Implementacion:
- delega a `ListUsersAdminUseCase`
- presenta el resultado con `ApiResponsePresenterInterface`

No hay logica compleja en controller que explique una pantalla en blanco.

---

## 4. UseCase real

Archivo:
- `backend/app/Application/User/UseCases/ListUsersAdminUseCase.php`

Comportamiento:
1. Obtiene tenant desde `TenantContextInterface`.
2. Si no hay tenant: retorna error `Debe indicar la empresa en el contexto.`
3. Si hay tenant: consulta usuarios de ese tenant.
4. Devuelve:
   - `message: Listado de usuarios.`
   - `data.users: [...]`

Consulta real:
- `UserModel::query()`
- `with(['role', 'staffProfile', 'accessibleBranches', 'branch'])`
- `where('tenant_id', $tenant->id)`
- `orderBy('name')`
- `get()`

---

## 5. Reproduccion real de API

### 5.1 Usuario administrador activo usado para la prueba
Se utilizo un admin real activo de la base `nigtpos`:

- `user_id`: `10`
- `username`: `LAURA`
- `role_slug`: `tenant_owner`
- `tenant_slug`: `C22`
- `branch_code`: `1`

### 5.2 Resultado HTTP
Llamada reproducida a:
- `GET /api/v1/admin/users`

Resultado:
- `HTTP/1.1 200 OK`

### 5.3 Payload real
El endpoint devolvio JSON valido con estructura:

```json
{
  "success": true,
  "message": "Listado de usuarios.",
  "data": {
    "users": [
      {
        "id": 23,
        "tenant_id": 2,
        "branch_id": 2,
        "branch_name": "EL JEFE",
        "role": "cashier",
        "role_name": "Cajero",
        "name": "lizvania",
        "username": "lizvania",
        "status": "active",
        "staff_role": "CASHIER"
      },
      {
        "id": 14,
        "tenant_id": 2,
        "branch_id": 2,
        "branch_name": "EL JEFE",
        "role": "waiter",
        "role_name": "Garzón",
        "name": "RANDI",
        "username": "RANDI",
        "status": "active",
        "staff_role": "WAITER"
      },
      {
        "id": 20,
        "tenant_id": 2,
        "branch_id": 2,
        "branch_name": "EL JEFE",
        "role": "girl",
        "role_name": "Chica",
        "name": "Alejandra",
        "username": "alejandra",
        "status": "active",
        "staff_role": "GIRL"
      }
    ]
  },
  "errors": {}
}
```

El payload contiene precisamente los tipos de personal que esas pantallas necesitan filtrar.

---

## 6. Permisos y contexto

### 6.1 Permiso requerido
La ruta exige:
- `admin.users.list`

### 6.2 Evidencia del admin usado
El admin `LAURA` pertenece a:
- tenant `C22`
- branch `1`
- rol `tenant_owner`

Y el endpoint respondio correctamente, lo que demuestra que:
- tenant resuelve bien;
- branch/contexto operativo no rompe este flujo;
- permiso suficiente existe en ese contexto.

---

## 7. Codigo HTTP y lectura tecnica

### Resultado observado
- `200 OK`
- payload coherente
- lista real de usuarios

### Implicacion
La API compartida de Personal no explica una pantalla completamente vacia.

Si la pantalla estuviera montando normalmente, tendria datos para renderizar.

---

## 8. Determinacion respecto de A-F

### A) El router no carga el componente
No corresponde al backend.

### B) El componente falla al montar
Consistente con la evidencia general del problema.
El backend no contradice esta hipotesis; al contrario, la refuerza, porque el endpoint esta sano.

### C) La API devuelve error
Descartado en la reproduccion real con admin activo.

### D) El componente espera datos que nunca llegan
Descartado como causa primaria backend.
La API si devuelve datos.

### E) Algun cambio reciente rompio esas pantallas
Puede ser cierto en frontend, pero no hay evidencia backend de regresion equivalente en esta cadena.

### F) Otra causa
No necesaria desde backend.

---

## Conclusión final
Desde backend, la cadena `GET /api/v1/admin/users` esta operativa y responde correctamente con datos validos para un administrador activo real.

Por tanto, el backend no explica la pantalla en blanco de Garzones, Cajeras y Chicas.

La evidencia backend descarta `C` y `D` como causa primaria y apunta a que el problema real esta en frontend, antes de la ejecucion de la llamada HTTP.
