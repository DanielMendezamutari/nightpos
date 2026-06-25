# Auditoría — Cierre de caja y cierre de turno (solo análisis, 2026-06-21)

**Objetivo gerencial:** que un administrador entienda el turno en menos de un minuto. No es reporte contable puro.

## Componentes actuales

| Componente | Rol | Gap vs spec gerencial |
|------------|-----|------------------------|
| `CloseCashSessionUseCase` | Cierra sesión con arqueo, valida blockers vía `CashSessionCloseCheckBuilder` | ✅ operativo cajera; no genera reporte gerencial |
| `CashSessionCloseCheckBuilder` | Blockers: comandas pendientes, liquidaciones, etc. | ✅ caja; no resume rendimiento turno |
| `CashSessionFinancialSummaryBuilder` | Totales ventas/métodos/movimientos por sesión | Parcial — base numérica útil |
| `ForceCloseCashSessionAdminUseCase` | Cierre admin sin arqueo falso | ✅ reciente; snapshot en sesión |
| `PrintableCashSessionReport.vue` (FE) | Ticket 80mm: arqueo, efectivo/QR/tarjeta, diferencia | ✅ enfoque cajera; falta liquidaciones detalladas |
| `PrintableShiftClosureReport.vue` (FE) | Ticket turno: ventas, caja, reconciliación productos | Parcial — no cubre piezas/chicas/garzones top |

## Datos disponibles hoy (sin nuevo modelo)

- **Ventas:** `sales`, `sale_payments`, venta directa
- **Caja:** `cash_sessions`, `cash_movements`, arqueo
- **Comandas:** `orders`, estados, correcciones vía auditoría
- **Piezas:** `room_services`, liquidación chica/house
- **Habitaciones:** `rooms`, estados OCCUPIED/CLEANING
- **Garzones / chicas:** comisiones en ventas, liquidaciones settlements
- **Liquidaciones:** módulo settlements + partial settlements
- **Force close:** snapshots en sesión forzada
- **Inventario:** `track_inventory` inerte — Kardex pendiente INV-*

## Reporte propuesto: Cierre de caja (cajera)

Enfoque arqueo. Extender `CashSessionFinancialSummaryBuilder` + `PrintableCashSessionReport`:

1. Resumen arqueo (apertura, declarado, sistema, diferencia)
2. Efectivo / QR / Tarjeta desglosado
3. Liquidaciones pagadas en sesión
4. Movimientos manuales
5. Comandas cobradas count + total
6. Incidencias (force close, diferencias)
7. Observaciones cierre

## Reporte propuesto: Cierre de turno (administrador)

Enfoque rendimiento. Nuevo presenter `ShiftManagerialSummaryBuilder` (reutilizar queries de shift console):

1. Resumen ejecutivo (ventas, ticket promedio, comandas, piezas)
2. Métodos de pago
3. Top garzones / top chicas / top productos / categorías
4. Piezas + habitaciones (activas, finalizadas, due)
5. Liquidaciones del turno
6. Correcciones, reimpresiones, force close
7. Incidencias + observaciones
8. Inventario (placeholder hasta Kardex)

## Separación recomendada

| Reporte | Audiencia | Canal |
|---------|-----------|-------|
| Cierre caja | Cajera | `PrintableCashSessionReport` + agente `CASH_CLOSE` |
| Cierre turno | Admin | `PrintableShiftClosureReport` + pantalla shift console export |

## Principio de implementación futura

- Reutilizar builders existentes; no duplicar queries de settlements/caja
- Misma numeración operación (#turno / #sesión) en encabezados
- Configurable por sucursal (plantillas P3)
- **No programar** hasta cerrar P1/P2 impresión operativa
