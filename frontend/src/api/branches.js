import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchAdminBranches(tenantSlug = null) {
  const headers = tenantSlug ? { 'X-Tenant-Slug': tenantSlug } : {}

  const response = await api.get('/admin/branches', { headers })

  return unwrapNightPosResponse(response).branches ?? []
}

export async function fetchAdminBranch(id, tenantSlug = null) {
  const headers = tenantSlug ? { 'X-Tenant-Slug': tenantSlug } : {}

  const response = await api.get(`/admin/branches/${id}`, { headers })

  return unwrapNightPosResponse(response).branch
}

export async function updateAdminBranch(id, payload, tenantSlug = null) {
  const headers = tenantSlug ? { 'X-Tenant-Slug': tenantSlug } : {}

  const response = await api.put(`/admin/branches/${id}`, payload, { headers })

  return unwrapNightPosResponse(response).branch
}

export async function createAdminBranch(payload) {
  const response = await api.post('/admin/branches', payload)

  return unwrapNightPosResponse(response)
}
