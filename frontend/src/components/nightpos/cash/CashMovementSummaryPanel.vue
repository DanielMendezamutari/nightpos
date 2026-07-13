<script setup>
import { formatMoney } from '@/composables/useOrderHelpers'
import { cashMovementCategoryLabel } from '@/constants/cashMovements'
import { paymentMethodLabel } from '@/constants/paymentMethods'

const props = defineProps({
  movement: {
    type: Object,
    required: true,
  },
  recentMovements: {
    type: Array,
    default: () => [],
  },
})

const fmtBob = value => formatMoney(value ?? 0, 'BOB')

const movementHeaders = [
  { title: 'Tipo', key: 'movement_type' },
  { title: 'Categoria', key: 'movement_category' },
  { title: 'Monto', key: 'amount' },
  { title: 'Metodo', key: 'payment_method' },
  { title: 'Fecha', key: 'created_at' },
]

const formatMovementDate = value => {
  if (!value)
    return '—'

  try {
    return new Date(value).toLocaleString('es-BO', {
      dateStyle: 'short',
      timeStyle: 'short',
    })
  }
  catch {
    return value
  }
}
</script>

<template>
  <VCard>
    <VCardTitle>Movimientos</VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="12" md="4">
          <div class="text-caption text-medium-emphasis">Ingresos manuales</div>
          <div class="text-h6">{{ fmtBob(movement.manual_income) }}</div>
        </VCol>
        <VCol cols="12" md="4">
          <div class="text-caption text-medium-emphasis">Pagos al personal</div>
          <div class="text-h6">{{ fmtBob(movement.settlement_payments) }}</div>
        </VCol>
        <VCol cols="12" md="4">
          <div class="text-caption text-medium-emphasis">Gastos operativos</div>
          <div class="text-h6">{{ fmtBob(movement.operating_expenses) }}</div>
        </VCol>
        <VCol cols="12" md="6">
          <div class="text-caption text-medium-emphasis">Compras</div>
          <div class="text-h6">{{ fmtBob(movement.purchases) }}</div>
        </VCol>
        <VCol cols="12" md="6">
          <div class="text-caption text-medium-emphasis">Otros egresos</div>
          <div class="text-h6">{{ fmtBob(movement.other_expenses) }}</div>
        </VCol>
      </VRow>

      <VDivider class="my-4" />

      <div class="text-subtitle-2 mb-2">Ultimos movimientos</div>
      <VDataTable
        :headers="movementHeaders"
        :items="recentMovements"
        density="comfortable"
        :items-per-page="5"
      >
        <template #item.movement_type="{ item }">
          <VChip size="small" :color="item.movement_type === 'INCOME' ? 'success' : 'warning'" variant="tonal">
            {{ item.movement_type === 'INCOME' ? 'Ingreso' : 'Egreso' }}
          </VChip>
        </template>

        <template #item.movement_category="{ item }">
          {{ cashMovementCategoryLabel(item.movement_category, item.movement_type) }}
        </template>

        <template #item.amount="{ item }">
          {{ fmtBob(item.amount) }}
        </template>

        <template #item.payment_method="{ item }">
          <VChip v-if="item.payment_method" size="x-small" variant="tonal">
            {{ paymentMethodLabel(item.payment_method) }}
          </VChip>
          <span v-else>—</span>
        </template>

        <template #item.created_at="{ item }">
          {{ formatMovementDate(item.created_at) }}
        </template>
      </VDataTable>
    </VCardText>
  </VCard>
</template>
