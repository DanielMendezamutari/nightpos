import api, { unwrapNightPosResponse } from '@/services/http'

/**
 * Request a short-lived SSE token (60 s TTL).
 * The caller must be authenticated and have a branch context set.
 */
export async function fetchSseToken() {
  const response = await api.post('/events/token')

  return unwrapNightPosResponse(response)
}
