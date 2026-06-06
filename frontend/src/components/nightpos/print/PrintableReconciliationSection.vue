<script setup>
import { computed } from 'vue'
import { formatMoney } from '@/composables/useOrderHelpers'

const props = defineProps({
  data: { type: Object, default: null },
})

const STATUS_LABEL = {
  OK: 'OK',
  MISSING_IN_SALE: 'Falta en venta',
  SOLD_WITHOUT_ORDER: 'Vendido sin comanda',
  QUANTITY_MISMATCH: 'Dif. cantidad',
  CANCELLED: 'Cancelado',
  DIRECT_SALE_ONLY: 'Venta directa',
  PENDING_NOT_SOLD: 'Pendiente',
}

const summary = computed(() => props.data?.summary ?? null)
const sold = computed(() => props.data?.sold ?? [])
const comparison = computed(() => props.data?.comparison ?? [])
const differences = computed(() => comparison.value.filter(r => !['OK', 'DIRECT_SALE_ONLY'].includes(r.status)))
const directSales = computed(() => sold.value.filter(r => Number(r.direct_sale_quantity) > 0))

const money = v => `${formatMoney(v)} BOB`
const hasData = computed(() => sold.value.length > 0 || comparison.value.length > 0)
</script>

<template>
  <template v-if="hasData">
    <hr class="nightpos-print-divider">
    <div class="nightpos-print-muted mb-1">
      Productos vendidos
    </div>
    <div
      v-for="row in sold"
      :key="`sold-${row.product_id}`"
      class="nightpos-print-row"
    >
      <span>{{ row.quantity_sold }}× {{ row.product_name }}</span>
      <span>{{ money(row.total_amount) }}</span>
    </div>

    <template v-if="comparison.length">
      <hr class="nightpos-print-divider">
      <div class="nightpos-print-muted mb-1">
        Conciliación
      </div>
      <div
        v-if="summary"
        class="nightpos-print-row"
      >
        <span>OK</span>
        <span>{{ summary.ok_count }} / {{ summary.total_products }}</span>
      </div>
      <div
        v-if="summary && summary.mismatch_count > 0"
        class="nightpos-print-row nightpos-print-row--strong"
      >
        <span>Con diferencias</span>
        <span>{{ summary.mismatch_count }}</span>
      </div>
      <div
        v-for="row in differences"
        :key="`diff-${row.product_id}`"
        class="nightpos-print-row"
      >
        <span>{{ row.product_name }}</span>
        <span>{{ STATUS_LABEL[row.status] ?? row.status }} ({{ row.ordered_quantity }}→{{ row.sold_quantity }})</span>
      </div>
    </template>

    <template v-if="directSales.length">
      <hr class="nightpos-print-divider">
      <div class="nightpos-print-muted mb-1">
        Venta directa
      </div>
      <div
        v-for="row in directSales"
        :key="`direct-${row.product_id}`"
        class="nightpos-print-row"
      >
        <span>{{ row.direct_sale_quantity }}× {{ row.product_name }}</span>
      </div>
    </template>
  </template>
</template>
