export const CASH_MOVEMENT_FAMILY_LABELS = {
  SALE: 'Venta',
  MANUAL: 'Manual',
  SETTLEMENT: 'Liquidación',
  EXPENSE: 'Gasto',
  ADJUSTMENT: 'Ajuste',
}

export const CASH_MOVEMENT_CATEGORY_LABELS = {
  SALE_COLLECTION: 'Cobro de comanda',
  DIRECT_SALE_COLLECTION: 'Cobro venta directa',
  BRACELET_COLLECTION: 'Cobro de manillas',
  ROOM_SERVICE_COLLECTION: 'Cobro de pieza',
  SHOW_COLLECTION: 'Cobro de show',
  MANUAL_INCOME: 'Ingreso manual',
  SETTLEMENT_GIRL_PAYMENT: 'Pago chicas',
  SETTLEMENT_WAITER_PAYMENT: 'Pago garzones',
  SETTLEMENT_CLEANING_PAYMENT: 'Pago limpieza',
  OPERATING_EXPENSE: 'Gasto operativo',
  PURCHASE: 'Compra',
  OTHER_INCOME: 'Otro ingreso',
  OTHER_EXPENSE: 'Otro egreso',
}

export function cashMovementFamilyLabel(value) {
  return CASH_MOVEMENT_FAMILY_LABELS[value] || 'Movimiento'
}

export function cashMovementCategoryLabel(value, movementType = null) {
  if (value && CASH_MOVEMENT_CATEGORY_LABELS[value])
    return CASH_MOVEMENT_CATEGORY_LABELS[value]

  return movementType === 'INCOME' ? 'Otro ingreso' : 'Otro egreso'
}