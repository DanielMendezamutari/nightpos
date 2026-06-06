import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchCleaningDashboard() {
  const response = await api.get('/cleaning/dashboard')

  return unwrapNightPosResponse(response)
}

export async function fetchCleaningRooms() {
  const response = await api.get('/cleaning/rooms')

  return unwrapNightPosResponse(response)
}

export async function checkCleaningRoomService(id) {
  const response = await api.post(`/cleaning/room-services/${id}/check`)

  return unwrapNightPosResponse(response)
}

export async function finishCleaningRoomService(id) {
  const response = await api.post(`/cleaning/room-services/${id}/finish`)

  return unwrapNightPosResponse(response)
}

export async function markCleaningRoomClean(id) {
  const response = await api.post(`/cleaning/rooms/${id}/mark-clean`)

  return unwrapNightPosResponse(response)
}

export async function fetchCleaningShiftEarnings() {
  const response = await api.get('/cleaning/shift-earnings')

  return unwrapNightPosResponse(response)
}
