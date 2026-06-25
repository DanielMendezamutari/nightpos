<script setup>
import PrintableTicketShell from '@/components/nightpos/print/PrintableTicketShell.vue'
import { paymentMethodLabel } from '@/constants/paymentMethods'
import {
  adjustmentTypeLabel,
  formatBob,
  formatSignedBob,
  settlementTypeLabel,
} from '@/constants/settlements'
import { PRINT_TICKET_FOOTER } from '@/constants/printTicket'
import { formatPrintTime } from '@/composables/usePrintTicketFormat'

const props = defineProps({
  settlement: {
    type: Object,
    default: null,
  },
  adjustments: {
    type: Array,
    default: () => [],
  },
  branchName: {
    type: String,
    default: '',
  },
  loading: {
    type: Boolean,
    default: false,
  },
  isReprint: {
    type: Boolean,
    default: false,
  },
  reprintNumber: {
    type: Number,
    default: null,
  },
  reprintedAt: {
    type: String,
    default: null,
  },
  reprintedByName: {
    type: String,
    default: null,
  },
})

const cleaningAdjustment = computed(() =>
  props.adjustments.find(row => row.adjustment_type === 'CLEANING_DEDUCTION'))

const manualDiscountAdjustment = computed(() =>
  props.adjustments.find(row => row.adjustment_type === 'MANUAL_DISCOUNT'))

const fineAdjustments = computed(() =>
  props.adjustments.filter(row => row.adjustment_type === 'MANUAL_FINE'))

const isWaiterSettlement = computed(() =>
  props.settlement?.settlement_type === 'WAITER' || props.settlement?.staff_role === 'WAITER')

const waiterSalesTotal = computed(() =>
  props.settlement?.waiter_sales_total ?? props.settlement?.waiter_snapshot?.sales_total ?? null)

const waiterCommissionPercent = computed(() =>
  props.settlement?.commission_percent ?? props.settlement?.waiter_snapshot?.commission_percent ?? null)

const waiterCommissionAmount = computed(() =>
  props.settlement?.commission_amount ?? props.settlement?.waiter_snapshot?.commission_amount ?? props.settlement?.gross_amount ?? null)

const finesTotal = computed(() =>
  fineAdjustments.value.reduce((sum, row) => sum + Number(row.amount ?? 0), 0))

const staffRoleLabel = role => ({
  GIRL: 'Chica',
  WAITER: 'Garzón',
  CLEANING: 'Limpieza',
}[role] || role)
</script>

<template>
  <PrintableTicketShell
    width="80mm"
    title="LIQUIDACIÓN PAGADA"
    :subtitle="branchName || 'Comprobante operativo'"
    :loading="loading"
  >
    <template v-if="settlement">
      <div
        v-if="isReprint"
        class="nightpos-print-banner text-center mb-2"
      >
        <div class="font-weight-bold">
          REIMPRESIÓN
        </div>
        <div v-if="reprintNumber">
          N° {{ reprintNumber }}
        </div>
        <div v-if="reprintedAt">
          {{ formatPrintTime(reprintedAt) }}
        </div>
        <div v-if="reprintedByName">
          {{ reprintedByName }}
        </div>
      </div>

      <div class="nightpos-print-row">
        <span>Persona</span>
        <span>{{ settlement.staff_name || '—' }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Rol</span>
        <span>{{ staffRoleLabel(settlement.staff_role) || '—' }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Tipo</span>
        <span>{{ settlementTypeLabel(settlement.settlement_type) }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Caja</span>
        <span>#{{ settlement.cash_session_id || '—' }}</span>
      </div>
      <div
        v-if="settlement.cut_label"
        class="nightpos-print-row"
      >
        <span>Corte</span>
        <span>{{ settlement.cut_label }}</span>
      </div>
      <div
        v-if="settlement.ticket_number"
        class="nightpos-print-row nightpos-print-row--strong"
      >
        <span>Ticket</span>
        <span>{{ settlement.ticket_number }}</span>
      </div>

      <hr class="nightpos-print-divider">

      <template v-if="isWaiterSettlement && waiterSalesTotal">
        <div class="text-center font-weight-bold mb-2">
          VENTA GARZÓN
        </div>
        <div class="nightpos-print-row">
          <span>Venta total</span>
          <span>{{ formatBob(waiterSalesTotal) }}</span>
        </div>
        <div
          v-if="waiterCommissionPercent"
          class="nightpos-print-row"
        >
          <span>Porcentaje</span>
          <span>{{ waiterCommissionPercent }}%</span>
        </div>
        <div class="nightpos-print-row nightpos-print-row--strong">
          <span>Comisión</span>
          <span>{{ formatBob(waiterCommissionAmount) }}</span>
        </div>
      </template>
      <template v-else>
        <div class="nightpos-print-row nightpos-print-row--strong">
          <span>BRUTO</span>
          <span>{{ formatBob(settlement.gross_amount ?? settlement.total_amount) }}</span>
        </div>
      </template>

      <div
        v-if="cleaningAdjustment"
        class="nightpos-print-row"
      >
        <span>{{ adjustmentTypeLabel('CLEANING_DEDUCTION') }}</span>
        <span>{{ formatSignedBob(cleaningAdjustment.amount) }}</span>
      </div>

      <div
        v-if="manualDiscountAdjustment"
        class="nightpos-print-row"
      >
        <span>{{ manualDiscountAdjustment.reason || adjustmentTypeLabel('MANUAL_DISCOUNT') }}</span>
        <span>{{ formatSignedBob(manualDiscountAdjustment.amount) }}</span>
      </div>

      <div
        v-if="isWaiterSettlement && finesTotal"
        class="nightpos-print-row"
      >
        <span>Multas</span>
        <span>{{ formatSignedBob(finesTotal) }}</span>
      </div>
      <template v-else>
        <div
          v-for="fine in fineAdjustments"
          :key="fine.id"
          class="nightpos-print-row"
        >
          <span>{{ fine.reason || fine.notes || adjustmentTypeLabel('MANUAL_FINE') }}</span>
          <span>{{ formatSignedBob(fine.amount) }}</span>
        </div>
      </template>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>NETO PAGADO</span>
        <span>{{ formatBob(settlement.net_amount ?? settlement.total_amount) }}</span>
      </div>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-row">
        <span>Método</span>
        <span>{{ paymentMethodLabel(settlement.payment_method) }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Pagado por</span>
        <span>{{ settlement.paid_by_name || '—' }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Fecha</span>
        <span>{{ formatPrintTime(settlement.paid_at) }}</span>
      </div>
      <div
        v-if="settlement.cash_movement_id"
        class="nightpos-print-row"
      >
        <span>Mov. caja</span>
        <span>#{{ settlement.cash_movement_id }}</span>
      </div>

      <div
        v-if="settlement.notes"
        class="nightpos-print-muted mt-2"
      >
        Obs: {{ settlement.notes }}
      </div>
    </template>

    <template #footer>
      {{ PRINT_TICKET_FOOTER }}
    </template>
  </PrintableTicketShell>
</template>
