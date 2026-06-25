<script setup>
import PrintableTicketShell from '@/components/nightpos/print/PrintableTicketShell.vue'
import { paymentMethodLabel } from '@/constants/paymentMethods'
import { formatMoney } from '@/composables/useOrderHelpers'
import { formatPrintTime } from '@/composables/usePrintTicketFormat'

defineProps({
  movement: {
    type: Object,
    default: null,
  },
  cashierName: {
    type: String,
    default: '',
  },
  branchName: {
    type: String,
    default: '',
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const movementTypeLabel = type => {
  if (type === 'INCOME')
    return 'Ingreso'
  if (type === 'EXPENSE')
    return 'Egreso'

  return type || '—'
}
</script>

<template>
  <PrintableTicketShell
    width="80mm"
    title="MOVIMIENTO DE CAJA"
    :subtitle="branchName || 'Comprobante operativo'"
    :loading="loading"
  >
    <template v-if="movement">
      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Tipo</span>
        <span>{{ movementTypeLabel(movement.movement_type) }}</span>
      </div>

      <div class="nightpos-print-row">
        <span>Método</span>
        <span>{{ paymentMethodLabel(movement.payment_method) }}</span>
      </div>

      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Monto</span>
        <span>{{ formatMoney(movement.amount) }} BOB</span>
      </div>

      <div class="nightpos-print-row">
        <span>Motivo</span>
        <span>{{ movement.reason_name || movement.description || '—' }}</span>
      </div>

      <div
        v-if="movement.notes"
        class="nightpos-print-row"
      >
        <span>Detalle</span>
        <span>{{ movement.notes }}</span>
      </div>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-row">
        <span>Cajera</span>
        <span>{{ cashierName || '—' }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Caja</span>
        <span>#{{ movement.cash_session_id || '—' }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Fecha</span>
        <span>{{ formatPrintTime(movement.created_at) }}</span>
      </div>
      <div
        v-if="branchName"
        class="nightpos-print-row"
      >
        <span>Sucursal</span>
        <span>{{ branchName }}</span>
      </div>

      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Estado</span>
        <span>REGISTRADO</span>
      </div>

      <div class="nightpos-print-muted text-center mt-2">
        Comprobante operativo — no fiscal.
      </div>
    </template>

    <template #footer>
      NightPOS — movimiento de caja
    </template>
  </PrintableTicketShell>
</template>
