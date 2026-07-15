<script setup>
import { formatMoney } from '@/composables/useOrderHelpers'

const props = defineProps({
  pending: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['go-settlements', 'go-charge'])

const fmtBob = value => formatMoney(value ?? 0, 'BOB')
</script>

<template>
  <VCard>
    <VCardTitle>Resumen del personal</VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="12" md="3">
          <div class="text-caption text-medium-emphasis">Chicas a pagar</div>
          <div class="text-h6">{{ fmtBob(pending.girls) }}</div>
          <div class="text-caption text-medium-emphasis mt-1">Provisional: {{ fmtBob(pending.girls_provisional) }}</div>
        </VCol>
        <VCol cols="12" md="3">
          <div class="text-caption text-medium-emphasis">Garzones a pagar</div>
          <div class="text-h6">{{ fmtBob(pending.waiters) }}</div>
          <div class="text-caption text-medium-emphasis mt-1">Ventas prov.: {{ fmtBob(pending.waiters_provisional_sales) }}</div>
        </VCol>
        <VCol cols="12" md="3">
          <div class="text-caption text-medium-emphasis">Limpieza</div>
          <div class="text-h6">{{ fmtBob(pending.cleaning) }}</div>
          <div class="text-caption text-medium-emphasis mt-1">Provisional: {{ fmtBob(pending.cleaning_provisional) }}</div>
        </VCol>
        <VCol cols="12" md="3">
          <div class="text-caption text-medium-emphasis">Total personal confirmado</div>
          <div class="text-h5 font-weight-bold">{{ fmtBob(pending.total) }}</div>
        </VCol>
      </VRow>

      <VRow class="mt-2">
        <VCol cols="12" md="4">
          <div class="text-subtitle-2 mb-2">Garzones</div>
          <div v-for="row in pending.personnel_rows?.waiters ?? []" :key="`w-${row.staff_user_id}`" class="mb-2">
            <div><strong>{{ row.staff_name }}</strong></div>
            <div class="text-caption">Ventas cobradas: {{ fmtBob(row.sales_total_amount) }}</div>
            <div class="text-caption">Ventas provisionales: {{ fmtBob(row.provisional_sales_total_amount) }}</div>
            <div class="text-caption">Total a pagar: {{ fmtBob(row.total_amount) }}</div>
          </div>
        </VCol>
        <VCol cols="12" md="4">
          <div class="text-subtitle-2 mb-2">Chicas</div>
          <div v-for="row in pending.personnel_rows?.girls ?? []" :key="`g-${row.staff_user_id}`" class="mb-2">
            <div><strong>{{ row.staff_name }}</strong></div>
            <div class="text-caption">Confirmado: {{ fmtBob(row.total_amount) }}</div>
            <div class="text-caption">Provisional: {{ fmtBob(row.provisional_total_amount) }}</div>
          </div>
        </VCol>
        <VCol cols="12" md="4">
          <div class="text-subtitle-2 mb-2">Limpieza</div>
          <div v-for="row in pending.personnel_rows?.cleaning ?? []" :key="`c-${row.staff_user_id}`" class="mb-2">
            <div><strong>{{ row.staff_name }}</strong></div>
            <div class="text-caption">Confirmado: {{ fmtBob(row.total_amount) }}</div>
            <div class="text-caption">Provisional: {{ fmtBob(row.provisional_total_amount) }}</div>
          </div>
        </VCol>
      </VRow>

      <VAlert
        v-if="pending.pending_orders_count > 0"
        type="warning"
        variant="tonal"
        class="mt-4"
      >
        Comandas pendientes de cobro: {{ pending.pending_orders_count }}
      </VAlert>

      <VAlert
        v-for="alert in pending.critical_alerts"
        :key="alert"
        type="error"
        variant="tonal"
        density="compact"
        class="mt-2"
      >
        {{ alert }}
      </VAlert>

      <div class="d-flex flex-wrap gap-2 mt-4">
        <VBtn size="small" variant="tonal" color="primary" @click="emit('go-settlements')">
          Ver chicas
        </VBtn>
        <VBtn size="small" variant="tonal" color="primary" @click="emit('go-settlements')">
          Ver garzones
        </VBtn>
        <VBtn size="small" variant="tonal" color="primary" @click="emit('go-settlements')">
          Ver limpieza
        </VBtn>
        <VBtn size="small" variant="tonal" color="warning" @click="emit('go-charge')">
          Ir a cobrar
        </VBtn>
      </div>
    </VCardText>
  </VCard>
</template>
