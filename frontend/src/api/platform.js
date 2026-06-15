import api, { unwrapNightPosResponse } from '@/services/http'

export async function platformSetup(payload) {
  const response = await api.post('/admin/platform/setup', payload)

  return unwrapNightPosResponse(response)
}

export async function fetchPlatformDashboard() {
  const response = await api.get('/admin/platform/dashboard')

  return unwrapNightPosResponse(response)
}
