# Plan Técnico de Arquitectura Hexagonal — Módulo 001: Core, Configuración y Auth JWT

**Módulo**: `001-auth-core-config`  
**Stack**: PHP 8.2+ / Laravel 12 (Backend) + Vue.js 3 / Pinia / Materialize Template (Frontend)  
**Patrón**: Hexagonal (Puertos y Adaptadores) + DDD + SOLID + JWT

---

## 1. Estructura de Capas Hexagonales (Backend)

```
app/
├── Domain/
│   ├── Auth/
│   │   ├── Services/ (Autenticador de dominio)
│   │   ├── Ports/ (TokenGeneratorInterface, HasherInterface)
│   │   └── Exceptions/ (InvalidCredentialsException, UserInactiveException)
│   ├── User/
│   │   ├── Entities/ (User, Role, Permission)
│   │   ├── ValueObjects/ (UserId, Username, Email, Pin)
│   │   └── Repositories/ (UserRepositoryInterface)
│   ├── Tenant/
│   │   ├── Entities/ (Tenant, Branch)
│   │   └── Repositories/ (TenantRepositoryInterface, BranchRepositoryInterface)
│   └── Settings/
│       ├── Entities/ (BranchSettings)
│       └── Repositories/ (SettingsRepositoryInterface)
├── Application/
│   ├── Auth/
│   │   ├── Commands/ (LoginWithPinCommand, LoginWithPasswordCommand)
│   │   ├── Handlers/ (LoginWithPinHandler, LoginWithPasswordHandler)
│   │   └── DTOs/ (AuthResponseDTO, UserProfileDTO)
│   └── Settings/
│       ├── Queries/ (GetBranchSettingsQuery)
│       └── Handlers/ (GetBranchSettingsHandler)
├── Infrastructure/
│   ├── Persistence/
│   │   └── Eloquent/
│   │       ├── Models/ (UserModel, TenantModel, BranchModel, RoleModel)
│   │       └── Repositories/ (EloquentUserRepository, EloquentTenantRepository)
│   ├── Auth/
│   │   ├── JwtTokenGenerator.php (Adaptador de generación/validación JWT)
│   │   └── BcryptHasher.php (Adaptador de hash seguro para contraseña y PIN)
│   └── Http/
│       └── Middleware/ (JwtAuthMiddleware, TenantScopeMiddleware)
└── Http/
    ├── Controllers/Api/V1/
    │   ├── AuthController.php (Endpoints de login-pin, login-password, me, logout)
    │   ├── BranchController.php
    │   └── SettingsBootstrapController.php
    ├── Requests/ (LoginPinRequest, LoginPasswordRequest)
    └── Resources/ (UserResource, AuthTokenResource, BranchResource)
```

---

## 2. Integración Frontend (Vue.js 3 + Materialize Template)

1. **Store Pinia (`src/stores/auth.js`)**:
   - Mantiene el estado de sesión: `token`, `user`, `tenantSlug`, `branchCode`, `permissions`.
   - Persistencia segura en `localStorage` sincronizada con cabeceras `Authorization: Bearer <token>`.
2. **Página de Login (`src/pages/login.vue`)**:
   - Teclado táctil PIN numérico con auto-envío al 4to dígito para cajeros/garzones.
   - Pestaña alternativa para usuario/password de administradores.
   - Selector visual de sucursal.
3. **Control de Acceso y Redirección (`src/router/`)**:
   - Guardia de navegación (`router.beforeEach`):
     - Si no está autenticado -> redirige a `/login`.
     - Si es Cajero -> redirige a `/cashier`.
     - Si es Garzón -> redirige a `/waiter`.
     - Si es Administrador -> redirige a `/admin` (dashboard).

---

## 3. Plan de Pruebas Automatizadas (TDD)

1. **Unit Tests (Dominio y Casos de Uso)**:
   - `LoginWithPinHandlerTest`: Valida que un PIN correcto emite token y usuario; un PIN incorrecto lanza excepción de dominio.
   - `PinValueObjectTest`: Valida que el PIN solo acepte dígitos numéricos y longitudes válidas.
2. **Integration / Feature Tests**:
   - `POST /api/v1/auth/login-pin`: Valida respuesta HTTP 200 con payload JWT estructurado.
   - `POST /api/v1/auth/login-password`: Valida autenticación administrativa con hash bcrypt.
   - `GET /api/v1/auth/me`: Valida que la ruta protegida devuelva los datos del usuario autenticado.
