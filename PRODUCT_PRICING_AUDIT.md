# PRODUCT_PRICING_AUDIT.md

**Proyecto:** NightPOS SaaS — Boliche  
**Tipo:** Auditoría crítica productos y precios  
**Fecha:** 2026-06-02  
**Implementación:** ver `PRODUCT_PRICING_IMPLEMENTATION_REPORT.md` (F1–F8 y B1–B5 aplicados; B2/B6 pendientes)  
**Referencias revisadas:** `NIGHTPOS_MASTER_AUDIT.md`, `backend/PHASE_6_REPORT.md`, `frontend/PHASE_R2_REPORT.md`, `frontend/PHASE_C1_REPORT.md`, `FRONTEND_GUIDELINES.md`, `backend/PHASE_C1_REPORT.md`, `frontend/QUICK_ACTIONS_PHASE_B_REPORT.md`

---

## 1. Resumen ejecutivo

El **backend** modela correctamente el boliche (SOLO_CLIENTE / CON_ACOMPANANTE, split chica/casa, precios por sucursal, validación de dominio) e incluye **`POST /products/quick`** para alta transaccional producto + precios.

El **frontend de catálogo** sigue un flujo **producto y precios desacoplados en pantallas distintas**, con etiquetas técnicas (`SOLO_CLIENTE`), sin edición de precios existentes, y **sin botón “Producto rápido”** en el módulo Productos (solo dentro de **comandas**).

**Conclusión:** El problema reportado por el administrador es **válido**. La operación frecuente (cerveza + dos precios) requiere demasiados pasos y pantallas; el atajo correcto existe en API pero está **escondido en el flujo de comanda**, no en Catálogo.

**Prioridad alineada con auditoría maestra:** corregir UX Productos/Precios **antes** de Fase C5 (reportes/impresión), junto con garzón móvil, caja y liquidaciones.

---

## 2. Arquitectura actual

### 2.1 Modelo de datos (backend)

```mermaid
erDiagram
    product_categories ||--o{ products : tiene
    products ||--o{ product_prices : tiene
    product_categories {
        int id
        int tenant_id
        int branch_id nullable
        string name
        string status
    }
    products {
        int id
        int tenant_id
        int category_id nullable
        string name
        string product_type
        string status
    }
    product_prices {
        int id
        int product_id
        int branch_id nullable
        string sale_mode
        decimal price
        decimal girl_amount nullable
        decimal house_amount nullable
        string status
        datetime starts_at nullable
        datetime ends_at nullable
    }
```

| Tabla | Rol boliche |
|-------|-------------|
| `product_categories` | Agrupa (Bebidas, Tragos, …) |
| `products` | Ítem vendible (cerveza, trago); **no lleva precio** |
| `product_prices` | Una fila por modalidad activa; CON_ACOMPANANTE lleva `girl_amount` + `house_amount` |

Migración: `2026_06_03_100005_create_product_catalog_tables.php`.

### 2.2 Flujo de pantallas (frontend)

```text
[Menú Productos]
       │
       ▼
 products/index.vue ──► GET /products + GET /products/{id}/prices (N llamadas)
       │
       ├──► products/create.vue ──► POST /products ──► redirect
       │                              products/[id]/index.vue (ficha, sin precios)
       │                                      │
       │                                      ├──► [Configurar precios]
       │                                      ▼
       │                              products/[id]/prices.vue
       │                              POST /products/{id}/prices (× modalidad)
       │
       └──► products/[id]/edit.vue ──► PUT /products (solo datos, sin precios)

[Menú Catálogo > Precios] ──► catalog/prices/index.vue (tabla enlaces a prices.vue)
[Config. precios]         ──► catalog/prices-config/index.vue (PLACEHOLDER)

[Comanda detalle] ──► OrderAddProductDialog ──► QuickProductCreateDialog
                                              POST /products/quick  ◄── único flujo “todo en uno” en UI
```

### 2.3 API backend — inventario verificado

