export const ROOM_TYPE_OPTIONS = [
  { title: 'Estándar', value: 'STANDARD' },
  { title: 'VIP', value: 'VIP' },
  { title: 'Suite', value: 'SUITE' },
]

export function roomStatusColor(status) {
  return {
    AVAILABLE: 'success',
    OCCUPIED: 'warning',
    CLEANING: 'info',
    MAINTENANCE: 'error',
  }[status] || 'default'
}

export function roomStatusIcon(status) {
  return {
    AVAILABLE: 'ri-checkbox-circle-line',
    OCCUPIED: 'ri-user-line',
    CLEANING: 'ri-brush-line',
    MAINTENANCE: 'ri-tools-line',
  }[status] || 'ri-question-line'
}
