import api, { unwrapNightPosResponse } from '@/services/http'

function buildParams(filters = {}) {
  const params = {}
  if (filters.dateFrom) params.date_from = filters.dateFrom
  if (filters.dateTo) params.date_to = filters.dateTo
  if (filters.officialShiftId) params.official_shift_id = filters.officialShiftId
  if (filters.cashSessionId) params.cash_session_id = filters.cashSessionId
  if (filters.cashierUserId) params.cashier_user_id = filters.cashierUserId
  if (filters.waiterUserId) params.waiter_user_id = filters.waiterUserId
  if (filters.girlUserId) params.girl_user_id = filters.girlUserId
  if (filters.paymentMethod) params.payment_method = filters.paymentMethod
  return params
}

export async function fetchDailyReport(filters = {}) {
  const response = await api.get('/reports/daily', { params: buildParams(filters) })
  return unwrapNightPosResponse(response)
}

export async function fetchSalesReport(filters = {}) {
  const response = await api.get('/reports/sales', { params: buildParams(filters) })
  return unwrapNightPosResponse(response)
}

export async function fetchCashReport(filters = {}) {
  const response = await api.get('/reports/cash', { params: buildParams(filters) })
  return unwrapNightPosResponse(response)
}

export async function fetchServicesReport(filters = {}) {
  const response = await api.get('/reports/services', { params: buildParams(filters) })
  return unwrapNightPosResponse(response)
}

export async function fetchSettlementsReport(filters = {}) {
  const response = await api.get('/reports/settlements', { params: buildParams(filters) })
  return unwrapNightPosResponse(response)
}

export async function fetchRoomsReport(filters = {}) {
  const response = await api.get('/reports/rooms', { params: buildParams(filters) })
  return unwrapNightPosResponse(response)
}

export async function fetchShiftClosureCheck() {
  const response = await api.get('/reports/shift-closure')
  return unwrapNightPosResponse(response)
}

export async function fetchProductReconciliation(filters = {}) {
  const response = await api.get('/reports/product-reconciliation', { params: buildParams(filters) })
  return unwrapNightPosResponse(response)
}
