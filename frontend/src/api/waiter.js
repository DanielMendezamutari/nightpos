import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchWaiterDashboard() {
  const response = await api.get('/waiter/dashboard')

  return unwrapNightPosResponse(response).dashboard
}

export async function fetchWaiterOrders(scope = 'active') {
  const response = await api.get('/waiter/orders', { params: { scope } })

  return unwrapNightPosResponse(response).orders ?? []
}

/** Ambientes para nueva comanda (permiso orders.create, no settings). */
export async function fetchWaiterServiceAreas(params = {}) {
  const response = await api.get('/waiter/service-areas', { params })

  return unwrapNightPosResponse(response).service_areas ?? []
}

/** Chicas operativas para comanda (permiso orders.add_items). */
export async function fetchWaiterGirls() {
  const response = await api.get('/waiter/girls')

  return unwrapNightPosResponse(response).items ?? []
}
