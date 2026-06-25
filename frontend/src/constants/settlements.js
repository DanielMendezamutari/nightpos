export const SETTLEMENT_TYPE_LABELS = {
  WAITER: 'Garzón',
  GIRL: 'Chica',
  CLEANING: 'Limpieza',
}

export const ADJUSTMENT_TYPE_LABELS = {
  CLEANING_DEDUCTION: 'Limpieza única',
  MANUAL_FINE: 'Multa',
  MANUAL_DISCOUNT: 'Descuento manual',
}

export const DISCOUNT_MODE_LABELS = {
  PERCENT: 'Porcentaje',
  AMOUNT: 'Monto fijo',
}

export const SOURCE_TYPE_LABELS = {
  WAITER_COMMISSION: 'Comisión garzón',
  GIRL_CONSUMPTION: 'Consumo acompañante',
  GIRL_BRACELET: 'Manilla',
  GIRL_BRACELET_ALLOCATION: 'Manilla combo',
  GIRL_ROOM: 'Pieza',
  GIRL_SHOW: 'Show',
  CLEANING_BASE: 'Base limpieza',
  CLEANING_ROOM: 'Limpieza pieza',
}

export const STAFF_FINE_STATUS_LABELS = {
  PENDING: 'Pendiente',
  APPLIED: 'Aplicada',
  CANCELLED: 'Cancelada',
}

export const STAFF_FINE_STATUS_COLORS = {
  PENDING: 'warning',
  APPLIED: 'success',
  CANCELLED: 'secondary',
}

export function adjustmentTypeLabel(type) {
  return ADJUSTMENT_TYPE_LABELS[type] ?? type ?? '—'
}

export function sourceTypeLabel(type) {
  return SOURCE_TYPE_LABELS[type] ?? type ?? '—'
}

export function settlementTypeLabel(type) {
  return SETTLEMENT_TYPE_LABELS[type] ?? type ?? '—'
}

export function staffFineStatusLabel(status) {
  return STAFF_FINE_STATUS_LABELS[status] ?? status ?? '—'
}

export function formatBob(amount) {
  if (amount === null || amount === undefined || amount === '')
    return '—'

  const value = Number(amount)

  if (Number.isNaN(value))
    return String(amount)

  return `${value.toFixed(2)} Bs`
}

export function formatSignedBob(amount) {
  if (amount === null || amount === undefined || amount === '')
    return '—'

  const value = Number(amount)

  if (Number.isNaN(value))
    return String(amount)

  const prefix = value > 0 ? '+' : ''

  return `${prefix}${value.toFixed(2)} Bs`
}

export function settlementHasAdjustments(row) {
  if (!row)
    return false

  const gross = row.gross_amount ?? row.total_amount
  const net = row.net_amount ?? row.total_amount

  if (gross !== undefined && net !== undefined && String(gross) !== String(net))
    return true

  const adjustmentsTotal = Number(row.adjustments_total ?? 0)

  return !Number.isNaN(adjustmentsTotal) && adjustmentsTotal !== 0
}
