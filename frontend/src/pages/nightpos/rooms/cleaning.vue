<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchCleaningRooms, markRoomClean } from '@/api/rooms'
import { useFilteredRoomsTabs } from '@/composables/useRoomsSectionTabs'
import { useOnContextChange } from '@/composables/useOnContextChange'
import NightPosSseBanner from '@/components/nightpos/layout/NightPosSseBanner.vue'
import { useActionLoading } from '@/composables/useActionLoading'
import { useRoomOperationalEvents } from '@/composables/useRoomOperationalEvents'
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
  { title: 'Finalizó', key: 'last_finished_at' },
  { title: 'Min. pendiente', key: 'minutes_since_finish' },
  { title: 'Responsable', key: 'cleaning_user_name' },
  { title: 'Acción', key: 'actions', sortable: false },
]

const load = async () => {
  loading.value = true
  try {
    const data = await fetchCleaningRooms()
    items.value = data.items ?? []
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const onMarkClean = async item => {
  if (!can('rooms.clean'))
    return

  await run(keyFor(item.id, 'clean'), async () => {
    try {
      await markRoomClean(item.id)
      notify('Habitación marcada como limpia')
      await load()
    }
    catch (error) {
      notify(getApiErrorMessage(error), 'error')
    }
  })
}

const { connected: sseConnected, reconnecting: sseReconnecting } = useRoomOperationalEvents(load, { toastOnDue: false })

onMounted(load)
useOnContextChange(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Limpieza"
      subtitle="Habitaciones pendientes tras finalizar pieza."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Habitaciones', to: { name: 'nightpos-rooms-dashboard' } },
        { title: 'Limpieza', disabled: true },
      ]"
    />
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
    <VCard v-if="!loading">
      <VDataTable
        :headers="headers"
        :items="items"
        density="comfortable"
      >
        <template #item.minutes_since_finish="{ item }">
          <VChip
            v-if="item.minutes_since_finish != null"
            :color="item.minutes_since_finish > 30 ? 'warning' : 'info'"
            size="small"
          >
            {{ item.minutes_since_finish }} min
          </VChip>
          <span v-else>—</span>
        </template>
        <template #item.cleaning_user_name="{ item }">
          {{ item.cleaning_user_name || '—' }}
        </template>
        <template #item.actions="{ item }">
          <VBtn
            v-if="can('rooms.clean')"
            color="success"
            size="small"
            prepend-icon="ri-check-line"
            :loading="isLoading(keyFor(item.id, 'clean'))"
            :disabled="isLoading(keyFor(item.id, 'clean'))"
            @click="onMarkClean(item)"
          >
            Marcar limpia
          </VBtn>
        </template>
      </VDataTable>
    </VCard>
  </div>
</template>
