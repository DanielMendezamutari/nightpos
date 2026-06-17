<script setup>
import PrintableTicketShell from '@/components/nightpos/print/PrintableTicketShell.vue'
import { formatCompanionBraceletLine, formatMoney, shouldShowCompanionBraceletLine } from '@/composables/useOrderHelpers'
import { saleModeLabel } from '@/composables/useProductSaleModeLabels'

const props = defineProps({
  order: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  waiterName: {
    type: String,
    default: '',
  },
  serviceAreaName: {
    type: String,
    default: '',
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

const visibleItems = computed(() =>
  (props.order?.items ?? []).filter(i => i.item_status !== 'CANCELLED'),
)
</script>

<template>
  <PrintableTicketShell
    width="80mm"
    title="Comanda a barra"
    :subtitle="order ? order.order_number : ''"
    :loading="loading"
  >
    <template v-if="order">
      <div class="nightpos-print-row">
        <span>Comanda</span>
        <span>{{ order.order_number }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Mesa / Ambiente</span>
        <span>{{ order.table_label || serviceAreaName || '—' }}</span>
      </div>
      <div
        v-if="waiterName"
        class="nightpos-print-row"
      >
        <span>Garzón</span>
        <span>{{ waiterName }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Fecha/Hora</span>
        <span>{{ formatDateTime(order.sent_to_bar_at || order.opened_at) }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Estado</span>
        <span>{{ order.status }}</span>
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
                v-if="item.allocations?.length"
                class="nightpos-print-muted d-block"
              >
                <span
                  v-for="alloc in item.allocations"
                  :key="alloc.id"
                  class="d-block"
                >{{ alloc.girl_name }} ×{{ alloc.units }}</span>
              </span>
              <span
                v-if="item.notes"
                class="nightpos-print-muted d-block"
              >Nota: {{ item.notes }}</span>
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

      <template v-if="order.notes">
        <hr class="nightpos-print-divider">
        <div class="nightpos-print-muted">
          Notas: {{ order.notes }}
        </div>
      </template>
    </template>

    <template #footer>
      NightPOS — comanda barra
    </template>
  </PrintableTicketShell>
</template>
