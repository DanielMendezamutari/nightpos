export const WAITER_ORDER_STATUS = {
  OPEN: { label: 'Abierta', color: 'primary' },
  SENT_TO_BAR: { label: 'En barra', color: 'info' },
  IN_PREPARATION: { label: 'Preparando', color: 'warning' },
  READY: { label: 'Lista', color: 'success' },
  BILLED: { label: 'Cobrada', color: 'secondary' },
  CANCELLED: { label: 'Anulada', color: 'error' },
}

export function waiterOrderStatus(status) {
  return WAITER_ORDER_STATUS[status] ?? { label: status, color: 'default' }
}
