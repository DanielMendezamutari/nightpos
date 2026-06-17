# Auditoría — Combos, unidades internas y manillas multichica

**Fecha:** 2026-06-16  
**Estado:** ✅ **IMPLEMENTADO** (CBA-1…CBA-6) — ver reports de implementación  
**Alcance original:** Análisis de código y esquema existente.  
**Regla de negocio:** Un combo con N manillas obliga a repartir exactamente N unidades entre una o más chicas antes de enviar a barra / cobrar, y a liquidar por chica según su parte.

---

## Resumen ejecutivo

El sistema actual modela ventas con acompañante como **una línea = una chica = un monto fijo de liquidación** (`order_items.girl_user_id`, `sale_items.girl_amount_snapshot`, liquidación `GIRL_CONSUMPTION` por `sale_item_id`).

Eso cubre Paceña/Huari con `CON_ACOMPANANTE` simple, pero **no soporta** combos tipo “Balde 6 cervezas” donde:

- El precio es **por combo** (línea),
- Las **manillas internas** (6) deben repartirse entre 1–N chicas,
- La liquidación debe ser **por chica y por unidades asignadas**, no un solo pago.

La extensión recomendada introduce **comportamiento de producto declarativo** (no `if name contains "combo"`) y tablas **`order_item_allocations` / `sale_item_allocations`**, con un nuevo `source_type` de liquidación distinto de `GIRL_CONSUMPTION` y de `GIRL_BRACELET` (manilla manual legacy).

---

## 1. Estado actual

### 1.1 Catálogo (`products`, `product_prices`)

| Campo / concepto | Estado |
|------------------|--------|
| `products.product_type` | Existe (`beverage`, `service`, `food`). **No hay semántica combo.** |
| `is_combo`, `internal_units`, `bracelet_units` | **No existen** |
| `requires_allocation`, `allocation_type` | **No existen** |
| `product_prices.sale_mode` | `SOLO_CLIENTE` \| `CON_ACOMPANANTE` |
| `product_prices.price` | Precio **por unidad de línea** (1 combo = 1 unidad de cantidad) |
| `product_prices.girl_amount` | Monto chica **por línea de catálogo**, copiado tal cual al ítem |
| `product_prices.house_amount` | Resto casa **por línea** |

Entidad de dominio `Product` solo expone `productType`, `unit`, etc. — sin flags de liquidación.

`ProductPriceResolver` resuelve precio activo por tenant/producto/sucursal/modalidad. **Sin lógica de unidades internas.**

### 1.2 Comandas (`orders`, `order_items`)

```32:51:backend/database/migrations/2026_06_03_100006_create_orders_tables.php
        Schema::create('order_items', function (Blueprint $table) {
            // ...
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('girl_amount', 12, 2)->nullable();
            $table->decimal('house_amount', 12, 2)->nullable();
            $table->foreignId('girl_user_id')->nullable()->constrained('users')->nullOnDelete();
            // ...
        });
```

- **Un solo** `girl_user_id` por línea.
- **No hay** tabla de allocations.
- `girl_amount` se guarda desde catálogo **sin multiplicar por `quantity`** en `AddOrderItemUseCase` (mismo valor si qty=1 o qty=2).

### 1.3 Ventas (`sales`, `sale_items`)

Misma forma que `order_items`: snapshot de precio, `girl_user_id` único, `girl_amount_snapshot` único. Sin desglose por chica.

### 1.4 Use cases relevantes

| Use case | Comportamiento actual |
|----------|----------------------|
| `AddOrderItemUseCase` | Precio × qty; `girlUserId` opcional; sin validación de reparto |
| `UpdateOrderItemUseCase` | Recalcula pricing; permite cambiar chica en SENT_TO_BAR; sin allocations |
| `AssignOrderItemGirlUseCase` | Asigna **una** chica a **un** ítem CON_ACOMPANANTE |
| `SendOrderToBarUseCase` | Bloquea si `CON_ACOMPANANTE && girl_user_id === null` |
| `ChargeOrderUseCase` | Misma validación; copia ítems a `sale_items` 1:1 |
| `CreateDirectSaleUseCase` | Exige `girl_user_id` por ítem CON_ACOMPANANTE; sin allocations |
| `OrderItemPricing` | `line_total = price × quantity`; `girl_amount` fijo del catálogo |

