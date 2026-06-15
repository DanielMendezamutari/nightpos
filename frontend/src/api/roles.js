import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchAdminRoles() {
  const response = await api.get('/admin/roles')

  return unwrapNightPosResponse(response).roles ?? []
}

export async function fetchAdminRole(id) {
  const response = await api.get(`/admin/roles/${id}`)

  return unwrapNightPosResponse(response).role
}

export async function fetchManageablePermissions() {
  const response = await api.get('/admin/permissions')

  return unwrapNightPosResponse(response).groups ?? []
}

export async function createAdminRole(payload) {
  const response = await api.post('/admin/roles', payload)

  return unwrapNightPosResponse(response).role
}

export async function updateAdminRole(id, payload) {
  const response = await api.put(`/admin/roles/${id}`, payload)

  return unwrapNightPosResponse(response).role
}

export async function updateAdminRolePermissions(id, permissionSlugs) {
  const response = await api.put(`/admin/roles/${id}/permissions`, {
    permission_slugs: permissionSlugs,
  })

  return unwrapNightPosResponse(response).role
}

export async function deleteAdminRole(id) {
  const response = await api.delete(`/admin/roles/${id}`)

  return unwrapNightPosResponse(response)
}
