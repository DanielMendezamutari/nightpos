import api from '@/services/http'

export async function fetchCashMovementReasons(params = {}) {
  const response = await api.get('/cash-movement-reasons', { params })
  return response.data?.cash_movement_reasons ?? []
}

export async function createCashMovementReason(payload) {
  const response = await api.post('/cash-movement-reasons', payload)
  return response.data?.cash_movement_reason
}

export async function updateCashMovementReason(id, payload) {
  const response = await api.put(`/cash-movement-reasons/${id}`, payload)
  return response.data?.cash_movement_reason
}