### 1.5 Liquidaciones (`staff_settlements`, `staff_settlement_items`)

Consumo con acompañante (venta):

```174:207:backend/app/Infrastructure/Persistence/Eloquent/Repositories/EloquentStaffSettlementRepository.php
                if ($girlAmount > 0 && $line->girl_user_id && $line->sale_mode === 'CON_ACOMPANANTE') {
                    if (! $this->saleItemAlreadySettled((int) $line->id, 'GIRL_CONSUMPTION')) {
                        // ... un ítem de liquidación por sale_item_id
                    }
                }
```

Constraints de deduplicación:

- `(sale_item_id, source_type)` — **máximo 1** liquidación `GIRL_CONSUMPTION` por línea vendida.
- `(source_id, source_type)` — usado para manillas manuales, piezas, shows, etc.

Manillas **manuales** (`bracelets`): `GIRL_BRACELET` con `source_id = bracelet.id`. Tabla independiente de comandas/ventas.

**No existe** generación de liquidación por unidad repartida dentro de una misma línea.

### 1.6 Manillas legacy vs consumo

| Fuente | Tabla / origen | `source_type` | Uso |
|--------|----------------|---------------|-----|
| Venta CON_ACOMPANANTE simple | `sale_items` | `GIRL_CONSUMPTION` | Automático al generar liquidaciones |
| Manilla manual | `bracelets` | `GIRL_BRACELET` | Registro excepcional por cajera/admin |
| Combo multichica (requerido) | **No implementado** | — | — |

Documentación previa (Fase 14–16) ya separó consumo de venta vs manilla manual. **Combos deben usar una tercera vía**, no mezclar con `bracelets`.

### 1.7 Reportes y conciliación

`EloquentReportReadRepository::productReconciliation()` agrupa por `product_id` y `quantity` en `sale_items` / `order_items`. **No expone manillas ni reparto por chica.**

Reportes de chicas / liquidaciones leen `girl_user_id` y montos a nivel línea.

---

## 2. Por qué el modelo actual no alcanza

### 2.1 Cardinalidad

`order_item.girl_user_id` es **0..1 chica**. Los casos 2–4 exigen **N filas (chica × unidades)** por una sola línea de combo.

Workarounds inviables:

- **Partir el combo en 6 líneas** — rompe precio único, UX, conciliación y auditoría.
- **Duplicar productos** (“Cerveza combo María”) — explosión de catálogo, no Open/Closed.
- **Usar `bracelets` manuales** — fuente distinta, sin vínculo a comanda/cobro, doble contabilidad.

### 2.2 Validación de suma exacta

No hay estructura donde persistir ni validar `sum(units) = bracelet_units × quantity`.

### 2.3 Liquidación

Un `sale_item` con `girl_amount_snapshot = 60` y tres chicas (3+2+1) no puede generar tres pagos con el constraint `(sale_item_id, GIRL_CONSUMPTION)`.

### 2.4 Precio chica vs unidades

Hoy `girl_amount` es **por línea de catálogo**, no por manilla. Para combo 6:

- Si `girl_amount = 30` Bs total del combo → cada manilla = 5 Bs (derivado).
- Si se quiere 5 Bs/manilla fijo → total línea = 30 Bs (6 × 5).

Hay que **definir y congelar** la regla en catálogo y snapshot, no inferirla en UI.

### 2.5 Cantidad de combos

2 combos × 6 manillas = **12** unidades obligatorias. El modelo actual ni escala `girl_amount` con `quantity` ni tiene contador de manillas.

---

## 3. Propuesta de modelo de datos

### 3.1 Principio Open/Closed

Introducir **comportamiento declarativo en producto**, interpretado por servicios de dominio:

```
ProductSettlementProfile (valor o JSON en products)
├── settlement_behavior: SIMPLE | GIRL_LINE | GIRL_BRACELET_ALLOCATION | NONE | ...
├── bracelet_units_per_line: int (default 1)
├── requires_allocation: bool
├── allocation_type: GIRL_BRACELET_UNITS | ... (extensible)
└── allowed_sale_modes: [SOLO_CLIENTE, CON_ACOMPANANTE]
```