| Método | Ruta | Permiso | Estado |
|--------|------|---------|--------|
| GET | `/products` | `products.list` | OK — lista por tenant; garzón solo `active` |
| GET | `/products/{id}` | `products.list` | OK |
| POST | `/products` | `products.create` | OK — **solo producto**, sin precios |
| PUT | `/products/{id}` | `products.update` | OK — **solo producto** |
| GET | `/products/{id}/prices` | `products.list` | OK — historial + activos/inactivos mezclados |
| POST | `/products/{id}/prices` | `products.create` | OK — un precio por request |
| POST | `/products/{id}/quick-prices` | `product_prices.quick_create` | OK — alias mismo `storePrice` |
| POST | `/products/quick` | `products.quick_create` | OK — producto + SOLO + opcional CON_ACOMPANANTE en transacción |
| GET/POST/PUT | `/product-categories` | varios | OK |
| **PUT** | `/products/{id}/prices/{priceId}` | — | **NO EXISTE** |
| **DELETE** | precios | — | **NO EXISTE** |
| PATCH | desactivar precio (`status: inactive`) | — | **NO EXPUESTO** en API |

**Precios activos (resolución en comandas):** `findActiveForProduct` — prioriza precio de **sucursal** (`branch_id`) sobre precio tenant-wide; respeta `status`, `starts_at`, `ends_at`.

**Duplicados:** `CreateProductPriceUseCase` rechaza segundo precio **activo** con mismo `sale_mode` → `duplicateActiveSaleMode`. Sin UI para “cambiar precio” (desactivar el anterior).

**Historial:** `listForProduct` devuelve **todas** las filas (activas e inactivas), orden por `sale_mode`. La UI en `prices.vue` las muestra como “Historial” sin distinguir vigencia ni fechas en columnas.

---

## 3. Flujo actual detallado (administrador)

### 3.1 Crear producto “Paceña” (solo catálogo)

| Paso | Pantalla | Acción |
|------|----------|--------|
| 1 | Menú lateral | Entrar a **Productos** |
| 2 | `products/index` | Clic **Nuevo producto** |
| 3 | `products/create` | Completar: nombre, tipo, categoría, unidad, estado (5 campos; categoría opcional) |
| 4 | `products/create` | Clic **Guardar** → `POST /products` |
| 5 | `products/[id]/index` | Ficha: alerta **“Sin precios registrados”** |
| 6 | `products/[id]/index` | Clic **Configurar precios** |
| 7 | `products/[id]/prices` | Pantalla dedicada precios |

Subtítulo explícito en create: *“configure precios en el paso siguiente”* — confirma diseño en dos fases.

### 3.2 Configurar precios SOLO 40 + CON_ACOMPANANTE 80 (40+40)

| Paso | Pantalla | Acción |
|------|----------|--------|
| 8 | `prices` — card “Agregar precio” | Modalidad **Solo cliente** (`SOLO_CLIENTE`) |
| 9 | | Precio total **40** → **Registrar precio** → `POST .../prices` |
| 10 | | Cambiar modalidad a **Con acompañante** |
| 11 | | Precio **80**, monto chica **40**, monto casa **40** |
| 12 | | **Registrar precio** (segundo POST) |
| 13 | Opcional | Cancelar / volver a listado |

**Formulario de precios:** una modalidad por envío; no hay vista unificada “Precio cliente / Precio acompañante” en una sola tarjeta como pide el negocio.

### 3.3 Conteo de clics (respuestas directas)

Estimación para **admin** con catálogo vacío, conociendo el sistema:

| Pregunta | Clics / interacciones |
|----------|---------------------|
| ¿Crear cerveza (producto sin precios vendibles)? | **~4 clics** navegación (Productos → Nuevo → Guardar → [ver ficha]) + **~5–8** campos/teclado |
| ¿Poner SOLO 40 + CON_ACOMPANANTE 80 (40+40)? | **+2 clics** navegación (Configurar precios + volver si aplica) + **+2** botones “Registrar precio” + **~6** campos |
| **Total mínimo botones** producto + ambos precios | **~10–12 clics** + **3 pantallas** (create → detail → prices) |
| ¿Atajo “Producto rápido” desde Catálogo? | **0** — no existe en `products/index` ni `create` |
| ¿Atajo desde comanda? | **~3–4 clics** + diálogo (`QuickProductCreateDialog`) si ya hay comanda abierta y permiso `products.quick_create` |

