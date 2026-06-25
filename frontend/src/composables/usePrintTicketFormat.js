const PAYMENT_LABELS = {
  CASH: 'EFECTIVO',
  QR: 'QR',
  CARD: 'TARJETA',
  MIXED: 'MIXTO',
}

export function resolvePrintLocationLabel(tableLabel) {
  const normalized = String(tableLabel ?? '').trim().toLowerCase()

  if (normalized.startsWith('pieza'))
    return 'Pieza'

  if (normalized.startsWith('habit'))
    return 'Habitación'

  if (normalized.startsWith('barra') || normalized.startsWith('bar '))
    return 'Barra'

  if (normalized.startsWith('vip'))
    return 'VIP'

  return 'Mesa'
}

export function formatPrintTime(value) {
  if (!value)
    return '—'

  try {
    return new Date(value).toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' })
  }
  catch {
    return value
  }
}

export function paymentModeLabel(mode) {
  return PAYMENT_LABELS[String(mode ?? '').toUpperCase()] ?? String(mode ?? '—')
}

export { PAYMENT_LABELS }
