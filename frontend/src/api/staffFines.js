import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchStaffFines(params = {}) {
  const response = await api.get('/staff-fines', {
    params: {
      status: params.status || undefined,
      staff_user_id: params.staff_user_id || undefined,
      official_shift_id: params.official_shift_id || undefined,
      cash_session_id: params.cash_session_id || undefined,
      limit: params.limit ?? 100,
    },
  })

  return unwrapNightPosResponse(response)
}

export async function createStaffFine(payload) {
  const response = await api.post('/staff-fines', payload)

  return unwrapNightPosResponse(response)
}

export async function cancelStaffFine(id, payload) {
  const response = await api.post(`/staff-fines/${id}/cancel`, payload)

  return unwrapNightPosResponse(response)
}
