<script setup>
defineProps({
  connected: {
    type: Boolean,
    default: true,
  },
  reconnecting: {
    type: Boolean,
    default: false,
  },
  pollHint: {
    type: String,
    default: 'actualizando cada 30 s',
  },
  showConnected: {
    type: Boolean,
    default: true,
  },
})
</script>

<template>
  <VChip
    v-if="showConnected && connected && !reconnecting"
    color="success"
    variant="tonal"
    size="small"
    class="mb-3 nightpos-sse-chip"
    prepend-icon="ri-wifi-line"
  >
    Tiempo real activo
  </VChip>

  <VAlert
    v-else-if="reconnecting"
    type="warning"
    variant="tonal"
    density="compact"
    class="mb-3"
    icon="ri-refresh-line"
  >
    Reconectando tiempo real…
  </VAlert>

  <VAlert
    v-else-if="!connected"
    type="warning"
    variant="tonal"
    density="compact"
    class="mb-3"
    icon="ri-wifi-off-line"
  >
    Tiempo real desconectado — {{ pollHint }}
  </VAlert>
</template>

<style scoped>
.nightpos-sse-chip {
  font-size: 0.75rem;
}
</style>
