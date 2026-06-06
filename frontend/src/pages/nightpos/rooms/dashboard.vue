<script setup>
import CardStatisticsVertical from '@core/components/cards/CardStatisticsVertical.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchRooms } from '@/api/rooms'
import { useFilteredRoomsTabs } from '@/composables/useRoomsSectionTabs'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { roomStatusColor } from '@/composables/useRoomStatus'
import NightPosSseBanner from '@/components/nightpos/layout/NightPosSseBanner.vue'
import { useRoomOperationalEvents } from '@/composables/useRoomOperationalEvents'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'rooms.access' } })

const roomTabs = useFilteredRoomsTabs()
const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const loading = ref(true)
const summary = ref({})
const recent = ref([])

const summaryCards = computed(() => {
  const s = summary.value || {}

  return [
    { title: 'Disponibles', color: 'success', icon: 'ri-checkbox-circle-line', stats: String(s.available ?? 0), subtitle: 'Listas para asignar', to: 'nightpos-rooms-available' },
    { title: 'Ocupadas', color: 'warning', icon: 'ri-user-line', stats: String(s.occupied ?? 0), subtitle: 'Con pieza activa', to: 'nightpos-rooms-list' },
    { title: 'Limpieza', color: 'info', icon: 'ri-brush-line', stats: String(s.cleaning ?? 0), subtitle: 'Pendientes de limpiar', to: 'nightpos-rooms-cleaning' },
    { title: 'Mantenimiento', color: 'error', icon: 'ri-tools-line', stats: String(s.maintenance ?? 0), subtitle: 'Fuera de servicio', to: 'nightpos-rooms-maintenance' },
    { title: 'Total', color: 'primary', icon: 'ri-hotel-bed-line', stats: String(s.total ?? 0), subtitle: 'Habitaciones', to: 'nightpos-rooms-list' },
  ]
})

const load = async () => {
  loading.value = true
  try {
    const data = await fetchRooms()
    summary.value = data.summary ?? {}
    recent.value = (data.items ?? []).slice(0, 8)
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const { connected: sseConnected, reconnecting: sseReconnecting } = useRoomOperationalEvents(load, { toastOnDue: false })

onMounted(load)
useOnContextChange(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Habitaciones"
      subtitle="Estado operativo del local — disponibilidad, ocupación y limpieza."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Operación', disabled: true },
        { title: 'Habitaciones', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          v-if="can('rooms.create')"
          color="primary"
          prepend-icon="ri-add-line"
          :to="{ name: 'nightpos-rooms-create' }"
        >
          Nueva habitación
        </VBtn>
      </template>
    </NightPosPageHeader>

    <NightPosSectionTabs :tabs="roomTabs" />

    <NightPosSseBanner
      :connected="sseConnected"
      :reconnecting="sseReconnecting"
    />

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VRow v-if="!loading">
      <VCol
        v-for="card in summaryCards"
        :key="card.title"
        cols="12"
        sm="6"
        md="4"
        lg="2"
      >
        <RouterLink
          v-if="card.to"
          :to="{ name: card.to }"
          class="text-decoration-none"
        >
          <CardStatisticsVertical v-bind="card" />
        </RouterLink>
        <CardStatisticsVertical
          v-else
          v-bind="card"
        />
      </VCol>
    </VRow>

    <VCard
      v-if="!loading"
      class="mt-4"
    >
      <VCardTitle class="d-flex align-center gap-2">
        <VIcon icon="ri-hotel-bed-line" />
        Resumen rápido
      </VCardTitle>
      <VCardText>
        <VDataTable
          :headers="[
            { title: 'Código', key: 'code' },
            { title: 'Nombre', key: 'name' },
            { title: 'Tipo', key: 'room_type_label' },
            { title: 'Estado', key: 'status' },
          ]"
          :items="recent"
          density="comfortable"
          hide-default-footer
        >
          <template #item.status="{ item }">
            <VChip
              :color="roomStatusColor(item.status)"
              size="small"
              label
            >
              {{ item.status_label }}
            </VChip>
          </template>
        </VDataTable>
        <div class="text-center mt-4">
          <VBtn
            variant="tonal"
            :to="{ name: 'nightpos-rooms-list' }"
          >
            Ver todas
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </div>
</template>
