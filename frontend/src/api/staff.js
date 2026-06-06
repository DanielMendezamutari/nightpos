import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchStaffGirls() {
  const response = await api.get('/staff/girls')

  return unwrapNightPosResponse(response).items ?? []
}

export async function quickCreateGirl(payload) {
  const response = await api.post('/staff/quick-girls', payload)

  return unwrapNightPosResponse(response).girl
}

export async function fetchStaffWaiters() {
  const response = await api.get('/staff/waiters')

  return unwrapNightPosResponse(response).items ?? []
}

export async function quickCreateWaiter(payload) {
  const response = await api.post('/staff/quick-waiters', payload)

  return unwrapNightPosResponse(response).waiter
}
