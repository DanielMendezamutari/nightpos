# Document Sequence 409 Fix — Segunda liquidación duplicada

**Fecha:** 2026-06-30  
**Estado:** Corregido  
**Síntoma en producción:** primera liquidación OK (`1-2026-000001`); segunda devuelve **HTTP 409** — *No se pudo asignar el número de comprobante. Intente nuevamente.*

---

## Diagnóstico

### Qué pasaba en hosting

| Paso | `document_sequences.last_value` (BD) | Valor devuelto por `reserveNext()` | Ticket generado |
|------|--------------------------------------|-------------------------------------|-----------------|
| 1.er pago | `1` (correcto) | `1` | `1-2026-000001` ✓ |
| 2.do pago | `2` (UPDATE en BD correcto) | **`1`** (incorrecto) | `1-2026-000001` → colisión → 409 |

La tabla **sí incrementaba** en MySQL. El bug estaba en **cómo se leía** el correlativo devuelto al PHP.

### Causa raíz

En producción (MySQL) el servicio usaba:

```sql
INSERT ... ON DUPLICATE KEY UPDATE last_value = LAST_INSERT_ID(last_value + 1)
```

y luego:

```php
return (int) DB::getPdo()->lastInsertId();
```

**Problema:** `PDO::lastInsertId()` devuelve el `AUTO_INCREMENT` del `id` de la fila insertada, **no** el valor asignado por `LAST_INSERT_ID(expr)` en el branch `ON DUPLICATE KEY UPDATE`.

- **Primer pago (INSERT):** `id = 1` y secuencia `1` → coinciden por casualidad.
- **Segundo pago (UPDATE):** MySQL actualiza `last_value = 2`, pero `lastInsertId()` sigue devolviendo `1` (o `0`) → se reutiliza `000001` → UNIQUE `(tenant_id, branch_id, ticket_number)` → 409 controlado.

Los tests locales **no detectaron** esto porque SQLite usaba `SELECT FOR UPDATE` (path distinto al de MySQL).

---

## Respuestas a las 10 preguntas de auditoría

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿`last_value` quedó en 1 tras el 1.er pago? | **Sí** — esperado. |
| 2 | ¿El 2.do pago intenta 000001 u 000002? | Intentaba **000001** otra vez (bug de lectura, no de BD). |
| 3 | ¿El `ON DUPLICATE KEY UPDATE` funciona? | **Sí** — `last_value` en BD pasa a 2. |
| 4 | ¿`LAST_INSERT_ID` se lee bien? | **No** — se usaba `PDO::lastInsertId()` en lugar de `SELECT LAST_INSERT_ID()`. |
| 5 | ¿Backfill de migración OK? | **Sí** — no es la causa del 409 en 2.do pago consecutivo. |
| 6 | ¿`document_type = SETTLEMENT_PAYMENT`? | **Sí** — enum `DocumentSequenceType::SettlementPayment`. |
| 7 | ¿`period_key` = año actual? | **Sí** — `now()->format('Y')` → `2026`. |
| 8 | ¿Mismos `tenant_id` / `branch_id`? | **Sí** — `MarkSettlementPaidUseCase` pasa `$tenant->id, $branch->id`. |
| 9 | ¿UNIQUE nuevo aplicado? | **Sí** — `(tenant_id, branch_id, ticket_number)`. |
| 10 | ¿Quedó índice viejo global? | **No debe quedar** — migración lo elimina. Verificar en hosting con SQL abajo. |

### SQL de verificación en producción

```sql
-- Tickets pagados
SELECT id, tenant_id, branch_id, status, ticket_number, paid_at
FROM staff_settlements
WHERE ticket_number IS NOT NULL
ORDER BY id DESC;

-- Secuencias
SELECT *
FROM document_sequences
ORDER BY tenant_id, branch_id, document_type, period_key;

-- Índices en staff_settlements
SHOW INDEX FROM staff_settlements WHERE Key_name LIKE '%ticket%';
```

**Esperado tras el fix y un pago de prueba:**

- `document_sequences.last_value = 2` si ya hay dos tickets pagados en esa sucursal/año.
- No debe existir `staff_settlements_ticket_number_unique`.
- Debe existir `staff_settlements_ticket_scope_unique` sobre `(tenant_id, branch_id, ticket_number)`.

---

## Corrección aplicada

**Archivo:** `app/Application/DocumentSequence/Services/DocumentSequenceService.php`

Se eliminó el path MySQL con `INSERT ... ON DUPLICATE KEY UPDATE` + `PDO::lastInsertId()`.

**Un solo path transaccional** para todos los drivers:

1. `SELECT ... FOR UPDATE` sobre la fila de secuencia.
2. Si existe → `last_value + 1`, update, return.
3. Si no existe → INSERT `last_value = 1` (con retry en race de concurrencia).

Este path ya estaba probado en tests (SQLite) y es el que debe ejecutarse también en MySQL dentro de la transacción de `MarkSettlementPaidUseCase`.

`SettlementTicketNumberGenerator` **no** usa `MAX(ticket_number)` — delega en `DocumentSequenceService`.

---

## Resultado esperado tras deploy

| Pago | Ticket |
|------|--------|
| 1.º | `1-2026-000001` |
| 2.º | `1-2026-000002` |
| 3.º | `1-2026-000003` |

---

## Deploy

1. Subir cambio de `DocumentSequenceService.php`.
2. **No requiere nueva migración.**
3. Verificar con SQL que `document_sequences.last_value` coincide con el máximo ticket pagado.
4. Si `last_value` quedó desincronizado por intentos fallidos, corregir manualmente:

```sql
-- Ejemplo: si ya hay 1-2026-000001 pagado y last_value sigue en 1, está OK.
-- Tras pagar el 2.º con el fix, last_value debe pasar a 2.
UPDATE document_sequences
SET last_value = 1
WHERE document_type = 'SETTLEMENT_PAYMENT'
  AND period_key = '2026'
  AND tenant_id = ? AND branch_id = ?;
-- (solo si hace falta alinear; normalmente no tras un solo pago exitoso)
```

---

## Tests

```bash
php artisan test tests/Feature/Api/V1/DocumentSequenceSettlementTest.php
php artisan test tests/Feature/Api/V1/SettlementPaymentAuditTest.php
```

29 tests OK — incluye 2.do pago → `000002`.

---

## Relacionados

- `DOCUMENT_NUMBER_SEQUENCE_AUDIT.md`
- `DOCUMENT_SEQUENCE_FIX_REPORT.md`
