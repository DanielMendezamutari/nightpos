/**
 * Tabs y mensajes vacíos para listados de comandas por scope operativo.
 */
export const ORDER_LIST_TABS = [
  { value: 'operational_active', label: 'Activas', scope: 'operational_active' },
  { value: 'open', label: 'Abiertas', scope: 'open' },
  { value: 'sent_to_bar', label: 'En barra', scope: 'sent_to_bar' },
  { value: 'pending_charge', label: 'Pendientes de cobro', scope: 'pending_charge' },
  { value: 'billed_recent', label: 'Cobradas recientes', scope: 'billed_recent' },
  { value: 'cancelled', label: 'Canceladas', scope: 'cancelled' },
]

export const CASHIER_ORDER_TABS = [
  { value: 'cashier_chargeable', label: 'Pendientes de cobro', scope: 'cashier_chargeable' },
  { value: 'billed_recent', label: 'Cobradas recientes', scope: 'billed_recent' },
]

export const ORDER_EMPTY_MESSAGES = {
  operational_active: 'No hay comandas activas.',
  open: 'No hay comandas abiertas.',
  sent_to_bar: 'No hay comandas en barra.',
  pending_charge: 'No hay comandas pendientes de cobro.',
  billed_recent: 'No hay comandas cobradas recientes.',
  cancelled: 'No hay comandas canceladas.',
  cashier_chargeable: 'No hay comandas pendientes de cobro.',
}

export function orderEmptyMessage(tab) {
  return ORDER_EMPTY_MESSAGES[tab] ?? 'No hay comandas en esta lista.'
}

export function resolveOrderTab(queryTab, defaultTab = 'operational_active') {
  const allowed = ORDER_LIST_TABS.map(t => t.value)

  if (typeof queryTab === 'string' && allowed.includes(queryTab))
    return queryTab

  return defaultTab
}
