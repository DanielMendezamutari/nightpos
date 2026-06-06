import { readonly, ref, watch } from 'vue'
import { useContextStore } from '@/stores/context'
import { useAuthStore } from '@/stores/auth'
import { fetchSseToken } from '@/api/events'

const BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api/v1'

/**
 * SSE composable — opens a persistent connection to the operational events stream.
 *
 * Usage:
 *   const { on, off, start, stop, connected, reconnecting } = useOperationalEvents()
 *   on('room_service.due', handler)
 *   start()  // usually called in onMounted
 *   stop()   // called in onUnmounted / on logout
 */
export function useOperationalEvents() {
  const context = useContextStore()
  const auth = useAuthStore()

  /** @type {Map<string, Set<Function>>} */
  const handlers = new Map()

  let es = null
  let reconnectTimer = null
  let backoff = 2000
  let lastEventId = 0
  let active = false

  const connected = ref(false)
  const reconnecting = ref(false)

  function on(eventType, handler) {
    if (!handlers.has(eventType))
      handlers.set(eventType, new Set())
    handlers.get(eventType).add(handler)
  }

  function off(eventType, handler) {
    handlers.get(eventType)?.delete(handler)
  }

  function dispatch(eventType, data) {
    handlers.get(eventType)?.forEach(h => h(data))
    handlers.get('*')?.forEach(h => h({ type: eventType, data }))
  }

  function closeSource() {
    if (es) {
      es.close()
      es = null
    }
    clearTimeout(reconnectTimer)
  }

  function handleDisconnect() {
    if (connected.value) {
      connected.value = false
      dispatch('error', {})
    }
    closeSource()
    if (active)
      scheduleReconnect()
  }

  function scheduleReconnect() {
    clearTimeout(reconnectTimer)
    reconnecting.value = true
    dispatch('reconnecting', {})
    reconnectTimer = setTimeout(() => {
      backoff = Math.min(backoff * 1.5, 30000)
      connect()
    }, backoff)
  }

  async function connect() {
    if (!active)
      return

    try {
      const { token } = await fetchSseToken()
      const url = `${BASE_URL}/events/stream?token=${encodeURIComponent(token)}&last_event_id=${lastEventId}`

      es = new EventSource(url)

      es.onopen = () => {
        connected.value = true
        reconnecting.value = false
        backoff = 2000
        dispatch('open', {})
      }

      es.onmessage = (evt) => {
        try {
          const payload = JSON.parse(evt.data)
          dispatch('message', payload)
        }
        catch (_) {}
      }

      es.onerror = () => {
        handleDisconnect()
      }

      const originalDispatch = es.dispatchEvent.bind(es)

      es.dispatchEvent = (event) => {
        if (event.lastEventId)
          lastEventId = Number(event.lastEventId) || lastEventId

        if (event.type && event.type !== 'message' && event.type !== 'error' && event.type !== 'open') {
          try {
            const payload = JSON.parse(event.data || 'null')
            dispatch(event.type, payload)
          }
          catch (_) {}
        }

        return originalDispatch(event)
      }
    }
    catch (_) {
      connected.value = false
      dispatch('error', {})
      if (active)
        scheduleReconnect()
    }
  }

  function start() {
    if (active)
      return
    active = true
    connect()
  }

  function stop() {
    active = false
    connected.value = false
    reconnecting.value = false
    closeSource()
    clearTimeout(reconnectTimer)
  }

  watch(
    () => auth.isAuthenticated,
    (loggedIn) => {
      if (!loggedIn)
        stop()
    },
  )

  watch(
    () => context.version,
    () => {
      if (active) {
        closeSource()
        lastEventId = 0
        backoff = 2000
        reconnecting.value = true
        dispatch('reconnecting', {})
        connect()
      }
    },
  )

  return {
    on,
    off,
    start,
    stop,
    connected: readonly(connected),
    reconnecting: readonly(reconnecting),
  }
}
