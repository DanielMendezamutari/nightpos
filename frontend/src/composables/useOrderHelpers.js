export const ORDER_STATUS_LABELS = {
  OPEN: 'Abierta',
  SENT_TO_BAR: 'En barra',
  IN_PREPARATION: 'En preparación',
  READY: 'Lista',
  BILLED: 'Cobrada',
  CANCELLED: 'Cancelada',
}

export const SALE_MODE_LABELS = {
  SOLO_CLIENTE: 'Solo cliente',
  CON_ACOMPANANTE: 'Con acompañante',
}

export { formatProductType, formatSaleMode, formatStatus, productCategoryLabel, productActivePrice } from '@/composables/useProductLabels'

export function orderStatusLabel(status) {
  return ORDER_STATUS_LABELS[status] || status
}

export function orderStatusColor(status) {
  const map = {
    OPEN: 'primary',
    SENT_TO_BAR: 'info',
    IN_PREPARATION: 'warning',
    READY: 'success',
    BILLED: 'secondary',
    CANCELLED: 'error',
  }

  return map[status] || 'default'
}

export function canModifyOrder(order) {
  return order && ['OPEN', 'SENT_TO_BAR'].includes(order.status)
}

export function activeOrderItems(order) {
  return (order?.items ?? []).filter(item => item.item_status !== 'CANCELLED')
}

export function orderItemStatusLabel(status) {
  const map = {
    PENDING: 'Pendiente',
    SENT: 'Enviado',
    CANCELLED: 'Cancelado',
  }

  return map[status] || status
}

export function itemsNeedingGirl(order) {
  if (!order?.items?.length)
    return []

  return order.items.filter(
    item => item.sale_mode === 'CON_ACOMPANANTE' && !item.girl_user_id,
  )
}

export function formatMoney(amount, currency = 'BOB') {
  const value = Number(amount)

  if (Number.isNaN(value))
    return `${amount} ${currency}`

  return `${value.toFixed(2)} ${currency}`
}
