import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchGirlShiftEarnings() {
  const response = await api.get('/girl/shift-earnings')

  return unwrapNightPosResponse(response).earnings ?? null
}