### 3.4 ¿Está claro visualmente?

| Aspecto | Veredicto |
|---------|-----------|
| Jerarquía “primero producto, después precios” | **Poco claro** para dueño/cajera; el mensaje en create lo admite pero no guía con formulario unificado |
| Etiquetas | **Técnicas**: `SOLO_CLIENTE`, `CON_ACOMPANANTE` en tablas y detalle |
| Montos chica/casa | Solo visibles al elegir CON_ACOMPANANTE en formulario lateral; **no** en listado principal (solo precio total en columnas “Con acompañante”) |
| ¿Precios escondidos? | **Sí, en subflujo**: botón “Precios” en tabla y ficha; menú **Catálogo > Precios** es otra tabla de enlaces a la misma pantalla |
| ¿Demasiados formularios? | **Sí**: create (producto) + prices (1–2 envíos) + edit separado |
| ¿Pantalla innecesaria? | **Sí, para alta inicial**: `products/[id]/index` como paso obligatorio entre crear y precios |

### 3.5 ¿Usa Materialize correctamente?

| Elemento plantilla | Uso actual | Gap |
|--------------------|------------|-----|
| KPI / widgets ecommerce (R2) | `products/index` — 2 mini widgets (total/activos) | Aceptable; no KPI “sin precio” |
| `CardStatisticsVertical` | Caja/ventas sí; productos usa patrón simplificado | Podría alinear con `dashboards/analytics` |
| Formulario 2 columnas | `ProductFormFields` — VRow/VCol | OK |
| Tabs catálogo | `NightPosSectionTabs` + `CATALOG_SECTION_TABS` | OK entre Productos/Categorías/Precios |
| Diálogo producto (demo ecommerce) | **No** en catálogo; quick solo en comanda | R2 menciona “modal precios mejorado” — hoy es **página** `prices.vue`, no modal |
| Placeholder | `catalog/prices-config` | Menú engañoso (“Config. precios” sin función) |

---

## 4. Problemas encontrados

### 4.1 UX / operación (críticos para boliche)

| ID | Problema | Impacto |
|----|----------|---------|
| PP-01 | Alta producto **sin precios** en `POST /products` + redirect a ficha vacía | Producto no vendible hasta 2–3 pantallas más |
| PP-02 | Precios en ruta **separada** `/products/:id/prices` | Fricción diaria; no coincide con mental model “un trago = un precio” |
| PP-03 | **Producto rápido** solo en comanda, no en módulo Productos | Admin que configura catálogo no descubre el atajo |
| PP-04 | Etiquetas `SOLO_CLIENTE` / `CON_ACOMPANANTE` | Confusión cajera/admin |
| PP-05 | No se puede **editar** precio: solo crear; duplicado activo → error | Cambiar Paceña de 40 a 45 requiere workaround inexistente en UI |
| PP-06 | Menú **Config. precios** → placeholder | Expectativa rota |
| PP-07 | Listado productos: **N requests** `GET .../prices` por fila | Lentitud con catálogo grande |

### 4.2 Backend (gaps funcionales)

| ID | Problema | Nota |
|----|----------|------|
| PB-01 | Sin `PUT`/`DELETE` precios | PHASE_6 pendiente “6.1” sigue vigente |
| PB-02 | Sin endpoint “crear producto con precios” unificado salvo `/products/quick` | `POST /products` no acepta array `prices` |
| PB-03 | `GET /products` no incluye precios activos embebidos | Obliga N+1 en frontend |
| PB-04 | Vigencia `starts_at`/`ends_at` sin UI | Campo en API, invisible |
| PB-05 | Cajero: `POST /products` forbidden; `POST /products/quick` permitido (C1) | Inconsistencia de permisos si se espera crear producto completo desde admin UI |

### 4.3 Documentación vs código

