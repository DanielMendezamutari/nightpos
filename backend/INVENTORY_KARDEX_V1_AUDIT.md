# Auditoría — Kardex / Inventario / Control de Stock V1 (Backend)

**Fecha:** 2026-06-17  
**Tipo:** Auditoría de producto + plan de implementación  
**Estado:** **NO IMPLEMENTADO** — solo diseño  
**Regla:** No se ha programado nada en esta fase.

---

## 1. Estado actual

NightPOS V1 tiene **catálogo de productos**, **ventas**, **conciliación productos vendidos vs comandados**, y **combos de liquidación (manillas)**. **No tiene kardex ni inventario operativo.**

| Capacidad | ¿Existe? |
|-----------|----------|
| Campo `products.track_inventory` | ✅ Sí (schema + API CRUD) |
| Stock actual por sucursal | ❌ No |
| Tabla movimientos kardex | ❌ No |
| Stock por lote | ❌ No |
| Descuento al cobrar comanda | ❌ No |
| Descuento venta directa | ❌ No |
| Devolución stock al cancelar comanda | N/A (nunca se descontó) |
| Componentes inventario de combo | ❌ No |
| Componentes liquidación combo (manillas) | ✅ Sí (`order_item_allocations`, `sale_item_allocations`) |
| Reporte conciliación productos | ✅ Sí (no es kardex) |
| Bounded context Inventory | ⚠️ Placeholder vacío |
| Rol staff `INVENTORY` | ✅ Enum existe, sin flujos |
| Permisos `inventory.*` | ❌ No |

### Evidencia técnica (resumen)

- `products.track_inventory` — migración `2026_06_03_100005`, persistido en CRUD, **nunca leído** en `ChargeOrderUseCase` ni `CreateDirectSaleUseCase`.
- `Domain/Inventory/*` — interfaces y DTO vacíos (`ARCHITECTURE_REPORT.md`: placeholder).
- Combos actuales = **`settlement_behavior = GIRL_BRACELET_ALLOCATION`** + `bracelet_units_per_line` — orientado a **liquidación chicas/manillas**, no a descontar Paceña ×6 del stock.
- `PRODUCT_RECONCILIATION_REPORT.md` declara explícitamente: **«No es Kardex»**.
- No existe endpoint ni use case de anulación de venta (`void`/`refund`/`RETURN`).

---

## 2. Tablas existentes relevantes

| Tabla | Relación con inventario |
|-------|-------------------------|
| `products` | `track_inventory` boolean (inerte) |
| `product_prices` | Precios; sin costo estándar |
| `order_items` / `sale_items` | Cantidades vendidas; sin hook stock |
| `order_item_allocations` / `sale_item_allocations` | Manillas combo **liquidación** |
| `sales` / `sale_payments` | Venta confirmada; sin movimiento inventario |
| `cash_movements` | Solo dinero, no productos |

**No existen:** `inventory_stock`, `inventory_movements`, `product_components`, `purchase_orders`, `stock_lots`.

---

## 3. Gaps (qué falta)

### Funcional

1. Stock actual por `tenant_id` + `branch_id` + `product_id`.
2. Kardex append-only con `quantity_before` / `quantity_after`.
3. Servicio de dominio `InventoryMovementService` invocado desde ventas.
4. Tabla `product_components` para combos inventario.
5. Apertura de stock (OPENING) al activar producto o carga inicial.
6. Entradas manuales (PURCHASE simplificado V1).
7. Ajustes IN/OUT con motivo obligatorio.
8. Detección stock bajo (`minimum_stock`).
9. Permisos RBAC `inventory.*`.
10. Evento SSE `inventory.movement.created`.
11. Resumen inventario en cierre caja/turno (advertencias).
12. Reportes stock actual + kardex + bajo mínimo.

### Integración ventas

