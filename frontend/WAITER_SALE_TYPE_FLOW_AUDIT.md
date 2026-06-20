# Auditoría UX — Flujo garzón por tipo de venta

**Fecha:** Jun 2026  
**Alcance:** Modo garzón móvil — agregar productos a comanda  
**Estado:** Solo auditoría — **sin implementación**

---

## 1. Resumen ejecutivo

El flujo actual **funciona** y está bien integrado con POS-CAT, combos y liquidaciones. El orden cognitivo, sin embargo, es **producto → modalidad**, mientras que en operación real el garzón piensa **modalidad → producto**.

**Conclusión:** El flujo propuesto (tipo de venta primero) se puede implementar **casi en su totalidad en frontend**, reutilizando:

- `GET /products/pos-catalog` + filtrado client-side por `active_prices` y `requires_allocation`
- `POST /orders/{id}/items` sin cambios de contrato
- `ComboAllocationDialog` tal cual
- `GET /waiter/girls` para listado de chicas

**Backend opcional (fase 2):** filtros `sale_mode`, `requires_allocation` o `catalog_intent` en pos-catalog para catálogos muy grandes y consistencia server-side.

**Cortesía / cover:** No existen como tipos de dominio hoy. Requieren convención de catálogo (categoría o `product_type` libre) antes de un bucket “Otros”.

---

## 2. Flujo actual (garzón móvil)

### 2.1 Rutas y pantallas

| Paso | Pantalla | Archivo |
|------|----------|---------|
| Mesas | Mis mesas | `src/pages/nightpos/waiter/index.vue` |
| Abrir comanda | Tap mesa → POST `/waiter/my-tables/{id}/open` | `useWaiterTables.js` |
| Detalle comanda | `/nightpos/waiter/orders/:id` | `src/pages/nightpos/waiter/orders/[id].vue` |
| Agregar producto | Dialog fullscreen | `OrderAddProductDialog.vue` + `PosProductPicker.vue` |

### 2.2 Jerarquía de componentes (add product)

```
waiter/orders/[id].vue
└── OrderAddProductDialog (mobile-waiter, allow-girl-on-add)
    ├── PosProductPicker (grid, búsqueda, categorías, favoritos/recientes)
    ├── VSelect chica (CON_ACOMPANANTE, sin búsqueda)
    └── ComboAllocationDialog (fullscreen, reparto manillas)
AssignGirlModal (al enviar a barra, VAutocomplete con búsqueda)
```

### 2.3 Secuencia actual por escenario

#### A) Bebida solo (SOLO_CLIENTE)

| # | Acción | UI |
|---|--------|-----|
| 1 | Tap **+ Producto** / **Agregar extra** | Abre dialog “Agregar bebida” |
| 2 | Buscar (≥2 letras) **o** categoría **o** favorito/reciente | `PosProductPicker` |
| 3 | Tap botón **Solo** en la tarjeta del producto | `@pick-mode` → `sale_mode = SOLO_CLIENTE` |
| 4 | (Opcional) Ajustar cantidad | `VTextField` |
| 5 | Tap **Agregar bebida** | `POST /orders/{id}/items` |

**Clics típicos:** 4–5 (sin contar apertura de mesa).

**Problema UX:** En el paso 2 el garzón ve **todos** los productos vendibles mezclados (solo, con compañía y combos). Debe leer cada tarjeta y elegir el botón correcto.

#### B) Con compañía (CON_ACOMPANANTE)

| # | Acción | UI |
|---|--------|-----|
| 1–2 | Igual que solo | Catálogo mixto |
| 3 | Tap **+Acomp.** en tarjeta | `sale_mode = CON_ACOMPANANTE` |
| 4 | Elegir chica en **VSelect** (lista completa, sin buscador) | Solo si `allowGirlOnAdd` y hay chicas |
| 5 | Cantidad + confirmar precio | Preview `GET /products/{id}/prices` |
| 6 | **Agregar bebida** | POST items |