| Doc | Afirmación | Realidad |
|-----|-----------|----------|
| PHASE_6 §9 | Update/delete precios pendiente | Correcto |
| PHASE_R2 | “Modal precios mejorado” | Es **página** full, no modal en listado |
| FRONTEND_GUIDELINES | Componentes `PriceTypeSelector`, etc. | **No implementados** en catálogo |
| PHASE_C1 | Quick product en comanda | Correcto; **no** en catálogo |

---

## 5. Lo que ya funciona (no re-auditar como roto)

- Validación `girl + house = price` en CON_ACOMPANANTE.
- Resolución de precio en comanda desde backend (no calcula front).
- `QuickProductCreateDialog`: nombre, categoría, solo, opcional acompañante + montos (C1).
- `QuickProductPriceCreateDialog` en comanda si falta precio (Fase B).
- `QuickCategoryCreateDialog` en create producto.
- Listado productos muestra columnas precio activo (tras carga cache).
- Tests `ProductCatalogTest` + quick create cajero.

---

## 6. Propuesta UX boliche (objetivo)

### 6.1 Pantalla única “Nuevo producto” (admin / owner)

Un solo `VCard` con secciones (patrón formulario ecommerce / `form-layouts` Materialize):

| Campo (label humano) | Mapeo API |
|----------------------|-----------|
| Nombre: PACEÑA | `name` |
| Categoría: Cervezas | `category_id` (+ quick categoría) |
| Precio cliente: 40 | `solo_price` → `SOLO_CLIENTE` |
| Precio con acompañante: 80 | `companion_price` → `CON_ACOMPANANTE` |
| Monto chica: 40 | `girl_amount` |
| Monto casa: 40 | `house_amount` |
| Activo: Sí | `status` |
| Tipo: Bebida (default, colapsable avanzado) | `product_type` |

**Un botón:** Guardar → `POST /products/quick` (ya existe).

Opcional segundo botón en listado: **Producto rápido** (mismo diálogo que comanda, abierto desde `products/index`).

### 6.2 Listado productos

- Botón primario: **Producto rápido** (dialog).
- Botón secundario: **Nuevo producto (completo)** si se mantienen campos avanzados (SKU, inventario).
- Columnas: Precio cliente | Precio acomp. | Chica | Casa (desde API embebida o cache único).
- Chip **“Sin precio”** en rojo.
- Acción **Editar precios** inline o drawer, no solo navegación a subruta.

### 6.3 Edición de precios existentes

- Cambiar 40 → 45 debe ser **una acción clara** (editar o “nuevo precio vigente” desactivando anterior).
- Requiere **backend** (ver §7).

---

## 7. Cambios recomendados (sin implementar)

### 7.1 Solo frontend (aprovechar API actual)

| # | Cambio | API usada |
|---|--------|-----------|
| F1 | Reemplazar flujo `create.vue` por formulario unificado → al guardar llamar **`quickCreateProduct`** en lugar de `createProduct` + redirect | `POST /products/quick` |
| F2 | Botón **Producto rápido** en `products/index` reutilizando `QuickProductCreateDialog` | Mismo |
| F3 | Tras quick create, redirect a **listado** o ficha con snackbar, no paso intermedio “sin precios” | — |
| F4 | Renombrar labels: “Precio cliente”, “Con acompañante”, “Monto chica”, “Monto casa” | — |
| F5 | En `prices.vue`: textos humanos; alerta si ya existe activo por modalidad | `GET prices` |
| F6 | Ocultar o redirigir menú **Config. precios** placeholder hasta tener contenido | — |
| F7 | Tabs en create: “Básico” / “Avanzado” (unidad, SKU, inventario) | `POST /products` opcional para casos raros |
| F8 | `catalog/prices/index`: mostrar precios en tabla (1 fetch products + batch prices o embed) | Mejor con F9 backend |

**Esfuerzo:** bajo–medio. **Desbloquea** el 80 % del dolor reportado.

### 7.2 Requieren backend

