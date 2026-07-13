import { describe, expect, it } from 'vitest'
import { classifyCloseCheckError } from '@/utils/closeCheckError'

describe('classifyCloseCheckError', () => {
  it('classifies permission denied', () => {
    expect(classifyCloseCheckError(403, 'Forbidden').type).toBe('permission_denied')
  })

  it('classifies invalid context', () => {
    expect(classifyCloseCheckError(422, 'Contexto operativo incompleto.').type).toBe('invalid_context')
  })

  it('classifies inactive tenant', () => {
    expect(classifyCloseCheckError(422, 'La empresa no esta activa o la suscripcion ha vencido.').type).toBe('tenant_inactive')
  })

  it('classifies server error', () => {
    expect(classifyCloseCheckError(500, 'Error').type).toBe('server_error')
  })

  it('classifies network error when no status exists', () => {
    expect(classifyCloseCheckError(undefined, 'Network Error').type).toBe('network_error')
  })
})
