# Document Sequence Service — Fix final

**Fecha:** 2026-06-30  
**Estado:** Implementado  
**Archivo principal:** `app/Application/DocumentSequence/Services/DocumentSequenceService.php`

---

## Problema (probado en base real)

1. `PDO::lastInsertId()` / `LAST_INSERT_ID()` devolvía el **AUTO_INCREMENT `id`** de `document_sequences` (ej. `2`), no el correlativo `last_value` (`1`).
2. Primer pago guardaba ticket `1-2026-000002` con `last_value=1`.
3. Pagos siguientes calculaban `1+1=2` → colisión → **HTTP 409** en bucle (rollback revertía el incremento).

Ver depuración: `DOCUMENT_SEQUENCE_409_DEBUG_REPORT.md`

---

## Solución

### Flujo único confiable (`lockForUpdate`)

1. Dentro de la transacción del use case (sin abrir TX propia).
2. `SELECT ... FOR UPDATE` por `(tenant_id, branch_id, document_type, period_key)`.
3. Si no existe fila → `INSERT last_value = 0` → re-leer con lock (manejo de race).
4. **Reconciliar** si `last_value` está atrasado respecto a tickets reales (`SETTLEMENT_PAYMENT`).
5. `next = last_value + 1` → `UPDATE` → **retornar `next`** (valor guardado, leído del modelo).

**Prohibido:** `PDO::lastInsertId()`, `LAST_INSERT_ID()` en SQL, `INSERT ... ON DUPLICATE KEY UPDATE` para reservar.

### Reconciliación automática

Antes de incrementar, para liquidaciones:

```php
$maxIssued = maxSettlementTicketSequence($tenantId, $branchId, $periodKey);
if ($last_value < $maxIssued) {
    $last_value = $maxIssued;
}
$next = $last_value + 1;
```

Ejemplo producción:

| Estado | Valor |
|--------|-------|
| Ticket existente | `1-2026-000002` |
| `document_sequences.last_value` | `1` (desfasado) |
| Tras reconciliar | `last_value = 2` |
| Siguiente reserva | **`3`** → ticket `1-2026-000003` |

---

## Tests

```bash
php artisan test tests/Feature/Api/V1/DocumentSequenceSettlementTest.php
php artisan test tests/Unit/Application/DocumentSequence/DocumentSequenceServiceTest.php
php artisan test tests/Feature/Api/V1/SettlementPaymentAuditTest.php
```

**33 tests OK**, incluyendo:

- Secuencia atrasada (`last_value=1`, ticket `000002` existente) → reserva `3`
- Pago API sin 409 en escenario desfasado → ticket `000003`
- Primera fila nueva parte de `last_value=0` → retorna `1`

---

## Deploy

1. Subir `DocumentSequenceService.php`.
2. **No requiere migración.**
3. En hosting, el fix auto-repara al pagar la siguiente liquidación (reconciliación en caliente).
4. Opcional — alinear manualmente antes del deploy:

```sql
UPDATE document_sequences ds
JOIN (
    SELECT tenant_id, branch_id,
           MAX(CAST(SUBSTRING_INDEX(ticket_number, '-', -1) AS UNSIGNED)) AS max_seq
    FROM staff_settlements
    WHERE ticket_number REGEXP '-[0-9]{4}-[0-9]{6}$'
    GROUP BY tenant_id, branch_id
) t ON t.tenant_id = ds.tenant_id AND t.branch_id = ds.branch_id
SET ds.last_value = t.max_seq
WHERE ds.document_type = 'SETTLEMENT_PAYMENT'
  AND ds.period_key = '2026';
```

(Ajustar `period_key` según año.)

---

## Relacionados

- `DOCUMENT_NUMBER_SEQUENCE_AUDIT.md`
- `DOCUMENT_SEQUENCE_FIX_REPORT.md`
- `DOCUMENT_SEQUENCE_409_FIX_REPORT.md`
- `DOCUMENT_SEQUENCE_409_DEBUG_REPORT.md`