| # | Cambio | Motivo |
|---|--------|--------|
| B1 | `PUT /products/{id}/prices/{priceId}` o `PATCH` status inactive | Corregir precios sin duplicado bloqueado |
| B2 | `POST /products` con body opcional `prices: [{ sale_mode, price, ... }]` | Un solo endpoint semántico “alta catálogo” |
| B3 | `GET /products?include=active_prices` | Eliminar N+1 y alimentar tabla |
| B4 | `GET /products/{id}` incluir `active_prices` en mapper | Ficha detalle sin segunda llamada |
| B5 | Política “cambio de precio”: desactivar anterior + crear nuevo en transacción | Historial real |
| B6 | UI vigencia fechas (opcional V2) | `starts_at` / `ends_at` |

**Esfuerzo:** medio. Necesario para **mantenimiento** diario de precios, no solo alta.

### 7.3 Priorización sugerida (alineada con auditoría maestra)

**Antes de Fase C5 / reportes / impresión:**

1. **UX Productos/Precios** — F1–F4 + B3 (esta auditoría).
2. **UX Garzón móvil** — pulido PWA/tabs (master audit).
3. **Flujo Caja** — caja compartida / visibilidad sesiones abiertas.
4. **Flujo Liquidaciones** — banners y validación pre-generate.

**Después:**

5. Reportes (C4/C5 documentación histórica).
6. Impresión.
7. Auditoría global.
8. PWA completa.

---

## 8. Comparativa flujos

| Flujo | Pantallas | POSTs | ¿Vendible al terminar? |
|-------|-----------|-------|------------------------|
| **Actual admin** | 3 | 1 product + 2 prices | Sí, tras paso 12+ |
| **Propuesto (quick en create)** | 1 | 1 quick | Sí, inmediato |
| **Actual comanda (quick)** | 0 (modal) | 1 quick | Sí, si hay comanda |
| **Ideal + edición** | 1–2 | 1 quick + PUT precio | Sí + mantenimiento |

---

## 9. Permisos relevantes

| Rol | Crear producto `POST /products` | Quick `POST /products/quick` | Agregar precio |
|-----|--------------------------------|------------------------------|----------------|
| tenant_owner / admin | Sí | Sí (con branch) | Sí |
| cashier | No (403) | Sí (C1 seeder) | `product_prices.quick_create` en comanda |
| waiter | No | No típico | Solo listar |

**Implicación:** La pantalla unificada con `quick` es adecuada para **cajera senior** y **admin**; el formulario largo `POST /products` puede quedar solo admin.

---

## 10. Validación manual sugerida (post-mejoras)

1. Admin: **Producto rápido** desde listado — Paceña, 40/80, 40/40 — verificar venta en comanda SOLO y CON_ACOMPANANTE.
2. Admin: listado muestra precios sin espera excesiva.
3. Intentar segundo precio SOLO activo — debe mostrar error claro o flujo “cambiar precio”.
4. Cajero con `products.quick_create`: mismo diálogo desde Productos (no solo comanda).
5. Garzón: solo ve productos con al menos un precio activo resoluble.

---

## 11. Cierre

El módulo **no está roto a nivel de negocio** (precios en comandas funcionan), pero **sí está roto a nivel de experiencia de catálogo**: demasiadas pantallas, lenguaje técnico, sin edición de precios, y el mejor endpoint (`/products/quick`) **no está en el camino del administrador**.

La propuesta del negocio (una sola pantalla con Precio cliente / Con acompañante / Montos) **ya coincide con lo que el backend puede hacer hoy** vía `QuickCreateProductUseCase`; el gap es **casi enteramente frontend y navegación**, más **PUT/desactivar precio** para el ciclo de vida.

**No programar en esta tarea.** Siguiente paso recomendado: implementar bloque **F1–F4** (frontend) como “Fase Productos UX” antes de reportes o impresión.

---

*Documentos relacionados: `NIGHTPOS_MASTER_AUDIT.md` §2.1, `SYSTEM_QUICK_ACTIONS_AUDIT.md` §Productos, `frontend/src/pages/nightpos/products/*`, `backend/app/Application/Product/UseCases/QuickCreateProductUseCase.php`.*
