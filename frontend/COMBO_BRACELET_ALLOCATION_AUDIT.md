# Auditoría frontend — Combos, unidades internas y manillas multichica

**Fecha:** 2026-06-16  
**Estado:** ✅ **IMPLEMENTADO** (CBA-1…CBA-6) — ver reports de implementación  
**Alcance original:** Análisis de UI y flujos actuales.  
**Par backend:** `backend/COMBO_BRACELET_ALLOCATION_AUDIT.md`

---

## Resumen ejecutivo

La UI actual trata `CON_ACOMPANANTE` como **selector de una sola chica** por línea. No existe pantalla de reparto de manillas, contador X/Y, ni visualización de distribución en detalle de comanda.

Implementar combos exige un **componente reutilizable de asignación** (`BraceletAllocationPanel`) consumido por garzón móvil, cajera y (V2) venta directa, apoyado en campos API `required_bracelet_units`, `allocations[]`, `allocation_complete`.

---

## 1. Estado actual

### 1.1 Agregar producto a comanda

**`OrderAddProductDialog.vue`**

- Formulario: `product_id`, `sale_mode`, `quantity`, `girl_user_id` (opcional).
- Si `allowGirlOnAdd && CON_ACOMPANANTE`: un `VSelect` de chica.
- **Sin** concepto de unidades internas ni reparto.

**`PosProductPicker.vue`**

- Grid/lista de productos con modalidad SOLO / CON_ACOMPANANTE.
- No muestra badge “Combo 6 manillas”.
- No dispara flujo de allocation.

### 1.2 Garzón móvil — detalle comanda

**`pages/nightpos/waiter/orders/[id].vue`**

- Agrega productos vía `OrderAddProductDialog` (`mobileWaiter=true`, `allowGirlOnAdd` según permisos).
- Antes de enviar a barra: `itemsNeedingGirl(order)` → abre `AssignGirlModal`.
- Lista ítems con nombre, modalidad, cantidad; **no muestra distribución multichica**.

**`AssignGirlModal.vue`**

- Un autocomplete **por ítem pendiente**.
- Mensaje: “productos CON_ACOMPANANTE sin chica”.
- Confirm emite `{ [itemId]: girlUserId }` → API `assignItemGirl` **una chica por ítem**.

### 1.3 Admin / cajera — detalle comanda

**`pages/nightpos/orders/[id].vue`**

- Misma lógica `itemsNeedingGirl` + `AssignGirlModal`.
- Modo corrección cajera (`cashierCorrectionMode`): edición qty, producto, chica vía `UpdateOrderItem`.
- Edición de chica = un solo `girl_user_id`; **sin UI de allocations**.

### 1.4 Helpers compartidos

**`composables/useOrderHelpers.js`**

```52:58:frontend/src/composables/useOrderHelpers.js
export function itemsNeedingGirl(order) {
  if (!order?.items?.length)
    return []
  return order.items.filter(
    item => item.sale_mode === 'CON_ACOMPANANTE' && !item.girl_user_id,
  )
}
```

- Solo detecta falta de **una** chica.
- Debe evolucionar a `itemsNeedingAllocation(order)` cuando `item.requires_allocation`.

### 1.5 Catálogo de productos

**`ProductFormFields.vue`**

- `product_type`: beverage | service | food.
- Campos: nombre, categoría, unidad, estado.
- **No** hay: unidades de manilla, requires_allocation, settlement_behavior.

**`QuickProductPriceCreateDialog.vue` / `products/[id]/prices.vue`**

- Precio, `girl_amount`, `house_amount` por modalidad.
- Sin hint “monto total del combo” vs “por manilla”.
- Sin cálculo preview `girl_amount / bracelet_units`.

### 1.6 Venta directa

**`pages/nightpos/cash/direct-sale.vue`**

- Carrito con `girl_user_id` por línea CON_ACOMPANANTE.
- Valida antes de cobrar: ítems CON_ACOMPANANTE sin chica.
- **No soporta** reparto multichica.

### 1.7 Liquidaciones y reportes

