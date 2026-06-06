<script setup>
import PrintableReconciliationSection from '@/components/nightpos/print/PrintableReconciliationSection.vue'
import PrintableTicketShell from '@/components/nightpos/print/PrintableTicketShell.vue'
import { formatMoney } from '@/composables/useOrderHelpers'

const props = defineProps({
  data: {
    type: Object,
    default: null,
  },
  reconciliation: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const formatDateTime = value => {
  if (!value)
    return '—'
  try {
    return new Date(value).toLocaleString('es-BO', { dateStyle: 'short', timeStyle: 'short' })
  }
  catch {
    return value
  }
}

const hasValue = v => v != null && v !== ''
const money = v => `${formatMoney(v)} BOB`

const diff = computed(() => props.data?.difference)
</script>

<template>
  <PrintableTicketShell
    width="80mm"
    title="Cierre de caja"
    :subtitle="data ? `Sesión #${data.id}` : ''"
    :loading="loading"
  >
    <template v-if="data">
      <div
        v-if="data.cashierName"
        class="nightpos-print-row"
      >
        <span>Cajera</span>
        <span>{{ data.cashierName }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Apertura</span>
        <span>{{ formatDateTime(data.openedAt) }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Cierre</span>
        <span>{{ data.closedAt ? formatDateTime(data.closedAt) : 'Abierta' }}</span>
      </div>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-row">
        <span>Monto inicial</span>
        <span>{{ money(data.openingAmount) }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Ventas efectivo</span>
        <span>{{ money(data.salesCash) }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Ventas QR</span>
        <span>{{ money(data.salesQr) }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Ventas tarjeta</span>
        <span>{{ money(data.salesCard) }}</span>
      </div>
      <div
        v-if="hasValue(data.income)"
        class="nightpos-print-row"
      >
        <span>Ingresos manuales</span>
        <span>{{ money(data.income) }}</span>
      </div>
      <div
        v-if="hasValue(data.expense)"
        class="nightpos-print-row"
      >
        <span>Egresos</span>
        <span>{{ money(data.expense) }}</span>
      </div>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Esperado en caja</span>
        <span>{{ money(data.expected) }}</span>
      </div>
      <div
        v-if="hasValue(data.counted)"
        class="nightpos-print-row"
      >
        <span>Efectivo contado</span>
        <span>{{ money(data.counted) }}</span>
      </div>
      <div
        v-if="hasValue(diff)"
        class="nightpos-print-row nightpos-print-row--strong"
      >
        <span>Diferencia</span>
        <span>{{ money(diff) }}</span>
      </div>

      <PrintableReconciliationSection :data="reconciliation" />
    </template>

    <template #footer>
      NightPOS — arqueo de caja
    </template>
  </PrintableTicketShell>
</template>
