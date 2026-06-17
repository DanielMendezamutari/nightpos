# CASH_MOVEMENT_FROM_SETTLEMENTS_REPORT.md (Frontend)

**Mejora:** movimiento rápido desde Liquidaciones  
**Fecha:** 2026-06-16  
**Estado:** Completado

---

## Componente `CashMovementDialog.vue`

Formulario reutilizable:

- Tipo: ingreso / egreso
- Monto
- **Método de pago** (Efectivo / QR / Tarjeta)
- Motivo (con creación rápida si aplica)
- Notas

Usado en:

- `pages/nightpos/cash/index.vue` (Mi Caja)
- `pages/nightpos/settlements/index.vue` — botón **Registrar movimiento** en header

---

## Flujo en Liquidaciones

1. Verifica caja abierta (`loadCashSession`)
2. Si no hay caja → aviso + `QuickOpenCashDialog`
3. Tras registrar → refresca caja y liquidaciones (SSE + callback)

---

## Referencias

- Backend: `backend/CASH_MOVEMENT_FROM_SETTLEMENTS_REPORT.md`
