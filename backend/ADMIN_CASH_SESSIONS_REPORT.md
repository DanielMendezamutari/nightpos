# Fiscalización multicaja (admin cash sessions)

## Diferencia: caja operativa vs fiscalización

| Concepto | Endpoint / módulo | Uso |
|----------|-------------------|-----|
| **Caja operativa** | `GET /cash/session/current` | Caja del usuario actual: cobrar, servicios, pagar liquidaciones, movimientos |
| **Fiscalización** | `GET /admin/cash-sessions*` | Ver todas las cajas de la sucursal (o tenant para superadmin). Solo lectura / auditoría |

El administrador **no necesita** caja abierta para fiscalizar. Para **pagar liquidaciones** sigue necesitando su propia caja operativa.

## Endpoints creados

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/v1/admin/cash-sessions` | `admin.cash_sessions.list` |
| GET | `/api/v1/admin/cash-sessions/summary` | `admin.cash_sessions.summary` |
| GET | `/api/v1/admin/cash-sessions/{id}` | `admin.cash_sessions.view` |

### Filtros (list + summary)

- `tenant_id` — solo superadmin
- `branch_id` — opcional (default: sucursal del contexto)
- `official_shift_id`
- `cashier_user_id`
- `status` — `OPEN` / `CLOSED`
- `date_from`, `date_to`

### Respuesta listado

Incluye: tenant, branch, cashier, official_shift, montos (efectivo esperado, QR, tarjeta, ventas, egresos manuales), fechas.

### Detalle `{id}`

Incluye: sesión, summary, movimientos, ventas, liquidaciones pagadas, ingresos/egresos separados.

## Permisos

- `admin.cash_sessions.list`
- `admin.cash_sessions.view`
- `admin.cash_sessions.summary`

**Asignados a:** `super_admin`, `tenant_owner` (admin sucursal), `cashier_senior` (manager/cajera senior).

**No asignados a:** `cashier`, `waiter`, `cleaning`.

Migración: `2026_06_10_100063_admin_cash_sessions_permissions.php`

## Tests

`tests/Feature/Api/V1/AdminCashSessionsTest.php` — **10/10**

## Validación manual

1. Login cajera A → abrir caja.
2. Login cajera B (o admin con caja propia) → abrir otra caja.
3. Login admin → Finanzas → **Fiscalización de cajas**.
4. Ver ambas cajas en «Cajas abiertas».
5. Abrir detalle de una sesión.
6. Confirmar admin fiscaliza sin caja abierta propia.
7. En Liquidaciones: pagar requiere caja del usuario; enlace a fiscalización visible.
