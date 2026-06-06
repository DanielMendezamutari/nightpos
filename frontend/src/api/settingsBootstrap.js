import api, { unwrapNightPosResponse } from '@/services/http'

export async function bootstrapOperationalData() {
  const response = await api.post('/settings/bootstrap-operational')

  return unwrapNightPosResponse(response)
}
