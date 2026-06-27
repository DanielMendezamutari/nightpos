import { describe, expect, it } from 'vitest'
import { classifyApiError, isAuthEndpoint } from '@/services/http'

describe('isAuthEndpoint', () => {
  it('excluye login-context del refresh JWT proactivo', () => {
    expect(isAuthEndpoint('/auth/login-context/tenants')).toBe(true)
    expect(isAuthEndpoint('/auth/login-context/branches')).toBe(true)
    expect(isAuthEndpoint('/auth/login-pin')).toBe(true)
    expect(isAuthEndpoint('/auth/refresh')).toBe(true)
    expect(isAuthEndpoint('/orders')).toBe(false)
  })
})

describe('classifyApiError', () => {
  it('timeout muestra mensaje amigable', () => {
    const result = classifyApiError({
      code: 'ECONNABORTED',
      message: 'timeout of 30000ms exceeded',
    })

    expect(result.kind).toBe('timeout')
    expect(result.userMessage).toBe('El servidor está tardando más de lo normal. Intente nuevamente en unos segundos.')
  })

  it('404 HTML muestra mensaje de hosting/API', () => {
    const result = classifyApiError({
      response: {
        status: 404,
        headers: { 'content-type': 'text/html; charset=UTF-8' },
        data: '<!DOCTYPE html><html><body>Not Found</body></html>',
      },
    })

    expect(result.kind).toBe('api_routing')
    expect(result.userMessage).toBe('La API no está respondiendo correctamente. Verifique configuración del hosting.')
  })

  it('network reset muestra mensaje de conexión', () => {
    const result = classifyApiError({
      code: 'ERR_NETWORK',
      message: 'Network Error',
    })

    expect(result.kind).toBe('network')
    expect(result.userMessage).toBe('No se pudo conectar con el servidor. Verifique internet o hosting.')
  })

  it('JSON backend usa message del servidor', () => {
    const result = classifyApiError({
      response: {
        status: 422,
        headers: { 'content-type': 'application/json' },
        data: { message: 'Empresa no encontrada', success: false },
      },
    })

    expect(result.kind).toBe('client')
    expect(result.userMessage).toBe('Empresa no encontrada')
  })
})
