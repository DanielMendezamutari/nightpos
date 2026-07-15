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
  canAssignManual: {
    type: Boolean,
    default: false,
  },
  payDisabled: {
    type: Boolean,
    default: false,
  },
  payDisabledReason: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['pay', 'multar', 'detail', 'assign-manual'])

const isPending = item => item.status === 'PENDING'
const isCancelled = item => item.status === 'CANCELLED'
const canOpenDetail = item => Boolean(item?.id) || Array.isArray(item?.details)
const canOperationalEdit = item => Boolean(item?.id) && !['PROVISIONAL', 'INFORMATIVE', 'MIXED'].includes(item?.status)
</script>

<template>
  <div class="settlement-row-actions d-flex gap-2 flex-wrap align-center">
    <VBtn
      v-if="canPay && isPending(item) && canOperationalEdit(item)"
      size="small"
      color="success"
      variant="tonal"
      prepend-icon="ri-check-line"
      class="settlement-row-actions__btn"
      :disabled="payDisabled"
      @click="emit('pay', item)"
    >
      Pagar
    </VBtn>
    <VBtn
      v-if="canAssignManual && isPending(item) && canOperationalEdit(item)"
      size="small"
      color="info"
      variant="flat"
      prepend-icon="ri-money-dollar-circle-line"
      class="settlement-row-actions__btn"
      @click="emit('assign-manual', item)"
    >
      Asignar monto
    </VBtn>
    <VBtn
      v-if="canMultar && !isCancelled(item) && canOperationalEdit(item)"
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
      :disabled="!canOpenDetail(item)"
      @click="emit('detail', item)"
    >
      Ver detalle
    </VBtn>
    <small
      v-if="payDisabled && payDisabledReason"
      class="text-warning"
    >
      {{ payDisabledReason }}
    </small>
  </div>
</template>

<style scoped>
.settlement-row-actions__btn {
  min-inline-size: 5.5rem;
}
</style>