- **`settlements/girls.vue`**: listado por liquidación; sin desglose manillas por combo.
- **Reportes**: conciliación productos sin manillas; ventas por `girl_user_id` a nivel línea.

### 1.8 API cliente

- Órdenes: add item envía `girl_user_id` único.
- No hay cliente para `PUT .../allocations`.
- Tipos TS (si existen) sin `allocations[]`.

---

## 2. Por qué el modelo UI actual no alcanza

| Necesidad negocio | UI actual |
|-------------------|-----------|
| Repartir 6 manillas entre N chicas | 1 select chica |
| Validar 6/6 en tiempo real | No hay contador |
| Ver “María ×3, Laura ×2” en comanda | Solo `girl_user_id` o nada |
| 2 combos = 12 manillas | qty aumenta línea, no manillas visibles |
| Cajera corrige distribución | Solo cambia chica única |
| Garzón entiende en celular | Modal simple insufficient |

Intentar “elegir la misma chica 6 veces” con líneas duplicadas **no es aceptable** operativamente ni compatible con precio combo único.

---

## 3. Propuesta de modelo de datos (vista frontend)

El frontend **no persiste** allocations; refleja API:

```typescript
interface OrderItemAllocation {
  id?: number
  girl_user_id: number
  girl_name?: string
  units: number
  unit_amount?: string
  total_amount?: string
}

interface OrderItem {
  // ... campos actuales
  requires_allocation?: boolean
  required_bracelet_units?: number
  allocated_bracelet_units?: number
  allocation_complete?: boolean
  allocations?: OrderItemAllocation[]
}
```

Estado local del asignador (antes de guardar):

```typescript
interface AllocationDraft {
  order_item_id: number
  required_units: number
  rows: { girl_user_id: number | null; units: number }[]
}
```

---

## 4. Propuesta frontend — componentes

### 4.1 `BraceletAllocationPanel.vue` (nuevo, core)

**Props:** `requiredUnits`, `allocations`, `girls`, `readonly`, `mobile`.

**UI (mobile-first):**

```
Este combo incluye 6 manillas.

Asignadas: 4 / 6   ⚠ Faltan 2

┌─────────────────────────────────┐
│ María        [ − ]  3  [ + ]    │
│ Laura        [ − ]  1  [ + ]    │
│ + Agregar chica                 │
└─────────────────────────────────┘

[ Guardar reparto ]
```

Comportamiento:

- Botones +/- por fila; mínimo 0 por fila, mínimo 1 si fila activa.
- “Agregar chica” abre autocomplete (reuse `QuickGirlCreateDialog`).
- Deshabilitar guardar si `sum !== required`.
- Mensajes: “Faltan X manillas” / “Te pasaste por X manilla”.
- Modo readonly: solo lista distribución.

### 4.2 `ComboAllocationDialog.vue` (nuevo)

- Wrapper fullscreen móvil / dialog desktop.
- Se abre al agregar producto con `requires_allocation` **antes** de confirmar ítem, o inmediatamente después de add si API devuelve ítem incompleto.

Flujo garzón propuesto:

1. Elige “Combo 6 Cervezas” + CON_ACOMPANANTE.
2. Si qty > 1, muestra “Total manillas: 12”.
3. Abre asignador → guarda via `PUT allocations`.
4. Cierra y refresca comanda.

### 4.3 Evolución `OrderAddProductDialog.vue`

- Tras pick producto, consultar perfil (cache en picker o preview API).
- Si `requires_allocation`: **ocultar** select chica simple; emit `needs-allocation` con producto/qty.
- Parent abre `ComboAllocationDialog`.

### 4.4 Evolución `AssignGirlModal.vue`

Dividir responsabilidades:

| Modal | Ítems |
|-------|-------|
| `AssignGirlModal` (legacy) | CON_ACOMPANANTE simple, 1 chica |
| `CompleteAllocationsModal` (nuevo) | Lista ítems incompletos con acceso rápido al panel |

O unificar en modal con tabs por ítem: simple → select; combo → mini panel.

