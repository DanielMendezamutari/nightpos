<script setup>
import { formatMoney } from '@/composables/useOrderHelpers'

const props = defineProps({
  sales: {
    type: Object,
    required: true,
  },
})

const fmtBob = value => formatMoney(value ?? 0, 'BOB')

const methodRows = [
  { key: 'cash_total', label: 'Efectivo' },
  { key: 'qr_total', label: 'QR' },
  { key: 'card_total', label: 'Tarjeta' },
  { key: 'mixed_total', label: 'Mixto' },
]

const sourceRows = [
  { key: 'order_sales', label: 'Comandas cobradas' },
  { key: 'direct_sales', label: 'Venta directa' },
  { key: 'room_services', label: 'Piezas' },
  { key: 'bracelets', label: 'Manillas' },
  { key: 'other_sales', label: 'Otros ingresos de venta' },
]
</script>

<template>
  <VCard>
    <VCardTitle>Venta total</VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="12" md="3">
          <div class="text-caption text-medium-emphasis">Venta total</div>
          <div class="text-h6">{{ fmtBob(sales.total_sales_amount ?? sales.total_sales) }}</div>
        </VCol>
        <VCol cols="12" md="3">
          <div class="text-caption text-medium-emphasis">Cantidad de ventas</div>
          <div class="text-h6">{{ sales.sales_count }}</div>
        </VCol>
        <VCol cols="12" md="3">
          <div class="text-caption text-medium-emphasis">Ticket promedio</div>
          <div class="text-h6">{{ fmtBob(sales.average_ticket) }}</div>
        </VCol>
        <VCol cols="12" md="3">
          <div class="text-caption text-medium-emphasis">Mixto</div>
          <div class="text-h6">{{ fmtBob(sales.mixed_total ?? 0) }}</div>
        </VCol>
      </VRow>

      <VRow class="mt-1">
        <VCol
          v-for="method in methodRows"
          :key="method.key"
          cols="12"
          md="3"
        >
          <VChip
            :color="method.key === 'cash_total' ? 'success' : method.key === 'qr_total' ? 'info' : method.key === 'card_total' ? 'warning' : 'secondary'"
            variant="tonal"
            size="small"
          >
            {{ method.label }}
          </VChip>
          <div class="text-h6 mt-1">{{ fmtBob(sales[method.key] ?? 0) }}</div>
        </VCol>
      </VRow>

      <VDivider class="my-4" />

      <div class="text-subtitle-2 mb-2">Ventas por origen</div>
      <VRow>
        <VCol
          v-for="source in sourceRows"
          :key="source.key"
          cols="12"
          md="4"
        >
          <div class="text-caption text-medium-emphasis">{{ source.label }}</div>
          <div class="text-h6">{{ fmtBob(sales.by_source?.[source.key] ?? sales.sales_by_source?.[source.key] ?? 0) }}</div>
        </VCol>
        <VCol cols="12" md="4">
          <div class="text-caption text-medium-emphasis">Total venta</div>
          <div class="text-h6 font-weight-bold">{{ fmtBob(sales.total_sales_amount ?? sales.total_sales) }}</div>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
