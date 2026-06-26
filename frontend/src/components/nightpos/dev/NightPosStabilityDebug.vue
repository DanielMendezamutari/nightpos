<script setup>
import { getOperationalEventsDebugState } from '@/composables/useOperationalEvents'
import { countBlockingOverlays } from '@/utils/overlaySafety'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const auth = useAuthStore()
const isDev = import.meta.env.DEV
const expanded = ref(false)
const overlayCount = ref(0)
const sseState = ref(getOperationalEventsDebugState())
const lastApiError = ref('')
const apiEvents = ref([])

let timer = null

const refresh = () => {
  overlayCount.value = countBlockingOverlays()
  sseState.value = getOperationalEventsDebugState()
}

async function unregisterServiceWorker() {
  if (!('serviceWorker' in navigator))
    return

  const regs = await navigator.serviceWorker.getRegistrations()
  await Promise.all(regs.map(r => r.unregister()))
  if ('caches' in window) {
    const keys = await caches.keys()
    await Promise.all(keys.map(k => caches.delete(k)))
  }
  window.location.reload()
}

if (isDev) {
  onMounted(() => {
    refresh()
    timer = setInterval(refresh, 1500)
  })

  onUnmounted(() => {
    if (timer)
      clearInterval(timer)
  })
}

if (isDev && typeof window !== 'undefined') {
  window.__nightposStability = {
    setLastApiError: message => { lastApiError.value = message || '' },
    pushApiEvent: (event, detail = {}) => {
      apiEvents.value = [{ at: new Date().toISOString(), event, detail }, ...apiEvents.value].slice(0, 8)
    },
  }
}
</script>

<template>
  <div
    v-if="isDev"
    class="nightpos-stability-debug"
  >
    <VBtn
      size="x-small"
      variant="tonal"
      color="secondary"
      @click="expanded = !expanded"
    >
      DBG
    </VBtn>
    <VCard
      v-if="expanded"
      class="nightpos-stability-debug__panel pa-2 text-caption"
      elevation="8"
    >
      <div><strong>Ruta:</strong> {{ route.name || route.path }}</div>
      <div><strong>Overlays:</strong> {{ overlayCount }}</div>
      <div><strong>SSE:</strong> {{ sseState.connected ? 'connected' : 'off' }} (refs {{ sseState.refCount }}, handlers {{ sseState.handlerCount }})</div>
      <div><strong>Auth loading:</strong> {{ auth.loading ? 'yes' : 'no' }}</div>
      <div v-if="lastApiError">
        <strong>Último API error:</strong> {{ lastApiError }}
      </div>
      <div v-if="apiEvents.length">
        <strong>API events:</strong>
        <div
          v-for="(ev, idx) in apiEvents"
          :key="idx"
        >
          {{ ev.event }} {{ ev.detail?.status ?? '' }} {{ ev.detail?.kind ?? '' }}
        </div>
      </div>
      <VBtn
        size="x-small"
        variant="text"
        class="mt-1"
        @click="unregisterServiceWorker"
      >
        Limpiar SW + cache (dev)
      </VBtn>
    </VCard>
  </div>
</template>

<style scoped>
.nightpos-stability-debug {
  position: fixed;
  z-index: 99999;
  inset-block-end: 12px;
  inset-inline-end: 12px;
}

.nightpos-stability-debug__panel {
  position: absolute;
  inset-block-end: 100%;
  inset-inline-end: 0;
  margin-block-end: 6px;
  max-inline-size: 320px;
  white-space: normal;
}
</style>
