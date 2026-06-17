/**
 * Chips operativos para cola de cobro cajera (Fase 0).
 * @param {object|null} order
 * @returns {Array<{ key: string, label: string, color: string }>}
 */
export function cashierOrderOperationalChips(order) {
  if (!order)
    return []

  const chips = []

  if (order.has_companion_items) {
    chips.push({ key: 'companion', label: 'Acompañante', color: 'secondary' })
  }

  if (order.has_combo_items) {
    chips.push({
      key: 'combo',
      label: order.allocation_incomplete ? 'Combo' : 'Combo listo',
      color: order.allocation_incomplete ? 'warning' : 'info',
    })
  }

  if (order.allocation_incomplete) {
    chips.push({ key: 'allocation', label: 'Falta manilla', color: 'error' })
  }

  if ((order.girl_missing_count ?? 0) > 0) {
    chips.push({
      key: 'girl',
      label: order.girl_missing_count === 1 ? 'Falta chica' : `Falta chica (${order.girl_missing_count})`,
      color: 'error',
    })
  }

  if (order.charge_blocked) {
    chips.push({ key: 'blocked', label: 'No cobrable', color: 'error' })
  }

  return chips
}

export function formatWaitingMinutes(minutes) {
  const value = Number(minutes ?? 0)

  if (value <= 0)
    return 'Recién'

  if (value === 1)
    return '1 min esperando'

  return `${value} min esperando`
}
