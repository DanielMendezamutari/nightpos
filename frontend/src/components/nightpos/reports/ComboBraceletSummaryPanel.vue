<script setup>
import { formatMoney } from '@/composables/useOrderHelpers'

defineProps({
  summary: {
    type: Object,
    default: null,
  },
  compact: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <VCard
    v-if="summary?.total_bracelet_units"
    variant="outlined"
    :class="compact ? 'mb-3' : 'mb-4'"
  >
    <VCardTitle class="text-subtitle-1">
      Combos y manillas
    </VCardTitle>
    <VCardText>
      <div class="d-flex flex-wrap gap-4 mb-3">
        <div>
          <div class="text-caption text-medium-emphasis">
            Combos vendidos
          </div>
          <div class="text-h6">
            {{ summary.total_combo_quantity ?? 0 }}
          </div>
        </div>
        <div>
          <div class="text-caption text-medium-emphasis">
            Manillas generadas
          </div>
          <div class="text-h6">
            {{ summary.total_bracelet_units ?? 0 }}
          </div>
        </div>
        <div v-if="summary.settlement_status">
          <div class="text-caption text-medium-emphasis">
            Liquidado / pendiente
          </div>
          <div class="text-body-2">
            {{ summary.settlement_status.settled_units ?? 0 }} /
            {{ summary.settlement_status.pending_units ?? 0 }} manillas
          </div>
        </div>
      </div>

      <div
        v-if="summary.products_sold?.length"
        class="mb-3"
      >
        <div class="text-caption text-medium-emphasis mb-1">
          Por producto
        </div>
        <div
          v-for="row in summary.products_sold"
          :key="row.product_id"
          class="text-body-2"
        >
          {{ row.product_name }}: {{ row.combo_quantity }} combo(s) · {{ row.bracelet_units_sold }} manillas · {{ formatMoney(row.total_amount) }}
        </div>
      </div>

      <div v-if="summary.distribution_by_girl?.length">
        <div class="text-caption text-medium-emphasis mb-1">
          Manillas por chica
        </div>
        <div
          v-for="row in summary.distribution_by_girl"
          :key="row.girl_user_id"
          class="text-body-2"
        >
          {{ row.girl_name }}: {{ row.units }} manillas · {{ formatMoney(row.total_amount) }}
        </div>
      </div>
    </VCardText>
  </VCard>
</template>
