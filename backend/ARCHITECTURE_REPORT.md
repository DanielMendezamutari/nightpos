# ARCHITECTURE_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Fase:** 3 — Fundación hexagonal  
**Fecha:** 2026-06-02  
**Referencias:** `DOMAIN_DESIGN.md`, `DEVELOPMENT_RULES.md`, `SAAS_ARCHITECTURE.md`  
**Alcance:** Solo carpeta `backend/`. Sin migraciones, sin frontend, sin tocar `restaurant_bolivia-1`.

---

## 1. Estructura creada

### 1.1 Vista general

```text
backend/app/
├── Domain/              # Puertos y reglas (sin Laravel)
├── Application/         # Casos de uso y DTOs (sin Controllers)
├── Infrastructure/      # Adaptadores Laravel (placeholders)
├── Shared/              # Kernel compartido entre contextos
├── Http/                # Laravel default (Controllers — sin lógica de negocio aún)
├── Models/              # User.php placeholder Laravel
└── Providers/           # AppServiceProvider
```

### 1.2 Bounded contexts (13)

| Contexto | Domain | Application | Infrastructure/Persistence |
| -------- | ------ | ----------- | ------------------------- |
| Tenant | `TenantDomainException`, `TenantRepositoryInterface` | `TenantDto`, `TenantUseCaseInterface`, `UseCases/` | placeholder |
| Branch | `BranchDomainException`, `BranchRepositoryInterface` | `BranchDto`, `BranchUseCaseInterface` | placeholder |
| Auth | `AuthDomainException`, `AuthRepositoryInterface` | `AuthDto`, `AuthUseCaseInterface` | placeholder |
| User | `UserDomainException`, `UserRepositoryInterface`, `StaffMemberRepositoryInterface` | `UserDto`, `UserUseCaseInterface` | placeholder |
| Shift | `ShiftDomainException`, `OfficialShiftRepositoryInterface` | `ShiftDto`, `ShiftUseCaseInterface` | placeholder |
| Cash | `CashDomainException`, `CashRegisterRepositoryInterface`, `CashSessionRepositoryInterface` | `CashDto`, `CashUseCaseInterface` | placeholder |
| Order | `OrderDomainException`, `OrderRepositoryInterface` | `OrderDto`, `OrderUseCaseInterface` | placeholder |
| Sale | `SaleDomainException`, `SaleRepositoryInterface` | `SaleDto`, `SaleUseCaseInterface` | placeholder |
| Product | `ProductDomainException`, `ProductRepositoryInterface` | `ProductDto`, `ProductUseCaseInterface` | placeholder |
| Inventory | `InventoryDomainException`, `InventoryRepositoryInterface` | `InventoryDto`, `InventoryUseCaseInterface` | placeholder |
| Printing | `PrintingDomainException`, `PrintJobRepositoryInterface`, `PrinterRepositoryInterface` | `PrintingDto`, `PrintingUseCaseInterface` | placeholder |
| StaffSettlement | `StaffSettlementDomainException`, `StaffSettlementRepositoryInterface` | `StaffSettlementDto`, `StaffSettlementUseCaseInterface` | placeholder |
| Reports | `ReportsDomainException`, `ReportReadRepositoryInterface` | `ReportsDto`, `ReportsUseCaseInterface` | placeholder |

### 1.3 Shared (kernel)

| Ruta | Contenido |
| ---- | --------- |
| `Shared/Contracts/` | `UseCaseInterface`, `RepositoryInterface`, `UnitOfWorkInterface`, `DomainEventDispatcherInterface`, `TenantContextInterface` |
| `Shared/Domain/ValueObjects/` | `TenantId`, `BranchId`, `Money`, `CommissionPercent` |
| `Shared/Domain/Enums/` | `PaymentMethod`, `OrderStatus`, `ShiftStatus`, `ProductSaleMode`, `StaffRole`, `PrintJobStatus` |
| `Shared/Domain/Exceptions/` | `DomainException`, `EntityNotFoundException` |
| `Shared/Domain/Events/` | `DomainEvent` (marker) |
| `Shared/Application/DTOs/` | `DataTransferObject`, `OperationResult` |
| `Shared/Application/Exceptions/` | `ApplicationException` |

### 1.4 Infrastructure

| Ruta | Contenido |
| ---- | --------- |
| `Infrastructure/Providers/NightPosServiceProvider.php` | Registro de bindings (vacío, listo para Fase 4) |
| `Infrastructure/Persistence/{Context}/` | Placeholders `.gitkeep` por contexto |
| `Infrastructure/Presentation/Http/Contracts/ApiResponsePresenterInterface.php` | Puerto de serialización JSON |

### 1.5 Registro Laravel

- `bootstrap/providers.php` → `NightPosServiceProvider` registrado.
- Tests: `tests/Unit/Shared/TenantIdTest.php`, `tests/Unit/Architecture/LayerAutoloadTest.php` (7 tests passing).

### 1.6 Lo que permanece sin cambios (Laravel default)

- `app/Http/Controllers/Controller.php`
- `app/Models/User.php`
- `routes/web.php` — sin `routes/api.php` aún
- Sin migraciones nuevas

---

## 2. Explicación de cada capa

### 2.1 Domain (`app/Domain/`)

**Responsabilidad:** Definir el contrato del negocio hacia el exterior (puertos) y excepciones de dominio por contexto.

**Contiene hoy:**

- `Exceptions/{Context}DomainException` — extienden `App\Shared\Domain\Exceptions\DomainException`
- `Repositories/*RepositoryInterface` — extienden `RepositoryInterface` (sin métodos hasta definir agregados en Fase 4)