### 4.5 Detalle comanda (garzón + admin)

En cada ítem combo:

```
Combo 6 Cervezas ×1
Con acompañante · Manillas 6/6 ✓

Distribución:
  María ×3
  Laura ×2
  Ana ×1

[ Editar reparto ]   (si canModify)
```

Badge warning si `!allocation_complete`.

### 4.6 Catálogo admin

**`ProductFormFields.vue`** — sección “Liquidación / Manillas” (solo permiso products.update):

| Campo UI | Mapeo API |
|----------|-----------|
| Comportamiento | `settlement_behavior` |
| Manillas por combo | `bracelet_units_per_line` |
| Requiere reparto | `requires_allocation` (auto si units > 1) |

**Precios** — en CON_ACOMPANANTE para combo:

- Label: “Monto total chicas (por 1 combo)”.
- Hint calculado: “≈ X Bs por manilla (6 u.)”.

### 4.7 Venta directa (V1)

- Al detectar producto `requires_allocation`: **toast + no agregar** al carrito.
- Copy: “Este combo debe venderse por comanda”.

V2: mismo `BraceletAllocationPanel` en drawer antes de agregar al carrito.

---

## 5. Propuesta frontend — flujos por rol

### Garzón (móvil)

```
PosProductPicker → Combo detectado
  → ComboAllocationDialog (obligatorio)
  → POST item + PUT allocations
  → Detalle comanda muestra distribución
  → Enviar barra bloqueado si incomplete (helper + backend)
```

### Cajera (corrección)

En `orders/[id].vue` modo corrección:

- Editar qty combo → modal “Debe reasignar 12 manillas” auto.
- Botón “Editar reparto” → `BraceletAllocationPanel`.
- Cambiar producto a no-combo → limpiar allocations (confirmación).

### Admin catálogo

- Crear combo: tipo beverage + 6 manillas + precio CON_ACOMPANANTE.
- Validación formulario: `bracelet_units >= 1`.

---

## 6. Reglas de validación (UI)

| Regla | Dónde |
|-------|-------|
| `sum(units) === required` | Panel, deshabilitar guardar |
| Al menos una chica con units > 0 | Panel |
| No enviar barra si pending | `waiter/orders/[id]` botón disabled + tooltip |
| qty cambia → recalcular required | Dialog confirmación |
| Chica duplicada en filas | Fusionar al guardar o validar |

**Validación server-side es obligatoria**; UI es guía, no seguridad.

Helpers nuevos en `useOrderHelpers.js`:

```javascript
export function itemRequiredBraceletUnits(item) {
  return (item.required_bracelet_units ?? 0) * (item.quantity ?? 1)
}

export function itemsNeedingAllocation(order) {
  return activeOrderItems(order).filter(
    item => item.requires_allocation && !item.allocation_complete,
  )
}

export function itemsNeedingGirl(order) {
  return activeOrderItems(order).filter(
    item => item.sale_mode === 'CON_ACOMPANANTE'
      && !item.requires_allocation
      && !item.girl_user_id,
  )
}
```

---

## 7. Liquidaciones (vista)

### Estado actual

- Pantallas settlements no distinguen manillas de combo vs consumo simple.
- Detalle liquidación chica: descripción textual del backend.

### V1 mínimo

- Sin cambio visual obligatorio si backend envía descripción clara:  
  `Manillas combo — Combo 6 Cervezas ×3 u.`
- Opcional: icono/badge “Combo” en ítem de liquidación.

### CBA-6

- Columna “Unidades” en detalle settlement chica.
- Filtro reporte por tipo fuente.

---

## 8. Reportes (vista)

| Pantalla | Cambio V1/V2 |
|----------|--------------|
| Conciliación productos | V2: fila expandible manillas |
| Dashboard / shift console | V2: KPI manillas turno |
| Detalle venta | Mostrar allocations bajo ítem combo |
| Ticket impreso | Distribución si aplica |

V1: detalle venta/comanda suficiente para operación.

---

## 9. Riesgos (frontend)

