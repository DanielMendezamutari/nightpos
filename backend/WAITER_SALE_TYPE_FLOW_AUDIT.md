# Auditoría API — Flujo garzón por tipo de venta

**Fecha:** Jun 2026  
**Alcance:** Endpoints usados al agregar ítems desde modo garzón  
**Estado:** Solo auditoría — **sin implementación**

Documento complementario a `frontend/WAITER_SALE_TYPE_FLOW_AUDIT.md`.

---

## 1. Resumen

El backend **ya soporta** el flujo propuesto sin cambios de contrato:

- Catálogo vendible con precios por modalidad embebidos
- Alta de ítem con `sale_mode` explícito
- Combos vía `requires_allocation` + endpoint de allocations
- Listado de chicas operativas

**Lo que falta** son filtros **opcionales** en catálogo y chicas para escala y consistencia. **No bloquean** una fase 1 frontend con filtrado client-side.

---

## 2. Endpoints del flujo garzón

### 2.1 Catálogo POS

```
GET /api/v1/products/pos-catalog
```

| Parámetro | Existe | Descripción |
|-----------|--------|-------------|
| `sellable_only` | Sí (default 1) | `has_active_pricing` |
| `unpriced_only` | Sí | Admin — sin precio |
| `category_id` | Sí | Filtro categoría (`0` = sin categoría) |
| `search` | Sí | ≥2 caracteres — nombre, sku, barcode, categoría |
| `product_ids` | Sí | CSV — favoritos/recientes |
| `limit` | Sí | Max 50, default 20 |
| `grouped` | Sí | Respuesta agrupada |

**Implementación:** `GetPosCatalogUseCase` → `EloquentProductRepository::listForTenant` + precios activos agrupados.

**Comportamiento sin filtros de búsqueda:** respuesta con `categories` + `meta`, **`products: []`** (lazy load — ver `backend/POS_CAT_REPORT.md`).

#### Filtros que **no** existen hoy

| Filtro propuesto | Utilidad | Workaround frontend |
|------------------|----------|---------------------|
| `sale_mode=SOLO_CLIENTE` | Solo productos con precio activo en ese modo | `active_prices.some(p => p.sale_mode === 'SOLO_CLIENTE')` |
| `sale_mode=CON_ACOMPANANTE` | Idem compañía | Idem |
| `requires_allocation=1` | Solo combos | `product.requires_allocation` |
| `product_type=` | Bucket Otros | Client-side |
| `settlement_behavior=` | Refinar combos / none | Client-side |
| `catalog_intent=solo\|companion\|combo\|other` | Semántica UX unificada | Componer filtros client-side |

### 2.2 Respuesta producto (campos relevantes)

```json
{
  "id": 1,
  "name": "Cerveza Paceña",
  "category_id": 2,
  "category_name": "Bebidas",
  "product_type": "beverage",
  "unit": "unit",
  "settlement_behavior": "GIRL_LINE",
  "requires_allocation": false,
  "allocation_type": null,
  "bracelet_units_per_line": null,
  "has_active_pricing": true,
  "active_prices": [
    {
      "sale_mode": "SOLO_CLIENTE",
      "price": "25.00",
      "status": "active"
    },
    {
      "sale_mode": "CON_ACOMPANANTE",
      "price": "80.00",
      "girl_amount": "40.00",
      "house_amount": "40.00",
      "status": "active"
    }
  ]
}
```

**Sellable por modalidad:** el flag `has_active_pricing` es **OR** de cualquier precio activo, no por modo. El frontend debe usar `ProductPriceResolver` logic o `active_prices` por fila.

### 2.3 Precios (preview)

```
GET /api/v1/products/{id}/prices
```

Resolución al cobrar/agregar: `ProductPriceResolver` — branch price first, fallback tenant-wide, filtro `sale_mode` + ventana fechas.

---

## 3. Agregar ítem a comanda

```
POST /api/v1/products/{id}/items   →   POST /api/v1/orders/{id}/items
```

**Validación** (`AddOrderItemRequest`):

| Campo | Regla |
|-------|-------|
| `product_id` | required |
| `sale_mode` | required — `SOLO_CLIENTE` \| `CON_ACOMPANANTE` |
| `quantity` | optional, 1–99 |
| `girl_user_id` | optional |
| `notes` | optional |

**Reglas de dominio** (`AddOrderItemUseCase`):

| Caso | Comportamiento |
|------|----------------|
| Producto combo (`requiresAllocation`) | Rechaza `girl_user_id` en POST — chica vía allocations |
| CON_ACOMPANANTE sin chica | **Permitido** al agregar |
| Precio inexistente para modo | 422 |
| Chica en combo | 422 `girlNotAllowedWithAllocation` |

**No requiere cambios** para flujo tipo-primero: el frontend sigue enviando el mismo payload.

### 3.1 Combo — allocations

```
PUT /api/v1/orders/{orderId}/items/{itemId}/allocations
```

Body: array `{ girl_user_id, units }` — suma debe igualar manillas requeridas.

Identidad combo en dominio:

| Campo | Valor combo |
|-------|-------------|
| `settlement_behavior` | `GIRL_BRACELET_ALLOCATION` |
| `requires_allocation` | `true` |
| `allocation_type` | `GIRL_BRACELET_UNITS` |
| `unit` | Convención `"combo"` en seeds — **no** usado en validación |

