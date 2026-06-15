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

let timer = null

const refresh = () => {
  overlayCount.value = countBlockingOverlays()
  sseState.value = getOperationalEventsDebugState()
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
