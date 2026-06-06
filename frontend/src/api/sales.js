import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchSales(currentSession = true) {
  const response = await api.get('/sales', {
    params: { current_session: currentSession ? 1 : 0 },
  })

  return unwrapNightPosResponse(response).sales ?? []
}

export async function fetchSale(id) {
  const response = await api.get(`/sales/${id}`)

  return unwrapNightPosResponse(response).sale
}

export async function chargeOrder(orderId, payments) {
  const response = await api.post(`/orders/${orderId}/charge`, { payments })

  return unwrapNightPosResponse(response)
}

export async function createDirectSale(payload) {
  const response = await api.post('/direct-sales', payload)

  return unwrapNightPosResponse(response)
}
