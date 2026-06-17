import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchCashMovementReasons(params = {}) {
  const response = await api.get('/cash-movement-reasons', { params })

  return unwrapNightPosResponse(response).cash_movement_reasons ?? []
}

/** Listado operativo en Mi Caja (permiso cash.access). */
export async function fetchCashMovementReasonsForCash(params = {}) {
  const response = await api.get('/cash/movement-reasons', { params })

  return unwrapNightPosResponse(response).cash_movement_reasons ?? []
}

export async function createCashMovementReason(payload) {
  const response = await api.post('/cash-movement-reasons', payload)

  return unwrapNightPosResponse(response).cash_movement_reason
}

export async function updateCashMovementReason(id, payload) {
  const response = await api.put(`/cash-movement-reasons/${id}`, payload)

  return unwrapNightPosResponse(response).cash_movement_reason
}
