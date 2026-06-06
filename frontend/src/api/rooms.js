import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchRooms(params = {}) {
  const response = await api.get('/rooms', { params })
  return unwrapNightPosResponse(response)
}

export async function fetchAvailableRooms() {
  const response = await api.get('/rooms/available')
  return unwrapNightPosResponse(response)
}

export async function fetchCleaningRooms() {
  const response = await api.get('/rooms/cleaning')
  return unwrapNightPosResponse(response)
}

export async function fetchRoom(id) {
  const response = await api.get(`/rooms/${id}`)
  return unwrapNightPosResponse(response)
}

export async function createRoom(payload) {
  const response = await api.post('/rooms', payload)
  return unwrapNightPosResponse(response)
}

export async function updateRoom(id, payload) {
  const response = await api.put(`/rooms/${id}`, payload)
  return unwrapNightPosResponse(response)
}

export async function markRoomClean(id) {
  const response = await api.post(`/rooms/${id}/mark-clean`)
  return unwrapNightPosResponse(response)
}

export async function markRoomMaintenance(id) {
  const response = await api.post(`/rooms/${id}/mark-maintenance`)
  return unwrapNightPosResponse(response)
}

export async function markRoomAvailable(id) {
  const response = await api.post(`/rooms/${id}/mark-available`)
  return unwrapNightPosResponse(response)
}
