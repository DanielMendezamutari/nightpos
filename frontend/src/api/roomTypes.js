import api from '@/services/http'

export async function fetchRoomTypes(params = {}) {
  const response = await api.get('/room-types', { params })
  return response.data?.room_types ?? []
}

export async function createRoomType(payload) {
  const response = await api.post('/room-types', payload)
  return response.data?.room_type
}

export async function updateRoomType(id, payload) {
  const response = await api.put(`/room-types/${id}`, payload)
  return response.data?.room_type
}
