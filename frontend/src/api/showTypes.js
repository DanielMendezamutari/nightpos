import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchShowTypes() {
  const response = await api.get('/show-types')

  return unwrapNightPosResponse(response).show_types ?? []
}

export async function createShowType(payload) {
  const response = await api.post('/show-types', payload)

  return unwrapNightPosResponse(response).show_type
}
