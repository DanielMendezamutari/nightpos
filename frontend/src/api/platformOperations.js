import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchPlatformOperationsDashboard() {
  const response = await api.get('/admin/platform/operations/dashboard')

  return unwrapNightPosResponse(response)
}

export async function fetchPlatformOperationsTenants(params = {}) {
  const response = await api.get('/admin/platform/operations/tenants', { params })

  return unwrapNightPosResponse(response)
}

export async function fetchPlatformOperationsTenant(tenantId) {
  const response = await api.get(`/admin/platform/operations/tenants/${tenantId}`)

  return unwrapNightPosResponse(response)
}

export async function fetchPlatformOperationsPrintAgents() {
  const response = await api.get('/admin/platform/operations/print-agents')

  return unwrapNightPosResponse(response)
}

export async function fetchPlatformOperationsTechnicalProfile(tenantId) {
  const response = await api.get(`/admin/platform/operations/tenants/${tenantId}/technical-profile`)

  return unwrapNightPosResponse(response)
}

export async function updatePlatformOperationsTechnicalProfile(tenantId, payload) {
  const response = await api.put(`/admin/platform/operations/tenants/${tenantId}/technical-profile`, payload)

  return unwrapNightPosResponse(response)
}

export async function fetchPlatformOperationsChecklist(tenantId) {
  const response = await api.get(`/admin/platform/operations/tenants/${tenantId}/checklist`)

  return unwrapNightPosResponse(response)
}

export async function patchPlatformOperationsChecklistItem(tenantId, key, payload) {
  const response = await api.patch(`/admin/platform/operations/tenants/${tenantId}/checklist/${key}`, payload)

  return unwrapNightPosResponse(response)
}
