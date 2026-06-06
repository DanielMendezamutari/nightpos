import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchAdminTenants() {
  const response = await api.get('/admin/tenants')

  return unwrapNightPosResponse(response).tenants ?? []
}

export async function fetchAdminTenant(id) {
  const response = await api.get(`/admin/tenants/${id}`)

  return unwrapNightPosResponse(response).tenant
}

export async function updateAdminTenant(id, payload) {
  const response = await api.put(`/admin/tenants/${id}`, payload)

  return unwrapNightPosResponse(response).tenant
}

export async function createAdminTenant(payload) {
  const response = await api.post('/admin/tenants', payload)

  return unwrapNightPosResponse(response)
}
