<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchAvailableRooms } from '@/api/rooms'
import { useFilteredRoomsTabs } from '@/composables/useRoomsSectionTabs'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'rooms.access' } })

const roomTabs = useFilteredRoomsTabs()
const { notify } = useNightPosNotify()
const loading = ref(true)
const items = ref([])

const headers = [
  { title: 'Código', key: 'code' },
  { title: 'Nombre', key: 'name' },
  { title: 'Tipo', key: 'room_type_label' },
  { title: 'Notas', key: 'notes' },
]

const load = async () => {
  loading.value = true
  try {
    const data = await fetchAvailableRooms()
    items.value = data.items ?? []
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
      title="Disponibles"
      subtitle="Habitaciones listas para registrar una pieza."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Habitaciones', to: { name: 'nightpos-rooms-dashboard' } },
        { title: 'Disponibles', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="roomTabs" />
    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />
    <VCard v-if="!loading">
      <VCardText>
        <VBadge
          :content="items.length"
          color="success"
          class="mb-4"
        >
          <span class="text-body-1">Disponibles ahora</span>
        </VBadge>
      </VCardText>
      <VDataTable
        :headers="headers"
        :items="items"
        density="comfortable"
      >
        <template #item.notes="{ item }">
          {{ item.notes || '—' }}
        </template>
      </VDataTable>
    </VCard>
  </div>
</template>
