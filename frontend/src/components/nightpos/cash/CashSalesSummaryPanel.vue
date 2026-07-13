<script setup>
import { formatMoney } from '@/composables/useOrderHelpers'

const props = defineProps({
  sales: {
    type: Object,
    required: true,
  },
})

const fmtBob = value => formatMoney(value ?? 0, 'BOB')
</script>

<template>
  <VCard>
    <VCardTitle>Ventas</VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="12" md="3">
          <div class="text-caption text-medium-emphasis">Venta total</div>
          <div class="text-h6">{{ fmtBob(sales.total_sales) }}</div>
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
          <div class="text-caption text-medium-emphasis">Pagos mixtos</div>
          <div class="text-h6">{{ sales.mixed_sales_count }}</div>
        </VCol>
      </VRow>

      <VRow class="mt-1">
        <VCol cols="12" md="4">
          <VChip color="success" variant="tonal" size="small">Efectivo</VChip>
          <div class="text-h6 mt-1">{{ fmtBob(sales.sales_cash) }}</div>
        </VCol>
        <VCol cols="12" md="4">
          <VChip color="info" variant="tonal" size="small">QR</VChip>
          <div class="text-h6 mt-1">{{ fmtBob(sales.sales_qr) }}</div>
        </VCol>
        <VCol cols="12" md="4">
          <VChip color="warning" variant="tonal" size="small">Tarjeta</VChip>
          <div class="text-h6 mt-1">{{ fmtBob(sales.sales_card) }}</div>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
