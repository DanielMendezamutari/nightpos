import { nextTick, onUnmounted, readonly, ref, watch } from 'vue'
import { useContextStore } from '@/stores/context'
import { useAuthStore } from '@/stores/auth'
import { fetchSseToken } from '@/api/events'

const BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api/v1'

const shared = {
  handlers: new Map(),
  refCount: 0,
  es: null,
  reconnectTimer: null,
  backoff: 2000,
  lastEventId: 0,
  active: false,
  connected: ref(false),
  reconnecting: ref(false),
}

function dispatch(eventType, data) {
  shared.handlers.get(eventType)?.forEach(handler => handler(data))
  shared.handlers.get('*')?.forEach(handler => handler({ type: eventType, data }))
}

function closeSource() {
  if (shared.es) {
    shared.es.close()
    shared.es = null
  }
  clearTimeout(shared.reconnectTimer)
}

function handleDisconnect() {
  if (shared.connected.value) {
    shared.connected.value = false
    dispatch('error', {})
  }
  closeSource()
  if (shared.active)
    scheduleReconnect()
}

function scheduleReconnect() {
  clearTimeout(shared.reconnectTimer)
  shared.reconnecting.value = true
  dispatch('reconnecting', {})
  shared.reconnectTimer = setTimeout(() => {
    shared.backoff = Math.min(shared.backoff * 1.5, 30000)
    connect()
  }, shared.backoff)
}

async function connect() {
  if (!shared.active)
    return

  try {
    const { token } = await fetchSseToken()
    const url = `${BASE_URL}/events/stream?token=${encodeURIComponent(token)}&last_event_id=${shared.lastEventId}`

    shared.es = new EventSource(url)

    shared.es.onopen = () => {
      shared.connected.value = true
      shared.reconnecting.value = false
      shared.backoff = 2000
      dispatch('open', {})
    }

    shared.es.onmessage = (evt) => {
      try {
        const payload = JSON.parse(evt.data)
        dispatch('message', payload)
      }
      catch (_) {}
    }

    shared.es.onerror = () => {
      handleDisconnect()
    }

    const originalDispatch = shared.es.dispatchEvent.bind(shared.es)

    shared.es.dispatchEvent = (event) => {
      if (event.lastEventId)
        shared.lastEventId = Number(event.lastEventId) || shared.lastEventId

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
    shared.connected.value = false
    dispatch('error', {})
    if (shared.active)
      scheduleReconnect()
  }
}

function internalStart() {
  if (shared.active)
    return
  shared.active = true
  connect()
}

function internalStop() {
  shared.active = false
  shared.connected.value = false
  shared.reconnecting.value = false
  closeSource()
  clearTimeout(shared.reconnectTimer)
}

/**
 * SSE singleton — una conexión compartida; handlers por instancia se limpian al desmontar.
 */
export function useOperationalEvents() {
  const context = useContextStore()
  const auth = useAuthStore()
  const localHandlers = []
  let startedHere = false

  function on(eventType, handler) {
    if (!shared.handlers.has(eventType))
      shared.handlers.set(eventType, new Set())
    shared.handlers.get(eventType).add(handler)
    localHandlers.push({ eventType, handler })
  }

  function off(eventType, handler) {
    shared.handlers.get(eventType)?.delete(handler)
    const idx = localHandlers.findIndex(h => h.eventType === eventType && h.handler === handler)
    if (idx >= 0)
      localHandlers.splice(idx, 1)
  }

  function cleanupLocalHandlers() {
    for (const { eventType, handler } of localHandlers)
      shared.handlers.get(eventType)?.delete(handler)
    localHandlers.length = 0
  }

  function start() {
    if (startedHere)
      return
    startedHere = true
    shared.refCount++
    if (shared.refCount === 1)
      internalStart()
  }

  function stop() {
    if (!startedHere)
      return
    startedHere = false
    cleanupLocalHandlers()
    shared.refCount = Math.max(0, shared.refCount - 1)
    if (shared.refCount === 0)
      internalStop()
  }

  onUnmounted(stop)

  watch(
    () => auth.isAuthenticated,
    (loggedIn) => {
      if (!loggedIn) {
        cleanupLocalHandlers()
        shared.refCount = 0
        internalStop()
      }
    },
  )

  watch(
    () => context.version,
    () => {
      if (shared.active) {
        closeSource()
        shared.lastEventId = 0
        shared.backoff = 2000
        shared.reconnecting.value = true
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
    connected: readonly(shared.connected),
    reconnecting: readonly(shared.reconnecting),
  }
}

export function getOperationalEventsDebugState() {
  return {
    refCount: shared.refCount,
    active: shared.active,
    connected: shared.connected.value,
    reconnecting: shared.reconnecting.value,
    handlerBuckets: shared.handlers.size,
    handlerCount: [...shared.handlers.values()].reduce((sum, set) => sum + set.size, 0),
  }
}
