import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchCurrentCashSession() {
  const response = await api.get('/cash/session/current')

  return unwrapNightPosResponse(response).session
}

export async function openCashSession(payload) {
  const response = await api.post('/cash/session/open', payload)

  return unwrapNightPosResponse(response).session
}

export async function registerCashMovement(payload) {
  const response = await api.post('/cash/movements', payload)

  return unwrapNightPosResponse(response).session
}

export async function closeCashSession(payload) {
  const response = await api.post('/cash/session/close', payload)

  return unwrapNightPosResponse(response).session
}
