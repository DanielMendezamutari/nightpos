# Document Sequence Fix — Settlement ticket_number

**Fecha:** 2026-06-28  
**Estado:** Implementado  
**Alcance:** liquidaciones pagadas — numeración transaccional

---

## Problema

```
SQLSTATE[23000]: Duplicate entry '1-2026-000001'
for key 'staff_settlements_ticket_number_unique'
```

`SettlementTicketNumberGenerator` leía el último `ticket_number` y sumaba 1 sin lock. El UNIQUE era **global** en `ticket_number`.

---

## Solución

### 1. Tabla `document_sequences`

| Campo | Descripción |
|-------|-------------|
| `tenant_id`, `branch_id` | Alcance |
| `document_type` | `SETTLEMENT_PAYMENT` (enum extensible) |
| `period_key` | Año (`2026`) |
| `last_value` | Último correlativo reservado |

UNIQUE: `(tenant_id, branch_id, document_type, period_key)`

Migración: `2026_06_28_100000_document_sequences_and_settlement_ticket_unique.php`

### 2. `DocumentSequenceService`

Reserva atómica con **`SELECT ... FOR UPDATE`** (todos los drivers, incluido MySQL):

1. Lock fila de secuencia.
2. Incrementar `last_value` y devolver el correlativo.

> **Nota 2026-06-30:** el path MySQL con `INSERT ... ON DUPLICATE KEY UPDATE` + `PDO::lastInsertId()` fue retirado — devolvía `1` en el 2.do pago. Ver `DOCUMENT_SEQUENCE_409_FIX_REPORT.md`.

`syncSettlementPaymentSequencesFromExistingTickets()` inicializa desde tickets PAID existentes (migración).

### 3. `SettlementTicketNumberGenerator`

Delega en `DocumentSequenceService`. Formato sin cambios:

`{BRANCH_CODE}-{YYYY}-{000001}`

### 4. UNIQUE en `staff_settlements`

Reemplazado:

- ~~`staff_settlements_ticket_number_unique` (global)~~
- **`staff_settlements_ticket_scope_unique`** → `(tenant_id, branch_id, ticket_number)`

### 5. Errores controlados

`MarkSettlementPaidUseCase` captura duplicado → `StaffSettlementDomainException::ticketNumberConflict()` → **HTTP 409** + log con contexto.

---

## Tests

`tests/Feature/Api/V1/DocumentSequenceSettlementTest.php` — 10 casos (consecutivos, cross-tenant, secuencia, sync, 409).

Ejecutar:

```bash
php artisan test tests/Feature/Api/V1/DocumentSequenceSettlementTest.php
php artisan test tests/Feature/Api/V1/SettlementPaymentAuditTest.php
```

---

## Deploy

```bash
php artisan migrate
```

La migración backfill automático desde `ticket_number` existentes.

---

## Futuro

`DocumentSequenceType` preparado para unificar ventas (`V-0001`) y comandas (`C-0001`) — **no incluido en este fix**.

---

## Relacionados

- `DOCUMENT_NUMBER_SEQUENCE_AUDIT.md`
- `SETTLEMENT_PAYMENT_AUDITABLE_IMPLEMENTATION_REPORT.md`
