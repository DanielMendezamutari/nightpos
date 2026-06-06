# Fix: Liquidaciones congeladas y caja abierta (frontend)

## Causa exacta

En `composables/useCurrentShiftSettlements.js` había **dos declaraciones** `const cleaning = ref([])`. Eso provoca un `SyntaxError` al importar el composable y rompe todas las rutas de liquidaciones.

## Archivos corregidos

| Archivo | Cambio |
|---------|--------|
| `composables/useCurrentShiftSettlements.js` | Eliminado `const` duplicado; log DEV en errores |
| `composables/useServiceCashSession.js` | Expone `cashSession`; watch `context.version`; log DEV |
| `composables/useSettlementPendingSources.js` | Carga aislada con `finally` y alerta de error |
| `components/nightpos/settlements/SettlementsCashBanner.vue` | Estado de caja + `QuickOpenCashDialog` |
| `pages/nightpos/settlements/index.vue` | Banner caja, pending-sources no bloqueante |
| `pages/nightpos/settlements/[id].vue` | Pago con gate de caja y abrir caja inline |

## Reglas de caja en UI

| Acción | Requiere caja |
|--------|----------------|
| Ver resumen / tabs | No |
| Generar liquidaciones | No |
| Marcar pagado | Sí (usuario actual) |

Mensaje: *«Debe abrir caja para pagar esta liquidación.»* + botón **Abrir caja ahora**.

## Resolver de caja

Mismo patrón que Servicios/Cobros:

```javascript
const session = await fetchCurrentCashSession()
cashSessionOpen.value = session?.status === 'OPEN'
```

Tras abrir caja desde `QuickOpenCashDialog`, se emite `cash-opened` y se refrescan liquidaciones sin recargar el navegador.

## Cómo se evitó el congelamiento

- Sin `const` duplicados ni imports rotos.
- APIs opcionales (pending-sources) no dejan `loading` en `true`.
- Errores muestran `VAlert` y el resto de la página sigue visible.
- No hay `watch` recursivo entre contexto y carga principal.

## Validación manual (pnpm run dev)

1. Login admin sucursal.
2. Abrir caja.
3. Ir a Liquidaciones → debe cargar y mostrar «Caja abierta».
4. Simular fallo de pending-sources (permiso o red) → alerta, sin congelar.
5. Generar y pagar liquidación; sin caja → diálogo abrir caja.

Backend: ver `backend/SETTLEMENTS_CASH_UI_FIX_REPORT.md`.