**Alternativa:** Omitir chica en paso 4 → asignar al **Enviar a barra** vía `AssignGirlModal` (ahí sí hay `VAutocomplete` “Buscar chica…”).

**Problema UX:** La búsqueda de chica existe **solo al enviar**, no al agregar. El `VSelect` en add es lento con muchas chicas.

#### C) Combo

| # | Acción | UI |
|---|--------|-----|
| 1–2 | Buscar / categoría | Catálogo mixto |
| 3 | Tap **Agregar combo** | `@pick-combo` → abre `ComboAllocationDialog` |
| 4 | Cantidad + “¿Cómo repartir?” | split-choice |
| 5 | **Todas para una chica** → grid de botones **o** reparto multi | Sin buscador en grid |
| 6 | (Auto) POST item + PUT allocations | Backend |

**Clics típicos:** 5–7.

**Nota:** El combo ya fuerza `CON_ACOMPANANTE` y no pide modalidad aparte. El flujo híbrido actual es adecuado; solo conviene **entrar** desde un bucket “Combos”.

#### D) Favoritos / recientes

| # | Acción | Problema |
|---|--------|----------|
| 1 | Tap chip favorito/reciente | `@pick-product` — **no** fija `sale_mode` |
| 2 | Debe tocar **Solo** o **+Acomp.** después | Paso extra vs flujo propuesto |

---

## 3. Flujo propuesto (tipo de venta primero)

### 3.1 Wireframe lógico

```
[Comanda Mesa 4]
┌─────────────────────────────────────┐
│  [ Bebidas solo ] [ Con compañía ]  │
│  [    Combos    ] [    Otros     ]  │  ← paso 0 (nuevo)
├─────────────────────────────────────┤
│  🔍 Buscar…                         │
│  [ chips categoría filtradas ]      │
│  ┌─────────┐ ┌─────────┐           │
│  │ Paceña  │ │ Ron     │  …        │  ← un tap = agregar o siguiente paso
│  └─────────┘ └─────────┘           │
└─────────────────────────────────────┘
```

### 3.2 Comportamiento por bucket

| Bucket | Filtro catálogo | Tap producto | Pasos extra |
|--------|-----------------|--------------|-------------|
| **Bebidas solo** | Tiene precio activo `SOLO_CLIENTE` y **no** es combo | Agregar directo `SOLO_CLIENTE` qty 1 | Cantidad opcional (sheet compacto) |
| **Con compañía** | Tiene precio activo `CON_ACOMPANANTE` y **no** es combo | Abre **buscador de chica** | Chica → agregar |
| **Combos** | `requires_allocation === true` | Abre `ComboAllocationDialog` actual | Sin cambios |
| **Otros** | Ver §6 | Según producto | Definir convención |

### 3.3 Experiencia objetivo (clics)

| Escenario | Flujo propuesto | Clics objetivo |
|-----------|-----------------|----------------|
| Bebida sola | Tipo → producto | **2** (+ abrir dialog) |
| Con compañía | Tipo → producto → buscar chica → confirmar | **4** |
| Combo | Tipo → combo → reparto | **4–5** (igual que hoy, sin mezcla con bebidas) |

**Reducción principal:** Eliminar elección de modalidad **después** de encontrar producto; eliminar catálogo mixto que confunde.

---

## 4. Qué datos necesita el frontend

### 4.1 Ya disponibles en pos-catalog

Por producto (`ProductMapper` + precios embebidos):

| Campo | Uso en flujo propuesto |
|-------|------------------------|
| `id`, `name`, `category_id`, `category_name` | Lista y chips |
| `active_prices[]` | Filtrar por `sale_mode` |
| `has_active_pricing` | Sellable (ya usado) |
| `requires_allocation` | Bucket Combos |
| `bracelet_units_per_line` | Label combo |
| `settlement_behavior` | Refuerzo combo (`GIRL_BRACELET_ALLOCATION`) |
| `product_type` | Bucket Otros (convención) |

Helpers existentes:

