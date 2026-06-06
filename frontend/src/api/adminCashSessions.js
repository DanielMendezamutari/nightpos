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
