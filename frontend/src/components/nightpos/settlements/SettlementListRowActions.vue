<script setup>
defineProps({
  item: {
    type: Object,
    required: true,
  },
  canPay: {
    type: Boolean,
    default: false,
  },
  canMultar: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['pay', 'multar', 'detail'])

const isPending = item => item.status === 'PENDING'
const isCancelled = item => item.status === 'CANCELLED'
</script>

<template>
  <div class="settlement-row-actions d-flex gap-2 flex-wrap align-center">
    <VBtn
      v-if="canPay && isPending(item)"
      size="small"
      color="success"
      variant="tonal"
      prepend-icon="ri-check-line"
      class="settlement-row-actions__btn"
      @click="emit('pay', item)"
    >
      Pagar
    </VBtn>
    <VBtn
      v-if="canMultar && !isCancelled(item)"
      size="small"
      color="warning"
      variant="flat"
      prepend-icon="ri-error-warning-line"
      class="settlement-row-actions__btn"
      @click="emit('multar', item)"
    >
      Multar
    </VBtn>
    <VBtn
      size="small"
      variant="text"
      class="settlement-row-actions__btn"
      @click="emit('detail', item)"
    >
      Ver detalle
    </VBtn>
  </div>
</template>

<style scoped>
.settlement-row-actions__btn {
  min-inline-size: 5.5rem;
}
</style>
