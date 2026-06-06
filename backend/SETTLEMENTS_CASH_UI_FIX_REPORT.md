# Fix: Liquidaciones congeladas y caja abierta

## Causa exacta

**Frontend — error de sintaxis en `useCurrentShiftSettlements.js`:**

```javascript
const cleaning = ref([])
const cleaning = ref([])  // SyntaxError: Identifier 'cleaning' has already been declared
```

Ese `const` duplicado impedía cargar el composable usado por todas las páginas de Liquidaciones (`index`, `waiters`, `girls`, `cleaning`). Vite fallaba al importar el módulo y la vista quedaba sin responder.

**Caja abierta no visible:** Liquidaciones no consultaba `GET /cash/session/current`. El admin podía tener caja abierta en otro módulo pero la pantalla de liquidaciones no mostraba estado.

## Permisos backend (admin sucursal)

El usuario `admin.demo` (rol `tenant_owner`) ya incluye en seeder:

- `settlements.access`
- `settlements.generate`
- `settlements.pay`
- `settlements.history`
- `settlements.pending_sources`

No se requirió migración de permisos adicional.

## Manejo de caja abierta

- Mismo resolver que Caja/Servicios: `useServiceCashSession` → `fetchCurrentCashSession()` → `GET /api/v1/cash/session/current` (`OpenCashSessionResolver` por usuario).
- Banner `SettlementsCashBanner` en resumen y detalle.
- **Ver / generar liquidaciones:** no exigen caja abierta.
- **Marcar pagado:** exige caja abierta del usuario que paga; mensaje y `QuickOpenCashDialog`.

## Cómo se evitó el congelamiento

1. Eliminado `const cleaning` duplicado.
2. `useSettlementPendingSources`: carga opcional con `try/catch/finally`; error en alerta, no bloquea resumen.
3. `loading` siempre vuelve a `false` en `useCurrentShiftSettlements`.
4. `console.error` solo en `import.meta.env.DEV` para endpoints fallidos.
5. Sin loops: `useOnContextChange` solo reacciona a `context.version`.

## Tests

`tests/Feature/Api/V1/SettlementsCashUiFixTest.php` — **7/7 passing**

- Admin ve liquidaciones y pending-sources
- Admin genera liquidaciones
- Pagar liquidación CLEANING sin caja → 422
- Pagar liquidación CLEANING con caja → egreso en caja
- Garzón sin permiso → 403
- `GET /cash/session/current` refleja sesión OPEN

**Nota:** En backend, solo liquidaciones `CLEANING` exigen caja al marcar pagado (egreso). El frontend exige caja para cualquier pago por consistencia operativa.

## Validación manual

1. Login admin sucursal → abrir caja.
2. Finanzas → Liquidaciones: carga sin congelar, banner «Caja abierta».
3. Generar liquidaciones.
4. Detalle → Marcar pagado (con caja) o abrir caja desde diálogo si cerrada.
5. Sin F5 tras abrir caja desde liquidaciones.
