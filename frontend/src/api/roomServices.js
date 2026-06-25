import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchCurrentShiftRoomServices() {
  const response = await api.get('/room-services')

  return unwrapNightPosResponse(response)
}

export async function fetchRoomControlOverview() {
  const response = await api.get('/room-services/control')

  return unwrapNightPosResponse(response)
}

export async function fetchDueRoomServices() {
  const response = await api.get('/room-services/due')

  return unwrapNightPosResponse(response)
}

export async function createRoomService(payload) {
  const response = await api.post('/room-services', payload)

  return unwrapNightPosResponse(response)
}

export async function fetchRoomService(id) {
  const response = await api.get(`/room-services/${id}`)

  return unwrapNightPosResponse(response)
}

export async function finishRoomService(id) {
  const response = await api.post(`/room-services/${id}/finish`)

  return unwrapNightPosResponse(response)
}

export async function checkRoomService(id) {
  const response = await api.post(`/room-services/${id}/check`)

  return unwrapNightPosResponse(response)
}

export async function printRoomService(id, { reprint = false } = {}) {
  const response = await api.post(`/room-services/${id}/print`, reprint ? { reprint: true } : {})

  return unwrapNightPosResponse(response)
}
