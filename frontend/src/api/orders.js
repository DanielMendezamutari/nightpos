import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchOrders(status = null) {
  const params = status ? { status } : {}
  const response = await api.get('/orders', { params })

  return unwrapNightPosResponse(response).orders ?? []
}

export async function fetchOrdersByScope(scope, extraParams = {}) {
  const response = await api.get('/orders', { params: { scope, ...extraParams } })

  return unwrapNightPosResponse(response).orders ?? []
}

/** Listado acotado al turno/caja actual de la cajera. */
export async function fetchCashierOrdersByScope(scope) {
  const params = { scope, cashier_scope: 1 }

  if (scope === 'billed_recent')
    params.current_session = 1

  return fetchOrdersByScope(scope, params)
}

export async function fetchCashierChargeableOrders() {
  return fetchCashierOrdersByScope('cashier_chargeable')
}

export async function fetchOrder(id) {
  const response = await api.get(`/orders/${id}`)

  return unwrapNightPosResponse(response).order
}

export async function fetchOrderPrecheck(id) {
  const response = await api.get(`/orders/${id}/precheck`)

  return unwrapNightPosResponse(response).precheck
}

export async function createOrder(payload) {
  const response = await api.post('/orders', payload)

  return unwrapNightPosResponse(response).order
}

export async function addOrderItem(orderId, payload) {
  const response = await api.post(`/orders/${orderId}/items`, payload)

  return unwrapNightPosResponse(response).order
}

export async function syncOrderItemAllocations(orderId, itemId, allocations) {
  const response = await api.put(`/orders/${orderId}/items/${itemId}/allocations`, {
    allocations,
  })

  return unwrapNightPosResponse(response).order
}

export async function assignOrderItemGirl(orderId, itemId, girlUserId) {
  const response = await api.patch(`/orders/${orderId}/items/${itemId}`, {
    girl_user_id: girlUserId,
  })

  return unwrapNightPosResponse(response).order
}

export async function sendOrderToBar(orderId) {
  const response = await api.post(`/orders/${orderId}/send-to-bar`)

  return unwrapNightPosResponse(response).order
}

export async function cancelOrder(orderId) {
  const response = await api.post(`/orders/${orderId}/cancel`)

  return unwrapNightPosResponse(response).order
}

export async function updateOrderItem(orderId, itemId, payload) {
  const response = await api.put(`/orders/${orderId}/items/${itemId}`, payload)

  return unwrapNightPosResponse(response).order
}

export async function removeOrderItem(orderId, itemId) {
  const response = await api.delete(`/orders/${orderId}/items/${itemId}`)

  return unwrapNightPosResponse(response).order
}

export async function cancelOrderItem(orderId, itemId, reason) {
  const response = await api.post(`/orders/${orderId}/items/${itemId}/cancel`, { reason })

  return unwrapNightPosResponse(response).order
}

export async function updateOrderHeader(orderId, payload) {
  const response = await api.patch(`/orders/${orderId}`, payload)

  return unwrapNightPosResponse(response).order
}
