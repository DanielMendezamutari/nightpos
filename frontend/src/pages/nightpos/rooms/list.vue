<script setup>
import CardStatisticsVertical from '@core/components/cards/CardStatisticsVertical.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchRooms } from '@/api/rooms'
import { useFilteredRoomsTabs } from '@/composables/useRoomsSectionTabs'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { roomStatusColor } from '@/composables/useRoomStatus'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { formatMoney } from '@/composables/useOrderHelpers'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'rooms.access' } })

const roomTabs = useFilteredRoomsTabs()
const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const loading = ref(true)
const items = ref([])
const summary = ref({})

const headers = [
  { title: 'Código', key: 'code' },
  { title: 'Nombre', key: 'name' },
  { title: 'Tipo', key: 'room_type_label' },
  { title: 'Estado', key: 'status' },
  { title: 'Notas', key: 'notes' },
  { title: 'Sucursal', key: 'branch_code' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const summaryCards = computed(() => {
  const s = summary.value || {}

  return [
    { title: 'Total', color: 'primary', icon: 'ri-hotel-bed-line', stats: String(s.total ?? 0), subtitle: 'Habitaciones' },
    { title: 'Disponibles', color: 'success', icon: 'ri-checkbox-circle-line', stats: String(s.available ?? 0), subtitle: 'AVAILABLE' },
  ]
})

const load = async () => {
  loading.value = true
  try {
    const data = await fetchRooms()
    items.value = data.items ?? []
    summary.value = data.summary ?? {}
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

onMounted(load)
useOnContextChange(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Habitaciones"
      subtitle="Catálogo de piezas físicas del local."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Operación', disabled: true },
        { title: 'Habitaciones', to: { name: 'nightpos-rooms-dashboard' } },
        { title: 'Listado', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          v-if="can('rooms.create')"
          color="primary"
          prepend-icon="ri-add-line"
          :to="{ name: 'nightpos-rooms-create' }"
        >
          Nueva
        </VBtn>
      </template>
    </NightPosPageHeader>

    <NightPosSectionTabs :tabs="roomTabs" />

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VRow
      v-if="!loading"
      class="mb-4"
    >
      <VCol
        v-for="card in summaryCards"
        :key="card.title"
        cols="12"
        md="6"
      >
        <CardStatisticsVertical v-bind="card" />
      </VCol>
    </VRow>

    <VCard v-if="!loading">
      <VDataTable
        :headers="headers"
        :items="items"
        density="comfortable"
      >
        <template #item.notes="{ item }">
          {{ item.notes || '—' }}
        </template>
        <template #item.status="{ item }">
          <VChip
            :color="roomStatusColor(item.status)"
            size="small"
            label
          >
            {{ item.status_label }}
          </VChip>
        </template>
        <template #item.actions="{ item }">
          <VBtn
            v-if="can('rooms.update')"
            size="small"
            variant="text"
            icon="ri-edit-line"
            :to="{ name: 'nightpos-rooms-id-edit', params: { id: item.id } }"
          />
        </template>
      </VDataTable>
    </VCard>
  </div>
</template>
