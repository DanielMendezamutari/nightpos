<script setup>
import PrintableTicketShell from '@/components/nightpos/print/PrintableTicketShell.vue'
import { formatMoney } from '@/composables/useOrderHelpers'
import { formatPrintTime, paymentModeLabel, resolvePrintLocationLabel } from '@/composables/usePrintTicketFormat'

const props = defineProps({
  sale: {
    type: Object,
    default: null,
  },
  order: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  cashierName: {
    type: String,
    default: '',
  },
  waiterName: {
    type: String,
    default: '',
  },
})

const operationNumber = computed(() =>
  props.order?.order_number || props.sale?.sale_number || '—',
)

const locationLabel = computed(() =>
  resolvePrintLocationLabel(props.order?.table_label || props.sale?.table_label),
)

const showPaymentBreakdown = computed(() =>
  props.sale?.payment_mode === 'MIXED' || (props.sale?.payments?.length ?? 0) > 1,
)
</script>

<template>
  <PrintableTicketShell
    width="80mm"
    :title="`PAGO #${operationNumber}`"
    subtitle="Ticket de cobro"
    :loading="loading"
  >
    <template v-if="sale">
      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Estado</span>
        <span>PAGADO</span>
      </div>
      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Método</span>
        <span>{{ paymentModeLabel(sale.payment_mode) }}</span>
      </div>

      <div
        v-if="order?.table_label || sale.table_label"
        class="nightpos-print-hero"
      >
        {{ locationLabel }}: {{ order?.table_label || sale.table_label }}
      </div>

      <div
        v-if="waiterName"
        class="nightpos-print-row"
      >
        <span>Garzón</span>
        <span>{{ waiterName }}</span>
      </div>

      <div
        v-if="cashierName"
        class="nightpos-print-row"
      >
        <span>Cajera</span>
        <span>{{ cashierName }}</span>
      </div>

      <div class="nightpos-print-row">
        <span>Cobro</span>
        <span>{{ formatPrintTime(sale.paid_at) }}</span>
      </div>

      <template v-if="showPaymentBreakdown">
        <hr class="nightpos-print-divider">
        <div
          v-for="(payment, idx) in (sale.payments ?? [])"
          :key="idx"
          class="nightpos-print-row"
        >
          <span>{{ paymentModeLabel(payment.payment_method) }}</span>
          <span>{{ formatMoney(payment.amount, sale.currency) }}</span>
        </div>
      </template>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-total">
        TOTAL
      </div>
      <div class="nightpos-print-total nightpos-print-total--amount">
        {{ formatMoney(sale.total, sale.currency) }} {{ sale.currency || 'BOB' }}
      </div>
    </template>

    <template #footer>
      NightPOS — ticket de cobro
    </template>
  </PrintableTicketShell>
</template>

<style scoped>
.nightpos-print-hero {
  font-size: 14px;
  font-weight: 700;
  text-align: center;
  margin-block: 8px;
}

.nightpos-print-total {
  text-align: center;
  font-weight: 700;
  font-size: 13px;
}

.nightpos-print-total--amount {
  font-size: 18px;
  margin-block-start: 2px;
}
</style>
