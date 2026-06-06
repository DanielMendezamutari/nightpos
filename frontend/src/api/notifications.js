import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchNotifications(limit = 50) {
  const response = await api.get('/notifications', { params: { limit } })

  return unwrapNightPosResponse(response)
}

export async function fetchUnreadNotificationCount() {
  const response = await api.get('/notifications/unread-count')

  return unwrapNightPosResponse(response)
}

export async function markNotificationRead(id) {
  const response = await api.post(`/notifications/${id}/read`)

  return unwrapNightPosResponse(response)
}

export async function markAllNotificationsRead() {
  const response = await api.post('/notifications/read-all')

  return unwrapNightPosResponse(response)
}
