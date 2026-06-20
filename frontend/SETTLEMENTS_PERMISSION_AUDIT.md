# AUDITORÍA — Liquidaciones y permisos cajera (Frontend)

**Fecha:** 2026-06-16  
**Estado:** Diagnóstico — **sin cambios de código**  
**Alcance:** UI liquidaciones, menú cajera, permisos, cache, mensajes generate/pay

---

## 1. Conclusión ejecutiva

| Problema | ¿Es frontend? | Causa |
|----------|---------------|-------|
| **1 — Generar muestra "no hay"** | **Parcial** | Backend devuelve `created_items = 0` o overview vacío por **scope `my_cash_session`**. Frontend interpreta correctamente el JSON pero **no distingue** "generado en turno por otro scope" vs "realmente vacío". |
| **2 — No aparecen para pagar** | **Sí (síntoma)** / **Backend (origen)** | `useCurrentShiftSettlements` pinta lo que devuelve API. Cajera recibe arrays vacíos. Tabs Garzones/Chicas/Limpieza no tienen filas → botón Pagar no aparece. |
| **3 — Permisos nuevos no muestran opciones** | **Sí** | Permisos en cookie `userData` al login. **No hay refresh de permisos al arrancar.** Menú cajera filtrado por `can()` sobre array stale. |

**Veredicto:** el frontend **no bloquea** liquidaciones por permiso en demo (tiene `settlements.access`). El cuello de botella es **respuesta API vacía** + **menú/permisos cacheados**.

---

## 2. Parte 1 — Rutas y guards vs backend

| Ruta Vue | `meta.permission` | Botones extra |
|----------|-------------------|---------------|
| `nightpos-settlements` (index) | `settlements.access` | Generate: `v-if="can('settlements.generate')"` |
| `nightpos-settlements-waiters` | `settlements.access` | Pay: `can('settlements.pay')` |
| `nightpos-settlements-girls` | `settlements.access` | Idem |
| `nightpos-settlements-cleaning` | `settlements.access` | Idem |
| `nightpos-settlements-history` | `settlements.history` | — |

Fuente: `definePage({ meta: { permission: ... } })` en cada `.vue`; guard en `plugins/1.router/guards.js`.

**Cajera demo:** pasa guard de `settlements.access` → puede entrar a liquidaciones vía tab **Más**.

---

## 3. Parte 5 — Navegación y shell cajera

### 3.1 Menú vertical principal

`useNightPosNavItems.js` líneas 145–148:

```javascript
if (isWaiterStaff(auth.user) || isGirlStaff(auth.user) || isBasicCashierStaff(auth.user))
  return []
```

**Cajera básica no ve el menú lateral** (Finanzas → Liquidaciones). Solo accede por:
- Tab **Más** (`useCashierMoreMenu.js`)
- Enlaces desde Caja / Consola de turno

### 3.2 Tab «Más» — filtrado por permiso

`useCashierMoreMenu.js`: cada ítem tiene `permission: 'slug'`. Visible si `can(item.permission)`.

Entrada liquidaciones:

```javascript
{ title: 'Liquidaciones', to: 'nightpos-settlements', permission: 'settlements.access' }
```

**Si el usuario agregó un permiso nuevo al rol pero no re-logueó:** `can('nuevo.permiso')` → `false` → ítem no aparece.

### 3.3 Rol `manager` en frontend

`cashierRouting.js` incluye `'manager'` en `FULL_MENU_ROLES` → menú completo. **No existe rol `manager` en seeder backend** — solo referencia frontend legacy.

---

## 4. Parte 4 — JWT, Pinia, CASL

| Capa | Almacén | Actualización permisos |
|------|---------|------------------------|
| Pinia `auth.user.permissions` | Cookie `userData` | Login, `persistSession`, **`fetchMe()`** |
| CASL `ability` | Cookie `userAbilityRules` | `syncAbilitiesFromUser()` |
| Router guard | `auth.hasPermission(meta.permission)` | Hydrate cookies al navegar |

**`fetchMe()`** (`stores/auth.js` línea 212): existe pero **solo se llama** desde `orders/[id].vue` (tras ciertas acciones). **No se ejecuta al bootstrap de la app.**

**`refreshSession()`:** actualiza token JWT; **no toca `user.permissions`.**

### Implicación problema 3

1. Admin edita rol cajera en BD ✓  
2. Cajera sigue con sesión activa → cookie vieja ✓  
3. `can('settings.service_tables')` → false ✓  
4. Ítem no aparece en Más ✓  

**Solución operativa (sin código):** logout + login.  
**Solución producto (futura):** llamar `/auth/me` al iniciar o tras editar permisos del propio usuario.

---

## 5. Parte 6 — Pantalla Generar (index.vue)

Archivo: `frontend/src/pages/nightpos/settlements/index.vue`

### 5.1 Lógica del toast tras generate

```javascript
if (result.created_items > 0) {
  notify(`Liquidaciones generadas (${result.created_items} líneas nuevas)`)
}
else if ((result.settlement_summary?.generated_pending_count ?? 0) > 0) {
  notify('No hay nuevas liquidaciones para generar. Tienes pagos pendientes por pagar.', 'warning')
}
else {
  notify('No hay liquidaciones nuevas para generar en este turno/caja.', 'info')
}
```

