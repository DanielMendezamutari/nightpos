# Auditoría — Numeración documental (ticket_number y correlativos)

**Fecha:** 2026-06-28  
**Síntoma:** HTTP 500 al pagar liquidación  

```text
SQLSTATE[23000]: Duplicate entry '1-2026-000001'
for key 'staff_settlements_ticket_number_unique'
```

**Stack:** `MarkSettlementPaidUseCase` → `SettlementTicketNumberGenerator::next()` → `EloquentStaffSettlementRepository::markPaid()`  
**Alcance:** auditoría arquitectónica — **sin implementación**

---

## 1. Conclusión ejecutiva

NightPOS **no tiene un mecanismo único de numeración documental**. Cada tipo de documento implementa su propio “siguiente número” con patrones distintos, sin tabla de secuencias, sin `SELECT … FOR UPDATE`, y sin reserva atómica del correlativo.

El error concreto en liquidaciones combina **al menos dos defectos de diseño**:

| Defecto | Impacto |
|---------|---------|
| **Generador no atómico** (`MAX`/`ORDER BY` + insert posterior) | Dos pagos concurrentes pueden calcular el mismo número |
| **UNIQUE global en `ticket_number`** | Índice sobre la columna sola; el generador filtra solo por `branch_id` → colisión entre sucursales con mismo `branch.code` (ej. `"1"`) |

El formato `1-2026-000001` indica sucursal con **código `"1"`**, año **2026**, secuencia **000001** — no es un “reinicio” misterioso del contador, sino el **primer correlativo del patrón** cuando el generador cree que no hay anterior **visible para esa sucursal**.

---

## 2. Mapa de numeración en el sistema

| Documento | Campo | Generador | Ubicación | UNIQUE DB | Año | Tenant | Sucursal |
|-----------|-------|-----------|-----------|-----------|-----|--------|----------|
| **Liquidación pagada** | `ticket_number` | `SettlementTicketNumberGenerator` | `{CODE}-{YYYY}-{000001}` | **Global** (`ticket_number`) | Sí | Implícito vía branch | Filtro lectura |
| **Comanda** | `order_number` | `EloquentOrderRepository::nextOrderNumber` | `C-0001` | `(branch_id, order_number)` | No | vía branch | Sí |
| **Venta** | `sale_number` | `EloquentSaleRepository::nextSaleNumber` | `V-0001` | `(branch_id, sale_number)` | No | vía branch | Sí |
| **Corte liquidación (UI)** | `cut_number` | Calculado al vuelo | ordinal por shift+staff+tipo | No persistido | — | — | — |
| **Print job** | `idempotency_key` | UUID/hash operación | no es folio | `(tenant, branch, key)` | — | Sí | Sí |
| **Caja / movimientos** | — | Sin folio documental | solo `id` | — | — | — | — |
| **Cierre caja** | — | Sin ticket_number | — | — | — | — | — |

**No existe:** tabla `document_sequences`, servicio `SequenceService`, Redis/cache de contadores, ni migración de series/folios.

---

## 3. Flujo que falla — liquidaciones

### 3.1 Orquestación (`MarkSettlementPaidUseCase`)

```
1. Validar permisos, status != PAID
2. DB::transaction {
     a. requireOpenCashSession
     b. applySelectedFines
     c. addMovement (EXPENSE)
     d. ticketNumber = SettlementTicketNumberGenerator::next(branchId)  ← aquí
     e. markPaid(..., ticketNumber)  ← UPDATE unique falla aquí
   }
3. createPrintJob (fuera del rollback del ticket si fallara antes — hoy falla dentro)
```

- Hay **transacción**, pero el generador **no lockea filas** ni reserva número.
- Guard `status === 'PAID'` evita **re-pago** de la misma liquidación; **no** evita colisión entre **dos liquidaciones distintas** pagadas a la vez.

### 3.2 Generador (`SettlementTicketNumberGenerator::next`)