- `productActivePrice(product, saleMode)` — `useOrderHelpers.js`
- `usePosCatalog({ sellableOnly: true })` — categorías + búsqueda

### 4.2 Chicas

| Fuente | Endpoint | Formato |
|--------|----------|---------|
| Operativas turno | `GET /waiter/girls` | `{ items: [{ id, name, username }] }` |
| Carga actual | `loadOperationalGirlsForSelect({ waiterMode: true })` | `{ title, value }` para VSelect |

**No hay:** búsqueda server-side, recientes, favoritas por mesa, chicas “activas del turno” como API dedicada.

### 4.3 Lo que no hace falta pedir al backend (fase 1)

- Nuevo `sale_mode` en dominio (siguen `SOLO_CLIENTE` | `CON_ACOMPANANTE`)
- Cambio en POST items
- Cambio en combo allocations

---

## 5. Cómo mostrar productos por tipo (frontend)

### 5.1 Filtros client-side recomendados

```javascript
// Bebidas solo
products.filter(p =>
  !p.requires_allocation &&
  productActivePrice(p, 'SOLO_CLIENTE')
)

// Con compañía
products.filter(p =>
  !p.requires_allocation &&
  productActivePrice(p, 'CON_ACOMPANANTE')
)

// Combos
products.filter(p => p.requires_allocation === true)

// Otros (convención — ver §6)
products.filter(p =>
  !p.requires_allocation &&
  !productActivePrice(p, 'SOLO_CLIENTE') &&
  !productActivePrice(p, 'CON_ACOMPANANTE') &&
  isOtherProduct(p) // categoría o product_type
)
```

### 5.2 Integración con POS-CAT (no romper)

| Regla POS-CAT | Cómo respetarla |
|---------------|-----------------|
| No grid de 200 productos al abrir | Mantener: favoritos/recientes + categorías + búsqueda ≥2 chars |
| Máx. 20 resultados API | Aplicar filtro **después** de `fetchResults`, o ampliar `limit` solo en bucket activo |
| Productos sin precio no vendibles | Ocultar en bucket o deshabilitar (igual que hoy) |

**Propuesta:** Añadir prop `catalogIntent` a `PosProductPicker` o wrapper `WaiterSaleTypePicker`:

- `intent`: `'solo' | 'companion' | 'combo' | 'other' | null`
- Cuando `intent` está seteado, chips de categoría muestran conteos **filtrados** (recalcular client-side desde meta + fetch por categoría si hace falta)
- Tarjetas de producto en modo “solo/compañía”: **un solo botón grande** (tap = pick), sin dual Solo/+Acomp.

### 5.3 UI móvil — botones tipo de venta

Ubicación: toolbar o fila sticky bajo título en `OrderAddProductDialog` cuando `mobileWaiter`.

- 4 botones grandes (`min-height` ~48–56px), icono + label corto
- Estado activo con color primary
- Cambiar tipo **resetea** producto seleccionado y scroll arriba
- Copy sugerido: **Solo** | **Con compañía** | **Combos** | **Otros**

---

## 6. Bucket “Otros” — auditoría cortesía / cover

### 6.1 Estado en el sistema

| Concepto | ¿Existe? | Evidencia |
|----------|----------|-----------|
| `sale_mode` cortesía | No | Solo `SOLO_CLIENTE`, `CON_ACOMPANANTE` |
| `product_type` cover/cortesía | No enum | Campo libre `max:50`; demo solo `beverage` |
| Categoría “Cortesía” / “Cover” | No en seeder demo | Categorías demo: Bebidas, Tragos, Cócteles |
| Servicio cover como producto | Posible | Producto `SOLO_CLIENTE` precio 0 o simbólico |
| Manilla / liquidación especial | Parcial | `settlement_behavior`: `GIRL_LINE`, `GIRL_BRACELET_ALLOCATION`, `NONE` |

### 6.2 Opciones para “Otros” (sin romper liquidaciones)

