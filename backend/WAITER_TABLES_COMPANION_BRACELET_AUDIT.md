# Auditoría operativa — Mesas, consumo con acompañante y manillas (Backend)

**Fecha:** 2026-06-16  
**Estado:** Auditoría completada — **sin implementación**  
**Par frontend:** `frontend/WAITER_TABLES_COMPANION_BRACELET_AUDIT.md`  
**Contexto:** Post P0 Fast Operation Mode — tres gaps operativos pendientes.

---

## Resumen ejecutivo

| Tema | Estado actual | Gap principal |
|------|---------------|---------------|
| **Mis mesas garzón** | No existe modelo de mesa ni asignación garzón↔mesa | Garzón escribe `table_label` libre; no hay LIBRE/OCUPADA |
| **Chica en CON_ACOMPANANTE** | Backend guarda `girl_user_id` pero **no expone `girl_name`** en ítems simples | Mapper incompleto; combos sí tienen `girl_name` en allocations |
| **Manilla = acompañante** | Lógica backend coherente en liquidaciones; terminología interna distinta a operación | `GIRL_CONSUMPTION` vs `GIRL_BRACELET` vs `GIRL_BRACELET_ALLOCATION` confunde UI |

---

## PARTE 1 — Mesas / «Mis mesas» del garzón

### 1. Estado actual de mesas

#### ¿Existe tabla real de mesas?

**No.** No hay entidad `service_tables`, `tables` ni equivalente.

Lo que existe:

| Artefacto | Tabla / campo | Rol |
|-----------|---------------|-----|
| Ambiente / salón | `service_areas` | Catálogo de zonas (`code`, `name`, `area_type`: `TABLE`/`BAR`/`OTHER`) |
| Referencia en comanda | `orders.table_label` | Texto libre (varchar 50), ej. «Mesa 5», «VIP» |
| FK opcional | `orders.service_area_id` | Apunta a `service_areas`; no identifica mesa numérica |
| Garzón responsable | `orders.waiter_user_id` | Quién atiende la comanda, no pre-asignación de mesas |

Migración: `2026_06_07_100029_phase_c3_master_data.php` crea `service_areas` y añade `service_area_id` a `orders`.

#### ¿O solo `service_areas` + `table_label` libre?

**Correcto.** El subtítulo del admin lo confirma: *«Opcional en comandas — puede usarse junto con etiqueta libre.»*

El garzón hoy:
- Toca un ambiente (`service_area_id`) **o** escribe texto (`table_label`) en `waiter/orders/new.vue`
- No elige mesa 1, 2, 3 de un salón preconfigurado

#### ¿Cómo se define hoy una mesa ocupada?

**No hay concepto de mesa ocupada en BD.** Solo se infiere indirectamente:

- Existe comanda con `table_label` = «Mesa 3» y `status` ∈ `{OPEN, SENT_TO_BAR, IN_PREPARATION, READY}` → operativamente «ocupada»
- No hay índice ni constraint que vincule mesa física ↔ comanda
- El KPI `active_tables` del dashboard garzón (`GetWaiterDashboardUseCase`) es **conteo de comandas activas del garzón**, no mesas distintas:

```php
'active_tables' => $this->orders->countForWaiter(..., statuses: ['OPEN','SENT_TO_BAR',...])
```

Nombre engañoso: debería llamarse `active_orders`.

#### ¿Qué pasa si hay dos comandas abiertas en la misma mesa?

**Permitido.** No hay validación en `CreateOrderUseCase` ni unique constraint sobre `(branch_id, table_label, status activo)`.

Riesgo real: dos garzones (o el mismo) abren «Mesa 5» → dos comandas OPEN con mismo label → cajera ve dos filas; «Mis mesas» no puede resolver cuál abrir con un tap.

#### ¿Cómo evitar duplicar comanda en la misma mesa?

**Hoy no se evita.** Opciones futuras (propuesta en §1.4):
- Guard en `CreateOrderUseCase` / endpoint tap-mesa: rechazar si hay comanda activa en `service_table_id`
- Índice parcial único (PostgreSQL) o check transaccional en aplicación

