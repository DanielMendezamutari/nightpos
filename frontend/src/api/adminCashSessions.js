import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchAdminCashSessions(params = {}) {
  const response = await api.get('/admin/cash-sessions', { params })

  return unwrapNightPosResponse(response)
}

export async function fetchAdminCashSession(id) {
  const response = await api.get(`/admin/cash-sessions/${id}`)

  return unwrapNightPosResponse(response)
}

export async function fetchAdminCashSessionsSummary(params = {}) {
  const response = await api.get('/admin/cash-sessions/summary', { params })

  return unwrapNightPosResponse(response)
}

export async function fetchAdminCashSessionCloseCheck(id) {
  const response = await api.get(`/admin/cash-sessions/${id}/close-check`)

  return unwrapNightPosResponse(response)
}

export async function forceCloseAdminCashSession(id, payload) {
  const response = await api.post(`/admin/cash-sessions/${id}/force-close`, payload)

  return unwrapNightPosResponse(response)
}

export const FORCE_CLOSE_REASONS = [
  { title: 'Cajera se retiró', value: 'cashier_left' },
  { title: 'Error operativo', value: 'operational_error' },
  { title: 'Caja no pudo cerrar por pendientes', value: 'blockers_unresolved' },
  { title: 'Cambio de turno', value: 'shift_change' },
  { title: 'Otro', value: 'other' },
]

export function forceCloseReasonLabel(value) {
  return FORCE_CLOSE_REASONS.find(r => r.value === value)?.title ?? value ?? '—'
}