| Escenario | Mensaje UI | ¿Correcto según API? |
|-----------|------------|----------------------|
| Admin ya generó; cajera pulsa Generar | Info/warning "no hay nuevas" | ✓ API `created_items=0` |
| Generate creó ítems pero overview vacío (scope) | Success con N líneas → refresh → alerta vacío | **Inconsistente UX** — backend creó pero read filtra |
| Sin permiso generate | Botón oculto (`v-if`) | ✓ |

**No existe** el string exacto *"No existen liquidaciones"* en frontend. Mensajes equivalentes:
- *"No hay liquidaciones para este turno/caja."* (VAlert línea ~401)
- *"No hay liquidaciones nuevas para generar en este turno/caja."* (notify generate)
- Backend GET: *"Sin liquidaciones para este turno/caja."*

### 5.2 Alertas de estado vacío

```javascript
const summaryHasData = computed(() => /* total_waiters|girls|cleaning|pending > 0 */)

const hasPendingPayments = computed(() =>
  settlementSummary.generated_pending_count > 0 || summary.total_pending > 0
)
```

Si API devuelve `summary` con ceros (por `empty_overview`) pero `settlement_summary.generated_pending_count > 0` en `context`:
- Puede mostrar warning de pendientes **sin filas** en tabs → confuso.

Scope label en UI:

```javascript
if (context.value?.scope === 'my_cash_session') return 'Mostrando: Mi caja actual'
```

**Evidencia:** la UI **sí expone** el scope; el usuario puede ver "Mi caja actual" con totales en cero mientras admin ve montos.

---

## 6. Parte 7 — Pantalla Pagar

Archivos: `waiters.vue`, `girls.vue`, `cleaning.vue`

```javascript
const canPay = computed(() => can('settlements.pay'))
// Botón Pagar: v-if="canPay && item.status === 'PENDING'"
```

| Check | Cajera demo |
|-------|-------------|
| Permiso `settlements.pay` | ✓ en seeder |
| Filas en tabla | **0** si API overview vacío |
| `useSettlementPayment` | Llama `POST .../mark-paid` — funcionaría si tuviera ID |

**Admin puede pagar:** tablas pobladas. **Cajera no:** no es `v-if` de permiso, es **datos vacíos**.

---

## 7. Composables de datos

| Composable | Endpoint | Notas |
|------------|----------|-------|
| `useCurrentShiftSettlements` | GET `current-shift` | No envía `scope`; backend resuelve por rol |
| `useSettlementPendingSources` | GET `pending-sources` | Skip si `!can('settlements.pending_sources')` |
| `useSettlementPayment` | POST `mark-paid` | Requiere caja abierta (banner `SettlementsCashBanner`) |

---

## 8. Parte 8 — Consistencia UI vs backend scope

| Pantalla | Usa scope backend | Muestra scope al usuario |
|----------|-------------------|--------------------------|
| Resumen index | Sí (`context.scope`) | Sí (`scopeLabel`) |
| Garzones/Chicas/Limpieza | Mismos datos composable | No repite scope |
| Historial | Shift filter manual | Admin-oriented |
| Cierre caja (`shifts/close.vue`) | Carga settlements aparte | Puede divergir si no usa mismo scope |

---

## 9. Matriz rápida: ¿dónde se evalúa permiso settlements?

| Permiso | Route guard | Nav vertical | Menú Más | v-if botón |
|---------|:-----------:|:------------:|:--------:|:----------:|
| settlements.access | ✓ | ✓ (no cajera básica) | ✓ | — |
| settlements.generate | ✗ | ✗ | ✗ | ✓ index |
| settlements.pay | ✗ | ✗ | ✗ | ✓ tabs |
| settlements.history | ✓ history | ✓ | ✓ | — |
| settlements.pending_sources | ✗ | ✗ | ✗ | composable |

**Permisos con backend OK pero UI limitada para cajera:** todos los de settlements — la cajera **puede entrar** pero ve vacío por scope API.

---

## 10. Recomendaciones (diagnóstico)

1. Tras fix backend de scope, revalidar UX generate (mensaje cuando `created_items>0` pero overview vacío).
2. Documentar en capacitación: permisos nuevos → **cerrar sesión y volver a entrar**.
3. Considerar mostrar `settlement_summary` del `context` cuando `summary` esté vacío por `empty_overview`.
4. Opcional: entrada "Liquidaciones" más visible en shell (hoy solo Más) — decisión producto, no bug permiso.

---

## 11. Archivos revisados

| Archivo | Rol |
|---------|-----|
| `pages/nightpos/settlements/index.vue` | Generate, alertas vacío |
| `pages/nightpos/settlements/waiters.vue` | Pay UI |
| `composables/useCurrentShiftSettlements.js` | Carga overview |
| `composables/useCashierMoreMenu.js` | Menú Más |
| `composables/useNightPosNavItems.js` | Nav vacío cajera |
| `composables/useNightPosPermissions.js` | Wrapper `can()` |
| `stores/auth.js` | Cookies, fetchMe, refresh |
| `utils/cashierRouting.js` | isBasicCashierStaff |
| `plugins/1.router/guards.js` | meta.permission |
| `navigation/vertical/nightpos-r4.js` | Nav admin settlements |