**No** usar `product_type = combo` como único discriminador; `product_type` puede ser `beverage` con `settlement_behavior = GIRL_BRACELET_ALLOCATION`.

Campos sugeridos en `products` (migración futura):

| Campo | Tipo | Ejemplo Combo 6 cervezas |
|-------|------|--------------------------|
| `settlement_behavior` | string | `GIRL_BRACELET_ALLOCATION` |
| `bracelet_units_per_line` | unsigned int | `6` |
| `requires_allocation` | bool | `true` |
| `allocation_type` | string | `GIRL_BRACELET_UNITS` |

Producto simple CON_ACOMPANANTE: `settlement_behavior = GIRL_LINE`, `bracelet_units_per_line = 1`, `requires_allocation = false` (compatibilidad).

### 3.2 Precios (`product_prices`)

Mantener precio total por modalidad:

| Modalidad | Combo 6 |
|-----------|---------|
| `SOLO_CLIENTE` | Opcional (si aplica venta sin chica) |
| `CON_ACOMPANANTE` | Precio total del combo (ej. 120 Bs) |

Montos chica/casa **por unidad de combo (qty=1)**:

- `girl_amount` = total que corresponde a chicas por **1 combo** (ej. 30 Bs).
- `house_amount` = resto por **1 combo** (ej. 90 Bs).
- **Monto por manilla (derivado):** `girl_amount / bracelet_units_per_line` (ej. 5 Bs).

Al persistir ítem con `quantity = Q`:

- `line_total = price × Q`
- `girl_pool = girl_amount × Q` (ajuste respecto al comportamiento actual)
- `required_bracelet_units = bracelet_units_per_line × Q`

Opcional en catálogo (V2): `girl_amount_per_unit` explícito; V1 puede calcularlo.

### 3.3 `order_item_allocations` (nueva)

| Campo | Tipo | Notas |
|-------|------|-------|
| `id` | bigint PK | |
| `tenant_id`, `branch_id` | FK | Scope |
| `order_item_id` | FK | |
| `girl_user_id` | FK users | Chica activa |
| `units` | unsigned int | Manillas asignadas |
| `unit_amount` | decimal(12,2) | Monto chica por manilla (calculado al guardar) |
| `total_amount` | decimal(12,2) | `units × unit_amount` |
| `allocation_type` | string | `GIRL_BRACELET_UNITS` |
| timestamps | | |

Índices: `(order_item_id)`, `(girl_user_id)`.

Regla: `SUM(units) = product.bracelet_units_per_line × order_item.quantity`.

### 3.4 `sale_item_allocations` (snapshot al cobrar)

| Campo | Tipo |
|-------|------|
| `id` | bigint PK |
| `sale_item_id` | FK |
| `girl_user_id` | FK |
| `units` | int |
| `unit_amount_snapshot` | decimal |
| `total_amount_snapshot` | decimal |
| `source_order_item_allocation_id` | FK nullable |
| `allocation_type` | string |

Inmutable post-cobro (como resto de snapshots de venta).

### 3.5 Compatibilidad con `girl_user_id` en línea

Para **GIRL_LINE** (producto simple):

- Seguir usando `girl_user_id` **o** crear 1 allocation implícita de 1 unidad (preferible migración gradual: backend acepta ambos, settlements unifican).

Para **GIRL_BRACELET_ALLOCATION**:

- `girl_user_id` en línea debe ser **NULL** (evitar ambigüedad).
- Toda la información en allocations.

### 3.6 `staff_settlement_items` — extensión

Nuevo `source_type` recomendado: **`GIRL_BRACELET_ALLOCATION`**

- `source_id` = `sale_item_allocations.id`
- `sale_item_id` = referencia opcional para trazabilidad
- `amount` = `total_amount_snapshot`

**No reutilizar** `GIRL_CONSUMPTION` (1 por sale_item) ni `GIRL_BRACELET` (tabla manual).

`GIRL_CONSUMPTION` permanece para líneas simples sin `requires_allocation`.

---

## 4. Propuesta backend

### 4.1 Capas (Open/Closed)