---

## 4. Chicas para garzón

```
GET /api/v1/waiter/girls
```

**Permiso:** `orders.create`

**Filtros query:** ninguno

**Criterios server-side:**

- Usuario activo, tenant actual
- `staff_profile.staff_role = GIRL`, activo
- Acceso sucursal (branch del perfil o `accessibleBranches`)

**Respuesta:**

```json
{
  "items": [
    { "id": 10, "name": "María", "username": "chica.centro" }
  ]
}
```

#### Faltante para UX propuesta

| Necesidad | Estado |
|-----------|--------|
| `?search=luc` | **No existe** — filtrar client-side (~50 chicas OK) |
| Orden por frecuencia | **No existe** |
| Chicas “en turno” vs todas activas | **Misma lista** hoy |

**Asignar chica después:**

```
PATCH /api/v1/orders/{id}/items/{itemId}
{ "girl_user_id": 10 }
```

Solo `CON_ACOMPANANTE`, no allocation products.

**Enviar a barra** exige chicas asignadas (`OrderItemReadinessChecker` → `GIRL_MISSING`).

---

## 5. Cortesía / cover / “Otros”

### 5.1 En dominio de producto

| Concepto | Estado |
|----------|--------|
| `SettlementBehavior::NONE` | Existe — sin línea chica automática |
| `GIRL_LINE` | Default bebidas con compañía |
| `GIRL_BRACELET_ALLOCATION` | Combos |
| Tipo “cortesía” | **No** — solo `product_type` string libre |
| Precio 0 | **Permitido** en precios |

### 5.2 Implicaciones liquidación

| Tipo venta | Comisión garzón | Chica |
|------------|-----------------|-------|
| SOLO_CLIENTE | Según `waiter_commission` en ítem | No |
| CON_ACOMPANANTE | Sí | `girl_amount_snapshot` |
| Combo allocations | Según reglas combo | Por allocation |
| Cortesía (precio 0) | Verificar reglas comisión | Depende config |

**Recomendación:** productos cortesía/cover como categoría admin + precio explícito; validar con negocio antes de bucket Otros.

---

## 6. Filtros existentes vs flujo propuesto

| Intención UX | ¿API lista? | ¿Frontend solo? |
|--------------|-------------|-----------------|
| Bebidas solo | Parcial | **Sí** — filter `active_prices` |
| Con compañía | Parcial | **Sí** |
| Combos | Parcial | **Sí** — `requires_allocation` |
| Otros | No | **Sí** — categoría / product_type |
| Buscar chica | N/A | **Sí** — lista completa + filter |

---

## 7. Cambios backend propuestos (solo si se aprueban fases 2–3)

### 7.1 Opcional — `GetPosCatalogInput`

```php
// Propuesta — NO implementado
?sale_mode=SOLO_CLIENTE          // productos con precio activo en ese modo
?requires_allocation=1             // solo combos
?product_types=cover,courtesy    // CSV
?catalog_intent=solo|companion|combo|other
```

**Ubicación:** `GetPosCatalogUseCase::filterProducts()` + join subquery precios activos.

**Beneficio:** Menos payload, conteos correctos en categorías por intent, menos lógica duplicada frontend.

### 7.2 Opcional — búsqueda chicas

```
GET /waiter/girls?search=luc&limit=20
```

**Ubicación:** `ListOperationalGirlsUseCase` — `where name like`.

### 7.3 No recomendado

- Nuevo `sale_mode` enum (CORTESIA, COVER) — rompe pricing/liquidaciones
- Auto-inferir `sale_mode` en POST — el garzón debe seguir enviando modo explícito

---

## 8. Compatibilidad — qué no tocar

| Área | Razón |
|------|-------|
| `AddOrderItemUseCase` pricing | Cobro y snapshots |
| `SyncOrderItemAllocationsUseCase` | Combos |
| `SendOrderToBarUseCase` readiness | Chica obligatoria |
| `ProductSettlementNormalizer` | Identidad combo |
| POS-CAT lazy load | Performance móvil |
| Permisos waiter | Sin cambios |

---

## 9. Tests existentes relevantes

| Test | Cobertura |
|------|-----------|
| `PhaseC4WaiterTest` | Login, mesas, girls, orders |
| `ComboBraceletAllocationTest` | Generate allocation settlements |
| POS-CAT feature tests | Catalog filters básicos |

**Al implementar backend filters:** añadir tests en `ProductApiTest` / nuevo `PosCatalogIntentTest`.

---

## 10. Veredicto backend

| Pregunta | Respuesta |
|----------|-----------|
| ¿Fase 1 requiere deploy backend? | **No** |
| ¿API actual suficiente para filtrar por tipo? | **Sí**, con lógica frontend sobre `active_prices` + `requires_allocation` |
| ¿Cuándo conviene API filter? | Catálogo >100 productos por bucket o conteos categoría por intent |
| ¿Búsqueda chicas backend? | Opcional; lista operativa suele ser pequeña |
| ¿Cortesía/cover? | **Modelar en catálogo** (categoría/producto), no en API garzón |

Ver plan por fases en documento frontend §11.
