import { describe, expect, it } from 'vitest'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

describe('close.vue close-check handling', () => {
  const filePath = resolve(process.cwd(), 'src/pages/nightpos/shifts/close.vue')
  const content = readFileSync(filePath, 'utf8')

  it('does not silently swallow close-check errors', () => {
    expect(content.includes('fetchShiftCloseCheck().catch(() => null)')).toBe(false)
  })

  it('renders explicit close-check error state with retry action', () => {
    expect(content.includes('closureCheckError')).toBe(true)
    expect(content.includes('Reintentar verificacion')).toBe(true)
    expect(content.includes('@click="loadClosureCheck"')).toBe(true)
  })

  it('keeps resolver button gated by available closureCheck data', () => {
    expect(content.includes('<template v-if="closureCheck">')).toBe(true)
    expect(content.includes('Resolver turnos abiertos duplicados')).toBe(true)
  })
})
