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
})
</script>

<template>
  <VAlert
    v-if="!connected"
    type="warning"
    variant="tonal"
    density="compact"
    class="mb-4"
    icon="ri-wifi-off-line"
  >
    <template v-if="reconnecting">
      Reconectando tiempo real…
    </template>
    <template v-else>
      Tiempo real desconectado — {{ pollHint }}
    </template>
  </VAlert>
</template>