```
Domain/Product/
  ValueObjects/SettlementBehavior.php
  ValueObjects/AllocationType.php
  Services/ProductSettlementProfileResolver.php
  Services/BraceletAllocationValidator.php

Domain/Order/
  Entities/OrderItemAllocation.php
  Repositories/OrderItemAllocationRepositoryInterface.php

Application/Order/
  Services/OrderItemAllocationService.php   // sync allocations, recalc amounts
  UseCases/SyncOrderItemAllocationsUseCase.php

Application/Sale/
  Services/SaleItemAllocationSnapshotter.php  // al cobrar / venta directa
```

Estrategia por `settlement_behavior` (patrón Strategy / registry), no switches por nombre de producto.

### 4.2 Cambios por use case

| Use case | Cambio propuesto |
|----------|------------------|
| `AddOrderItemUseCase` | Resolver perfil; si `requires_allocation`, crear ítem sin chica y exigir payload allocations **o** devolver ítem “incompleto” + endpoint sync |
| `SyncOrderItemAllocationsUseCase` | **Nuevo** — PUT allocations, validar suma, chicas, montos |
| `UpdateOrderItemUseCase` | Si cambia `quantity`, recalcular `required_units` e invalidar/revalidar allocations |
| `SendOrderToBarUseCase` | Validator: líneas simples → `girl_user_id`; líneas combo → allocations completas |
| `ChargeOrderUseCase` | Re-validar allocations; snapshot a `sale_item_allocations`; `girl_user_id` null en sale_item combo |
| `CreateDirectSaleUseCase` | V1: **rechazar** combos con allocation **o** mismo flujo que comanda (ver §11) |
| `GenerateCurrentShiftSettlementsUseCase` / repo | Iterar `sale_item_allocations` → `GIRL_BRACELET_ALLOCATION` |
| `GetSettlementPendingSourcesUseCase` | Incluir allocations no liquidadas |
| `GetGirlShiftEarningsUseCase` | Sumar por allocations |
| `ProductPriceResolver` / `OrderItemPricing` | Escalar `girl_amount`/`house_amount` × quantity para consistencia |

### 4.3 API (borrador)

```
POST   /orders/{id}/items                    // + allocations[] opcional
PUT    /orders/{id}/items/{itemId}/allocations
GET    /orders/{id}                          // items[].allocations[]
POST   /sales/charge-order                   // defensa backend allocations
POST   /sales/direct                         // según decisión V1
GET    /products/{id}                        // settlement profile
PATCH  /products/{id}                        // admin: bracelet_units, behavior
```

Payload allocations:

```json
{
  "allocations": [
    { "girl_user_id": 12, "units": 3 },
    { "girl_user_id": 15, "units": 2 },
    { "girl_user_id": 18, "units": 1 }
  ]
}
```

### 4.4 Mapper / respuestas

Extender `OrderMapper::item()`:

```json
{
  "id": 101,
  "product_name": "Combo 6 Cervezas",
  "quantity": 1,
  "requires_allocation": true,
  "required_bracelet_units": 6,
  "allocated_bracelet_units": 6,
  "allocation_complete": true,
  "allocations": [
    { "girl_user_id": 12, "girl_name": "María", "units": 6, "total_amount": "30.00" }
  ]
}
```

---

## 5. Propuesta frontend (referencia cruzada)

Ver `frontend/COMBO_BRACELET_ALLOCATION_AUDIT.md` para UI garzón, cajera y catálogo.

Backend debe ser **fuente de verdad**; frontend solo refleja `required_bracelet_units`, `allocated_bracelet_units`, errores de validación.

---

## 6. Reglas de validación

### 6.1 Dominio — `BraceletAllocationValidator`

Para ítems con `requires_allocation = true`:

1. `allocations` no vacío.
2. Cada `girl_user_id` único en la lista (o fusionar duplicados en sync).
3. Cada chica activa, rol GIRL, tenant correcto (`GirlStaffValidator`).
4. Cada `units >= 1`.
5. `SUM(units) === bracelet_units_per_line × quantity` — **exacto**, no tolerancia.
6. `SUM(total_amount) === girl_pool` (±0.01 redondeo).
7. Sin chicas duplicadas inactivas.

### 6.2 Puntos de enforcement

