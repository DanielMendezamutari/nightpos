<script setup>
defineProps({
  showAdd: { type: Boolean, default: false },
  showCharge: { type: Boolean, default: false },
  showSend: { type: Boolean, default: false },
  showCancel: { type: Boolean, default: false },
  chargeHint: { type: String, default: '' },
  actionLoading: { type: Boolean, default: false },
})

const emit = defineEmits(['add', 'charge', 'send', 'cancel'])

const onAdd = () => {
  if (import.meta.env.DEV)
    console.log('[OrderActionsBar] add clicked')
  emit('add')
}

const onCharge = () => {
  if (import.meta.env.DEV)
    console.log('[OrderActionsBar] charge clicked')
  emit('charge')
}

const onSend = () => {
  if (import.meta.env.DEV)
    console.log('[OrderActionsBar] send clicked')
  emit('send')
}

const onCancel = () => {
  if (import.meta.env.DEV)
    console.log('[OrderActionsBar] cancel clicked')
  emit('cancel')
}
</script>

<template>
  <div class="order-actions-bar">
    <VAlert
      v-if="chargeHint"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-3"
    >
      {{ chargeHint }}
    </VAlert>

    <VBtn
      v-if="showAdd"
      color="primary"
      size="x-large"
      block
      class="mb-3 order-actions-bar__btn"
      @click="onAdd"
    >
      <VIcon
        icon="ri-add-circle-line"
        start
      />
      Agregar producto
    </VBtn>

    <VBtn
      v-if="showCharge"
      color="success"
      size="x-large"
      block
      class="mb-3 order-actions-bar__btn"
      @click="onCharge"
    >
      <VIcon
        icon="ri-money-dollar-circle-line"
        start
      />
      Cobrar comanda
    </VBtn>

    <VBtn
      v-if="showSend"
      color="info"
      size="x-large"
      block
      class="mb-3 order-actions-bar__btn"
      :loading="actionLoading"
      @click="onSend"
    >
      <VIcon
        icon="ri-send-plane-line"
        start
      />
      Enviar a barra
    </VBtn>

    <VBtn
      v-if="showCancel"
      color="error"
      variant="outlined"
      size="large"
      block
      class="order-actions-bar__btn"
      @click="onCancel"
    >
      Cancelar comanda
    </VBtn>
  </div>
</template>

<style scoped>
.order-actions-bar {
  position: sticky;
  z-index: 12;
  inset-block-end: 0;
  padding-block: 1rem;
  background: rgb(var(--v-theme-surface));
  border-block-start: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.order-actions-bar__btn {
  min-block-size: 3.25rem;
}
</style>
