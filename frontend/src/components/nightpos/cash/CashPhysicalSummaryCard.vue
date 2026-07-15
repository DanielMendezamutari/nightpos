<script setup>
import { formatMoney } from '@/composables/useOrderHelpers'

const props = defineProps({
  summary: {
    type: Object,
    required: true,
  },
  sessionStatus: {
    type: String,
    default: 'OPEN',
  },
  openedAt: {
    type: String,
    default: null,
  },
})

const fmtBob = value => formatMoney(value ?? 0, 'BOB')

const openDuration = computed(() => {
  if (!props.openedAt)
    return '—'

  const opened = new Date(props.openedAt)
  if (Number.isNaN(opened.getTime()))
    return '—'

  const diffMinutes = Math.max(0, Math.floor((Date.now() - opened.getTime()) / 60000))
  const hours = Math.floor(diffMinutes / 60)
  const minutes = diffMinutes % 60

  return `${hours}h ${minutes}m`
})

const differenceColor = computed(() => {
  const diff = Number(props.summary.cash_difference ?? 0)
  if (diff === 0)
    return 'success'

  return diff > 0 ? 'info' : 'error'
})
</script>

<template>
  <VCard>
    <VCardTitle class="d-flex flex-wrap align-center gap-2">
      Caja física
      <VChip
        :color="sessionStatus === 'OPEN' ? 'success' : 'secondary'"
        size="small"
        label
      >
        {{ sessionStatus === 'OPEN' ? 'Abierta' : 'Cerrada' }}
      </VChip>
    </VCardTitle>

    <VCardText>
      <VRow>
        <VCol cols="12" md="6">
          <div class="text-caption text-medium-emphasis">Fondo inicial</div>
          <div class="text-h6">{{ fmtBob(summary.opening_cash) }}</div>
        </VCol>
        <VCol cols="12" md="6">
          <div class="text-caption text-medium-emphasis">Tiempo abierta</div>
          <div class="text-h6">{{ openDuration }}</div>
        </VCol>
        <VCol cols="12" md="6">
          <div class="text-caption text-medium-emphasis">Ventas cobradas en efectivo</div>
          <div class="text-h6">{{ fmtBob(summary.cash_income_sales) }}</div>
        </VCol>
        <VCol cols="12" md="6">
          <div class="text-caption text-medium-emphasis">Ingresos manuales en efectivo</div>
          <div class="text-h6">{{ fmtBob(summary.cash_income_manual) }}</div>
        </VCol>
        <VCol cols="12" md="6">
          <div class="text-caption text-medium-emphasis">Liquidaciones pagadas en efectivo</div>
          <div class="text-h6">{{ fmtBob(summary.settlement_payments ?? 0) }}</div>
        </VCol>
        <VCol cols="12" md="6">
          <div class="text-caption text-medium-emphasis">Egresos en efectivo</div>
          <div class="text-h6">{{ fmtBob(summary.cash_expense_total) }}</div>
        </VCol>
        <VCol cols="12" md="6">
          <div class="text-caption text-medium-emphasis">Efectivo esperado</div>
          <div class="text-h5 font-weight-bold">{{ fmtBob(summary.expected_cash) }}</div>
        </VCol>
      </VRow>

      <VDivider class="my-4" />

      <VRow>
        <VCol cols="12" md="6">
          <div class="text-caption text-medium-emphasis">Efectivo contado</div>
          <div class="text-h6">{{ summary.counted_cash == null ? '—' : fmtBob(summary.counted_cash) }}</div>
        </VCol>
        <VCol cols="12" md="6">
          <div class="text-caption text-medium-emphasis">Diferencia</div>
          <VChip
            size="small"
            :color="differenceColor"
            variant="tonal"
          >
            {{ summary.cash_difference == null ? '—' : fmtBob(summary.cash_difference) }}
          </VChip>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
