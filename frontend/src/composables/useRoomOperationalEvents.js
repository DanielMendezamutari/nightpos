import { onMounted, onUnmounted } from 'vue'
import { useOperationalEvents } from '@/composables/useOperationalEvents'
import { useNightPosNotify } from '@/composables/useNightPosNotify'

const ROOM_SSE_EVENTS = [
  'room_service.created',
  'room_service.due',
  'room_service.finished',
  'room.cleaned',
  'cleaning.earnings.updated',
]

/**
 * SSE operativo para piezas / habitaciones / limpieza.
 */
export function useRoomOperationalEvents(reloadFn, options = {}) {
  const { on, start, stop, connected, reconnecting } = useOperationalEvents()
  const { notify } = useNightPosNotify()

  let debounceTimer = null
  const debouncedReload = () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => reloadFn(), options.debounceMs ?? 400)
  }

  ROOM_SSE_EVENTS.forEach((eventType) => {
    if (eventType === 'room_service.due' && options.toastOnDue !== false) {
      on(eventType, (data) => {
        debouncedReload()
        const label = data?.payload?.summary ?? data?.summary ?? 'Pieza'
        notify(`⏰ ${label}`, 'warning')
      })
    }
    else {
      on(eventType, debouncedReload)
    }
  })

  onMounted(() => start())
  onUnmounted(() => {
    clearTimeout(debounceTimer)
    stop()
  })

  return { connected, reconnecting }
}
