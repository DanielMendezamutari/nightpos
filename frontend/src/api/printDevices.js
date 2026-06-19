import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchPrintSettings() {
  const response = await api.get('/print-settings')

  return unwrapNightPosResponse(response)
}

export async function updatePrintSettings(payload) {
  const response = await api.patch('/print-settings', payload)

  return unwrapNightPosResponse(response)
}

export async function fetchPrintDevices() {
  const response = await api.get('/print-devices')

  return unwrapNightPosResponse(response).devices ?? []
}

export async function registerPrintDevice(payload) {
  const response = await api.post('/print-devices/register', payload)

  return unwrapNightPosResponse(response)
}

export async function updatePrintDevice(id, payload) {
  const response = await api.patch(`/print-devices/${id}`, payload)

  return unwrapNightPosResponse(response).device
}

export async function rotatePrintDeviceKey(id) {
  const response = await api.post(`/print-devices/${id}/rotate-key`)

  return unwrapNightPosResponse(response)
}

export async function fetchPrintJobs(params = {}) {
  const response = await api.get('/print-jobs', { params })

  return unwrapNightPosResponse(response).jobs ?? []
}

export async function fetchOrderPrintStatus(orderId) {
  const response = await api.get(`/orders/${orderId}/print-status`)

  return unwrapNightPosResponse(response).print_job
}

export async function reprintOrderCommand(orderId) {
  const response = await api.post(`/orders/${orderId}/reprint`)

  return unwrapNightPosResponse(response).job
}