| Momento | Acción si falla |
|---------|-----------------|
| Guardar allocations | 422 con mensaje “Faltan X manillas” / “Sobran X” |
| Enviar a barra | 422 `allocations_incomplete` |
| Cobrar comanda | 422 (defensa, no confiar en frontend) |
| Venta directa | 422 o bloqueo producto |
| Generar liquidaciones | Omitir líneas incompletas **no debe ocurrir** si cobro validó |

### 6.3 Producto simple (regresión)

| Condición | Regla |
|-----------|-------|
| `requires_allocation = false` + CON_ACOMPANANTE | `girl_user_id` obligatorio (comportamiento actual) |
| SOLO_CLIENTE | Sin chica ni allocations |
| Combo qty cambia 1→2 | `required_units` 6→12; allocations previas inválidas hasta reasignar |

---

## 7. Liquidaciones

### 7.1 Flujo propuesto

```
Cobro → sale_item_allocations (snapshot)
      → GenerateCurrentShiftSettlements
      → Por cada allocation:
            ensureSettlement(girl_user_id, GIRL)
            createItem(source_type=GIRL_BRACELET_ALLOCATION, source_id=allocation.id, amount=total_amount_snapshot)
```

Ejemplo Combo 6 (30 Bs chica total, 5 Bs/manilla):

| Chica | Units | Liquidación |
|-------|-------|-------------|
| María | 3 | 15.00 Bs |
| Laura | 2 | 10.00 Bs |
| Ana | 1 | 5.00 Bs |

### 7.2 Comparación `source_type`

| Tipo | Cuándo | `source_id` |
|------|--------|-------------|
| `GIRL_CONSUMPTION` | Línea simple 1 chica | — (usa `sale_item_id` unique) |
| `GIRL_BRACELET_ALLOCATION` | Combo / reparto | `sale_item_allocations.id` |
| `GIRL_BRACELET` | Manilla manual legacy | `bracelets.id` |

### 7.3 Compatibilidad liquidaciones parciales

Tras fix de liquidaciones parciales (múltiples cortes por turno), cada allocation es fuente independiente con `sourceAlreadySettled(allocation_id, GIRL_BRACELET_ALLOCATION)`.

### 7.4 Descripción en ítem de liquidación

Ejemplo: `Manillas combo — Combo 6 Cervezas ×3 u. (Venta #1042)`.

---

## 8. Reportes

### 8.1 Impacto

| Reporte | Cambio |
|---------|--------|
| Productos vendidos | Mantener qty combos; agregar métrica opcional `bracelet_units_sold` |
| Conciliación productos | OK a nivel combo; sub-reporte opcional de manillas |
| Liquidaciones chicas | Incluir `GIRL_BRACELET_ALLOCATION` en consumo/manillas |
| Reporte por chica | Sumar unidades y montos desde allocations |
| Cierre caja / turno | Sin cambio en ingresos; egresos liquidación aumentan ítems |

### 8.2 Vista deseada (CBA-6)

```
Combo 6 Cervezas — vendidos: 1
Manillas generadas: 6
  María ×3 — 15.00 Bs
  Laura ×2 — 10.00 Bs
  Ana ×1 — 5.00 Bs
```

Implementación: join `sale_items` → `sale_item_allocations` → users; agregar sección en reporte de servicios/ventas.

---

## 9. Riesgos

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| Regresión productos simples | Alta | Defaults `GIRL_LINE`, tests de no-regresión |
| `girl_amount` no escalaba con qty | Media | Corregir al introducir combos; documentar; tests |
| UX garzón lenta / confusa | Alta | Flujo dedicado móvil, contador X/Y |
| Cajera edita qty sin reasignar | Alta | Backend invalida; UI fuerza re-asignación |
| Doble liquidación combo + consumo | Alta | No poblar `girl_user_id` en combos; skip `GIRL_CONSUMPTION` si hay allocations |
| Mezclar con manillas manuales | Media | Naming claro; no auto-crear `bracelets` |
| Constraint `(sale_item_id, source_type)` | Media | Usar `GIRL_BRACELET_ALLOCATION` con `source_id`, no `GIRL_CONSUMPTION` múltiple |
| Venta directa sin allocator | Media | Bloquear en V1 |
| Performance N allocations | Baja | Pocas filas por combo (<10) |

---

## 10. Plan por fases

### CBA-1 — Catálogo combo

