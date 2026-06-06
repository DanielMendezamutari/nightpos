# Fase C2 — Consola operativa de turno (Backend)

**Fecha:** 2026-06-02  
**Referencias:** `NIGHTPOS_OPERATION_AUDIT.md`, `PHASE_C1_REPORT.md`

## Resumen

Endpoint agregado de resumen operativo del turno para cajera/admin, seeder demo ampliado y rol opcional `cashier_senior`. **179 tests** pasando (`PhaseC2Test` — 8 casos).

## Endpoint

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/v1/shift-console/current` | `shift_console.access` |

### Respuesta (`data`)

| Clave | Contenido |
|-------|-----------|
| `shift` | Turno oficial abierto (o `null`) |
| `cash_session` | Sesión de caja del usuario actual + `sales_by_method` |
| `cash_totals` | Apertura, efectivo, QR, tarjeta, total cobrado, esperado |
| `orders_summary` | Conteos y listas breves (abiertas, barra, pend. cobro, cobradas) |
| `rooms_summary` | Disponibles, ocupadas, limpieza, mantenimiento, total |
| `services_summary` | Manillas, piezas activas, shows del turno |
| `settlements_summary` | Liquidaciones pendientes/pagadas del turno |
| `alerts` | Limpieza, piezas vencidas, garzones/chicas, otras cajas, productos sin precio |
| `cards` | KPIs para tarjetas principales de la UI |

### Seguridad

- Filtra por `tenant_id` y `branch_id` del contexto.
- Requiere permiso explícito.
- Sucursal no autorizada → `403` (middleware de sucursal).

## Permisos y roles

- Nuevo permiso: `shift_console.access` → owner, cajero.
- Rol demo **`cashier_senior`** (`slug: cashier_senior`) con permisos operativos ampliados (quick product/price, rooms.create, show_types.create, etc.) sin afectar cajero estándar.

### Permisos recomendados cajera senior (documentados)

- `shift_console.access`, `rooms.create`, `product_prices.quick_create`, `products.quick_create`, `show_types.create`, `staff.quick_create_girl`, `staff.quick_create_waiter`

## Seeder demo (`NightPosSeeder`)

- 2 garzones (`5678`, `5688`)
- 3 chicas (`9012`, `9022`, `9032`)
- 1 limpieza (`3333`)
- Categorías Bebidas/Tragos + 4 productos con precios SOLO y CON_ACOMPANANTE
- 3 tipos de show
- Habitación `LX` en estado `CLEANING` para probar alertas (sin afectar piezas demo en P1–P4)

## Archivos principales

- `app/Application/ShiftConsole/UseCases/GetCurrentShiftConsoleUseCase.php`
- `app/Http/Controllers/Api/V1/ShiftConsoleController.php`

## Validación manual

1. Login `admin.demo` / PIN `1234` con sucursal `CENTRO`.
2. `GET /api/v1/shift-console/current` con token y header `X-Branch-Code`.
3. Abrir caja, crear comanda, registrar pieza → refrescar consola y ver KPIs/alertas.

## Próxima fase recomendada

**C3** — Reportes operativos básicos por turno (sin BI avanzado) o caja compartida por sucursal según auditoría A-01.