```php
$prefix = strtoupper(branch.code ?: 'B'.$branchId);
$year   = (int) now()->format('Y');
$pattern = $prefix.'-'.$year.'-%';

$lastTicket = StaffSettlementModel::query()
    ->where('branch_id', $branchId)
    ->whereNotNull('ticket_number')
    ->where('ticket_number', 'like', $pattern)
    ->orderByDesc('ticket_number')
    ->value('ticket_number');

$sequence = 1;
if (preg_match('/-(\d{6})$/', $lastTicket, $matches)) {
    $sequence = (int) $matches[1] + 1;
}
return sprintf('%s-%d-%06d', $prefix, $year, $sequence);
```

| Pregunta | Respuesta |
|----------|-----------|
| ¿Lee MAX()? | Equivalente: `ORDER BY ticket_number DESC LIMIT 1` (orden lexicográfico, OK con `%06d`) |
| ¿Tabla secuencias? | **No** |
| ¿Cache? | **No** |
| ¿Transacción propia? | **No** — corre dentro de la del use case pero sin lock |
| ¿FOR UPDATE? | **No** |
| ¿Dos procesos mismo número? | **Sí** — ventana clásica read-modify-write |
| ¿Depende año? | **Sí** — reinicia a `000001` cada enero |
| ¿Depende tenant? | **No en generador** — solo `branch_id` |
| ¿Depende sucursal? | **Sí** en lectura; **No** en UNIQUE |
| ¿Depende tipo documento? | Formato fijo liquidación pagada |

### 3.3 Restricción DB (migración `2026_06_21_100094`)

```php
$table->unique('ticket_number', 'staff_settlements_ticket_number_unique');
```

- **UNIQUE global** en toda la tabla `staff_settlements`.
- **No** es `(tenant_id, branch_id, ticket_number)` ni `(branch_id, year, seq)`.
- Dos sucursales con `code = "1"` (en distintos tenants, permitido por `unique(tenant_id, code)`) generan el **mismo string** `1-2026-000001`.

### 3.4 Persistencia (`markPaid`)

```php
$model->update([
    'status' => 'PAID',
    // ...
    'ticket_number' => $ticketNumber,
]);
```

El 500 ocurre en este `UPDATE`/`INSERT` cuando MySQL rechaza duplicado.

---

## 4. Por qué intenta otra vez `1-2026-000001`

Escenarios ordenados por probabilidad:

### A. Condición de carrera (misma sucursal)

Dos POST `mark-paid` concurrentes (dos cajeras, doble click, retry automático):

1. Tx A: `next()` → no ve fila committed → `1-2026-000001`
2. Tx B: `next()` → tampoco ve → `1-2026-000001`
3. A commit OK; B → **Duplicate entry**

El generador **no ve** filas uncommitted de la otra transacción (aislamiento READ COMMITTED).

### B. Colisión cross-sucursal (mismo `branch.code`)

1. Sucursal A (`code=1`, id=5) ya pagó → `1-2026-000001` existe.
2. Sucursal B (`code=1`, id=8, otro tenant) → generador mira solo `branch_id=8`, no encuentra tickets → propone `1-2026-000001`.
3. **UNIQUE global** → 500.

### C. Reinicio lógico de año (normal, no es este bug)

Enero 2026: patrón `1-2026-%` vacío → `000001` aunque exista `1-2025-000099`. **No colisiona** (strings distintos).

### D. Migración / datos legacy

Si existía fila con `1-2026-000001` insertada manualmente o por test, y el generador no la encuentra (branch_id distinto, `ticket_number` NULL en filas viejas), volvería a proponer `000001`. Menos probable en producción.

### E. Reintento tras error

Si la transacción **completa** falla, rollback elimina el ticket → reintento legítimo vuelve a pedir `000001`. Eso es correcto **salvo** que otra tx ya hubiera commitado el mismo número (escenario A).

---

## 5. Otros documentos — riesgo de duplicado

### 5.1 Comandas (`order_number`)

```php
// EloquentOrderRepository::nextOrderNumber
$last = OrderModel::query()
    ->where('branch_id', $branchId)
    ->where('order_number', 'like', 'C-%')
    ->orderByDesc('id')  // nota: por id, no por número
    ->value('order_number');
return 'C-'.str_pad((int)preg_replace('/\D/', '', $last) + 1, 4, '0', STR_PAD_LEFT);
```

