# PHASE_8_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend + Frontend Caja  
**Fase:** 8 — Caja (apertura, movimientos, cierre)  
**Fecha:** 2026-06-02  
**Referencias:** `DOMAIN_DESIGN.md`, `MIGRATION_PLAN.md`, `PHASE_7_REPORT.md`, `FRONTEND_GUIDELINES.md`

---

## 1. Tablas creadas

Migración `2026_06_03_100008_create_cash_tables.php`:

| Tabla | Descripción |
| ----- | ----------- |
| `cash_registers` | Caja configurada por sucursal |
| `cash_sessions` | Sesión OPEN/CLOSED del cajero |
| `cash_movements` | Ingresos y egresos manuales |

Seeder: caja `CAJA-01` en sucursal CENTRO.

---

## 2. Endpoints API

Permiso: `cash.access`. Sucursal obligatoria.

| Método | Ruta | Acción |
| ------ | ---- | ------ |
| GET | `/api/v1/cash/session/current` | Sesión abierta del usuario |
| POST | `/api/v1/cash/session/open` | Abrir caja |
| POST | `/api/v1/cash/movements` | Registrar ingreso/egreso |
| POST | `/api/v1/cash/session/close` | Cerrar con arqueo |

---

## 3. Reglas implementadas

| Regla | Implementación |
| ----- | -------------- |
| Una sesión OPEN por cajero y sucursal | `findOpenForUser` |
| Movimientos solo con sesión abierta | `RegisterCashMovementUseCase` |
| Cierre calcula esperado = apertura + ingresos − egresos | Repositorio |
| Diferencia = contado − esperado | Al cerrar |
| Sin ventas ni cobro de comandas | Fuera de alcance (Fase 9) |

---

## 4. Frontend Fase 8.5 (Materialize)

| Ruta | Archivo |
| ---- | ------- |
| `/nightpos/cash` | `src/pages/nightpos/cash/index.vue` |

Menú lateral **Caja**, dashboard habilitado. Componentes: `VCard`, `VDataTable`, `VDialog`, botones `x-large`.

---

## 5. Tests

`tests/Feature/Api/V1/CashApiTest.php` — apertura, movimiento, cierre, doble apertura rechazada, admin con PIN.

---

## 6. Próxima fase

**Fase 9 — Ventas / cobro de comandas:** `ChargeOrderUseCase`, pagos, estado `BILLED`, vínculo `cash_session_id`.

```bash
cd backend
php artisan migrate
php artisan test
```

Demo cajero: PIN `1234`, sucursal `CENTRO`.