#### ¿Cómo se libera la mesa al cobrar?

**Por convención, no por entidad mesa:**
1. `ChargeOrderUseCase` → `orders.status = BILLED`
2. La comanda sale de scopes activos (`cashier_chargeable`, waiter `active`)
3. No queda registro «mesa libre» — solo ausencia de comanda activa con ese identificador

Cancelación (`CancelOrderUseCase`) libera igual. No hay evento SSE de mesa (gap ya auditado en Fast Operation).

#### ¿Cómo asignar mesas a un garzón?

**No implementado.** No existe:
- `waiter_table_assignments`
- `waiter_service_area_assignments` (más allá de permiso `settings.service_areas` para listar ambientes)
- Configuración admin «Carlos → VIP → mesas 1-7»

Cualquier garzón con `orders.create` abre comanda con cualquier `table_label`. La cajera puede asignar `waiter_user_id` al crear comanda (no garzón).

#### ¿Qué cambios backend hacen falta?

Ver propuesta §1.4 y plan §4.

---

### 2. APIs garzón existentes (referencia)

| Endpoint | Use case | Notas |
|----------|----------|-------|
| `GET /waiter/dashboard` | `GetWaiterDashboardUseCase` | KPIs + 6 comandas recientes; **no mesas** |
| `GET /waiter/orders?scope=` | `ListWaiterOrdersUseCase` | Por scope, filtrado por `waiter_user_id` |
| `GET /waiter/service-areas` | `ListServiceAreasUseCase` | Lista ambientes activos; **sin asignación** |
| `POST /orders` | `CreateOrderUseCase` | Requiere `table_label` o `service_area_id` |

Tests: `PhaseC4WaiterTest.php` valida dashboard, ownership de comandas, service areas.

---

### 3. Propuesta de modelo de datos (solo diseño)

#### Tabla `service_tables`

Mesas físicas dentro de un salón.

| Campo | Tipo | Notas |
|-------|------|-------|
| `id` | PK | |
| `tenant_id`, `branch_id` | FK | |
| `service_area_id` | FK | Salón VIP, Principal, etc. |
| `code` | string | Ej. `VIP-01` |
| `label` | string | «Mesa 1» (display) |
| `sort_order` | int | Orden en grid garzón |
| `status` | enum | `active` / `inactive` |

#### Tabla `waiter_table_assignments`

Asignación pre-turno (admin/cajera configura; garzón solo consume).

| Campo | Tipo | Notas |
|-------|------|-------|
| `id` | PK | |
| `tenant_id`, `branch_id` | FK | |
| `waiter_user_id` | FK | Garzón |
| `service_table_id` | FK | Mesa |
| `official_shift_id` | FK nullable | Si null = plantilla permanente; si set = solo ese turno |
| `assigned_by_user_id` | FK | Admin/cajera |
| `assigned_at` | timestamp | |

Alternativa más simple: asignación a nivel salón (`waiter_service_area_assignments`) + mesas numeradas fijas por salón sin asignación 1:1 (garzón ve todas las mesas de «su» salón).

#### Cambio en `orders`

| Campo nuevo | Notas |
|-------------|-------|
| `service_table_id` | FK nullable; reemplaza uso principal de `table_label` libre |
| Mantener `table_label` | Denormalizado para tickets/histórico |

#### Resolución LIBRE / OCUPADA (query)

Para cada mesa asignada al garzón en turno abierto:

```sql
-- Pseudológica
LEFT JOIN orders o ON o.service_table_id = st.id
  AND o.official_shift_id = :shift
  AND o.status IN ('OPEN','SENT_TO_BAR','IN_PREPARATION','READY')
-- OCUPADA si o.id IS NOT NULL
```

---

### 4. Propuesta de endpoints (solo diseño)

