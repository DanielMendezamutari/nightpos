<script setup>
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

defineEmits(['register-fine'])

const { canManageSettlementFines } = useNightPosPermissions()

const links = [
  {
    title: 'Ver chicas',
    subtitle: 'Liquidaciones y pagos',
    icon: 'ri-women-line',
    color: 'secondary',
    to: { name: 'nightpos-settlements-girls' },
  },
  {
    title: 'Ver garzones',
    subtitle: 'Comisiones del turno',
    icon: 'ri-user-star-line',
    color: 'primary',
    to: { name: 'nightpos-settlements-waiters' },
  },
  {
    title: 'Ver limpieza',
    subtitle: 'Pagos por pieza',
    icon: 'ri-brush-line',
    color: 'success',
    to: { name: 'nightpos-settlements-cleaning' },
  },
]
</script>

<template>
  <VRow class="mb-4">
    <VCol
      v-for="link in links"
      :key="link.title"
      cols="12"
      sm="6"
      md="3"
    >
      <VCard
        :to="link.to"
        variant="outlined"
        class="settlement-hub-quick-nav h-100"
      >
        <VCardText class="d-flex align-center gap-3 py-4">
          <VAvatar
            :color="link.color"
            variant="tonal"
            size="44"
          >
            <VIcon :icon="link.icon" />
          </VAvatar>
          <div>
            <div class="text-subtitle-1 font-weight-medium">
              {{ link.title }}
            </div>
            <div class="text-caption text-medium-emphasis">
              {{ link.subtitle }}
            </div>
          </div>
        </VCardText>
      </VCard>
    </VCol>
    <VCol
      v-if="canManageSettlementFines"
      cols="12"
      sm="6"
      md="3"
    >
      <VCard
        variant="outlined"
        color="warning"
        class="settlement-hub-quick-nav h-100"
        @click="$emit('register-fine')"
      >
        <VCardText class="d-flex align-center gap-3 py-4">
          <VAvatar
            color="warning"
            variant="tonal"
            size="44"
          >
            <VIcon icon="ri-error-warning-line" />
          </VAvatar>
          <div>
            <div class="text-subtitle-1 font-weight-medium">
              Registrar multa
            </div>
            <div class="text-caption text-medium-emphasis">
              También disponible en cada fila
            </div>
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style scoped>
.settlement-hub-quick-nav {
  cursor: pointer;
  transition: border-color 0.15s ease;
}

.settlement-hub-quick-nav:hover {
  border-color: rgb(var(--v-theme-primary));
}
</style>
