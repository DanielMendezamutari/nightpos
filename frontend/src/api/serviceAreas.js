import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchServiceAreas(params = {}) {
  const response = await api.get('/service-areas', { params })

  return unwrapNightPosResponse(response).service_areas ?? []
}

export async function createServiceArea(payload) {
  const response = await api.post('/service-areas', payload)

  return unwrapNightPosResponse(response).service_area
}

export async function updateServiceArea(id, payload) {
  const response = await api.put(`/service-areas/${id}`, payload)

  return unwrapNightPosResponse(response).service_area
}
