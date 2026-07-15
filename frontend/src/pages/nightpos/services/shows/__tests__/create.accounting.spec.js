import { describe, expect, it } from 'vitest'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

describe('shows create accounting semantics', () => {
  const filePath = resolve(process.cwd(), 'src/pages/nightpos/services/shows/create.vue')
  const content = readFileSync(filePath, 'utf8')

  it('explica que el show no aumenta ingresos ni efectivo esperado', () => {
    expect(content.includes('No suma ingresos ni efectivo esperado en caja.')).toBe(true)
  })

  it('ya no envia payment_method al backend', () => {
    expect(content.includes('payment_method: form.value.payment_method')).toBe(false)
    expect(content.includes("label=\"Método de pago *\"")).toBe(false)
  })
})