| Método | Ruta | Acción |
|--------|------|--------|
| `GET` | `/api/v1/waiter/my-tables` | Lista mesas asignadas + `{ status: 'FREE'|'OCCUPIED', order_id?, order_summary? }` |
| `POST` | `/api/v1/waiter/my-tables/{tableId}/open` | Si LIBRE → crea comanda (`service_table_id`, `waiter_user_id`, turno) y devuelve `order_id`; si OCUPADA → devuelve comanda existente (idempotente tap) |
| CRUD admin | `/api/v1/settings/service-tables` | Gestión mesas por salón |
| CRUD admin | `/api/v1/settings/waiter-table-assignments` | Asignar mesas/salones a garzones |

Permisos sugeridos: `settings.service_tables.manage`, `settings.waiter_assignments.manage`, `waiter.my_tables`.

---

## PARTE 2 — CON ACOMPAÑANTE simple no muestra chica

### 1. ¿Backend devuelve `girl_name`?

| Caso | `girl_user_id` | `girl_name` | Dónde |
|------|----------------|-------------|-------|
| CON_ACOMPANANTE simple | ✅ `OrderMapper::item` | ❌ **No** | Solo ID |
| Combo / allocation | ✅ en `allocations[]` | ✅ | `OrderMapper::allocation()` ← join en repo |
| Lista comanda (brief) | — | — | Sin ítems en listados |

`OrderPresentationService::presentOrder()` usa `OrderMapper::item()` sin enriquecer nombre de chica para ítem simple.

El repositorio de allocations sí resuelve nombre:

```php
// EloquentOrderItemAllocationRepository
girlName: $model->girl?->name,
```

**Conclusión:** el dato existe en BD (`order_items.girl_user_id` → `users.name`); **no se proyecta al JSON** del ítem simple.

### 2. ¿Se pierde en mapper?

**Sí.** `OrderMapper::item()` líneas 84-95 incluyen `girl_user_id` pero omiten `girl_name`.

No es bug de persistencia: `AssignOrderItemGirlUseCase`, `AddOrderItemUseCase` y `UpdateOrderItemUseCase` guardan `girl_user_id` correctamente.

### 3. Liquidaciones y cobro

Al cobrar (`ChargeOrderUseCase` → sale items):
- CON_ACOMPANANTE con `girl_user_id` y sin allocations → liquidación `GIRL_CONSUMPTION`
- Descripción backend: `'Consumo con acompañante — {producto} ({sale_number})'`
- **No incluye nombre de chica en descripción** (solo producto)

Combo allocations → `GIRL_BRACELET_ALLOCATION` por chica/unidades.  
Manillas manuales (`bracelets` table) → `GIRL_BRACELET`.

### 4. Tickets / precuenta (backend)

Los endpoints de impresión/precheck devuelven la misma estructura de orden vía `OrderPresentationService` — **mismo gap**: sin `girl_name` en ítem simple.

`PrintableOrderTicket` en frontend muestra «Chica asignada» genérico si `girl_user_id` (sin nombre).

---

## PARTE 3 — Manilla = consumo con acompañante

### Mapa de conceptos backend

| Concepto operativo | Mecanismo técnico | Origen |
|--------------------|-------------------|--------|
| 1 consumo con acompañante (simple) | `sale_mode = CON_ACOMPANANTE`, `girl_user_id`, `girl_amount` | Comanda → venta |
| 1 manilla (simple) | Mismo registro; liquidación `GIRL_CONSUMPTION` | No usa tabla `bracelets` |
| N manillas en combo | `order_item_allocations` → `GIRL_BRACELET_ALLOCATION` | Producto `requires_allocation` |
| Manilla manual legacy | `bracelets` → `GIRL_BRACELET` | Módulo Servicios → Manillas |

**Regla de negocio ya implementada en liquidaciones:**
- Simple acompañante ≠ registro en `bracelets`
- Combo = allocations con unidades
- Manual = excepción administrativa

### Manillas manuales (`bracelets`)

| Aspecto | Estado |
|---------|--------|
| API | `POST/GET /api/v1/bracelets` |
| Permisos | `bracelets.access`, `bracelets.create` |
| Roles default | Cajera/admin (no garzón) |
| Uso en turno | `GenerateCurrentShiftSettlementsUseCase` incluye `GIRL_BRACELET` |