- Migración campos en `products` (settlement profile).
- Admin API + validaciones (`bracelet_units_per_line >= 1`).
- Seed/documentar primer combo “Combo 6 Cervezas”.
- Tests: resolver perfil, precios CON_ACOMPANANTE.

### CBA-2 — Allocations en comanda

- Tabla `order_item_allocations`.
- `SyncOrderItemAllocationsUseCase` + validator.
- Extender `AddOrderItem` / mapper.
- Tests: casos 1–4 del negocio, suma incorrecta rechazada.

### CBA-3 — Cobro y snapshots

- Tabla `sale_item_allocations`.
- `ChargeOrderUseCase` snapshot + validación.
- Ajuste `OrderItemPricing` (girl × qty).
- Tests cobro multichica.

### CBA-4 — Liquidaciones desde allocations

- `GIRL_BRACELET_ALLOCATION` en repository.
- Pending sources + girl earnings.
- Tests liquidación 3 chicas / 1 combo.

### CBA-5 — Corrección cajera

- UI/API editar allocations en OPEN/SENT_TO_BAR.
- Recalcular al cambiar qty/producto.
- Permisos corrección existentes.

### CBA-6 — Reportes y cierre

- Manillas en reportes ventas/servicios.
- Conciliación ampliada (opcional).
- Impresión ticket / detalle comanda.

---

## 11. Qué queda para V1

**Incluir en V1 (MVP operativo):**

- CBA-1 a CBA-5 (catálogo, comanda, cobro, liquidaciones, corrección cajera).
- Comportamiento `GIRL_BRACELET_ALLOCATION` + `GIRL_LINE` legacy.
- Flujo garzón móvil + detalle comanda admin.
- Validación estricta suma manillas.
- Liquidación por chica vía allocations.
- **Bloquear combos con allocation en venta directa** (mensaje claro: “Use comanda”).

**Excluir de V1 (documentar):**

- Venta directa con allocator.
- Combos SOLO_CLIENTE sin manillas.
- Desglose de productos internos del combo (6 filas de cerveza en inventario).
- Promos dinámicas / packs variables.
- Auto-creación de manillas en tabla `bracelets`.
- Reportes avanzados de manillas (solo básico en CBA-6 mínimo).

---

## 12. Qué queda para V2

- Venta directa con mismo asignador.
- `settlement_behavior = NONE` (combo sin manillas, solo precio).
- Combo con componentes (`combo_items` → productos hijos) e inventario.
- `girl_amount_per_unit` explícito en catálogo vs derivado.
- Promociones y reglas temporales.
- Kardex / stock por unidad interna.
- Analytics: ranking chicas por manillas, no solo por monto.
- Migración opcional: allocations implícitas para todo CON_ACOMPANANTE simple.

---

## Anexo A — Precios: respuestas a preguntas clave

| Pregunta | Recomendación V1 |
|----------|------------------|
| ¿Precio total del combo? | Sí, `product_prices.price` por modalidad |
| ¿Precio SOLO_CLIENTE? | Opcional; si no hay precio, no vendible en esa modalidad |
| ¿Precio CON_ACOMPANANTE? | Obligatorio para combos con manillas |
| ¿Cuánto gana cada chica por manilla? | `girl_amount / bracelet_units_per_line` (derivado) |
| ¿Cálculo monto chica? | Por unidad interna × units asignadas; casa = `house_amount × qty` |
| ¿Casa gana el resto? | Sí, `house_amount` por combo × quantity |

---

## Anexo B — Archivos backend a modificar (implementación futura)

- `database/migrations/*` — products, allocations, sale_item_allocations
- `app/Domain/Product/*`
- `app/Domain/Order/*`
- `app/Application/Order/UseCases/*`
- `app/Application/Sale/UseCases/ChargeOrderUseCase.php`, `CreateDirectSaleUseCase.php`
- `app/Application/Order/Services/OrderItemPricing.php`
- `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentOrderRepository.php`
- `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentStaffSettlementRepository.php`
- `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentReportReadRepository.php`
- `app/Http/Controllers/Api/V1/OrderController.php`, `ProductController.php`
- `tests/Feature/Api/V1/*Combo*`, regresión Order/Settlements

---

**Estado:** Auditoría completa. **No se ha programado ni migrado nada.** Esperar aprobación del diseño antes de CBA-1.
