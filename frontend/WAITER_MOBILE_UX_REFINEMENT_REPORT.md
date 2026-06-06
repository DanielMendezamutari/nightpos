# WAITER_MOBILE_UX_REFINEMENT_REPORT

Refinamiento visual del modo garzón móvil (Jun 2026). Objetivo: comandar rápido desde celular, sin pantallas cargadas ni textos técnicos.

---

## 1. Qué se simplificó

| Área | Antes | Después |
|------|--------|---------|
| Cabecera | Barra genérica / datos extra | Solo nombre, sucursal, conexión, salir (+ título de pantalla y volver) |
| Inicio | Mezcla de acciones y tablas | 4 KPI cards táctiles + últimas comandas en cards |
| Listados | Tablas o filas densas | `WaiterOrderCard` con mesa, estado en español, total, ítems |
| Estados | `OPEN`, `SENT_TO_BAR`, etc. | Etiquetas: Abierta, En barra, Pendientes cobro… (`useWaiterOrderStatus`) |
| Nueva comanda | Mensaje técnico “Indique mesa…” | Ambientes + campo libre siempre activo; hint: *“Escribe la mesa o ambiente para abrir la comanda.”* |
| Agregar bebida | Diálogo admin completo | Modo `mobile-waiter`: buscador grande, favoritos, recientes, grupos por tipo, botones SOLO / CON ACOMPAÑANTE `x-large` |
| Detalle comanda | Chip con código de estado | Chip tonal en español; ítems con “Solo” / “Con acompañante” |
| Navegación inferior | `VBottomNavigation` en layout `blank` (error Vuetify) | `WaiterBottomNav`: `VSheet` + `VBtn` fijos |
| APIs en garzón | Posibles 403 a `/service-areas`, `/staff/girls`, `/admin/users` | `/waiter/service-areas`, `/waiter/girls` vía `waiterMode: true` |

---

## 2. Componentes de plantilla reutilizados

Patrones tomados del template Materialize / Vuetify del proyecto (sin quitar componentes existentes):

| Patrón origen | Uso en garzón |
|---------------|----------------|
| `CardStatisticsWithIcon` (core) | `WaiterKpiCard` — avatar tonal, icono, valor, título |
| `VCard` + `VChip` tonal | `WaiterOrderCard`, estados de comanda |
| `VAlert` | Hints en nueva comanda y lista vacía |
| `VBtn` `size="x-large"` / `block` | Abrir comanda, SOLO / CON ACOMPAÑANTE, acciones en cards |
| `VToolbar` + fullscreen dialog | `OrderAddProductDialog` con prop `mobile-waiter` |
| `VList` / `VListItem` | Productos, favoritos, recientes |
| Layout `blank` | Sin sidebar ni navbar admin |

Estilos compartidos: `src/assets/styles/waiter-mobile.scss` (`.waiter-shell`, padding para bottom nav).

---

## 3. Archivos principales

| Archivo | Rol |
|---------|-----|
| `components/nightpos/waiter/WaiterMobileHeader.vue` | Cabecera móvil |
| `components/nightpos/waiter/WaiterKpiCard.vue` | KPI táctil inicio |
| `components/nightpos/waiter/WaiterOrderCard.vue` | Card de comanda |
| `components/nightpos/waiter/WaiterOrderActions.vue` | Ver / + Bebida / Enviar barra |
| `components/nightpos/waiter/WaiterBottomNav.vue` | Nav inferior |
| `composables/useWaiterOrderStatus.js` | Labels de estado |
| `composables/useOperationalGirls.js` | `waiterMode` → solo `/waiter/girls` |
| `composables/useOrderProductShortcuts.js` | Favoritos/recientes + `repairStorage()` |
| `components/nightpos/orders/OrderAddProductDialog.vue` | Prop `mobileWaiter` |
| `pages/nightpos/waiter/**` | Dashboard, listados, nueva, detalle |

---

## 4. Flujo del garzón

```mermaid
flowchart TD
  A[Login PIN 5678] --> B[/nightpos/waiter]
  B --> C{Nueva comanda}
  B --> D{KPI: Abiertas / Barra / Cobro}
  C --> E[Ambiente o texto mesa]
  E --> F[Abrir comanda]
  F --> G[Detalle + add=1]
  G --> H[Agregar bebida SOLO o CON ACOMPAÑANTE]
  H --> I[Enviar a barra]
  D --> J[Listado cards por scope]
  J --> G
```

1. Login → redirección a `nightpos-waiter` (sin menú admin).
2. Dashboard: tocar KPI o “Nueva comanda”.
3. Nueva: botón de ambiente **o** texto → **Abrir comanda**.
4. Detalle: **+ Bebida** (diálogo móvil) → **Enviar barra** si aplica.
5. Bottom nav: inicio / comandas / nueva.

---

## 5. Limpieza de errores

- **403:** En páginas garzón, chicas operativas solo con `loadOperationalGirlsForSelect({ waiterMode: true })` → `GET /api/v1/waiter/girls`. No se llama fallback a `/staff/girls` ni `/admin/users`.
- **Ambientes:** `fetchWaiterServiceAreas` (`GET /waiter/service-areas`), no `/service-areas` de admin.
- **localStorage:** Si favoritos/recientes están corruptos, `repairStorage()` limpia claves y el render continúa (sin `.map` sobre `null`).
- **Consola:** Sin `VBottomNavigation` huérfano en layout blank.

---

## 6. Validación móvil

Con `pnpm run dev` y contexto demo (`casa-demo` / `CENTRO`):

| # | Paso | Esperado |
|---|------|----------|
| 1 | Login garzón PIN `5678` | Entra directo a `/nightpos/waiter` |
| 2 | KPI **Nueva comanda** | Formulario simple |
| 3 | Ambiente o texto + **Abrir comanda** | Redirección a detalle |
| 4 | **+ Bebida** → SOLO | Ítem agregado |
| 5 | **+ Bebida** → CON ACOMPAÑANTE (+ chica si aplica) | Ítem agregado |
| 6 | **Enviar barra** | Estado “En barra” |
| 7 | KPI Abiertas / En barra / Pendientes cobro | Listados en cards |
| 8 | Consola del navegador | Sin 403 repetidos a admin/staff; sin error de bottom-navigation |

---

## 7. Pendientes (fuera de este refinamiento)

- Tabs por categoría de producto en agregar bebida (más allá de agrupación por `product_type`).
- Nombre de turno en header (requiere API).
- PWA / vibración en cambios de estado.
- Tests E2E del flujo garzón.

---

*Ver también `WAITER_MOBILE_FIX_REPORT.md`, `WAITER_MOBILE_AUDIT.md`, `PHASE_C4_WAITER_REPORT.md`.*
