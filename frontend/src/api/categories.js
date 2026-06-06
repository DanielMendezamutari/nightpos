import api, { unwrapNightPosResponse } from '@/services/http'

export async function fetchCategories() {
  const response = await api.get('/product-categories')

  return unwrapNightPosResponse(response).categories ?? []
}

export async function fetchCategory(id) {
  const response = await api.get(`/product-categories/${id}`)

  return unwrapNightPosResponse(response).category
}

export async function updateCategory(id, payload) {
  const response = await api.put(`/product-categories/${id}`, payload)

  return unwrapNightPosResponse(response).category
}

export async function createCategory(payload) {
  const response = await api.post('/product-categories', payload)

  return unwrapNightPosResponse(response).category
}
