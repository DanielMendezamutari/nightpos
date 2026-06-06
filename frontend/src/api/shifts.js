import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchCurrentShift() {
  const response = await api.get('/shifts/current')

  return unwrapNightPosResponse(response).shift
}

export async function fetchShifts() {
  const response = await api.get('/shifts')

  return unwrapNightPosResponse(response).shifts ?? []
}

export async function fetchShift(id) {
  const response = await api.get(`/shifts/${id}`)

  return unwrapNightPosResponse(response).shift
}

export async function fetchShiftSummary(id) {
  const response = await api.get(`/shifts/${id}/summary`)

  return unwrapNightPosResponse(response)
}

export async function openShift(payload) {
  const response = await api.post('/shifts/open', payload)

  return unwrapNightPosResponse(response).shift
}

export async function closeShift(id, payload) {
  const response = await api.post(`/shifts/${id}/close`, payload)

  return unwrapNightPosResponse(response)
}

export async function downloadShiftCsv(id) {
  const response = await api.get(`/shifts/${id}/export.csv`, { responseType: 'blob' })
  const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')

  link.href = url
  link.download = `turno-${id}.csv`
  link.click()
  URL.revokeObjectURL(url)
}
