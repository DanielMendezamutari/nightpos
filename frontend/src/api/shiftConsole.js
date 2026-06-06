import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchShiftConsoleCurrent() {
  const response = await api.get('/shift-console/current')

  return unwrapNightPosResponse(response)
}
