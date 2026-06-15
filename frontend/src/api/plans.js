import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchPlatformPlans() {
  const response = await api.get('/admin/platform/plans')

  return unwrapNightPosResponse(response).plans ?? []
}

export async function createPlatformPlan(payload) {
  const response = await api.post('/admin/platform/plans', payload)

  return unwrapNightPosResponse(response).plan
}

export async function updatePlatformPlan(id, payload) {
  const response = await api.put(`/admin/platform/plans/${id}`, payload)

  return unwrapNightPosResponse(response).plan
}

export async function deletePlatformPlan(id) {
  const response = await api.delete(`/admin/platform/plans/${id}`)

  return unwrapNightPosResponse(response)
}

export async function fetchPlatformPlanLimits(id) {
  const response = await api.get(`/admin/platform/plans/${id}/limits`)

  return unwrapNightPosResponse(response)
}

export async function updatePlatformPlanLimits(id, limits) {
  const response = await api.put(`/admin/platform/plans/${id}/limits`, { limits })

  return unwrapNightPosResponse(response)
}

export async function duplicatePlatformPlan(id) {
  const response = await api.post(`/admin/platform/plans/${id}/duplicate`)

  return unwrapNightPosResponse(response).plan
}
