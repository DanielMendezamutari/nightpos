# Auditoría — Kardex / Inventario / Control de Stock V1 (Frontend)

**Fecha:** 2026-06-17  
**Tipo:** Auditoría de producto + plan de implementación UI  
**Estado:** **NO IMPLEMENTADO**  
**Regla:** No se ha programado nada en esta fase.

---

## 1. Estado actual

| Área | Estado |
|------|--------|
| Menú «Inventario» en `nightpos-r4.js` | ❌ No existe |
| Páginas `/nightpos/inventory/*` | ❌ No existen |
| Campo `track_inventory` en formulario producto | ❌ **No expuesto** (API lo soporta, UI no) |
| Componentes combo inventario | ❌ No existe |
| Stock en POS-CAT / picker | ❌ No |
| Reportes stock/kardex | ❌ No — solo tab «Productos» = conciliación |
| Alertas stock bajo | ❌ No |
| Cierre caja/turno inventario | ❌ No |
| SSE inventario | ❌ No |
| Demo ecommerce inventory (Materialize) | ⚠️ Existe en `apps/ecommerce` — **no es NightPOS** |

### Respuestas UX a preguntas clave

| # | Pregunta | Respuesta frontend |
|---|----------|-------------------|
| 1 | ¿Producto permite marcar control stock? | **No** en UI |
| 2 | ¿Se ve stock actual? | **No** |
| 3 | ¿Hay pantalla kardex? | **No** |
| 4 | ¿Entradas/ajustes? | **No** |
| 5 | ¿Combo define componentes inventario? | **No** — solo «manillas por combo» (liquidación) |
| 6 | ¿Venta muestra impacto stock? | **No** |
| 7 | ¿Cierre muestra inventario? | Solo productos vendidos / conciliación |
| 8 | ¿Reportes stock? | **No** |

---

## 2. Gaps de experiencia

### Críticos para operación

1. Cajera/admin **no puede saber** cuánto queda de Paceña sin salir del sistema o contar físicamente.
2. Formulario producto mezcla **liquidación combo** (manillas) con ausencia total de **inventario**.
3. Conciliación productos en reportes **parece** inventario pero **no lo es** — riesgo de confusión.
4. Sin alertas visuales de stock bajo en shell cajera ni POS.

### Fricción esperada post-implementación (a diseñar bien)

- Carga inicial de stock de 200+ productos — necesita flujo bulk o import V1.1.
- Combo con componentes — paso extra en alta de producto.
- Kardex en móvil — tabla densa; priorizar desktop + scroll en cajera tablet.

---

## 3. Modelo UI propuesto V1

### 3.1 Productos — crear/editar

Extender `ProductFormFields.vue`:

| Campo | Tipo | Notas |
|-------|------|-------|
| Controla stock | switch | `track_inventory` |
| Stock mínimo | number | solo si controla stock |
| Stock inicial | number | solo en create o al activar control |
| Es combo inventario | switch/derivado | si tiene componentes |
| Componentes | tabla editable | producto hijo + cantidad |

**Sección separada visualmente:**

```
── Inventario ──
[✓] Controla stock
Stock mínimo: [10]
Stock inicial: [100]  (solo alta)

── Liquidación (existente) ──
Comportamiento: Combo manillas / Línea simple
Manillas por combo: 6
```

**Advertencia:**

> «Este combo no descontará inventario» — si `track_inventory=false` y sin componentes.

**Sub-sección componentes:**

| Componente | Cantidad | Acción |
|------------|----------|--------|
| Paceña botella | 6 | Eliminar |
| + Agregar componente | | picker productos |

### 3.2 Navegación — nueva sección

En `nightpos-r4.js`, después de **Catálogo** o dentro de **Operación**:

```
Inventario (inventory.access)
├── Stock actual      (inventory.stock.view)
├── Kardex            (inventory.kardex.view)
├── Entradas          (inventory.movements.create)
├── Ajustes           (inventory.adjust)
└── Stock bajo        (inventory.stock.view) — puede ser filtro en Stock actual
```

**Shell cajera básica:**

- No agregar tab principal Inventario (mantener 5 tabs).
- En **Más** → enlace «Stock actual» y «Stock bajo» si `inventory.stock.view`.
- Opcional badge en tab Piezas/Caja — V1.1.

