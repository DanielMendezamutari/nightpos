import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchCurrentShiftSettlements() {
  const response = await api.get('/settlements/current-shift')

  return unwrapNightPosResponse(response)
}

export async function fetchSettlementPendingSources() {
  const response = await api.get('/settlements/current-shift/pending-sources')

  return unwrapNightPosResponse(response)
}

export async function generateCurrentShiftSettlements() {
  const response = await api.post('/settlements/generate-current-shift')

  return unwrapNightPosResponse(response)
}

export async function fetchSettlement(id) {
  const response = await api.get(`/settlements/${id}`)

  return unwrapNightPosResponse(response)
}

export async function markSettlementPaid(id, payload = {}) {
  const response = await api.post(`/settlements/${id}/mark-paid`, payload)

  return unwrapNightPosResponse(response)
}

export async function fetchSettlementHistory(params = {}) {
  const response = await api.get('/settlements/history', {
    params: {
      limit: params.limit ?? 50,
      official_shift_id: params.official_shift_id || undefined,
      staff_user_id: params.staff_user_id || undefined,
      settlement_type: params.settlement_type || undefined,
      status: params.status || undefined,
      date_from: params.date_from || undefined,
      date_to: params.date_to || undefined,
    },
  })

  return unwrapNightPosResponse(response)
}