| Opción | Pros | Contras |
|--------|------|---------|
| **A) Categoría dedicada** (Cortesía, Cover, Extras) | Simple, filtrable en POS-CAT | Requiere carga admin |
| **B) `product_type` convención** (`cover`, `courtesy`, `extra`) | Filtrable en UI | Sin validación backend |
| **C) Flag futuro `catalog_tags[]`** | Explícito | Requiere backend |

**Recomendación fase 1:** Opción A — categorías “Cover” y “Cortesía” configuradas por tenant; bucket Otros = unión de esas categorías + productos con `settlement_behavior = NONE` sin precio compañía.

**Riesgo:** Cortesía con precio 0 puede afectar reportes; validar con negocio si genera comisión garzón/chica.

---

## 7. Buscador de chicas

### 7.1 Estado actual

| Contexto | Control | Búsqueda |
|----------|---------|----------|
| Agregar CON_ACOMPANANTE (waiter) | `VSelect` | **No** |
| Enviar a barra (items sin chica) | `VAutocomplete` | **Sí** (“Buscar chica…”) |
| Combo una chica | Grid `VBtn` por nombre | **No** |
| Combo multi | `BraceletAllocationPanel` +/- | **No** |

Referencia con búsqueda en otro módulo: `room-services/create.vue` (patrón reutilizable).

### 7.2 Propuesta UX (con compañía)

Pantalla intermedia fullscreen **“Elegir chica”** tras tap producto:

```
┌──────────────────────────────┐
│ ← Paceña · Con compañía      │
├──────────────────────────────┤
│ 🔍 Buscar chica              │  ← autofocus, inputmode text
├──────────────────────────────┤
│ ┌──────────┐ ┌──────────┐    │
│ │ Luciana  │ │ Lucía    │    │  ← filtro client-side includes
│ └──────────┘ └──────────┘    │
│ ┌──────────┐                 │
│ │ Lucero   │                 │
│ └──────────┘                 │
└──────────────────────────────┘
```

Requisitos bajo presión:

- Autofocus al montar (`nextTick` + ref)
- Filtrar en tiempo real (normalizar acentos: `localeCompare` / deburr)
- Botón limpiar (X en input)
- Tarjetas grandes (`cols="6"`, `size="x-large"`)
- Si `filtered.length === 1` → highlight + permitir Enter (teclado) o auto-select configurable
- Mostrar bajo nombre: **“Manilla: {nombre}”** en línea de comanda (`formatCompanionBraceletLine` ya existe)

### 7.3 Componente sugerido

`GirlQuickPicker.vue` (nuevo):

- Props: `girls`, `productName`, `autofocus`
- Emits: `select(girlId)`, `cancel`
- Reutilizar en: add compañía, combo single-girl (sustituir grid cuando > N chicas, p.ej. >8)

---

## 8. Chicas favoritas / recientes — auditoría

| Idea | Factible hoy | Complejidad | Recomendación |
|------|--------------|-------------|---------------|
| **Recientes** (últimas N chicas usadas por garzón) | Sí — `localStorage` como favoritos producto | Baja | **Fase 2** — alto valor |
| **Activas del turno** | Parcial — todas en `/waiter/girls` ya son activas sucursal | Media | Mostrar subset si API expone “con ventas hoy” (no existe) |
| **Frecuentes por mesa** | No — sin historial mesa→chica en API | Alta | **Fase 3** — requiere backend analytics |
| **Favoritas garzón** | Sí — localStorage | Baja | Opcional fase 2 |

**No implementar en fase 1** si complica; el buscador resuelve el 80% del dolor.

---

## 9. Clics actuales vs objetivo

| Escenario | Actual (típico) | Objetivo | Delta |
|-----------|-----------------|----------|-------|
| Solo, producto en favoritos | 4–5 | 3 | −1/−2 |
| Solo, búsqueda | 5–6 | 4 | −1/−2 |
| Con compañía + chica al agregar | 6–7 | 4–5 | −2 |
| Con compañía, chica al enviar | 5 + envío | 4–5 al agregar | Más consistente |
| Combo | 5–7 | 4–6 | −1 (menos ruido catálogo) |

