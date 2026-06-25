<script setup>
import PrintableTicketShell from '@/components/nightpos/print/PrintableTicketShell.vue'
import { formatAllocationSummary, formatCompanionBraceletLine, formatMoney, shouldShowCompanionBraceletLine } from '@/composables/useOrderHelpers'
import { formatPrintTime, resolvePrintLocationLabel } from '@/composables/usePrintTicketFormat'
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

const visibleItems = computed(() =>
  (order.value?.items ?? []).filter(i => i.item_status !== 'CANCELLED'),
)

const locationLabel = computed(() =>
  resolvePrintLocationLabel(order.value?.table_label || order.value?.service_area_name),
)
</script>

<template>
  <PrintableTicketShell
    width="80mm"
    :title="order ? `PRECUENTA #${order.order_number}` : 'PRECUENTA'"
    subtitle="Pendiente de cobro"
    :loading="loading"
  >
    <template v-if="order">
      <div class="nightpos-print-row nightpos-print-row--strong">
        <span>Estado</span>
        <span>PENDIENTE DE COBRO</span>
      </div>

      <div class="nightpos-print-hero">
        {{ locationLabel }}: {{ order.table_label || order.service_area_name || '—' }}
      </div>

      <div
        v-if="order.waiter_name"
        class="nightpos-print-row"
      >
        <span>Garzón</span>
        <span>{{ order.waiter_name }}</span>
      </div>

      <div class="nightpos-print-row">
        <span>Creada</span>
        <span>{{ formatPrintTime(order.opened_at) }}</span>
      </div>
      <div class="nightpos-print-row">
        <span>Impresa</span>
        <span>{{ formatPrintTime(order.opened_at) }}</span>
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
          Manilla: {{ item.girl_name || formatCompanionBraceletLine(item) }}
        </div>
        <div
          v-if="item.requires_allocation"
          class="nightpos-print-muted"
        >
          Manillas: {{ item.allocated_bracelet_units ?? 0 }}/{{ item.required_bracelet_units ?? 0 }}
        </div>
        <div
          v-if="item.requires_allocation && item.allocations?.length"
          class="nightpos-print-muted"
        >
          {{ formatAllocationSummary(item) }}
        </div>
      </div>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-total">
        TOTAL
      </div>
      <div class="nightpos-print-total nightpos-print-total--amount">
        {{ formatMoney(order.total) }} {{ order.currency || 'BOB' }}
      </div>

      <hr class="nightpos-print-divider">

      <div class="nightpos-print-muted text-center">
        Gracias por su preferencia.
      </div>
      <div class="nightpos-print-muted text-center">
        No tiene validez fiscal.
      </div>
    </template>

    <template #footer>
      NightPOS — precuenta
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

.nightpos-print-item + .nightpos-print-item {
  margin-block-start: 6px;
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