### 3.3 Stock actual (`/nightpos/inventory/stock`)

Tabla:

| Producto | Categoría | Stock | Mínimo | Estado | Acciones |
|----------|-----------|-------|--------|--------|----------|
| Paceña | Cervezas | 93 | 20 | OK | Entrada · Ajuste · Kardex |

**Estados chip:**

- `OK` — verde
- `BAJO` — warning (`on_hand <= minimum`)
- `NEGATIVO` — error (`on_hand < 0`)
- `SIN CONTROL` — gris (no track)

**Filtros:** categoría, búsqueda, solo bajo mínimo, solo negativo.

**Acciones rápidas:** drawer Entrada / Ajuste sin salir de lista.

### 3.4 Kardex (`/nightpos/inventory/movements`)

Filtros: producto, rango fechas, tipo movimiento, usuario.

Tabla:

| Fecha | Producto | Tipo | Cant. | Antes | Después | Referencia | Usuario |

Tipos con etiquetas humanas: Venta, Venta directa, Combo, Entrada, Ajuste +, Ajuste −, Merma, Apertura.

Export CSV (mismo patrón reportes).

### 3.5 Entradas (`/nightpos/inventory/entries`)

Formulario rápido:

- Producto (autocomplete POS-CAT style)
- Cantidad
- Costo unitario opcional
- Nota

Botón **Guardar entrada** → toast + refresh SSE.

### 3.6 Ajustes (`/nightpos/inventory/adjustments`)

- Producto
- Tipo: Entrada manual / Salida manual / Merma
- Cantidad
- **Motivo obligatorio** (select + texto)
- Nota

### 3.7 Combos UI

En detalle producto combo (`products/[id]`):

Pestaña o bloque **«Componentes de inventario»** (distinto de manillas liquidación).

Empty state:

> «Sin componentes — al vender este combo no se descontará stock de otros productos.»

---

## 4. Integración caja / cierre

### Mi caja — diálogo cierre

Nueva sección colapsable (no bloqueante):

**Inventario del turno**

- Movimientos de stock en sesión: N
- Productos con stock negativo: lista
- Productos bajo mínimo: lista

Link «Ver stock» → `/inventory/stock?filter=low`.

### Cierre turno

Misma sección en página shift-close + ticket imprimible opcional V1.1.

### Conciliación existente

Mantener `ProductReconciliationPanel` — renombrar subtítulo en UI:

> «Conciliación comandado vs vendido — no reemplaza kardex»

---

## 5. Reportes

Nueva pestaña en `/nightpos/finance/reports` o sección Inventario:

| Reporte | Fuente API |
|---------|------------|
| Stock actual | `GET /reports/inventory-stock` |
| Movimientos kardex | `GET /reports/inventory-movements` |
| Stock bajo | filtro stock |
| Top vendidos | reutilizar sales report + cruce stock opcional |

Permiso: `inventory.stock.view` o `reports.access` (admin).

---

## 6. Permisos y visibilidad

| Rol | UI visible |
|-----|------------|
| Cajera básica | Más → Stock actual (solo lectura); sin entradas/ajustes |
| Cajera senior | + Kardex, Entradas |
| Admin | Menú Inventario completo + configurar componentes |
| Garzón | Sin inventario V1 (no distraer) |

Guards: `definePage({ meta: { permission: 'inventory.stock.view' } })` por ruta.

Actualizar `cashierRouting.js` allowlist: `/nightpos/inventory` para cajera con permiso.

---

## 7. SSE / tiempo real

Suscribir en `inventory/stock` y `inventory/movements`:

```javascript
on('inventory.movement.created', debouncedRefresh)
```

Banner SSE existente — sin cambio.

Opcional POS-CAT V1.1: chip «Quedan 12» bajo nombre producto si `track_inventory`.

---

## 8. Flujo manual de validación (15 pasos)

1. Crear Paceña, control stock, inicial 100.
2. Vender 1 Paceña (venta directa o comanda).
3. Stock = 99.
4. Kardex: SALE −1.
5. Crear Combo 6 Paceñas.
6. Agregar componente Paceña ×6.
7. Vender 1 combo (comanda si tiene manillas).
8. Stock Paceña = 93.
9. Kardex: COMBO_COMPONENT_SALE −6.
10. Entrada +20 → 113.
11. Ajuste salida −3 → 110.
12. Fijar mínimo 15 — estado OK.
13. Vender hasta bajar bajo 15 — chip BAJO.
14. Reporte stock refleja movimientos.
15. Cierre caja muestra advertencias sin bloquear.

