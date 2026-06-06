import api from '@/services/http'

export async function fetchPaymentMethods(params = {}) {
  const response = await api.get('/payment-methods', { params })
  return response.data?.payment_methods ?? []
}

export async function createPaymentMethod(payload) {
  const response = await api.post('/payment-methods', payload)
  return response.data?.payment_method
}

export async function updatePaymentMethod(id, payload) {
  const response = await api.put(`/payment-methods/${id}`, payload)
  return response.data?.payment_method
}
