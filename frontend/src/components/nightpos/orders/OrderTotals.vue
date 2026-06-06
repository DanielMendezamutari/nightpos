<script setup>
import { formatMoney } from '@/composables/useOrderHelpers'

defineProps({
  order: { type: Object, required: true },
})
</script>

<template>
  <VCard
    v-if="order.items?.length"
    variant="tonal"
    color="primary"
    class="mb-4"
  >
    <VCardText class="d-flex justify-space-between align-center">
      <span class="text-h6">Total comanda</span>
      <span class="text-h4 font-weight-bold">{{ formatMoney(order.total, order.currency) }}</span>
    </VCardText>
  </VCard>
</template>
