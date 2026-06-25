<script setup>
import PrintableTicketShell from '@/components/nightpos/print/PrintableTicketShell.vue'
import { formatCompanionBraceletLine, shouldShowCompanionBraceletLine } from '@/composables/useOrderHelpers'
import { formatPrintTime, resolvePrintLocationLabel } from '@/composables/usePrintTicketFormat'
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
  isReprint: {
    type: Boolean,
    default: false,
  },
  correctionNumber: {
    type: Number,
    default: null,
  },
})

const visibleItems = computed(() =>
  (props.order?.items ?? []).filter(i => i.item_status !== 'CANCELLED'),
)

const headerTitle = computed(() => {
  if (!props.order)
    return 'Comanda'

  if (props.isReprint && props.correctionNumber)
    return `REIMPRESIÓN #${props.order.order_number}-${props.correctionNumber}`

  if (props.isReprint)
    return 'REIMPRESIÓN'

  return `COMANDA #${props.order.order_number}`
})

const headerSubtitle = computed(() => {
  if (props.isReprint && props.correctionNumber)
    return `Corrección #${props.correctionNumber}`

  return props.serviceAreaName || ''
})

const locationLabel = computed(() =>
  resolvePrintLocationLabel(props.order?.table_label || props.serviceAreaName),
)
</script>

<template>
  <PrintableTicketShell
    width="80mm"
    :title="headerTitle"
    :subtitle="headerSubtitle"
    :loading="loading"
  >
    <template v-if="order">
      <div
        v-if="order.table_label || serviceAreaName"
        class="nightpos-print-hero"
      >
        {{ locationLabel }}: {{ order.table_label || serviceAreaName }}
      </div>

      <div
        v-if="waiterName"
        class="nightpos-print-row"
      >
        <span>Garzón</span>
        <span>{{ waiterName }}</span>
      </div>

      <div class="nightpos-print-row">
        <span>Creada</span>
        <span>{{ formatPrintTime(order.opened_at) }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Impresa</span>
        <span>{{ formatPrintTime(order.sent_to_bar_at || order.opened_at) }}</span>
      </div>
      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Estado</span>
        <span>EN BARRA</span>
      </div>

      <hr class="nightpos-print-divider">

      <div
        v-for="item in visibleItems"
        :key="item.id"
        class="nightpos-print-item"
      >
        <div class="nightpos-print-row nightpos-print-row--strong">
          <span>{{ item.quantity }}x</span>
          <span>{{ item.product_name }}</span>
        </div>
        <div
          v-if="saleModeLabel(item.sale_mode)"
          class="nightpos-print-muted"
        >
          {{ saleModeLabel(item.sale_mode) }}
        </div>
        <div
          v-if="shouldShowCompanionBraceletLine(item)"
          class="nightpos-print-muted"
        >
          {{ formatCompanionBraceletLine(item) }}
        </div>
        <div
          v-if="item.requires_allocation"
          class="nightpos-print-muted"
        >
          Manillas: {{ item.allocated_bracelet_units ?? 0 }}/{{ item.required_bracelet_units ?? 0 }}
        </div>
        <div
          v-if="item.allocations?.length"
          class="nightpos-print-muted"
        >
          <span
            v-for="alloc in item.allocations"
            :key="alloc.id"
            class="d-block"
          >{{ alloc.girl_name }} ×{{ alloc.units }}</span>
        </div>
        <div
          v-if="item.notes"
          class="nightpos-print-muted"
        >
          Nota: {{ item.notes }}
        </div>
      </div>

      <template v-if="order.notes">
        <hr class="nightpos-print-divider">
        <div class="nightpos-print-muted">
          Observaciones: {{ order.notes }}
        </div>
      </template>
    </template>

    <template #footer>
      NightPOS — comanda barra
    </template>
  </PrintableTicketShell>
</template>

<style scoped>
.nightpos-print-hero {
  font-size: 15px;
  font-weight: 700;
  text-align: center;
  margin-block-end: 8px;
}

.nightpos-print-item + .nightpos-print-item {
  margin-block-start: 6px;
}
</style>
