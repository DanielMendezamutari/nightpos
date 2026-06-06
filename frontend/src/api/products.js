import api, { unwrapNightPosResponse } from '@/services/http'



export async function fetchProducts(params = {}) {

  const response = await api.get('/products', { params })



  return unwrapNightPosResponse(response).products ?? []

}



export async function fetchPosCatalog(params = {}) {

  const response = await api.get('/products/pos-catalog', { params })



  return unwrapNightPosResponse(response)

}



export async function fetchProduct(id) {

  const response = await api.get(`/products/${id}`)



  return unwrapNightPosResponse(response).product

}



export async function createProduct(payload) {

  const response = await api.post('/products', payload)



  return unwrapNightPosResponse(response).product

}



export async function quickCreateProduct(payload) {

  const response = await api.post('/products/quick', payload)



  return unwrapNightPosResponse(response)

}



export async function updateProduct(id, payload) {

  const response = await api.put(`/products/${id}`, payload)



  return unwrapNightPosResponse(response).product

}



export async function fetchProductPrices(productId) {

  const response = await api.get(`/products/${productId}/prices`)



  return unwrapNightPosResponse(response).prices ?? []

}



export async function createProductPrice(productId, payload) {

  const response = await api.post(`/products/${productId}/prices`, payload)



  return unwrapNightPosResponse(response).price

}



export async function replaceActiveProductPrice(productId, payload) {

  const response = await api.put(`/products/${productId}/prices/active`, payload)



  return unwrapNightPosResponse(response).price

}



export async function createQuickProductPrice(productId, payload) {

  const response = await api.post(`/products/${productId}/quick-prices`, payload)



  return unwrapNightPosResponse(response).price

}

