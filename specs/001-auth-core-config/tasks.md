# Tareas de Implementación (TDD) — Módulo 001: Core, Configuración y Auth JWT

**Módulo**: `001-auth-core-config`  
**Estrategia**: Test-Driven Development (Red-Green-Refactor)

---

## Fase 1: Dominio y Pruebas Unitarias Puras (Backend)
- [ ] **T01**: Crear prueba unitaria `Tests\Unit\Auth\PinValueObjectTest` (validación de formato y longitud de PIN).
- [ ] **T02**: Crear prueba unitaria `Tests\Unit\Auth\LoginWithPinHandlerTest` (lógica de verificación de PIN y emisión de token).
- [ ] **T03**: Implementar Value Object `App\Domain\User\ValueObjects\Pin` y entidad pura `User`.
- [ ] **T04**: Implementar Caso de Uso `App\Application\Auth\Handlers\LoginWithPinHandler` y `LoginWithPasswordHandler`.

## Fase 2: Infraestructura, Persistencia y JWT (Backend)
- [ ] **T05**: Verificar y ajustar migraciones para `tenants`, `branches`, `users`, `roles` con soporte de `pin_hash`.
- [ ] **T06**: Implementar `EloquentUserRepository` satisfaciendo el puerto `UserRepositoryInterface`.
- [ ] **T07**: Configurar el servicio JWT en `App\Infrastructure\Auth\JwtTokenGenerator` asegurando compatibilidad con `auth:api`.

## Fase 3: Endpoints REST API y Pruebas de Integración (Backend)
- [ ] **T08**: Crear prueba de integración `Tests\Feature\Api\V1\AuthApiTest` (login con PIN, login con contraseña, endpoint `/me`).
- [ ] **T09**: Conectar `AuthController` con los Handlers de aplicación, formateando respuestas mediante `AuthResponseDTO` y Resources.
- [ ] **T10**: Ejecutar `php artisan test --filter=Auth` y verificar 100% de pruebas en verde.

## Fase 4: Frontend Vue.js 3 y Plantilla Materialize
- [ ] **T11**: Actualizar `src/stores/auth.js` en el frontend para manejar sesión, tokens JWT y sucursales.
- [ ] **T12**: Conectar la vista `src/pages/login.vue` con el endpoint `POST /api/v1/auth/login-pin` y `login-password`.
- [ ] **T13**: Asegurar redirecciones según el rol del usuario autenticado (Caja, Mesas, Admin).

## Fase 5: Convergencia y Entrega
- [ ] **T14**: Verificar paridad con `specs/000-reconocimiento/mapa-sistema.md` y roles legacy.
- [ ] **T15**: Realizar commit en Git y generar reporte de pruebas para el usuario.