| Evento | Debe afectar stock V1 |
|--------|----------------------|
| Crear comanda | ❌ No |
| Enviar a barra | ❌ No |
| Precuenta | ❌ No |
| Cancelar comanda (no cobrada) | ❌ No (nunca descontó) |
| **Cobrar comanda** | ✅ Sí |
| **Venta directa** | ✅ Sí |
| Cancelar ítem antes de cobrar | ❌ No |
| Anular venta cobrada | ❌ No en V1 UI → documentar |

### Combos — gap conceptual

Hoy «Combo 6 cervezas» puede existir como producto con `unit=combo` y `GIRL_BRACELET_ALLOCATION` (6 manillas para chicas). **Eso no descuenta 6 botellas del stock de Paceña.**

Se necesita **capa separada** `product_components` para inventario, independiente de allocations de manillas.

---

## 4. Respuestas a las 12 preguntas clave

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿`products` tiene `track_inventory`? | **Sí** — boolean, default `false`, API acepta el campo |
| 2 | ¿Existe stock actual por sucursal? | **No** |
| 3 | ¿Existe tabla de movimientos? | **No** |
| 4 | ¿Existe stock por lote? | **No** |
| 5 | ¿Se descuenta algo al cobrar? | **No** |
| 6 | ¿Venta directa descuenta? | **No** |
| 7 | ¿Comanda cobrada descuenta? | **No** |
| 8 | ¿Cancelación devuelve stock? | **No** (y no hay reversa de venta) |
| 9 | ¿Combos tienen componentes? | **No inventario**; sí allocations manillas |
| 10 | ¿Reportes muestran stock? | **No** — solo conciliación vendido vs comandado |
| 11 | ¿Cierre muestra vendidos pero no inventario? | **Sí** — conciliación productos, combo bracelets; sin stock |
| 12 | ¿Qué falta? | Todo el módulo kardex V1 (ver §3) |

---

## 5. Modelo propuesto V1

### 5.1 `inventory_stock`

```sql
inventory_stock
  id
  tenant_id          FK
  branch_id          FK
  product_id         FK
  quantity_on_hand   DECIMAL(12,3) default 0
  minimum_stock      DECIMAL(12,3) nullable
  updated_at

UNIQUE (tenant_id, branch_id, product_id)
INDEX (tenant_id, branch_id, quantity_on_hand)
```

**Reglas:**

- Solo productos con `track_inventory = true` tienen fila (o se crea al primer movimiento).
- `quantity_on_hand` puede ser negativo (ver §6).
- Scope: siempre `tenant_id` + `branch_id` del contexto operativo.

### 5.2 `inventory_movements` (kardex)

```sql
inventory_movements
  id
  tenant_id
  branch_id
  product_id
  movement_type       VARCHAR(40)
  quantity            DECIMAL(12,3)   -- signo: positivo entrada, negativo salida
  quantity_before     DECIMAL(12,3)
  quantity_after      DECIMAL(12,3)
  unit_cost           DECIMAL(12,4) nullable
  total_cost          DECIMAL(12,2) nullable
  reference_type      VARCHAR(40) nullable  -- sale, sale_item, cash_session, manual, opening
  reference_id        BIGINT nullable
  reason              VARCHAR(100) nullable
  notes               TEXT nullable
  created_by_user_id  FK users
  created_at

INDEX (tenant_id, branch_id, product_id, created_at)
INDEX (tenant_id, branch_id, movement_type, created_at)
INDEX (reference_type, reference_id)
```

**Tipos V1:**

| Tipo | Uso |
|------|-----|
| `OPENING` | Stock inicial al configurar producto |
| `PURCHASE` | Entrada manual (compra simplificada V1) |
| `SALE` | Salida por ítem en cobro comanda |
| `DIRECT_SALE` | Salida por ítem venta directa |
| `COMBO_COMPONENT_SALE` | Salida componente al vender combo padre |
| `ADJUSTMENT_IN` | Ajuste positivo |
| `ADJUSTMENT_OUT` | Ajuste negativo |
| `WASTE` | Merma |
| `RETURN` | Reservado V1.1 (anulación venta) |
| `CANCEL_REVERSAL` | Reservado V1.1 |

