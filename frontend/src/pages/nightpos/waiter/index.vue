<script setup>
import WaiterBottomNav from '@/components/nightpos/waiter/WaiterBottomNav.vue'
import WaiterMobileHeader from '@/components/nightpos/waiter/WaiterMobileHeader.vue'
import WaiterTablesGrid from '@/components/nightpos/waiter/WaiterTablesGrid.vue'
import NightPosSseBanner from '@/components/nightpos/layout/NightPosSseBanner.vue'
import { useWaiterTables } from '@/composables/useWaiterTables'

definePage({
  meta: {
    layout: 'blank',
    permission: 'waiter.my_tables',
  },
})

const {
  loading,
  openingId,
  summary,
  groupedByArea,
  load,
  tapTable,
  sseConnected,
  sseReconnecting,
} = useWaiterTables()

const hasTables = computed(() => summary.value.total > 0)
</script>

<template>
  <div class="waiter-shell">
    <WaiterMobileHeader title="Mis mesas" />

    <VContainer class="py-3 px-4">
      <NightPosSseBanner
        :connected="sseConnected"
        :reconnecting="sseReconnecting"
      />

      <div
        v-if="hasTables && !loading"
        class="waiter-summary mb-4"
      >
        <VChip
          color="success"
          variant="tonal"
          size="small"
          prepend-icon="ri-checkbox-blank-circle-line"
        >
          {{ summary.free }} libre{{ summary.free === 1 ? '' : 's' }}
        </VChip>
        <VChip
          color="primary"
          variant="tonal"
          size="small"
          prepend-icon="ri-restaurant-2-line"
        >
          {{ summary.occupied }} ocupada{{ summary.occupied === 1 ? '' : 's' }}
        </VChip>
        <VBtn
          icon
          variant="text"
          size="small"
          aria-label="Actualizar mesas"
          :loading="loading"
          @click="load"
        >
          <VIcon icon="ri-refresh-line" />
        </VBtn>
      </div>

      <div
        v-if="loading && !hasTables"
        class="waiter-tables-skeleton"
      >
        <VSkeletonLoader
          v-for="n in 4"
          :key="n"
          type="image"
          class="waiter-tables-skeleton__item mb-3"
        />
      </div>

      <WaiterTablesGrid
        v-else-if="hasTables"
        :groups="groupedByArea"
        :opening-id="openingId"
        @tap="tapTable"
      />

      <VCard
        v-else-if="!loading"
        variant="tonal"
        color="info"
        class="mb-6"
      >
        <VCardText class="text-center py-8">
          <VIcon
            icon="ri-layout-grid-line"
            size="48"
            class="mb-3 text-medium-emphasis"
          />
          <div class="text-h6 font-weight-bold mb-2">
            No tienes mesas asignadas
          </div>
          <p class="text-body-2 text-medium-emphasis mb-4">
            Pide a la cajera o administradora que te asigne tus mesas para este turno.
          </p>
          <VBtn
            variant="outlined"
            color="primary"
            prepend-icon="ri-refresh-line"
            :loading="loading"
            @click="load"
          >
            Actualizar
          </VBtn>
        </VCardText>
      </VCard>
    </VContainer>

    <WaiterBottomNav />
  </div>
</template>

<style scoped lang="scss">
@use '@styles/waiter-mobile';

.waiter-summary {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
}

.waiter-tables-skeleton__item {
  min-height: 112px;
  border-radius: 16px;
}
</style>