**Errores evitados:**

- Agregar combo pensando que era bebida solo
- Tocar “Solo” en producto que solo tiene precio CON_ACOMPANANTE (botón ya disabled — OK)
- Elegir chica incorrecta en lista larga sin buscar

---

## 10. Riesgos

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Romper POS-CAT (grid masivo) | Alto | Mantener `showResults` + intent filter |
| Romper combo allocation | Alto | No tocar `ComboAllocationDialog` / PUT allocations |
| Producto dual-mode (solo y compañía) | Medio | Aparece en **ambos** buckets — correcto |
| Bucket Otros vacío sin catálogo admin | Medio | Ocultar chip hasta hay productos |
| Filtro client-side con `limit: 20` | Medio | Subir limit por intent o filtrar en API fase 2 |
| Chica obligatoria al enviar | Bajo | Mantener validación `SendOrderToBar` |
| Impresión / barra | Bajo | Sin cambio en payload item |
| Liquidaciones | Bajo | Mismos `sale_mode` y allocations |

---

## 11. Plan de implementación por fases

### Fase 1 — UX core (solo frontend, ~3–5 días)

1. `WaiterSaleTypeTabs` en `OrderAddProductDialog` (`mobileWaiter` only).
2. Extender `PosProductPicker` con prop `intent` + filtros client-side.
3. Modo solo: tap producto → submit directo (qty 1, sheet opcional).
4. Modo compañía: tap producto → `GirlQuickPicker` con búsqueda.
5. Modo combo: catálogo solo combos → flujo actual.
6. Ocultar botones dual Solo/+Acomp. cuando hay `intent`.
7. Tests manuales en móvil (PWA garzón).

**Backend:** ninguno obligatorio.

### Fase 2 — Otros + polish (~2–3 días)

1. Definir con negocio categorías Cover/Cortesía/Extras.
2. Bucket Otros por categoría.
3. Chicas recientes en `localStorage`.
4. `GirlQuickPicker` en combo single-girl si lista > 8.
5. Conteos en tabs (“Solo (12)”) vía fetch categoría o meta ampliada.

**Backend opcional:** `category_ids` múltiples en pos-catalog.

### Fase 3 — Escala y analytics (opcional)

1. API `GET /products/pos-catalog?sale_mode=SOLO_CLIENTE` etc.
2. API `GET /waiter/girls?search=luc` server-side.
3. Chicas frecuentes por mesa / turno (persistencia).

---

## 12. Archivos a tocar (cuando se implemente)

| Archivo | Cambio |
|---------|--------|
| `OrderAddProductDialog.vue` | Tabs tipo venta; orquestación chica |
| `PosProductPicker.vue` | Prop `intent`, UI single-tap |
| **Nuevo** `WaiterSaleTypeTabs.vue` | Botones grandes |
| **Nuevo** `GirlQuickPicker.vue` | Búsqueda chicas |
| `usePosCatalog.js` | Helpers filterByIntent (o composable nuevo) |
| `ComboAllocationDialog.vue` | Opcional: integrar GirlQuickPicker |
| `waiter/orders/[id].vue` | Mínimo — props si hace falta |

**No tocar en fase 1:**

- `orders/[id].vue` (cajera)
- Backend use cases de item/combo
- Liquidaciones / caja

---

## 13. Veredicto

| Pregunta | Respuesta |
|----------|-----------|
| ¿Se puede con lo existente? | **Sí**, fase 1 casi 100% frontend |
| ¿Requiere backend? | **No obligatorio**; recomendable en fase 3 o catálogos >100 ítems por bucket |
| ¿Rompe POS-CAT? | **No**, si se respeta lazy load + filtros |
| ¿Cortesía/cover listos? | **No** — convención de catálogo pendiente |
| ¿Buscador chicas? | **Parcial** — hay patrón en AssignGirlModal; falta en add y combo |

**Siguiente paso recomendado:** Aprobar fase 1 + convención categorías para “Otros”, luego implementar.
