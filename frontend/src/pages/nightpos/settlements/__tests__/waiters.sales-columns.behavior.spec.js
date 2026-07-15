import { describe, expect, it } from 'vitest'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

describe('settlements waiters sales columns behavior', () => {
  const waitersPath = resolve(process.cwd(), 'src/pages/nightpos/settlements/waiters.vue')
  const waitersContent = readFileSync(waitersPath, 'utf8')

  const detailPath = resolve(process.cwd(), 'src/pages/nightpos/settlements/[id].vue')
  const detailContent = readFileSync(detailPath, 'utf8')

  it('muestra la columna Cant. ventas en la tabla de garzones', () => {
    expect(waitersContent.includes("title: 'Cant. ventas'")).toBe(true)
    expect(waitersContent.includes("key: 'sales_count'")).toBe(true)
  })

  it('muestra la columna Total vendido en la tabla de garzones', () => {
    expect(waitersContent.includes("title: 'Total vendido'")).toBe(true)
    expect(waitersContent.includes("key: 'sales_total_amount'")).toBe(true)
  })

  it('formatea montos en BOB para total vendido en la vista de garzones', () => {
    expect(waitersContent.includes('Intl.NumberFormat')).toBe(true)
    expect(waitersContent.includes('BOB')).toBe(true)
  })

  it('declara useDisplay para evitar crash en setup de la ruta', () => {
    expect(waitersContent.includes("import { useDisplay } from 'vuetify'"))
      .toBe(true)
    expect(waitersContent.includes('const { smAndDown } = useDisplay()')).toBe(true)
  })

  it('estado vacio muestra mensaje y no pantalla en blanco', () => {
    expect(waitersContent.includes('Sin turno clasificado. Genere liquidaciones desde el resumen cuando haya ventas cobradas.')).toBe(true)
    expect(waitersContent.includes('VDataTable')).toBe(true)
  })

  it('detalle de liquidacion de garzon incluye cantidad y total vendido', () => {
    expect(detailContent.includes('Cant. ventas')).toBe(true)
    expect(detailContent.includes('Total vendido')).toBe(true)
    expect(detailContent.includes('sales_total_amount')).toBe(true)
  })

  it('detalle de lineas muestra hora y forma de pago cuando existen', () => {
    expect(detailContent.includes("title: 'Hora'")).toBe(true)
    expect(detailContent.includes("title: 'Pago'")).toBe(true)
    expect(detailContent.includes('item.payment_method')).toBe(true)
  })
})
