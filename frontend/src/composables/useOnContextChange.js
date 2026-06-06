import { useContextStore } from '@/stores/context'

/**
 * Ejecuta callback cuando cambia empresa/sucursal (sin F5).
 */
export function useOnContextChange(callback, { immediate = false } = {}) {
  const context = useContextStore()

  watch(
    () => context.version,
    () => callback(),
    { immediate },
  )
}
