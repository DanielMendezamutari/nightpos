import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchCurrentShiftShows() {
  const response = await api.get('/shows')

  return unwrapNightPosResponse(response)
}

export async function createShow(payload) {
  const response = await api.post('/shows', payload)

  return unwrapNightPosResponse(response)
}

export async function fetchShow(id) {
  const response = await api.get(`/shows/${id}`)

  return unwrapNightPosResponse(response)
}
