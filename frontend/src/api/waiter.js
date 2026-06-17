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

/** Mesas asignadas al garzón con estado LIBRE / OCCUPIED. */
export async function fetchWaiterMyTables() {
  const response = await api.get('/waiter/my-tables')

  return unwrapNightPosResponse(response).tables ?? []
}

/** Tap en mesa: crea comanda si libre o devuelve la activa. */
export async function openWaiterTable(tableId) {
  const response = await api.post(`/waiter/my-tables/${tableId}/open`)

  return unwrapNightPosResponse(response)
}