### 5.3 `product_components`

```sql
product_components
  id
  tenant_id
  parent_product_id   FK products  -- combo vendible
  component_product_id FK products -- ej. Paceña botella
  quantity            DECIMAL(12,3) -- ej. 6 por 1 combo
  created_at
  updated_at

UNIQUE (tenant_id, parent_product_id, component_product_id)
```

**Reglas combo inventario:**

- Si padre `track_inventory=true` **sin** componentes → descuenta cantidad del padre (combo como SKU único).
- Si padre tiene componentes → **no** descontar padre (o descontar solo si también trackea); descontar cada componente: `qty_venta × component.quantity`.
- Si padre es combo liquidación (`GIRL_BRACELET_ALLOCATION`) **y** tiene componentes → ambos sistemas coexisten (manillas + stock).
- Componente debe tener `track_inventory=true` para generar movimiento; si no, skip con log/warning.

### 5.4 Extensión `products` (mínima)

Opcional V1 — puede vivir solo en `inventory_stock.minimum_stock`:

- Mantener `track_inventory` en `products`.
- Agregar `inventory_tracked_at` nullable (cuándo se activó control) — opcional V1.1.

---

## 6. Flujo de descuento de stock

### Punto de enganche (transaccional)

Dentro de la misma transacción DB de:

1. `ChargeOrderUseCase` — **después** de crear `sale` + `sale_items`, **antes** de commit.
2. `CreateDirectSaleUseCase` — idem.

**Nuevo servicio:** `RecordSaleInventoryMovementsService`

```
Para cada sale_item:
  resolver producto
  si !track_inventory → skip
  si tiene product_components:
    para cada componente con track_inventory:
      qty = sale_item.quantity × component.quantity
      movement COMBO_COMPONENT_SALE (-qty)
  else:
    qty = sale_item.quantity
    movement SALE o DIRECT_SALE (-qty)
```

**Idempotencia:** `reference_type=sale_item`, `reference_id=sale_item.id` — unique constraint parcial para evitar doble descuento.

### Regla stock negativo — **recomendación V1**

| Opción | Recomendación |
|--------|---------------|
| A) Bloquear venta sin stock | ❌ No recomendado para boliche |
| B) Permitir negativo + alerta | ✅ **Recomendado V1** |

**Justificación:** En operación real el inventario físico suele ir detrás de las ventas. Bloquear cobro genera cola en caja. Mejor:

- Permitir `quantity_on_hand < 0`.
- Registrar movimiento igual.
- Marcar producto `LOW_STOCK` o `NEGATIVE_STOCK` en UI/reportes.
- Opcional config sucursal V1.1: `inventory.block_negative_sales` (default false).

### Reversas V1

- **No existe** anulación de venta en UI/API.
- Documentar: ventas cobradas son definitivas en V1.
- Preparar tipos `RETURN` / `CANCEL_REVERSAL` en schema para V1.1.
- Cancelar comanda **antes** de cobrar: no requiere reversa (nunca descontó).

---

## 7. Combos — diseño V1

### Tipos de producto (regla de negocio)

| Tipo | track_inventory | components | Descuento |
|------|-----------------|------------|-----------|
| Simple (Paceña) | sí | — | −qty venta |
| Simple sin control | no | — | nada |
| Combo SKU único | sí | vacío | −qty combo |
| Combo con componentes | cualquiera | Paceña ×6 | −6×qty combo en Paceña |
| Combo solo liquidación | — | sin components inventario | solo manillas |
| Compuesto futuro | V2 | recetas multi-nivel | V2 |

### Ejemplo «Combo 6 Paceñas»