| Aspecto | Estado |
|---------|--------|
| UNIQUE | `(branch_id, order_number)` ✓ acotado |
| Transacción | `CreateOrderUseCase` **sin** `DB::transaction` envolviendo next+create |
| FOR UPDATE | No |
| Riesgo duplicado | **Sí** bajo concurrencia (misma clase de bug) |
| Lógica distinta | Usa `orderByDesc('id')` no `order_number` — puede desincronizarse si hay imports/updates |

Usado en: `CreateOrderUseCase`, `OpenWaiterTableUseCase`, `CreateRoomServiceUseCase`.

### 5.2 Ventas (`sale_number`)

```php
// EloquentSaleRepository::nextSaleNumber — misma idea que comandas
->orderByDesc('id')->value('sale_number');
return 'V-'.str_pad(..., 4, '0', ...);
```

| Aspecto | Estado |
|---------|--------|
| UNIQUE | `(branch_id, sale_number)` ✓ |
| Transacción | `ChargeOrderUseCase` / `CreateDirectSaleUseCase` usan transacción en creación venta |
| FOR UPDATE | No |
| Riesgo duplicado | **Sí**, mitigado parcialmente por tx pero **sin lock de secuencia** |

### 5.3 Impresión

- `print_jobs.idempotency_key`: evita duplicar **mismo job**, no asigna folio visible.
- Tickets muestran `order_number`, `sale_number`, `ticket_number` — heredan numeración de origen.

### 5.4 Liquidaciones — otros números

- `cut_number`: ordinal calculado (`resolveCutNumber` / `computeCutNumbers`) — **no es folio legal**, no UNIQUE.
- No hay `settlement_number` aparte de `ticket_number` al pagar.

---

## 6. Respuestas a las 15 preguntas

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Quién genera `ticket_number`? | `SettlementTicketNumberGenerator`, invocado desde `MarkSettlementPaidUseCase` |
| 2 | ¿Cómo obtiene el siguiente? | Lee último `ticket_number` LIKE `{CODE}-{YEAR}-%` por `branch_id`, parsea 6 dígitos, +1 |
| 3 | ¿Lee MAX()? | Equivalente (`ORDER BY ticket_number DESC`) |
| 4 | ¿Tabla secuencias? | **No existe** |
| 5 | ¿Cache? | **No** |
| 6 | ¿Transacciones? | Sí en mark-paid, **pero generador sin lock** |
| 7 | ¿SELECT FOR UPDATE? | **No** en ningún generador |
| 8 | ¿Dos procesos mismo número? | **Sí** |
| 9 | ¿Migraciones reiniciaron contador? | Columna nueva jun 2026; liquidaciones PAID previas sin ticket → primer pago pide `000001`. No explica duplicado salvo carrera/cross-branch |
| 10 | ¿Depende del año? | **Sí** — segmento `{YYYY}` en formato |
| 11 | ¿Depende del tenant? | Lectura: no explícito. UNIQUE: **global** (problemático) |
| 12 | ¿Depende de sucursal? | Lectura: **sí** (`branch_id`). UNIQUE: **no** |
| 13 | ¿Depende del tipo documento? | Solo liquidación pagada usa este formato |
| 14 | ¿Riesgo en ventas/comandas? | **Sí** — mismo anti-patrón; UNIQUE por branch reduce cross-branch pero no concurrencia |
| 15 | ¿Por qué otra vez `1-2026-000001`? | Generador cree que no hay previo **para esa sucursal** OR carrera concurrente OR colisión **global** con otra sucursal code `"1"` |

---

## 7. Comparativa de implementaciones (deuda técnica)

```
Liquidaciones:  ORDER BY ticket_number DESC + LIKE + regex
Ventas:         ORDER BY id DESC + strip non-digits
Comandas:       ORDER BY id DESC + strip non-digits + prefix C-
```

Tres algoritmos distintos, cero abstracción compartida.

