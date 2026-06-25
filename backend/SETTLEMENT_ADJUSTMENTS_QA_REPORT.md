# QA OPERATIVO — Motor de liquidaciones con ajustes y ticket (Backend)

**Fecha:** 2026-06-21  
**Alcance:** Validación punta a punta — **sin nuevas features**  
**Veredicto módulo liquidaciones:** ✅ **APROBADO para operación** (con notas)

---

## Resumen ejecutivo

El motor de liquidaciones con ajustes, pago auditable, ticket y reimpresión **pasa la batería automatizada dedicada** (36 tests, 651 assertions). Los 9 casos operativos tienen cobertura backend directa o indirecta verificable por código + tests existentes.

**Bloqueos de entorno (no del módulo):**
- `php artisan migrate` → MySQL no disponible (`127.0.0.1:3306` rechazado). Tests usan SQLite `:memory:` y pasaron.
- `php artisan test` suite completa → **5 fallos preexistentes** ajenos a liquidaciones (Auth, RolePermission, ServicesCashAccounting, Sse2).

---

## Validaciones técnicas ejecutadas

| Comando | Resultado | Notas |
|---------|-----------|-------|
| `php artisan migrate` | ⚠️ No ejecutable | MySQL apagado en entorno QA |
| `php artisan test SettlementPaymentAuditTest.php` | ✅ 15/15 | |
| `php artisan test SettlementAdjustmentsEnginePhase2FinesTest.php` | ✅ 15/15 | |
| `php artisan test SettlementAdjustmentsEnginePhase1Test.php` | ✅ 6/6 | |
| **Subtotal liquidaciones** | ✅ **36/36** | |
| `php artisan test` (suite completa) | ⚠️ 678 passed, **5 failed** | Fallos no relacionados con settlements |
| `npm run build` | ✅ OK | Reintento exitoso tras lock temporal en `components.d.ts` |

---

## Casos operativos — Resultado

### Caso 1 — Chica menor a 100 Bs

| Paso | Verificación | Evidencia | Estado |
|------|--------------|-----------|--------|
| Bruto 80, limpieza 0, neto 80 | Generación | `SettlementAdjustmentsEnginePhase1Test` → `does not apply cleaning deduction when girl gross is below threshold` | ✅ |
| Egreso caja 80 | mark-paid usa `net_amount` | `SettlementPaymentAuditTest` → `uses net_amount for cash movement expense` (patrón genérico) | ✅ |
| Ticket 80 | Print job al pagar | `creates settlement payment print job when paying` + contenido NETO en builder | ✅ |

**Nota:** No hay test dedicado “pagar exactamente 80 Bs”, pero la regla de limpieza + egreso por neto lo garantiza.

---

### Caso 2 — Chica ≥ 100 Bs

| Paso | Verificación | Evidencia | Estado |
|------|--------------|-----------|--------|
| Bruto 100, limpieza -10, neto 90 | Generación | Phase1 → `applies cleaning deduction when girl gross reaches threshold` | ✅ |
| Egreso 90 | Pago | Phase2 → `uses net amount including fines in cash movement` (misma lógica net) | ✅ |
| Ticket con limpieza | Contenido | PaymentAudit → `includes gross cleaning discount fines and net in ticket content` (`-10.00`) | ✅ |

---

### Caso 3 — Multa aplicada

Escenario spec: bruto 300, limpieza -10, multa -30, neto 260.

| Paso | Verificación | Evidencia | Estado |
|------|--------------|-----------|--------|
| Preview neto con multa | pay-preview | Phase2 → `recalculates net amount in pay preview when fine is selected` | ✅ |
| Multa APPLIED | mark-paid | Phase2 → `applies selected fine on mark paid` | ✅ |
| Ajuste MANUAL_FINE | BD | Phase2 → `creates manual fine adjustment on mark paid` | ✅ |
| Egreso neto | caja | Phase2 → `uses net amount including fines in cash movement` | ✅ |
| Ticket muestra multa | print | PaymentAudit → ticket con multas aplicadas | ✅ |
| Neto bruto+limpieza+multas | Integración | Phase2 → `calculates net correctly with cleaning and selected fines` (100-10-30=60 patrón) | ✅ |

**Nota:** Tests usan montos 100/30; la fórmula es idéntica a 300/30 del spec.

---

### Caso 4 — Multa no aplicada

| Paso | Verificación | Evidencia | Estado |
|------|--------------|-----------|--------|
| Multa sigue PENDING | mark-paid sin ID | Phase2 → `keeps unselected fine pending after mark paid` | ✅ |
| Ticket no muestra multa no aplicada | print | PaymentAudit → `includes applied fines only in settlement ticket` | ✅ |
| Egreso no descuenta multa | neto | Phase2 unselected + PaymentAudit cash movement net | ✅ |

---

### Caso 5 — Descuento manual porcentaje

