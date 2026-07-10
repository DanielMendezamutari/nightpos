<script setup>
import { formatBob, formatSignedBob } from '@/constants/settlements'

const props = defineProps({
  grossAmount: {
    type: [String, Number],
    default: '0.00',
  },
  netAmount: {
    type: [String, Number],
    default: '0.00',
  },
  adjustments: {
    type: Array,
    default: () => [],
  },
  title: {
    type: String,
    default: 'Resumen de pago',
  },
  grossLabel: {
    type: String,
    default: 'Pago bruto',
  },
  totalLabel: {
    type: String,
    default: 'Total a pagar',
  },
})

const cleaningAmount = computed(() => {
  const row = props.adjustments.find(item => (item.adjustment_type || item.type) === 'CLEANING_DEDUCTION')

  return row ? Math.abs(Number(row.amount ?? 0)) : 0
})

const otherAdjustments = computed(() => {
  return props.adjustments
    .filter(item => (item.adjustment_type || item.type) !== 'CLEANING_DEDUCTION')
    .reduce((sum, item) => sum + Number(item.amount ?? 0), 0)
})

const cleaningSignedAmount = computed(() => cleaningAmount.value > 0 ? -1 * cleaningAmount.value : 0)
</script>

<template>
  <VCard variant="outlined">
    <VCardTitle class="text-subtitle-1">
      {{ title }}
    </VCardTitle>
    <VCardText>
      <div class="d-flex justify-space-between mb-2">
        <span>{{ grossLabel }}</span>
        <strong>{{ formatBob(grossAmount) }}</strong>
      </div>

      <div class="d-flex justify-space-between mb-2">
        <span>Limpieza</span>
        <span>{{ formatSignedBob(cleaningSignedAmount) }}</span>
      </div>

      <div class="d-flex justify-space-between mb-2">
        <span>Otros ajustes</span>
        <span>{{ formatSignedBob(otherAdjustments) }}</span>
      </div>

      <VDivider class="my-3" />

      <div class="d-flex justify-space-between text-success">
        <span class="font-weight-medium">{{ totalLabel }}</span>
        <strong class="text-h6">{{ formatBob(netAmount) }}</strong>
      </div>
    </VCardText>
  </VCard>
</template>