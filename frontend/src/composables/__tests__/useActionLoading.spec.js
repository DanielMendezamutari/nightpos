import { describe, expect, it } from 'vitest'
import { useActionLoading } from '@/composables/useActionLoading'

describe('useActionLoading', () => {
  it('marca loading durante la acción async', async () => {
    const { isLoading, run, keyFor } = useActionLoading()
    const key = keyFor(42, 'finish')

    expect(isLoading(key)).toBe(false)

    const promise = run(key, async () => {
      expect(isLoading(key)).toBe(true)
      await Promise.resolve()
    })

    await promise
    expect(isLoading(key)).toBe(false)
  })

  it('genera claves estables por id y acción', () => {
    const { keyFor } = useActionLoading()

    expect(keyFor(10, 'clean')).toBe('clean:10')
    expect(keyFor(10, 'finish')).toBe('finish:10')
  })
})