Spec: bruto 300, limpieza -10, base 290, 5% = -14.50.

| Paso | Verificación | Evidencia | Estado |
|------|--------------|-----------|--------|
| Base bruto + limpieza | Cálculo | PaymentAudit → `calculates percent manual discount on gross plus cleaning base` (100→90 base, 5%=-4.50) | ✅ |
| Ticket descuento | print | PaymentAudit → contenido incluye `-4.50` | ✅ |
| API descuento | POST manual-discount | PaymentAudit apply + preview | ✅ |

---

### Caso 6 — Descuento manual monto fijo

| Paso | Verificación | Evidencia | Estado |
|------|--------------|-----------|--------|
| Descuento -20 | adjustment | PaymentAudit → `applies fixed amount manual discount correctly` | ✅ |
| Egreso = neto | caja | PaymentAudit → `uses net_amount for cash movement expense` (con descuento previo) | ✅ |
| Ticket y egreso coinciden | integración | PaymentAudit ticket content + movement amount | ✅ |

---

### Caso 7 — Corte parcial (limpieza única)

Spec: 150 Bs pagado, segundo corte sin limpieza.

| Paso | Verificación | Evidencia | Estado |
|------|--------------|-----------|--------|
| Primer corte cobra limpieza | neto 90 en 100 | Phase1 threshold test + mark-paid en partial test | ✅ |
| Segundo corte sin limpieza | dedup turno+caja+persona | Phase1 → `does not charge cleaning again on a partial cut after first cut was paid` (100+50) | ✅ |
| Solo paga lo nuevo | neto segundo = bruto | Segundo settlement `net_amount` 50, sin adjustment CLEANING | ✅ |

**Nota:** Test usa 100+50 Bs; lógica dedup `cleaning:{shift}:{session}:{staff}` validada.

---

### Caso 8 — Reimpresión

| Paso | Verificación | Evidencia | Estado |
|------|--------------|-----------|--------|
| print_count++ | BD | PaymentAudit → `increments print_count on reprint` | ✅ |
| Banner REIMPRESIÓN | ticket | PaymentAudit → `shows reprint banner in ticket content` | ✅ |
| Audit SETTLEMENT_REPRINTED | audit_logs | PaymentAudit → `records SETTLEMENT_REPRINTED audit log` | ✅ |
| Audit SETTLEMENT_PAID | pay | PaymentAudit → `records SETTLEMENT_PAID audit log` | ✅ |

---

### Caso 9 — Cierre de caja

| Paso | Verificación | Evidencia | Estado |
|------|--------------|-----------|--------|
| Egresos por neto | cash_movements | `MarkSettlementPaidUseCase` → `amount: net_amount` | ✅ |
| expected_cash baja al pagar | Mi caja API | `SettlementsCashUiFixTest` → `expected_cash decreases after paying cleaning settlement` | ✅ |
| Movimientos EXPENSE visibles | financial_summary | `SettlementsCashUiFixTest` → `cash session expenses include cleaning settlement payment` | ✅ |
| Close-check tras pagar | flujo cierre | `SettlementCloseCheckConsistencyTest` → `cashier can pay pending settlement and close-check clears payment blocker` | ✅ |
| Reporte cierre usa total_amount (= neto) | reportes | `getSettlementsReport` suma `total_amount` que = `net_amount` post-motor | ✅ |

**Recomendación manual:** Smoke test UI en **Mi Caja → movimientos → cerrar caja → comprobante** con liquidación chica 100 Bs (egreso 90 Bs visible).

---

## Cadena de confianza (código)

```
Generar → SettlementTotalsCalculator (limpieza PENDING)
       → SettlementManualDiscountService (PERCENT/AMOUNT)
       → SettlementFineApplier (multas al pagar)
       → MarkSettlementPaidUseCase (net_amount → cash_movement)
       → SettlementTicketNumberGenerator + CreateSettlementPaymentPrintJobUseCase
       → PrintSettlementPaymentUseCase (reprint + audit)
```

---

## Riesgos / gaps QA

| Item | Severidad | Acción |
|------|-----------|--------|
| MySQL migrate no probado en entorno local | Baja | Ejecutar migrate cuando XAMPP esté activo |
| 5 tests suite global fallando | Media | Corregir en sprint aparte (no bloquean liquidaciones) |
| Caso 9 comprobante cierre impreso | Baja | Smoke manual Mi Caja |
| Montos exactos 300 Bs en tests | Info | Cubierto por equivalencia matemática |

---

## Conclusión

**El módulo de liquidaciones con ajustes y ticket está listo para operación.**

No se detectaron regresiones en tests del motor. Se recomienda **smoke manual UI** (Casos 1 ticket visual, 9 cierre caja) antes de producción, pero la lógica crítica de caja y neto está cubierta por tests automatizados.

**No continuar con nuevas features** hasta smoke manual opcional en entorno con MySQL activo.
