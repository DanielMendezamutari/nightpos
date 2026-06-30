# Document Sequence — Depuración real con base de hosting (409)

**Fecha:** 2026-06-30  
**Base:** MySQL local `nigtpos` (dump del hosting)  
**Estado:** Causa exacta identificada — **sin cambios de código aplicados**

---

## Resumen ejecutivo

El 409 **no es un problema de hosting**. Se reproduce idéntico en local con la misma base.

**Escenario confirmado: B** — el servicio devuelve correlativo **2**, pero `1-2026-000002` **ya existe** en `staff_settlements` (id=5).

**Causa raíz:** desfase entre `document_sequences.last_value` y los tickets reales, originado en el **primer pago exitoso** (settlement id=5). Ese pago guardó ticket `000002` mientras `last_value` quedó en `1`. Todos los pagos siguientes recalculan `1+1=2` → mismo ticket → 409 → **ROLLBACK** deja `last_value` en `1` para siempre (bucle infinito).

---

## Datos reales en la base (al momento de depurar)

### Tickets pagados

| id | tenant_id | branch_id | status | ticket_number | paid_at |
|----|-----------|-----------|--------|---------------|---------|
| 5 | 2 | 2 | PAID | **1-2026-000002** | 2026-06-30 08:30:38 |
| 2 | 3 | 3 | PAID | 1-2026-000002 | 2026-06-28 23:22:23 |
| 1 | 3 | 3 | PAID | 1-2026-000001 | 2026-06-28 23:20:50 |

### Liquidaciones pendientes (tenant 2, branch 2)

| id | status | ticket_number | net_amount |
|----|--------|---------------|------------|
| 3 | PENDING | NULL | 53.00 |
| 4 | PENDING | NULL | 80.00 |
| 6 | PENDING | NULL | 190.00 |
| 7 | PENDING | NULL | 90.00 |

### document_sequences

| id | tenant_id | branch_id | document_type | period_key | last_value | created_at |
|----|-----------|-----------|---------------|------------|------------|------------|
| 1 | 3 | 3 | SETTLEMENT_PAYMENT | 2026 | **2** | 2026-06-30 08:29:49 |
| 2 | 2 | 2 | SETTLEMENT_PAYMENT | 2026 | **1** | 2026-06-30 08:30:38 |

**Desfase crítico tenant 2:** ticket máximo pagado = `000002`, pero `last_value = 1`.

### Índices en staff_settlements

- ✓ Existe `staff_settlements_ticket_scope_unique` → `(tenant_id, branch_id, ticket_number)`
- ✗ No existe `staff_settlements_ticket_number_unique` (índice global viejo eliminado)

### Branch codes

| id | code | name |
|----|------|------|
| 2 | 1 | EL JEFE |
| 3 | 1 | DEMO |

---

## Reproducción paso a paso — POST mark-paid settlement id=3

Ejecutado con `php scripts/trace_mark_paid.php 3` contra la base real (simulación con ROLLBACK al final).

### 1. Settlement recibido

```
id          = 3
tenant_id   = 2
branch_id   = 2
status      = PENDING
ticket_number = NULL
```

### 2. document_sequences ANTES de pedir secuencia

```sql
SELECT * FROM document_sequences
WHERE tenant_id=2 AND branch_id=2
  AND document_type='SETTLEMENT_PAYMENT' AND period_key='2026';
```

| id | last_value | updated_at |
|----|------------|------------|
| 2 | **1** | 2026-06-30 08:30:38 |

### 3. DocumentSequenceService::reserveNextWithLock

```
path                 = increment_existing_row
last_value leído     = 1
last_value calculado = 2
last_value guardado  = 2        (dentro de TX)
valor retornado      = 2
```

**Código:** `DocumentSequenceService.php` líneas 109-117

### 4. SettlementTicketNumberGenerator::next

```
branch.code       = 1
sequence recibida = 2
ticket generado   = 1-2026-000002
```

