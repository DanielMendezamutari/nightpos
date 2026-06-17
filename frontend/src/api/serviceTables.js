import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchServiceTables(params = {}) {
  const response = await api.get('/service-tables', { params })

  return unwrapNightPosResponse(response).service_tables ?? []
}

export async function createServiceTable(payload) {
  const response = await api.post('/service-tables', payload)

  return unwrapNightPosResponse(response).service_table
}

export async function updateServiceTable(id, payload) {
  const response = await api.put(`/service-tables/${id}`, payload)

  return unwrapNightPosResponse(response).service_table
}

export async function fetchWaiterTableAssignments(params = {}) {
  const response = await api.get('/waiter-table-assignments', { params })

  return unwrapNightPosResponse(response).waiter_table_assignments ?? []
}

export async function syncWaiterTableAssignments(payload) {
  const response = await api.put('/waiter-table-assignments/sync', payload)

  return unwrapNightPosResponse(response).waiter_table_assignments ?? []
}
