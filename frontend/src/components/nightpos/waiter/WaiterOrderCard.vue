<script setup>
import { formatMoney } from '@/composables/useOrderHelpers'
import { waiterOrderStatus } from '@/composables/useWaiterOrderStatus'

const props = defineProps({
  order: { type: Object, required: true },
})

const status = computed(() => waiterOrderStatus(props.order.status))
</script>

<template>
  <VCard
    class="waiter-order-card mb-3"
    variant="elevated"
  >
    <VCardText class="pa-4">
      <div class="d-flex align-center justify-space-between mb-2">
        <div class="text-h6 font-weight-bold">
          {{ order.table_label || 'Sin referencia' }}
        </div>
        <VChip
          size="small"
          :color="status.color"
          variant="tonal"
        >
          {{ status.label }}
        </VChip>
      </div>
      <div class="text-body-2 text-medium-emphasis mb-2">
        {{ order.items_count ?? order.items?.length ?? 0 }} productos
      </div>
      <div class="text-h5 font-weight-bold mb-4">
        {{ formatMoney(order.total, order.currency) }}
      </div>
      <slot />
    </VCardText>
  </VCard>
</template>

<style scoped>
.waiter-order-card {
  border-radius: 12px;
}
</style>