```
products:
  id=100 name="Combo 6 Paceñas" track_inventory=false settlement_behavior=GIRL_BRACELET_ALLOCATION bracelet_units_per_line=6

product_components:
  parent=100 component=50(Paceña botella) quantity=6

Al vender 1 combo:
  - 6 movimientos COMBO_COMPONENT_SALE en producto 50 (si track_inventory)
  - Liquidación: 6 manillas (sistema actual)
```

### Advertencia combo sin componentes

Si `track_inventory=true` en combo sin `product_components` → descuenta el combo como SKU.  
Si `track_inventory=false` y sin componentes → **warning** en admin: «Este combo no descontará inventario.»

---

## 8. API propuesta V1

| Método | Ruta | Permiso | Descripción |
|--------|------|---------|-------------|
| GET | `/inventory/stock` | `inventory.stock.view` | Listado stock actual + filtros |
| GET | `/inventory/stock/low` | `inventory.stock.view` | Bajo mínimo / negativo |
| GET | `/inventory/movements` | `inventory.kardex.view` | Kardex paginado |
| POST | `/inventory/movements/entry` | `inventory.movements.create` | Entrada PURCHASE |
| POST | `/inventory/movements/adjustment` | `inventory.adjust` | Ajuste IN/OUT |
| POST | `/inventory/opening` | `inventory.configure` | Stock inicial masivo o por producto |
| GET | `/products/{id}/components` | `inventory.configure` | Listar componentes |
| PUT | `/products/{id}/components` | `inventory.configure` | CRUD componentes combo |
| GET | `/reports/inventory-stock` | `reports.access` o `inventory.stock.view` | Reporte stock |
| GET | `/reports/inventory-movements` | `reports.access` o `inventory.kardex.view` | Reporte kardex |

**Extensión productos existentes:**

- `PUT /products/{id}` — ya acepta `track_inventory`; al pasar a `true` con `opening_quantity` opcional → OPENING movement.

---

## 9. Permisos propuestos

| Slug | Descripción |
|------|-------------|
| `inventory.access` | Entrada menú Inventario |
| `inventory.stock.view` | Ver stock actual y bajo mínimo |
| `inventory.kardex.view` | Ver kardex |
| `inventory.movements.create` | Entradas |
| `inventory.adjust` | Ajustes ± |
| `inventory.configure` | Stock inicial, mínimos, componentes combo |

### Roles sugeridos

| Rol | Permisos |
|-----|----------|
| Cajera básica | `inventory.stock.view` |
| Cajera senior | + `inventory.kardex.view`, `inventory.movements.create` |
| Admin / owner | todos `inventory.*` |
| Superadmin | todos |

**Nota:** sincronizar en `SeedsNightPosFoundation` **y** `TenantDefaultRolePermissions` (evitar drift documentado en release audit).

---

## 10. Reportes y cierre

### Nuevos reportes V1

1. **Stock actual** — producto, categoría, on_hand, mínimo, estado (OK/BAJO/NEGATIVO).
2. **Kardex** — movimientos filtrables export CSV.
3. **Stock bajo** — subset alerta.
4. **Ajustes del período** — ADJUSTMENT_* + WASTE.

### Extensión cierre caja / turno (advertencias, no bloqueo)

Agregar sección opcional en `CashSessionCloseCheckBuilder` / `GetShiftCloseReport`:

```json
"inventory_warnings": {
  "negative_stock_products": [...],
  "below_minimum_products": [...],
  "movements_in_session": 142
}
```

No bloquear cierre por stock negativo en V1.

---

## 11. SSE

Emitir en cada movimiento:

```json
{
  "type": "inventory.movement.created",
  "payload": {
    "product_id": 50,
    "movement_type": "SALE",
    "quantity_after": "93.000",
    "refresh": ["inventory"]
  }
}
```

Suscripciones: pantallas Stock actual, Kardex, POS-CAT (badge stock opcional V1.1).

---

