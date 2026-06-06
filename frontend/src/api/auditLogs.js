import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchAuditLogs() {
  const response = await api.get('/audit-logs')

  return unwrapNightPosResponse(response).logs ?? []
}
