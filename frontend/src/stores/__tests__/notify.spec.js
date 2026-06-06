import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it } from 'vitest'
import { useNotifyStore } from '@/stores/notify'

describe('useNotifyStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('muestra mensaje success por defecto', () => {
    const store = useNotifyStore()

    store.notify('Pieza terminada')

    expect(store.show).toBe(true)
    expect(store.text).toBe('Pieza terminada')
    expect(store.color).toBe('success')
  })

  it('oculta el snackbar al llamar hide', () => {
    const store = useNotifyStore()

    store.notify('Error', 'error')
    store.hide()

    expect(store.show).toBe(false)
  })
})