---

## 8. Arquitectura objetivo recomendada (futuro)

Un **único servicio de secuencias documentales**, reutilizable:

### 8.1 Tabla propuesta (conceptual)

```sql
document_sequences (
  tenant_id,
  branch_id,
  document_type,   -- SETTLEMENT_TICKET | SALE | ORDER | ...
  period_key,      -- '2026' o 'GLOBAL'
  last_value,
  PRIMARY KEY (tenant_id, branch_id, document_type, period_key)
)
```

### 8.2 Reserva atómica

Dentro de la misma transacción del use case:

```sql
SELECT last_value FROM document_sequences
WHERE ... FOR UPDATE;

UPDATE document_sequences SET last_value = last_value + 1;
-- formatear según reglas del document_type
```

Alternativa: `INSERT ... ON DUPLICATE KEY UPDATE last_value = LAST_INSERT_ID(last_value + 1)`.

### 8.3 Reglas UNIQUE alineadas

| Documento | UNIQUE recomendado |
|-----------|-------------------|
| Settlement ticket | `(tenant_id, branch_id, ticket_number)` o incluir ticket en PK compuesta |
| Sale | mantener `(branch_id, sale_number)` — branch ya implica tenant |
| Order | mantener `(branch_id, order_number)` |

### 8.4 Formato

Separar **motor de secuencia** (entero atómico) de **presentación** (`SettlementTicketFormatter` → `1-2026-000001`).

---

## 9. Fix inmediato vs arquitectura (orientación, no implementado)

| Enfoque | Tipo | Notas |
|---------|------|-------|
| Cambiar UNIQUE a `(tenant_id, branch_id, ticket_number)` | Migración | Corrige colisión cross-sucursal mismo code |
| `lockForUpdate()` en última fila / fila secuencia | Código | Mitiga carrera en una sucursal |
| Tabla `document_sequences` | Arquitectura | Solución definitiva multi-documento |
| “Incrementar +1 a mano” sin lock | Parche | **Insuficiente** bajo concurrencia |

---

## 10. Verificación manual sugerida

En hosting/DB al momento del 500:

```sql
-- ¿Ya existe el ticket?
SELECT id, tenant_id, branch_id, status, ticket_number, paid_at
FROM staff_settlements
WHERE ticket_number = '1-2026-000001';

-- ¿Otra sucursal con mismo code?
SELECT b.id, b.tenant_id, b.code
FROM branches b
WHERE UPPER(b.code) = '1';

-- Tickets 2026 sucursal afectada
SELECT ticket_number, branch_id, paid_at
FROM staff_settlements
WHERE ticket_number LIKE '%-2026-%'
ORDER BY ticket_number;
```

Si fila (1) existe con **otro** `branch_id` que el pago actual → confirma hipótesis **B**.  
Si misma sucursal y timestamps simultáneos → hipótesis **A**.

---

## 11. Tests existentes

`SettlementPaymentAuditTest::generates consecutive ticket numbers per branch and year` — secuencial **en un solo proceso**, no cubre concurrencia ni cross-branch same code.

---

## 12. Archivos clave

| Archivo | Rol |
|---------|-----|
| `Application/StaffSettlement/Services/SettlementTicketNumberGenerator.php` | Generador ticket |
| `Application/StaffSettlement/UseCases/MarkSettlementPaidUseCase.php` | Orquestación pago |
| `Infrastructure/.../EloquentStaffSettlementRepository.php` | `markPaid()` |
| `database/migrations/2026_06_21_100094_settlement_payment_auditable.php` | UNIQUE global |
| `Infrastructure/.../EloquentOrderRepository.php` | `nextOrderNumber()` |
| `Infrastructure/.../EloquentSaleRepository.php` | `nextSaleNumber()` |

---

## 13. Relacionados

- `SETTLEMENT_PAYMENT_AUDITABLE_IMPLEMENTATION_REPORT.md`
- `SETTLEMENT_PAYMENT_AUDIT.md`
- `PARTIAL_SETTLEMENTS_AUDIT.md`
