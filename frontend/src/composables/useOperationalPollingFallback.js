import { onMounted, onUnmounted } from 'vue'
import { useOperationalEvents } from '@/composables/useOperationalEvents'

/**
 * Polling de respaldo para pantallas operativas.
 * Si onlyWhenDisconnected=true, solo recarga cuando SSE no está conectado.
 */
export function useOperationalPollingFallback(reloadFn, options = {}) {
  const {
    intervalMs = 30000,
    onlyWhenDisconnected = false,
  } = options

  const { connected } = useOperationalEvents()
  let pollTimer = null

  const tick = () => {
    if (onlyWhenDisconnected && connected.value)
      return

    reloadFn()
  }

  onMounted(() => {
    pollTimer = setInterval(tick, intervalMs)
  })

  onUnmounted(() => {
    if (pollTimer)
      clearInterval(pollTimer)
  })

  return { connected }
}
