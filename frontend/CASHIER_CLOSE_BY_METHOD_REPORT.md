# Cierre de caja por método — Frontend

**Fecha:** 2026-06-17

## Mi Caja — Resumen por método

Tabla ampliada en `cash/index.vue`:

| Columna | Efectivo | QR | Tarjeta |
|---------|----------|-----|---------|
| Inicial | Sí (apertura) | — | — |
| Ingresos | Sí | Sí | Sí |
| Ventas | Sí | Sí | Sí |
| Egresos | Sí | Sí | Sí |
| Esperado / neto | Sí | Sí | Sí |

Fuente: `financial_summary` con fallbacks a campos legacy.

## Diálogo «Cerrar caja»

Antes de confirmar:

1. Tabla resumen por método (mismas columnas).
2. **Debe declarar:**
   - Efectivo contado → diferencia efectivo (bloquea arqueo real).
   - QR verificado → diferencia QR (informativa).
   - Tarjeta verificada → diferencia tarjeta (informativa).
3. Notas de cierre.

Al enviar:

- `declared_closing_amount` → API (sin cambio).
- QR/tarjeta y diferencias → concatenados en `closing_notes`.

## Prefill

Al abrir diálogo (post close-check OK), los tres campos se prellenan con `expected_by_method`.

## Sin romper

- Close-check y blockers operativos.
- Solo efectivo determina cierre de sesión en backend.
- Liquidaciones y movimientos existentes.