---

## 9. Riesgos UX

| Riesgo | Mitigación UI |
|--------|---------------|
| Confundir conciliación con kardex | Labels claros |
| Confundir manillas combo con componentes stock | Secciones separadas en formulario |
| Demasiados clics para entrada | Drawer rápido desde stock |
| Cajera ve menú admin inventario completo | Permisos + shell Más limitado |
| Stock negativo asusta | Copy: «Permitido en V1 — revisar entradas» |

---

## 10. Plan por fases (frontend)

### INV-FE-1 — Producto + componentes
- `ProductFormFields`: track_inventory, mínimo, inicial
- Pestaña componentes en detalle producto
- API wiring components CRUD

**Estimado:** 2 días

### INV-FE-2 — Sección Inventario
- Páginas stock, kardex, entradas, ajustes
- Nav R4 + permisos
- Tablas + filtros + drawers

**Estimado:** 3 días

### INV-FE-3 — Integración operación
- Warnings cierre caja/turno
- Enlaces en Más (cajera)
- Renombrar conciliación

**Estimado:** 1 día

### INV-FE-4 — Reportes + SSE
- Tabs reportes inventario
- `useOperationalEvents` en stock
- CSV export

**Estimado:** 1–2 días

### INV-FE-5 — QA + pulido móvil
- Prueba 15 pasos manual
- Ajustes densidad tablas tablet

**Estimado:** 1–2 días

**Total frontend:** ~8–10 días (paralelo backend INV-3/4)

---

## 11. Qué entra en V1 / V1.1 / V2

### V1 UI

- Formulario producto: control stock, mínimo, inicial, componentes
- Menú Inventario (admin/senior)
- Stock actual, kardex, entradas, ajustes
- Más cajera: ver stock (lectura)
- Advertencias cierre
- Reportes stock + movimientos
- SSE refresh stock

### V1.1 UI

- Badge stock en POS-CAT
- Import CSV opening
- Anulación venta + reversa kardex
- Bloqueo venta sin stock (config)

### V2 UI

- Compras, proveedores
- Conteo físico / inventario cíclico
- Traspasos sucursales
- Dashboard inventario gerencial

---

## 12. Archivos previstos (referencia implementación futura)

| Archivo | Acción futura |
|---------|---------------|
| `components/nightpos/forms/ProductFormFields.vue` | Extender |
| `pages/nightpos/inventory/stock/index.vue` | Crear |
| `pages/nightpos/inventory/movements/index.vue` | Crear |
| `pages/nightpos/inventory/entries/index.vue` | Crear |
| `pages/nightpos/inventory/adjustments/index.vue` | Crear |
| `components/nightpos/inventory/*` | Crear |
| `api/inventory.js` | Crear |
| `navigation/vertical/nightpos-r4.js` | Sección Inventario |
| `composables/useCashierMoreMenu.js` | Enlaces stock |
| `utils/cashierRouting.js` | Allowlist inventory |
| `pages/nightpos/cash/index.vue` | Warnings cierre |
| `pages/nightpos/finance/reports/index.vue` | Tabs inventario |

---

## 13. Recomendación

1. **No mostrar** `track_inventory` al usuario hasta que INV-FE-1 esté listo (hoy el API guarda pero UI ignora — inconsistente).
2. Implementar **INV-1 + INV-2 backend antes** de UI stock (evitar pantalla vacía).
3. Tratar **conciliación** y **kardex** como conceptos distintos en toda la UI.
4. Adoptar regla **stock negativo permitido** — reflejar en copy y chips NEGATIVO, no modal bloqueante.
5. Incluir **15 tests manuales** en V1-98 QA cuando el módulo exista.

---

## 14. Impacto en V1 RELEASE CANDIDATE

Si el negocio define kardex como **requisito V1** (esta auditoría), el RELEASE CANDIDATE **no debe declararse** hasta completar INV-1…INV-5 + QA inventario.

Si kardex queda **opcional piloto**, mantener acta actual «sin kardex» hasta que el módulo esté entregado.

---

*Documento de auditoría y plan UI. Sin cambios de código en esta entrega.*
