# Backend Reports V1 Report

## Fase: V1-96 — Reportes y Cierre de Turno

**Fecha:** 2026-06-06  
**Tests:** 376 pasando (11 nuevos para V1-96)

---

## Endpoints implementados

| Método | Ruta | Permiso | Descripción |
|--------|------|---------|-------------|
| GET | `/api/v1/reports/daily` | `reports.access` | Resumen diario: ventas, servicios, liquidaciones, caja, habitaciones |
| GET | `/api/v1/reports/sales` | `reports.access` | Detalle de ventas con ítems y pagos |
| GET | `/api/v1/reports/cash` | `reports.access` | Sesiones de caja con movimientos |
| GET | `/api/v1/reports/services` | `reports.access` | Manillas, piezas y shows del turno |
| GET | `/api/v1/reports/settlements` | `reports.access` | Liquidaciones pagadas y pendientes |
| GET | `/api/v1/reports/rooms` | `reports.access` | Uso y estadísticas por habitación |
| GET | `/api/v1/reports/shift-closure` | `reports.access` | Verificación pre-cierre de turno |
| GET | `/api/v1/reports/product-reconciliation` | `reports.access` | Conciliación de productos vendidos vs. comandados (ver `PRODUCT_RECONCILIATION_REPORT.md`) |

## Filtros disponibles

Todos los endpoints aceptan:
- `date_from` — fecha desde (YYYY-MM-DD)
- `date_to` — fecha hasta (YYYY-MM-DD)
- `official_shift_id` — ID de turno específico

Endpoints adicionales:
- `sales`: `cashier_user_id`, `waiter_user_id`, `payment_method`
- `services`: `girl_user_id`
- `product-reconciliation`: `cash_session_id`, `waiter_user_id`

## Arquitectura

### Hexagonal clean:
- `Domain/Reports/Repositories/ReportReadRepositoryInterface` → 8 métodos
- `Infrastructure/Persistence/Eloquent/Repositories/EloquentReportReadRepository` → implementación
- `Application/Reports/UseCases/*` → 8 Use Cases lean (uno por reporte)
- `Http/Controllers/Api/V1/ReportController` → 8 métodos

### Bindings en NightPosServiceProvider:
```php
$this->app->singleton(ReportReadRepositoryInterface::class, EloquentReportReadRepository::class);
$this->app->singleton(GetDailyReportUseCase::class);
// ... 6 más
```

## Shift Closure Check

El endpoint `GET /api/v1/reports/shift-closure` devuelve:

```json
{
  "can_close": false,
  "blockers": [
    { "code": "open_cash_sessions", "message": "Hay 1 caja abierta...", "count": 1 }
  ],
  "warnings": [
    { "code": "pending_settlements", "message": "Hay 3 liquidaciones pendientes...", "count": 3 }
  ],
  "summary": {
    "open_cash_sessions": 1,
    "active_room_services": 0,
    "pending_settlements": 3,
    "rooms_in_cleaning": 1,
    "cash_difference": -10.50
  }
}
```

**Bloqueantes** (impiden cierre):
- `open_cash_sessions` — cajas abiertas sin cerrar
- `active_room_services` — piezas activas o vencidas

**Advertencias** (solo aviso):
- `pending_settlements` — liquidaciones sin pagar
- `no_settlements_generated` — no se generaron liquidaciones
- `rooms_in_cleaning` — habitaciones en limpieza
- `cash_difference` — diferencia de caja detectada

## Tests (11 casos)

1. Daily report suma ventas de comanda
2. Daily report suma venta directa
3. Daily report separa CASH/QR/CARD
4. Cash report incluye sesión abierta
5. Services report suma manillas
6. Settlements report separa pagado/pendiente
7. Rooms report cuenta habitaciones
8. Tenant isolation
9. Branch isolation (estructura esperada)
10. Filtro por official_shift_id
11. Shift-closure check: blockers cuando hay caja abierta

## Total tests: 376 pasando, 0 fallando
