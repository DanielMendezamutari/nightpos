<script setup>
/**
 * OfflineBanner — indicator shown when the device loses internet connection.
 *
 * Uses useNetworkStatus (which wraps @vueuse/core useOnline).
 * When back online, shows a brief "Conexión restaurada" confirmation.
 *
 * Props:
 *   compact  Boolean  — use compact/tonal chip instead of full VAlert.
 */
defineProps({
  compact: {
    type: Boolean,
    default: false,
  },
})

const { isOffline } = useNetworkStatus()

const justReconnected = ref(false)
let reconnectTimer = null

watch(isOffline, (offline) => {
  if (!offline) {
    justReconnected.value = true
    clearTimeout(reconnectTimer)
    reconnectTimer = setTimeout(() => {
      justReconnected.value = false
    }, 3000)
  }
})

onUnmounted(() => clearTimeout(reconnectTimer))
</script>

<template>
  <!-- Offline alert -->
  <VAlert
    v-if="isOffline && !compact"
    type="error"
    variant="tonal"
    density="compact"
    class="mb-3"
    icon="ri-wifi-off-line"
  >
    <div>
      <strong>Sin conexión</strong>
    </div>
    <div class="text-body-2 mt-1">
      NightPOS necesita internet para comandar y cobrar. Revisa tu red.
    </div>
  </VAlert>

  <VChip
    v-else-if="isOffline && compact"
    color="error"
    variant="tonal"
    size="small"
    class="mb-3"
    prepend-icon="ri-wifi-off-line"
  >
    Sin conexión
  </VChip>

  <!-- Brief reconnection confirmation -->
  <VChip
    v-else-if="justReconnected"
    color="success"
    variant="tonal"
    size="small"
    class="mb-3"
    prepend-icon="ri-wifi-line"
  >
    Conexión restaurada
  </VChip>
</template>
