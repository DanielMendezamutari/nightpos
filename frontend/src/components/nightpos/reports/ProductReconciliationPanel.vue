<script setup>
import { computed } from 'vue'

const props = defineProps({
  data: { type: Object, default: null },
  loading: { type: Boolean, default: false },
  title: { type: String, default: 'Productos vendidos vs. comandados' },
  showSold: { type: Boolean, default: true },
})

const STATUS_META = {
  OK: { label: 'OK', color: 'success' },
  MISSING_IN_SALE: { label: 'Falta en venta', color: 'error' },
  SOLD_WITHOUT_ORDER: { label: 'Vendido sin comanda', color: 'error' },
  QUANTITY_MISMATCH: { label: 'Diferencia cantidad', color: 'warning' },
  CANCELLED: { label: 'Cancelado', color: 'secondary' },
  DIRECT_SALE_ONLY: { label: 'Venta directa', color: 'info' },
  PENDING_NOT_SOLD: { label: 'Pendiente (no cobrado)', color: 'warning' },
}

const statusMeta = status => STATUS_META[status] ?? { label: status, color: 'default' }

const summary = computed(() => props.data?.summary ?? null)
const comparison = computed(() => props.data?.comparison ?? [])
const sold = computed(() => props.data?.sold ?? [])

const directSales = computed(() => sold.value.filter(r => Number(r.direct_sale_quantity) > 0))

const fmtMoney = v => `${Number(v ?? 0).toFixed(2)} BOB`
</script>

<template>
  <div>
    <div v-if="title" class="text-subtitle-1 font-weight-bold mb-2">{{ title }}</div>

    <VAlert
      v-if="summary?.has_differences"
      type="warning"
      variant="tonal"
      density="compact"
      class="mb-3"
      icon="ri-error-warning-line"
    >
      Hay diferencias entre productos comandados y vendidos.
    </VAlert>

    <VRow v-if="summary" class="mb-3" dense>
      <VCol cols="6" sm="3">
        <VCard variant="tonal"><VCardText class="py-2">
          <div class="text-caption">Productos</div>
          <div class="text-h6">{{ summary.total_products }}</div>
        </VCardText></VCard>
      </VCol>
      <VCol cols="6" sm="3">
        <VCard variant="tonal" color="success"><VCardText class="py-2">
          <div class="text-caption">OK</div>
          <div class="text-h6">{{ summary.ok_count }}</div>
        </VCardText></VCard>
      </VCol>
      <VCol cols="6" sm="3">
        <VCard variant="tonal" :color="summary.mismatch_count > 0 ? 'error' : 'default'"><VCardText class="py-2">
          <div class="text-caption">Con diferencias</div>
          <div class="text-h6">{{ summary.mismatch_count }}</div>
        </VCardText></VCard>
      </VCol>
      <VCol cols="6" sm="3">
        <VCard variant="tonal" color="info"><VCardText class="py-2">
          <div class="text-caption">Solo venta directa</div>
          <div class="text-h6">{{ summary.direct_only_count }}</div>
        </VCardText></VCard>
      </VCol>
    </VRow>

    <VProgressLinear v-if="loading" indeterminate class="mb-3" />

    <!-- Comparison -->
    <VTable density="compact" class="mb-4">
      <thead>
        <tr>
          <th>Producto</th>
          <th class="text-center">Comandado cobrado</th>
          <th class="text-center">Vendido</th>
          <th class="text-center">Diferencia</th>
          <th class="text-center">Estado</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in comparison" :key="row.product_id">
          <td>{{ row.product_name }}</td>
          <td class="text-center">{{ row.ordered_quantity }}</td>
          <td class="text-center font-weight-bold">{{ row.sold_quantity }}</td>
          <td
            class="text-center"
            :class="row.difference_quantity !== 0 ? 'text-error font-weight-bold' : 'text-medium-emphasis'"
          >
            {{ row.difference_quantity > 0 ? '+' : '' }}{{ row.difference_quantity }}
          </td>
          <td class="text-center">
            <VChip size="x-small" :color="statusMeta(row.status).color">{{ statusMeta(row.status).label }}</VChip>
          </td>
        </tr>
      </tbody>
    </VTable>
    <div v-if="!comparison.length && !loading" class="text-center py-4 text-medium-emphasis">
      Sin productos para el período seleccionado.
    </div>

    <!-- Sold breakdown -->
    <template v-if="showSold && sold.length">
      <div class="text-subtitle-2 font-weight-bold mb-2">Detalle de venta</div>
      <VTable density="compact" class="mb-4">
        <thead>
          <tr>
            <th>Producto</th>
            <th class="text-center">Vendido</th>
            <th class="text-center">Manillas</th>
            <th class="text-center">De comanda</th>
            <th class="text-center">Venta directa</th>
            <th class="text-end">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in sold" :key="row.product_id">
            <td>{{ row.product_name }}</td>
            <td class="text-center">{{ row.quantity_sold }}</td>
            <td class="text-center">{{ row.bracelet_units_sold || '—' }}</td>
            <td class="text-center">{{ row.order_sale_quantity }}</td>
            <td class="text-center">{{ row.direct_sale_quantity }}</td>
            <td class="text-end">{{ fmtMoney(row.total_amount) }}</td>
          </tr>
        </tbody>
      </VTable>
    </template>

    <!-- Direct sales only -->
    <template v-if="showSold && directSales.length">
      <div class="text-subtitle-2 font-weight-bold mb-2">Venta directa</div>
      <VTable density="compact">
        <thead>
          <tr>
            <th>Producto</th>
            <th class="text-center">Cantidad</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in directSales" :key="row.product_id">
            <td>{{ row.product_name }}</td>
            <td class="text-center">{{ row.direct_sale_quantity }}</td>
          </tr>
        </tbody>
      </VTable>
    </template>
  </div>
</template>
