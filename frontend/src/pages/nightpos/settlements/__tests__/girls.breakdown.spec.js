import { describe, expect, it } from 'vitest'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

describe('settlements girls breakdown visibility', () => {
  const filePath = resolve(process.cwd(), 'src/pages/nightpos/settlements/girls.vue')
  const content = readFileSync(filePath, 'utf8')

  it('muestra columnas confirmadas y provisionales para chicas', () => {
    expect(content.includes("{ title: 'Consumos cobrados', key: 'consumption_total' }")).toBe(true)
    expect(content.includes("{ title: 'Piezas', key: 'pieces_total' }")).toBe(true)
    expect(content.includes("{ title: 'Manillas / reparto', key: 'bracelets_total' }")).toBe(true)
    expect(content.includes("{ title: 'Show', key: 'shows_total' }")).toBe(true)
    expect(content.includes("{ title: 'Total pagable', key: 'total_amount' }")).toBe(true)
    expect(content.includes("{ title: 'Provisional sin cobrar', key: 'provisional_total_amount' }")).toBe(true)
  })

  it('abre detalle operativo local con filas confirmadas y provisionales', () => {
    expect(content.includes('const openDetailDialog = item => {')).toBe(true)
    expect(content.includes('Detalle operativo')).toBe(true)
    expect(content.includes("detail.provisional ? 'Provisional' : 'Confirmado'")).toBe(true)
  })
})