**Código:** `SettlementTicketNumberGenerator.php` líneas 25-32

### 5. Ticket que se intenta guardar

```
ticket_number = 1-2026-000002
```

**Código destino:** `EloquentStaffSettlementRepository.php` línea ~916 (`$model->update(['ticket_number' => $ticketNumber])`)

### 6. ¿Escenario A o B?

**Escenario B confirmado.**

El servicio devolvió **2**, no 1.

```sql
SELECT id, tenant_id, branch_id, status, ticket_number
FROM staff_settlements
WHERE ticket_number='1-2026-000002';
```

| id | tenant | branch | status | ticket |
|----|--------|--------|--------|--------|
| 2 | 3 | 3 | PAID | 1-2026-000002 |
| **5** | **2** | **2** | **PAID** | **1-2026-000002** |

En el scope del UNIQUE `(tenant_id=2, branch_id=2, ticket_number='1-2026-000002')` ya existe **id=5**.

### 7. Dónde se lanza el 409

```
MarkSettlementPaidUseCase.php ~271  → settlements->markPaid(..., '1-2026-000002')
EloquentStaffSettlementRepository.php ~916 → UPDATE staff_settlements SET ticket_number=...
MySQL → Duplicate entry '2-2-1-2026-000002' for key 'staff_settlements_ticket_scope_unique'
MarkSettlementPaidUseCase.php ~292-302 → catch QueryException → ticketNumberConflict() → HTTP 409
```

**Log real** (`storage/logs/laravel.log`):

```
[2026-06-30 08:50:30] mark-paid duplicate ticket_number
{"tenant_id":2,"branch_id":2,"settlement_id":4,"ticket_number":"1-2026-000002",...}
```

---

## 8. last_value ANTES y DESPUÉS del commit

### Dentro de transacción (antes de fallar)

```
document_sequences.last_value = 2   (incremento aplicado)
```

### Después del ROLLBACK por 409

```
document_sequences.last_value = 1   (vuelve al estado previo)
```

**Confirmado:** el rollback deshace el incremento de `document_sequences`.

---

## 9. ¿El rollback explica que siempre vuelva a 1?

**Sí.** Bucle confirmado:

```
last_value=1  →  reserveNext()=2  →  ticket 000002  →  DUPLICATE  →  ROLLBACK  →  last_value=1
       ↑__________________________________________________________________________|
```

Cada intento fallido **nunca avanza** la secuencia. El 409 se repetirá indefinidamente mientras exista settlement id=5 con `1-2026-000002` y `last_value=1`.

---

## 10. Cadena completa línea → valor (segundo pago)

```
MarkSettlementPaidUseCase.php:259
  ↓ ticketNumbers->next(2, 2)

DocumentSequenceService.php:109-117  [lockForUpdate, row exists]
  ↓ last_value leído = 1
  ↓ last_value calculado = 2
  ↓ last_value guardado = 2 (en TX)
  ↓ valor retornado = 2

SettlementTicketNumberGenerator.php:32
  ↓ sequence = 2
  ↓ ticket = "1-2026-000002"

MarkSettlementPaidUseCase.php:271 → markPaid(..., "1-2026-000002")

EloquentStaffSettlementRepository.php:916
  ↓ UPDATE staff_settlements SET ticket_number='1-2026-000002'

MySQL UNIQUE staff_settlements_ticket_scope_unique
  ↓ colisión con staff_settlements.id=5 (ya PAID con mismo ticket)

MarkSettlementPaidUseCase.php:302
  ↓ HTTP 409 + ROLLBACK (last_value vuelve a 1)
```

---

## Origen del desfase — primer pago (settlement id=5)

El primer pago exitoso para tenant 2 ocurrió el **2026-06-30 08:30:38** y creó:

- `staff_settlements.id=5` → ticket **`1-2026-000002`** (saltó 000001)
- `document_sequences.id=2` → **`last_value=1`** (no 2)

