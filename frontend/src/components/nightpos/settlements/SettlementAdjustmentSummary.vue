<script setup>
import {
  adjustmentTypeLabel,
  formatBob,
  formatSignedBob,
} from '@/constants/settlements'

const props = defineProps({
  grossAmount: {
    type: [String, Number],
    default: null,
  },
  netAmount: {
    type: [String, Number],
    default: null,
  },
  adjustments: {
    type: Array,
    default: () => [],
  },
  title: {
    type: String,
    default: 'Resumen de liquidación',
  },
  showNetHighlight: {
    type: Boolean,
    default: true,
  },
})

const gross = computed(() => props.grossAmount ?? '0.00')
const net = computed(() => props.netAmount ?? props.grossAmount ?? '0.00')

function adjustmentLabel(adjustment) {
  if (adjustment.adjustment_type === 'MANUAL_FINE' || adjustment.type === 'MANUAL_FINE')
    return adjustment.reason || adjustment.notes || adjustmentTypeLabel('MANUAL_FINE')

  if (adjustment.adjustment_type === 'MANUAL_DISCOUNT' || adjustment.type === 'MANUAL_DISCOUNT')
    return adjustment.reason || adjustment.notes || adjustmentTypeLabel('MANUAL_DISCOUNT')

  return adjustmentTypeLabel(adjustment.adjustment_type || adjustment.type)
}

function adjustmentAmount(adjustment) {
  return adjustment.amount
}
</script>

<template>
  <VCard variant="outlined">
    <VCardTitle class="text-subtitle-1">
      {{ title }}
    </VCardTitle>
    <VCardText>
      <div class="d-flex justify-space-between mb-2">
        <span>Bruto generado</span>
        <strong>{{ formatBob(gross) }}</strong>
      </div>

      <div
        v-for="(adjustment, index) in adjustments"
        :key="adjustment.id || adjustment.fine_id || `${adjustment.type}-${index}`"
        class="d-flex justify-space-between mb-2 text-medium-emphasis"
      >
        <span>{{ adjustmentLabel(adjustment) }}</span>
        <span>{{ formatSignedBob(adjustmentAmount(adjustment)) }}</span>
      </div>

      <VDivider class="my-3" />

      <div
        class="d-flex justify-space-between"
        :class="{ 'text-success': showNetHighlight }"
      >
        <span class="font-weight-medium">Neto{{ showNetHighlight ? ' a pagar' : '' }}</span>
        <strong class="text-h6">{{ formatBob(net) }}</strong>
      </div>
    </VCardText>
  </VCard>
</template>