| Riesgo | Mitigación |
|--------|------------|
| Garzón confundido con dos modales (chica vs reparto) | Un solo flujo por tipo producto |
| Estado desincronizado local/API | Refetch order tras PUT allocations |
| Modal allocation pesado en móvil | Fullscreen, botones grandes, pocas filas default |
| Olvidar actualizar `itemsNeedingGirl` | Separar helpers; tests e2e |
| Venta directa silenciosa rota | Bloqueo explícito V1 |
| Corrección qty sin UX reassign | Watch qty → banner + modal obligatorio |

---

## 10. Plan por fases (frontend)

### CBA-1 — Catálogo combo

- Form producto: manillas por combo, behavior.
- Form precio: labels/hints combo.
- API types + `products` service.

### CBA-2 — Allocations en comanda

- `BraceletAllocationPanel`, `ComboAllocationDialog`.
- API client `syncOrderItemAllocations`.
- Integrar garzón + admin add product.
- Helpers `itemsNeedingAllocation`.

### CBA-3 — Cobro y snapshots

- Detalle comanda readonly allocations post-envío.
- Detalle venta (si existe vista) con snapshot.

### CBA-4 — Liquidaciones

- Consumir descripciones backend; badge opcional.

### CBA-5 — Corrección cajera

- Editar reparto en `orders/[id].vue`.
- Reassign al cambiar qty.

### CBA-6 — Reportes

- Expand combo en listados ventas.
- Export/print distribución.

---

## 11. Qué queda para V1 (frontend)

**Incluir:**

- Panel asignación reutilizable.
- Flujo garzón completo (add → assign → view → send).
- Detalle comanda con distribución.
- Corrección cajera reparto + reassign on qty change.
- Catálogo admin campos combo.
- Bloqueo venta directa combos.
- Helpers separados simple vs allocation.

**Excluir:**

- Venta directa allocator.
- Reportes avanzados manillas.
- Picker badge/inventory por unidad interna.
- Offline/cache allocations.

---

## 12. Qué queda para V2 (frontend)

- Venta directa con panel.
- Conciliación expandible por manillas.
- Wizard combo con componentes hijos.
- Analytics UI ranking manillas.
- Impresión térmica formato barra con desglose.

---

## Anexo — Archivos frontend a tocar (implementación futura)

| Archivo | Cambio |
|---------|--------|
| `components/nightpos/orders/BraceletAllocationPanel.vue` | **Nuevo** |
| `components/nightpos/orders/ComboAllocationDialog.vue` | **Nuevo** |
| `components/nightpos/orders/OrderAddProductDialog.vue` | Rama combo |
| `components/nightpos/orders/AssignGirlModal.vue` | Split o unificar |
| `components/nightpos/forms/ProductFormFields.vue` | Campos combo |
| `components/nightpos/catalog/QuickProductPriceCreateDialog.vue` | Hints |
| `components/nightpos/catalog/PosProductPicker.vue` | Badge combo |
| `pages/nightpos/waiter/orders/[id].vue` | Flujo allocation |
| `pages/nightpos/orders/[id].vue` | Corrección + display |
| `pages/nightpos/cash/direct-sale.vue` | Bloqueo V1 |
| `composables/useOrderHelpers.js` | Nuevos helpers |
| `api/orders.js` | sync allocations |
| `api/products.js` | settlement fields |

---

## Anexo — Wireframe garzón (referencia)

```
┌──────────────────────────────┐
│ ← Combo 6 Cervezas           │
├──────────────────────────────┤
│ Cantidad: 1                  │
│ Total manillas: 6            │
│                              │
│ Asignadas: 6 / 6  ✓          │
│                              │
│ María          [-] 3 [+]     │
│ Laura          [-] 2 [+]     │
│ Ana            [-] 1 [+]     │
│                              │
│ [+ Otra chica]               │
│                              │
│ [ GUARDAR COMBO ]            │
└──────────────────────────────┘
```

---

**Estado:** Auditoría completa. **No se ha modificado código frontend.** Alinear con backend antes de CBA-1.
