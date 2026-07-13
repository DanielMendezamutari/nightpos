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
    <VCardTitle>Pendientes operativos</VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="12" md="3">
          <div class="text-caption text-medium-emphasis">Chicas pendientes</div>
          <div class="text-h6">{{ fmtBob(pending.girls) }}</div>
        </VCol>
        <VCol cols="12" md="3">
          <div class="text-caption text-medium-emphasis">Garzones pendientes</div>
          <div class="text-h6">{{ fmtBob(pending.waiters) }}</div>
        </VCol>
        <VCol cols="12" md="3">
          <div class="text-caption text-medium-emphasis">Limpieza pendiente</div>
          <div class="text-h6">{{ fmtBob(pending.cleaning) }}</div>
        </VCol>
        <VCol cols="12" md="3">
          <div class="text-caption text-medium-emphasis">Total pendiente</div>
          <div class="text-h5 font-weight-bold">{{ fmtBob(pending.total) }}</div>
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
