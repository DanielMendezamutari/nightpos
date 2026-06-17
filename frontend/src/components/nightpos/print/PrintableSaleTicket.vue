<script setup>
import PrintableTicketShell from '@/components/nightpos/print/PrintableTicketShell.vue'
import { formatCompanionBraceletLine, formatMoney, SALE_MODE_LABELS, shouldShowCompanionBraceletLine } from '@/composables/useOrderHelpers'

const props = defineProps({
  sale: {
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

const PAYMENT_LABELS = { CASH: 'Efectivo', QR: 'QR', CARD: 'Tarjeta', MIXED: 'Mixto' }

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

const saleType = computed(() => (props.sale?.order_id ? 'Comanda' : 'Venta directa'))
</script>

<template>
  <PrintableTicketShell
    width="80mm"
    title="Ticket de venta"
    :subtitle="sale ? sale.sale_number : ''"
    :loading="loading"
  >
    <template v-if="sale">
      <div class="nightpos-print-row">
        <span>Venta</span>
        <span>{{ sale.sale_number }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Tipo</span>
        <span>{{ saleType }}</span>
      </div>
      <div
        v-if="sale.order_id"
        class="nightpos-print-row"
      >
        <span>Comanda</span>
        <span>#{{ sale.order_id }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Fecha/Hora</span>
        <span>{{ formatDateTime(sale.paid_at) }}</span>
      </div>
      <div
        v-if="cashierName"
        class="nightpos-print-row"
      >
        <span>Cajera</span>
        <span>{{ cashierName }}</span>
      </div>
      <div
        v-if="waiterName"
        class="nightpos-print-row"
      >
        <span>Garzón</span>
        <span>{{ waiterName }}</span>
      </div>

      <hr class="nightpos-print-divider">

      <table class="nightpos-print-line-table">
        <thead>
          <tr>
            <th>Producto</th>
            <th class="text-end">
              Cant
            </th>
            <th class="text-end">
              Total
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in (sale.items ?? [])"
            :key="item.id"
          >
            <td>
              {{ item.product_name_snapshot || item.product_name }}
              <span class="nightpos-print-muted d-block">
                {{ SALE_MODE_LABELS[item.sale_mode] || item.sale_mode }}
              </span>
              <span
                v-if="shouldShowCompanionBraceletLine(item)"
                class="nightpos-print-muted d-block"
              >{{ formatCompanionBraceletLine(item) }}</span>
              <span
                v-if="item.requires_allocation"
                class="nightpos-print-muted d-block"
              >
                Manillas: {{ item.allocated_bracelet_units ?? 0 }}/{{ item.required_bracelet_units ?? 0 }}
              </span>
              <span
                v-if="item.allocations?.length"
                class="nightpos-print-muted d-block"
              >
                <span
                  v-for="alloc in item.allocations"
                  :key="alloc.id"
                  class="d-block"
                >{{ alloc.girl_name }} ×{{ alloc.units }} — {{ formatMoney(alloc.total_amount, sale.currency) }}</span>
              </span>
            </td>
            <td class="text-end">
              {{ item.quantity }}
            </td>
            <td class="text-end">
              {{ formatMoney(item.line_total, sale.currency) }}
            </td>
          </tr>
        </tbody>
      </table>

      <hr class="nightpos-print-divider">

      <template v-if="sale.payments?.length">
        <div class="nightpos-print-muted mb-1">
          Pago{{ sale.payment_mode === 'MIXED' ? ' mixto' : '' }}:
        </div>
        <div
          v-for="(payment, idx) in sale.payments"
          :key="idx"
          class="nightpos-print-row"
        >
          <span>{{ PAYMENT_LABELS[payment.payment_method] || payment.payment_method }}</span>
          <span>{{ formatMoney(payment.amount, sale.currency) }}</span>
        </div>
      </template>
      <div
        v-else
        class="nightpos-print-row"
      >
        <span>Método de pago</span>
        <span>{{ PAYMENT_LABELS[sale.payment_mode] || sale.payment_mode }}</span>
      </div>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>TOTAL</span>
        <span>{{ formatMoney(sale.total, sale.currency) }} {{ sale.currency || 'BOB' }}</span>
      </div>
    </template>

    <template #footer>
      NightPOS — ticket de venta
    </template>
  </PrintableTicketShell>
</template>
