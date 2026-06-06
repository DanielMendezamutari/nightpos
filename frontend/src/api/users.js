import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchAdminUsers() {
  const response = await api.get('/admin/users')

  return unwrapNightPosResponse(response).users ?? []
}

export async function fetchAdminUser(id) {
  const response = await api.get(`/admin/users/${id}`)

  return unwrapNightPosResponse(response).user
}

export async function createAdminUser(payload) {
  const response = await api.post('/admin/users', payload)

  return unwrapNightPosResponse(response).user
}

export async function updateAdminUser(id, payload) {
  const response = await api.put(`/admin/users/${id}`, payload)

  return unwrapNightPosResponse(response).user
}

export async function resetAdminUserPin(id, pin) {
  const response = await api.post(`/admin/users/${id}/reset-pin`, { pin })

  return unwrapNightPosResponse(response)
}

export async function resetAdminUserPassword(id, password) {
  const response = await api.post(`/admin/users/${id}/reset-password`, { password })

  return unwrapNightPosResponse(response)
}

export async function grantAdminUserBranch(id, branchId) {
  const response = await api.post(`/admin/users/${id}/branches`, { branch_id: branchId })

  return unwrapNightPosResponse(response).user
}

export async function revokeAdminUserBranch(id, branchId) {
  const response = await api.delete(`/admin/users/${id}/branches/${branchId}`)

  return unwrapNightPosResponse(response).user
}
