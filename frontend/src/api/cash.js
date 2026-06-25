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

  return unwrapNightPosResponse(response)
}

export async function fetchCashMovement(id) {
  const response = await api.get(`/cash/movements/${id}`)

  return unwrapNightPosResponse(response)
}

export async function printCashMovement(id, { reprint = false } = {}) {
  const response = await api.post(`/cash/movements/${id}/print`, reprint ? { reprint: true } : {})

  return unwrapNightPosResponse(response)
}

export async function closeCashSession(payload) {
  const response = await api.post('/cash/session/close', payload)

  return unwrapNightPosResponse(response)
}

export async function fetchCashSession(id) {
  const response = await api.get(`/cash/sessions/${id}`)

  return unwrapNightPosResponse(response)
}

export async function printCashClose(sessionId, { reprint = false } = {}) {
  const response = await api.post(`/cash/sessions/${sessionId}/print-close`, reprint ? { reprint: true } : {})

  return unwrapNightPosResponse(response)
}

export async function fetchCashSessionCloseCheck() {
  const response = await api.get('/cash/session/current/close-check')

  return unwrapNightPosResponse(response)
}
