<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchRooms, markRoomAvailable } from '@/api/rooms'
import { useFilteredRoomsTabs } from '@/composables/useRoomsSectionTabs'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { roomStatusColor } from '@/composables/useRoomStatus'
import { useActionLoading } from '@/composables/useActionLoading'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'rooms.access' } })

const roomTabs = useFilteredRoomsTabs()
const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const { isLoading, run, keyFor } = useActionLoading()
const loading = ref(true)
const items = ref([])

const headers = [
  { title: 'Código', key: 'code' },
  { title: 'Nombre', key: 'name' },
  { title: 'Estado', key: 'status' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const load = async () => {
  loading.value = true
  try {
    const data = await fetchRooms({ status: 'MAINTENANCE' })
    items.value = data.items ?? []
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const releaseRoom = async room => {
  if (!can('rooms.maintenance'))
    return

  await run(keyFor(room.id, 'available'), async () => {
    try {
      await markRoomAvailable(room.id)
      notify('Habitación disponible')
      await load()
    }
    catch (error) {
      notify(getApiErrorMessage(error), 'error')
    }
  })
}

onMounted(load)
useOnContextChange(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Mantenimiento"
      subtitle="Habitaciones fuera de servicio o por liberar."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Habitaciones', to: { name: 'nightpos-rooms-dashboard' } },
        { title: 'Mantenimiento', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="roomTabs" />
    <VAlert
      type="info"
      variant="tonal"
      class="mb-4"
    >
      Desde el listado general puede enviar habitaciones disponibles a mantenimiento. Aquí gestiona las que están en MAINTENANCE.
    </VAlert>
    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />
    <VCard v-if="!loading">
      <VDataTable
        :headers="headers"
        :items="items"
        density="comfortable"
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
        <template #item.actions="{ item }">
          <VBtn
            v-if="can('rooms.maintenance') && item.status === 'MAINTENANCE'"
            color="success"
            size="small"
            class="me-2"
            :loading="isLoading(keyFor(item.id, 'available'))"
            :disabled="isLoading(keyFor(item.id, 'available'))"
            @click="releaseRoom(item)"
          >
            Disponible
          </VBtn>
        </template>
      </VDataTable>
    </VCard>
  </div>
</template>
