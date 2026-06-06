# Quick Actions — Fase A (Backend)

**Fecha:** 2026-06-02  
**Referencia:** `SYSTEM_QUICK_ACTIONS_AUDIT.md` (QA-01 a QA-06)

## Resumen

Fase A **no añade endpoints ni migraciones**. Reutiliza APIs existentes; el trabajo es de integración frontend y permisos ya sembrados.

## Flujos: antes vs después

| ID | Antes | Después |
|----|--------|---------|
| QA-01 | Comanda CON_ACOMPANANTE: lista vacía o ID manual; salir a Personal | `POST /staff/quick-girls` desde modal; chica seleccionada al crear |
| QA-02 | Manilla: `VSelect` sin CTA si no hay chicas | Mismo patrón que piezas |
| QA-03 | Show: igual que manillas | Mismo patrón |
| QA-04 | Pieza sin habitación AVAILABLE: alerta, ir a Habitaciones | `POST /rooms` inline; estado AVAILABLE; refresco y selección |
| QA-05 | Cobro con caja cerrada: botón deshabilitado; ir a Caja | `POST /cash/session/open` en modal sin salir de comanda |
| QA-06 | Producto: categoría solo en módulo Catálogo | `POST /product-categories` desde crear producto |

## Endpoints reutilizados

| Acción | Método | Ruta | Permiso middleware |
|--------|--------|------|-------------------|
| Listar chicas operativas | GET | `/api/v1/staff/girls` | `staff.quick_create_girl` |
| Alta rápida chica | POST | `/api/v1/staff/quick-girls` | `staff.quick_create_girl` |
| Crear habitación | POST | `/api/v1/rooms` | `rooms.create` |
| Listar habitaciones disponibles | GET | `/api/v1/rooms/available` | `rooms.list` (lectura) |
| Abrir sesión de caja | POST | `/api/v1/cash/session/open` | `cash.access` |
| Listar categorías | GET | `/api/v1/product-categories` | `product-categories.list` / `products.list` |
| Crear categoría | POST | `/api/v1/product-categories` | `products.create` |
| Cobrar comanda | POST | `/api/v1/orders/{id}/charge` | `sales.charge` (+ caja abierta) |

### Alta rápida chica (existente)

- **Request:** `{ name, pin?, notes? }`
- **Response:** usuario creado con `staff_role: GIRL`, `status: active`
- **Tests:** `tests/Feature/Api/V1/QuickGirlCreateTest.php`

### Habitación (Fase 18)

- **Request:** `code`, `name`, `room_type`, `default_duration_minutes`, `suggested_price`
- **Estado inicial:** `AVAILABLE` (caso de uso create)
- **Tests:** `tests/Feature/Api/V1/RoomsPhase18Test.php`

### Caja y categorías

- Sin cambios de contrato respecto a fases anteriores.
- Cobro sigue exigiendo sesión OPEN del usuario (`cashSessionRequired`).

## Permisos (seeder)

| Permiso | Roles típicos |
|---------|----------------|
| `staff.quick_create_girl` | `tenant_owner`, `cashier` |
| `rooms.create` | Admin / dueño (cajera según política del tenant) |
| `cash.access` | Cajera, admin |
| `products.create` | Admin (incluye `POST product-categories`) |

**Nota:** Si la cajera no puede crear habitaciones, QA-04 mostrará error al guardar; delegar `rooms.create` según operación del local.

## Validación manual (API)

Con token de cajera/admin y sucursal activa:

1. `POST /staff/quick-girls` → 201, nombre único.
2. `GET /staff/girls` → incluye la chica nueva.
3. `POST /rooms` → habitación `AVAILABLE`.
4. `POST /cash/session/open` → sesión `OPEN`.
5. `POST /product-categories` → categoría activa.
6. `php artisan test` — suite existente (QuickGirl, Rooms, cash, productos).

## Archivos backend relevantes (sin cambios Fase A)

- `routes/api.php` — rutas listadas arriba
- `app/Http/Controllers/Api/V1/StaffController.php` — quick girls
- `database/seeders/NightPosSeeder.php` — permisos sincronizados
