import { onMounted, onUnmounted } from 'vue'
import { useOperationalEvents } from '@/composables/useOperationalEvents'
import { useNightPosNotify } from '@/composables/useNightPosNotify'

function resolveOrderId(data) {
  if (!data || typeof data !== 'object')
    return null

  const id = data.order_id ?? data.entity?.id

  return id != null ? Number(id) : null
}

/**
 * SSE operativo para comandas — listas y detalle.
 */
export function useOrderOperationalEvents(reloadFn, options = {}) {
  const {
    orderId = null,
    toastOnCreated = false,
    toastOnUpdated = false,
    toastOnSentToBar = false,
    createdDebounceMs = 100,
    updatedDebounceMs = 500,
    onTerminalStatus = null,
  } = options

  const { on, start, stop, connected, reconnecting } = useOperationalEvents()
  const { notify } = useNightPosNotify()

  let createdTimer = null
  let updatedTimer = null

  const scopedOrderId = orderId != null ? Number(orderId) : null

  const shouldRefresh = (data) => {
    if (scopedOrderId == null)
      return true

    return resolveOrderId(data) === scopedOrderId
  }

  const scheduleCreated = () => {
    clearTimeout(createdTimer)
    createdTimer = setTimeout(() => reloadFn(), createdDebounceMs)
  }

  const scheduleUpdated = () => {
    clearTimeout(updatedTimer)
    updatedTimer = setTimeout(() => reloadFn(), updatedDebounceMs)
  }

  on('order.created', (data) => {
    if (!shouldRefresh(data))
      return

    if (toastOnCreated)
      notify('Nueva comanda recibida.', 'info')

    scheduleCreated()
  })

  on('order.updated', (data) => {
    if (!shouldRefresh(data))
      return

    if (toastOnUpdated)
      notify('Comanda actualizada.', 'info')

    scheduleUpdated()
  })

  on('order.sent_to_bar', (data) => {
    if (!shouldRefresh(data))
      return

    if (toastOnSentToBar)
      notify('Nueva comanda enviada a barra', 'info')

    scheduleUpdated()
  })

  const handleTerminal = (data) => {
    if (!shouldRefresh(data))
      return

    scheduleUpdated()

    if (onTerminalStatus)
      onTerminalStatus(data?.status ?? null)
  }

  on('order.billed', handleTerminal)
  on('order.cancelled', handleTerminal)

  onMounted(() => start())

  onUnmounted(() => {
    clearTimeout(createdTimer)
    clearTimeout(updatedTimer)
    stop()
  })

  return { connected, reconnecting }
}
