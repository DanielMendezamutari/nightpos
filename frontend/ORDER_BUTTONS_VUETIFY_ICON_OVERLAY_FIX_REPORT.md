# ORDER BUTTONS — VUETIFY ICON & OVERLAY FIX REPORT

**Fecha:** 2026-06-05  
**Pantalla afectada:** Operación → Comandas → Cobrar comandas → Ver / corregir  
**Archivo principal:** `frontend/src/pages/nightpos/orders/[id].vue`

---

## Síntomas reportados

1. Botones principales (**Agregar**, **Cobrar**, **Cancelar**, **Corregir**) no respondían al click.
2. Consola mostraba:
   - `Uncaught (in promise) Error: Could not find aliased icon "$primary"` (icons.mjs)
   - `Uncaught (in promise) TypeError: Cannot read properties of undefined (reading 'classList')` (scrollStrategies.mjs)

---

## Error 1 — `$primary` icono no encontrado

### Causa

`ChargeOrderModal.vue` tenía un `VAlert` con `type="primary"`:

```vue
<!-- INCORRECTO: Vuetify 3 VAlert solo acepta success | info | warning | error -->
<VAlert type="primary" variant="tonal" class="mb-4">
  Total a cobrar: ...
</VAlert>
```

Vuetify 3 usa aliases de icono (`$success`, `$info`, `$warning`, `$error`) para el ícono del tipo en `VAlert`. Cuando se pasa `type="primary"`, busca el alias `$primary` que no existe, lanzando el error.

También se encontró `type="secondary"` en `frontend/src/pages/nightpos/waiter/orders/[id].vue` (línea 328), con el mismo problema potencial.

### Corrección

**`ChargeOrderModal.vue`** (línea ~148):
```vue
<!-- CORRECTO -->
<VAlert type="info" variant="tonal" class="mb-4">
  Total a cobrar: ...
</VAlert>
```

**`waiter/orders/[id].vue`** (línea 328):
```vue
<!-- CORRECTO -->
<VAlert type="info" variant="tonal">
  Comanda en solo lectura
</VAlert>
```

### Regla a seguir

`VAlert` en Vuetify 3 solo acepta `type` de los valores: `"success"`, `"info"`, `"warning"`, `"error"`.  
Para mostrar texto en color primario, usar `color="primary"` sin `type`, o usar `type="info"`.  
**Nunca** usar `type="primary"`, `type="secondary"`, ni ningún color personalizado como `type`.

---

## Error 2 — `classList` / scroll strategy

### Causa

`ChargeOrderModal.vue` renderizaba `QuickOpenCashDialog` (que es a su vez un `VDialog`) **dentro del slot de `VDialog`** padre:

```vue
<!-- INCORRECTO: dialogo anidado dentro del slot del VDialog padre -->
<VDialog :model-value="modelValue" persistent ...>
  <VCard> ... </VCard>

  <QuickOpenCashDialog    <!-- ← otro VDialog dentro del slot -->
    v-model="showOpenCash"
    @opened="onCashOpened"
  />
</VDialog>
```

Vuetify monta `VDialog` (y `VOverlay` internamente) en el `document.body` vía `Teleport`, gestionando su propia estrategia de scroll. Al colocar otro `VDialog` como hijo del slot, Vuetify intenta aplicar su `scrollStrategy` con referencia al elemento scroll container, que queda `undefined` porque el contexto ya fue teleportado. Esto produce:

```
Cannot read properties of undefined (reading 'classList')
```

### Corrección

`QuickOpenCashDialog` se movió **fuera** del `VDialog` padre, al mismo nivel en el template:

```vue
<!-- CORRECTO -->
<VDialog :model-value="modelValue" persistent ...>
  <VCard> ... </VCard>
</VDialog>

<QuickOpenCashDialog   <!-- ← al mismo nivel, no dentro del slot -->
  v-model="showOpenCash"
  @opened="onCashOpened"
/>
```

### Regla a seguir

**Nunca colocar un `VDialog`, `VMenu`, `VOverlay` u otro componente con Teleport dentro del slot de otro `VDialog`.**  
Todos los diálogos secundarios deben estar en el mismo nivel del template o en el componente padre.

---

## Archivos modificados

| Archivo | Cambio |
|---|---|
| `frontend/src/components/nightpos/orders/ChargeOrderModal.vue` | `type="primary"` → `type="info"`; `QuickOpenCashDialog` movido fuera del `VDialog` |
| `frontend/src/pages/nightpos/waiter/orders/[id].vue` | `type="secondary"` → `type="info"` |
| `frontend/src/components/nightpos/orders/OrderActionsBar.vue` | Agregados logs DEV en cada botón |
| `frontend/src/pages/nightpos/orders/[id].vue` | Logs DEV en `openChargeDialog`, `openCancelDialog`, `openAddProductDialog`; panel debug en DEV |

---

## Helpers de diagnóstico agregados (modo DEV)

### `OrderActionsBar.vue`

Cada botón ahora emite al log en DEV:
```
[OrderActionsBar] add clicked
[OrderActionsBar] charge clicked
[OrderActionsBar] send clicked
[OrderActionsBar] cancel clicked
```

### `orders/[id].vue`

Cada handler de apertura de diálogo emite al log en DEV:
```
[OrderDetail] opening add dialog
[OrderDetail] opening charge dialog
[OrderDetail] opening cancel dialog
```

El `watchEffect` existente en DEV registra continuamente:
```
[order-detail actions] { isOpen, canCharge, showAdd, showCharge, showCancel, ... }
```

Panel visual de debug (solo en `import.meta.env.DEV`): muestra `order.status`, todos los flags de visibilidad, y tres botones directos **DEBUG Agregar / DEBUG Cobrar / DEBUG Cancelar** que llaman directamente a las funciones de apertura de diálogo, sin pasar por `OrderActionsBar`. Si los botones de debug funcionan y los de `OrderActionsBar` no, el problema es en `OrderActionsBar`. Si los de debug tampoco funcionan, el problema es en los VDialog o el estado.

---

## Checklist de validación

- [ ] No aparece `Error: Could not find aliased icon "$primary"` en consola
- [ ] No aparece `TypeError: Cannot read properties of undefined (reading 'classList')` en consola
- [ ] Click en **Agregar** abre el modal de productos (log visible en DEV)
- [ ] Click en **Cobrar** abre el modal de cobro (log visible en DEV)
- [ ] Click en **Cancelar** abre la confirmación (log visible en DEV)
- [ ] Click en **Corregir** en una fila abre el diálogo de acciones
- [ ] Cambiar producto en una línea funciona y actualiza la comanda
- [ ] Consola limpia al navegar a la pantalla