### Prueba MySQL reproducible

Con la misma situación (fila seed id=1 ya existente, nueva fila AUTO_INCREMENT id=2):

```
php scripts/verify_lastinsertid_hypothesis.php
```

Resultado:

```
fila seed id (tenant 3 equivalente) = 1
fila nueva id (AUTO_INCREMENT)      = 2
last_value en BD                      = 1
PDO::lastInsertId()                   = 2    ← devuelve id AUTO_INCREMENT, no last_value
SELECT LAST_INSERT_ID()               = 2
ticket que generaría                  = 1-2026-000002
```

**Conclusión:** el primer pago usó el path `INSERT ... ON DUPLICATE KEY UPDATE` + `PDO::lastInsertId()`. Cuando `document_sequences.id=2` (porque id=1 ya existía para tenant 3), **`LAST_INSERT_ID()` devuelve 2** (el AUTO_INCREMENT del `id`), no el correlativo `last_value=1`. El ticket quedó `000002` con `last_value=1` persistido.

Eso explica por qué tenant 3 (`document_sequences.id=1`) tiene secuencia correcta (`last_value=2` con tickets 000001 y 000002), pero tenant 2 (`document_sequences.id=2`) quedó desfasado desde el primer pago.

---

## Respuestas a las 10 preguntas

| # | Pregunta | Respuesta con datos reales |
|---|----------|---------------------------|
| 1 | ¿last_value=1 tras 1.er pago? | **Sí** — `document_sequences.id=2 last_value=1` |
| 2 | ¿2.do pago intenta 000001 o 000002? | **000002** (escenario B) |
| 3 | ¿ON DUPLICATE KEY UPDATE funciona en BD? | Irrelevante en código actual (lockForUpdate); en el 1.er pago el INSERT sí escribió `last_value=1` |
| 4 | ¿LAST_INSERT_ID leído correctamente? | **No en el 1.er pago** — devolvió `2` (= `document_sequences.id`) |
| 5 | ¿Backfill OK? | Tenant 3 sí (`last_value=2`); tenant 2 no refleja ticket `000002` |
| 6 | ¿document_type = SETTLEMENT_PAYMENT? | **Sí** |
| 7 | ¿period_key = 2026? | **Sí** |
| 8 | ¿Mismos tenant/branch? | **Sí** — settlement y secuencia usan tenant=2, branch=2 |
| 9 | ¿UNIQUE nuevo aplicado? | **Sí** — `staff_settlements_ticket_scope_unique` |
| 10 | ¿Índice viejo global? | **No** — eliminado |

---

## Scripts de depuración usados (temporales)

```bash
php scripts/trace_mark_paid.php 3    # liquidación pendiente que falla
php scripts/trace_mark_paid.php 4    # mismo resultado
php scripts/verify_lastinsertid_hypothesis.php  # prueba MySQL id=2 → correlativo 2
```

No modifican código de producción. Usan ROLLBACK para no alterar datos en la simulación.

---

## Corrección de datos manual (solo diagnóstico, NO aplicada)

Para desbloquear pagos **sin cambiar código**, alinear secuencia con ticket existente:

```sql
UPDATE document_sequences
SET last_value = 2
WHERE tenant_id = 2 AND branch_id = 2
  AND document_type = 'SETTLEMENT_PAYMENT'
  AND period_key = '2026';
```

Tras eso, el siguiente `reserveNext()` devolvería **3** → ticket `1-2026-000003` (sin colisión).

**Nota:** esto es parche de datos. La corrección de código debe evitar que el correlativo dependa de `LAST_INSERT_ID()` / `PDO::lastInsertId()` cuando compite con AUTO_INCREMENT del `id`.

---

## Próximo paso (fuera de alcance de esta depuración)

Corregir `DocumentSequenceService` para que el valor retornado sea siempre `last_value` de la fila, no `PDO::lastInsertId()`. **No implementado en esta sesión** — pendiente de aprobación.
