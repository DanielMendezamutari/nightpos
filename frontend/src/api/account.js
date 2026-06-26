import api, { getApiErrorMessage, unwrapNightPosResponse } from '@/services/http'

export async function changeOwnPassword(payload) {
  const response = await api.patch('/auth/me/password', payload)

  return unwrapNightPosResponse(response)
}

export async function changeOwnPin(payload) {
  const response = await api.patch('/auth/me/pin', payload)

  return unwrapNightPosResponse(response)
}

export { getApiErrorMessage }