**No contiene:** Eloquent, HTTP, JWT, casos de uso, DTOs de aplicación.

**Próximo paso:** Entidades, agregados, domain services y métodos en repositorios según `DOMAIN_DESIGN.md`.

### 2.2 Application (`app/Application/`)

**Responsabilidad:** Orquestar flujos (casos de uso), entrada/salida tipada (DTOs), sin conocer HTTP ni SQL.

**Contiene hoy:**

- `Contracts/{Context}UseCaseInterface` — extiende `UseCaseInterface`
- `DTOs/{Context}Dto` — clase abstracta base por contexto
- `UseCases/` — carpetas vacías (`.gitkeep`) para implementaciones futuras

**Regla:** Los use cases **no** ejecutan queries; delegan en `RepositoryInterface` implementado en Infrastructure.

### 2.3 Infrastructure (`app/Infrastructure/`)

**Responsabilidad:** Adaptadores técnicos: persistencia, JWT, middleware tenant, presenters HTTP, Print Agent, PDF.

**Contiene hoy:** Provider, placeholders de persistencia, contrato `ApiResponsePresenterInterface`.

**Puede depender de:** Laravel, Eloquent, facades.

### 2.4 Shared (`app/Shared/`)

**Responsabilidad:** Contratos y value objects usados por varios bounded contexts (multi-tenant, dinero, enums de boliche).

**Regla:** Igual que Domain — **sin** imports de `Illuminate\*`.

### 2.5 Http / Models (Laravel)

Permanecen en `app/Http` y `app/Models` por convención del framework. En fases posteriores:

- Controllers en `app/Http/Controllers/Api/V1/` llamarán solo a use cases.
- Modelos Eloquent solo bajo `Infrastructure/Persistence`.

---

## 3. Cómo se respetó la arquitectura hexagonal

| Principio | Implementación en Fase 3 |
| --------- | ------------------------ |
| Dominio en el centro | `Domain/` + `Shared/Domain` sin dependencias Laravel |
| Puertos | `*RepositoryInterface`, `UseCaseInterface`, `UnitOfWorkInterface`, `TenantContextInterface` |
| Adaptadores (futuros) | `Infrastructure/Persistence`, `NightPosServiceProvider` |
| Inversión de dependencias | Application dependerá de interfaces Domain; Infrastructure implementará |
| Sin lógica en controllers | No se añadieron controllers de negocio |
| Sin queries en use cases | Carpetas `UseCases/` vacías; contrato documentado en interfaces |
| Multi-tenant preparado | `TenantId`, `BranchId`, `TenantContextInterface` |
| Alineación con DOMAIN_DESIGN | 13 contextos, enums y VOs de Fase 2 |

**Flujo objetivo (cuando existan use cases):**

```text
HTTP Controller
    → Application UseCase (DTO in)
        → Domain (entities / policies)
        → Repository port
            → Infrastructure Eloquent adapter
    → OperationResult
        → ApiResponsePresenter
    → JSON
```

---

## 4. Qué NO se implementó todavía

| Ítem | Fase prevista |
| ---- | ------------- |
| Entidades y agregados de dominio | Fase 4+ |
| Métodos en repository interfaces | Fase 4+ |
| Implementaciones Eloquent | Fase 4+ |
| Migraciones y seeders SaaS | Fase 4+ |
| Casos de uso concretos (`LoginUseCase`, etc.) | Fase 4–5 |
| JWT, middleware `tenant` / `subscription` | Fase 4–5 |
| `routes/api.php` y controllers `/api/v1` | Fase 5 |
| `ApiResponsePresenter` implementación | Fase 5 |
| `UnitOfWork` Laravel | Fase 4 |
| Domain event handlers | Fase 6+ |
| Lógica SOLO / CON_ACOMPANANTE, caja, comisiones | Módulos según ROADMAP |
| Frontend Vue | Fuera de alcance |
| Modificaciones a `restaurant_bolivia-1` | Prohibido |

---

## 5. Próxima fase recomendada

**Fase 4 — Auth / Tenant / Branch (fundación SaaS + persistencia inicial)**

1. Migraciones base: `tenants`, `plans`, `subscriptions`, `branches`, `users`, `roles`, `permissions` (según `DATABASE_GUIDELINES.md`).
2. Implementar `EloquentTenantRepository`, `EloquentBranchRepository`, bindings en `NightPosServiceProvider`.
3. Entidades de dominio mínimas: `Tenant`, `Branch`.
4. Casos de uso: `LoginUseCase`, `LoginWithPinUseCase` (stubs con puertos).
5. Respuesta API uniforme + excepción handler que mapee `DomainException` → JSON.
6. Middleware `TenantContext` (Infrastructure) implementando `TenantContextInterface`.

**Orden sugerido:** Tenant → Branch → Auth/User → luego Product/Price → Cash/Shift.

---

## 6. Comandos útiles

```bash
cd backend
composer dump-autoload
php artisan test
```

---

## 7. Criterios de aceptación Fase 3

- [x] Estructura `Domain`, `Application`, `Infrastructure`, `Shared` en `backend/app`
- [x] 13 bounded contexts con excepciones, repositorios (puertos), DTO base y use case interface
- [x] Shared kernel con VOs, enums y contratos transversales
- [x] Domain sin dependencia de Laravel
- [x] Application sin controllers
- [x] `NightPosServiceProvider` registrado
- [x] Sin migraciones ni CRUDs genéricos
- [x] Sin tocar frontend ni heredado
- [x] Tests de autoload y value object
- [x] `ARCHITECTURE_REPORT.md` generado

---

*Fundación hexagonal lista. Esperar instrucciones para Fase 4.*
