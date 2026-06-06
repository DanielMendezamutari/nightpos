import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchCurrentShiftBracelets() {
  const response = await api.get('/bracelets')

  return unwrapNightPosResponse(response)
}

export async function createBracelet(payload) {
  const response = await api.post('/bracelets', payload)

  return unwrapNightPosResponse(response)
}

export async function fetchBracelet(id) {
  const response = await api.get(`/bracelets/${id}`)

  return unwrapNightPosResponse(response)
}
