<script setup>
import PrintableTicketShell from '@/components/nightpos/print/PrintableTicketShell.vue'
import { formatAllocationSummary, formatCompanionBraceletLine, formatMoney, shouldShowCompanionBraceletLine } from '@/composables/useOrderHelpers'
import { saleModeLabel } from '@/composables/useProductSaleModeLabels'

const props = defineProps({
  precheck: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const order = computed(() => props.precheck?.order ?? null)

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

const visibleItems = computed(() =>
  (order.value?.items ?? []).filter(i => i.item_status !== 'CANCELLED'),
)
</script>

<template>
  <PrintableTicketShell
    width="80mm"
    :title="precheck?.label || 'PRECUENTA — NO PAGADO'"
    :subtitle="order ? order.order_number : ''"
    :loading="loading"
  >
    <template v-if="order">
      <VAlert
        type="warning"
        variant="tonal"
        density="compact"
        class="mb-3 nightpos-print-alert"
      >
        {{ precheck?.label || 'PRECUENTA — NO PAGADO' }}
      </VAlert>

      <div class="nightpos-print-row">
        <span>Mesa / Ambiente</span>
        <span>{{ order.table_label || order.service_area_name || '—' }}</span>
      </div>
      <div
        v-if="order.waiter_name"
        class="nightpos-print-row"
      >
        <span>Garzón</span>
        <span>{{ order.waiter_name }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Fecha/Hora</span>
        <span>{{ formatDateTime(order.opened_at) }}</span>
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
            v-for="item in visibleItems"
            :key="item.id"
          >
            <td>
              {{ item.product_name }}
              <span class="nightpos-print-muted d-block">{{ saleModeLabel(item.sale_mode) }}</span>
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
                v-if="item.requires_allocation && item.allocations?.length"
                class="nightpos-print-muted d-block"
              >
                {{ formatAllocationSummary(item) }}
              </span>
            </td>
            <td class="text-end">
              {{ item.quantity }}
            </td>
            <td class="text-end">
              {{ formatMoney(item.line_total) }}
            </td>
          </tr>
        </tbody>
      </table>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>TOTAL</span>
        <span>{{ formatMoney(order.total) }} {{ order.currency || 'BOB' }}</span>
      </div>
    </template>

    <template #footer>
      NightPOS — precuenta
    </template>
  </PrintableTicketShell>
</template>

<style scoped>
@media print {
  .nightpos-print-alert {
    border: 1px solid #000 !important;
    background: transparent !important;
    color: #000 !important;
  }
}
</style>
