import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchLoginContextTenants() {
  const response = await api.get('/auth/login-context/tenants')

  return unwrapNightPosResponse(response).tenants ?? []
}

export async function fetchLoginContextBranches(tenantSlug) {
  const response = await api.get('/auth/login-context/branches', {
    params: { tenant_slug: tenantSlug },
  })

  return unwrapNightPosResponse(response).branches ?? []
}
