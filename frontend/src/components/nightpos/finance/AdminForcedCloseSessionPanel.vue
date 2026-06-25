<script setup>
import { forceCloseReasonLabel } from '@/api/adminCashSessions'
import { formatMoney } from '@/composables/useOrderHelpers'

defineProps({
  session: { type: Object, default: null },
  summary: { type: Object, default: null },
  compact: { type: Boolean, default: false },
})

const formatDate = value => value ? new Date(value).toLocaleString('es-BO') : '—'

const blockerMessages = session => {
  const blockers = session?.close_blockers_snapshot?.blockers ?? []

  return blockers.map(b => b.message).filter(Boolean)
}
</script>

<template>
  <VCard
    v-if="session?.is_forced_close"
    :class="compact ? 'mb-4' : 'mb-4'"
    variant="tonal"
    color="warning"
  >
    <VCardTitle class="d-flex align-center gap-2 flex-wrap">
      <VChip
        color="warning"
        size="small"
        label
      >
        Cierre administrativo
      </VChip>
      <VChip
        v-if="session.closed_with_observations"
        color="error"
        size="small"
        variant="outlined"
        label
      >
        Cerrada con observaciones
      </VChip>
    </VCardTitle>
    <VCardText>
      <VRow>
        <VCol
          cols="12"
          md="6"
        >
          <div><strong>Cajera original:</strong> {{ session.cashier?.name || '—' }}</div>
          <div><strong>Cerró (admin):</strong> {{ session.forced_closed_by?.name || '—' }}</div>
          <div><strong>Motivo:</strong> {{ forceCloseReasonLabel(session.forced_close_reason) }}</div>
          <div><strong>Fecha cierre admin:</strong> {{ formatDate(session.forced_closed_at) }}</div>
        </VCol>
        <VCol
          cols="12"
          md="6"
        >
          <div><strong>Notas:</strong> {{ session.forced_close_notes || '—' }}</div>
          <div class="mt-2">
            <strong>Arqueo:</strong> Sin arqueo — cierre administrativo.
          </div>
          <div v-if="session.financial_summary_snapshot?.expected_cash">
            <strong>Efectivo esperado (congelado):</strong>
            {{ formatMoney(session.financial_summary_snapshot.expected_cash) }} BOB
          </div>
        </VCol>
      </VRow>

      <div
        v-if="blockerMessages(session).length"
        class="mt-4"
      >
        <div class="text-subtitle-2 mb-2">
          Pendientes al cierre:
        </div>
        <VAlert
          v-for="(message, index) in blockerMessages(session)"
          :key="index"
          type="error"
          variant="tonal"
          density="compact"
          class="mb-2"
        >
          {{ message }}
        </VAlert>
      </div>
    </VCardText>
  </VCard>
</template>