## 12. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Doble descuento en reintento cobro | Idempotencia por `sale_item_id` |
| Combo manillas vs componentes confundidos | UI y docs separan «liquidación» vs «inventario» |
| Stock negativo sin control | Reportes + alertas; config bloqueo V1.1 |
| Performance en pico ventas | Movimientos batch en misma TX; índices |
| Productos sin opening | Primera venta crea stock negativo — onboarding debe cargar opening |
| Migración datos existentes | Script opening=0 para track_inventory=true post-deploy |
| Tenant/branch leak | Tests aislamiento obligatorios |

---

## 13. Plan por fases (implementación futura)

### INV-1 — Fundación (backend)
- Migraciones: `inventory_stock`, `inventory_movements`, `product_components`
- Domain: `InventoryMovementService`, repositorios
- Permisos + seeders
- Tests 6–8 (opening, adjust, tenant/branch)

**Estimado:** 3–4 días

### INV-2 — Hook ventas
- Integrar `ChargeOrderUseCase` + `CreateDirectSaleUseCase`
- Lógica combo componentes
- Idempotencia
- Tests 1–5, 14–15

**Estimado:** 2–3 días

### INV-3 — API operativa
- Endpoints stock, kardex, entradas, ajustes, components CRUD
- Tests 6–12

**Estimado:** 2–3 días

### INV-4 — Reportes + cierre + SSE
- Reportes stock/kardex/bajo mínimo
- Warnings en close-check / shift close
- SSE emitter
- Test 13

**Estimado:** 2 días

### INV-5 — Frontend (paralelo INV-3/4)
- Ver `frontend/INVENTORY_KARDEX_V1_AUDIT.md`

**Estimado:** 4–5 días

**Total estimado V1 kardex:** ~12–17 días dev + 2 días QA manual

---

## 14. Qué entra en V1 vs V1.1 vs V2

### V1 (este diseño)

- Stock por sucursal
- Kardex movimientos
- Opening, entradas, ajustes, mermas
- Descuento en cobro + venta directa
- Componentes combo inventario
- Stock negativo permitido + alertas
- Reportes stock/kardex/bajo mínimo
- Permisos RBAC
- SSE movimiento creado
- Advertencias cierre (no bloqueo)

### V1.1

- Anulación venta + `RETURN`/`CANCEL_REVERSAL`
- Bloqueo opcional venta sin stock (config sucursal)
- Badge stock en POS-CAT
- Costo promedio ponderado
- Import CSV opening masivo

### V2

- Compras / proveedores / órdenes compra
- Lotes y vencimientos
- Traspasos entre sucursales
- Producto compuesto multi-nivel (recetas)
- Inventario cíclico / conteo físico con hoja de conteo
- Integración compras → PURCHASE automático

---

## 15. Tests backend planificados (15 escenarios)

| # | Test |
|---|------|
| 1 | Venta producto simple descuenta stock |
| 2 | Venta directa descuenta stock |
| 3 | Combo descuenta componentes |
| 4 | Combo sin componentes no descuenta padre si track=false + warning flag |
| 5 | Producto sin track_inventory no descuenta |
| 6 | Ajuste entrada aumenta stock |
| 7 | Ajuste salida baja stock |
| 8 | Movimiento guarda before/after |
| 9 | Stock bajo detectado |
| 10 | Tenant isolation |
| 11 | Branch isolation |
| 12 | Venta con stock insuficiente permite negativo |
| 13 | Close-check incluye productos bajo/negativo |
| 14 | No descuenta al crear comanda |
| 15 | No descuenta al send-to-bar |

---

## 16. Recomendación

**Proceder con implementación INV-1 → INV-2 → INV-3** antes de declarar V1 RELEASE CANDIDATE si el negocio exige control de stock.

El campo `track_inventory` actual es **deuda técnica engañosa** — ocultar en UI hasta INV-5 o implementar de inmediato.

**Prioridad sugerida:** P1 para cierre V1 operativo completo (junto con V1-98 QA).

---

*Documento de auditoría y plan. Sin migraciones ni cambios de código en esta entrega.*
