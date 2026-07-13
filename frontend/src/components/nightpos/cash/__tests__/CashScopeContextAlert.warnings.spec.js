import { describe, expect, it } from 'vitest'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

describe('CashScopeContextAlert scope warning rendering guards', () => {
  const filePath = resolve(process.cwd(), 'src/components/nightpos/cash/CashScopeContextAlert.vue')
  const content = readFileSync(filePath, 'utf8')

  it('filters known warnings already represented by dedicated alerts', () => {
    expect(content.includes('const knownWarnings = new Set([')).toBe(true)
    expect(content.includes("'La caja incluye actividad de multiples official_shift_id.'")).toBe(true)
    expect(content.includes("'Existen multiples turnos OPEN en la sucursal.'")).toBe(true)
  })

  it('renders contextual warnings from filtered computed collection', () => {
    expect(content.includes('const contextualWarnings = computed(() =>')).toBe(true)
    expect(content.includes('v-for="warning in contextualWarnings"')).toBe(true)
  })
})
