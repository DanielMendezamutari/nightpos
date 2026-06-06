<script setup>
import { orderStatusColor, orderStatusLabel } from '@/composables/useOrderHelpers'

defineProps({
  order: { type: Object, required: true },
})
</script>

<template>
  <VCard class="mb-4">
    <VCardText>
      <div class="d-flex flex-wrap justify-space-between align-start gap-2 mb-3">
        <div>
          <h4 class="text-h4 mb-1">
            {{ order.order_number }}
          </h4>
          <p class="mb-0 text-body-1">
            {{ order.table_label || 'Sin mesa' }}
          </p>
        </div>
        <VChip
          :color="orderStatusColor(order.status)"
          label
        >
          {{ orderStatusLabel(order.status) }}
        </VChip>
      </div>

      <VList density="compact">
        <VListItem v-if="order.notes">
          <VListItemTitle>Notas</VListItemTitle>
          <VListItemSubtitle>{{ order.notes }}</VListItemSubtitle>
        </VListItem>
        <VListItem v-if="order.sent_to_bar_at">
          <VListItemTitle>Enviada a barra</VListItemTitle>
          <VListItemSubtitle>{{ order.sent_to_bar_at }}</VListItemSubtitle>
        </VListItem>
        <VListItem v-if="order.cancelled_at">
          <VListItemTitle>Cancelada</VListItemTitle>
          <VListItemSubtitle>{{ order.cancelled_at }}</VListItemSubtitle>
        </VListItem>
      </VList>
    </VCardText>
  </VCard>
</template>