**Recomendación (no implementar aún):** mantener backend; restringir menú a admin/ajustes avanzados; renombrar UI «Manillas manuales / ajustes».

### Terminología en liquidaciones (backend)

| `source_type` | Label actual frontend (`settlements/[id].vue`) |
|---------------|-----------------------------------------------|
| `GIRL_CONSUMPTION` | Consumo con acompañante |
| `GIRL_BRACELET` | Manilla |
| `GIRL_BRACELET_ALLOCATION` | (sin label dedicado en mapa — cae al raw type) |

Propuesta: unificar copy en descripciones generadas (`createItem`) para incluir nombre chica y «1 manilla» donde aplique.

---

## Riesgos

| # | Riesgo | Impacto |
|---|--------|---------|
| R1 | Implementar mesas sin migrar comandas históricas | Comandas viejas solo con `table_label` invisible en «Mis mesas» |
| R2 | Duplicar comandas misma mesa en producción actual | Caos en caja hasta tener guard |
| R3 | Añadir `girl_name` solo en frontend resolviendo IDs | N+1 requests; inconsistente con combos |
| R4 | Ocultar manillas manuales sin comunicar | Operaciones que dependen del módulo pierden acceso |
| R5 | Renombrar `GIRL_*` en backend | Rompe reportes, tests, integraciones |
| R6 | `active_tables` mal nombrado | Confusión en diseño de dashboard mesas |

---

## Plan de implementación por fases (propuesta)

### Fase A — Quick win acompañante (1-2 días)

**Backend**
1. Enriquecer `OrderMapper::item()` / `OrderPresentationService` con `girl_name` (batch load users por `girl_user_id`).
2. Incluir nombre en descripción liquidación `GIRL_CONSUMPTION` (opcional).
3. Test regresión API order detail con CON_ACOMPANANTE.

**Sin migraciones. Sin tocar SSE/CBA.**

### Fase B — Mesas MVP (1 sprint)

1. Migraciones `service_tables`, `waiter_table_assignments`, `orders.service_table_id`.
2. CRUD admin mesas + asignación garzón/salón.
3. `GET /waiter/my-tables`, `POST .../open` con guard anti-duplicado.
4. Seed/bootstrap mesas por sucursal demo.

### Fase C — UX garzón «Mis mesas» (frontend, ver doc par)

Reemplazar home garzón / flujo `new.vue` por grid mesas.

### Fase D — Lenguaje operativo unificado

1. Glosario UI: «Manilla» = unidad de acompañante en comanda.
2. Labels liquidación/reportes alineados.
3. Ocultar/mover módulo manillas manuales en nav.

### Fase E — Endurecimiento

1. Renombrar KPI `active_tables` → `active_orders` (breaking API menor).
2. Eventos SSE `table.status_changed` (futuro, post mesas).
3. Migración opcional `table_label` → `service_table_id` en comandas abiertas.

---

## Respuestas rápidas (checklist Parte 1)

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Tabla real de mesas? | **No** |
| 2 | ¿Solo areas + label libre? | **Sí** |
| 3 | ¿Mesa ocupada hoy? | Inferida por comanda activa + mismo `table_label` (informal) |
| 4 | ¿Dos comandas misma mesa? | **Permitido** |
| 5 | ¿Evitar duplicado? | **No hay guard** |
| 6 | ¿Liberar al cobrar? | Status BILLED → sale de activos; no entidad mesa |
| 7 | ¿Asignar mesas a garzón? | **No existe** |
| 8 | ¿Cambios necesarios? | Modelo mesas + assignments + endpoints + guards |

---

## Referencias de código

- `CreateOrderUseCase.php`, `orders` migration
- `service_areas` — `2026_06_07_100029_phase_c3_master_data.php`
- `OrderMapper.php`, `OrderPresentationService.php`
- `GetWaiterDashboardUseCase.php`, `PhaseC4WaiterTest.php`
- `EloquentStaffSettlementRepository.php` — `GIRL_CONSUMPTION`, `GIRL_BRACELET`, `GIRL_BRACELET_ALLOCATION`
- `CreateBraceletUseCase` / rutas `bracelets`
