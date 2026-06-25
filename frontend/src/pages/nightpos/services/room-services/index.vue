<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchCurrentShiftRoomServices, finishRoomService } from '@/api/roomServices'
import NightPosSseBanner from '@/components/nightpos/layout/NightPosSseBanner.vue'
import { useActionLoading } from '@/composables/useActionLoading'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { useRoomOperationalEvents } from '@/composables/useRoomOperationalEvents'
import { useFilteredServiceTabs } from '@/composables/useServiceSectionTabs'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { formatMoney } from '@/composables/useOrderHelpers'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'room_services.access' } })

const serviceTabs = useFilteredServiceTabs()
const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const { isLoading, run, keyFor } = useActionLoading()

const loading = ref(true)
const shift = ref(null)
const summary = ref(null)
const items = ref([])

const headers = [
  { title: 'Chica', key: 'girl_name' },
  { title: 'Pieza', key: 'room_label' },
  { title: 'Registró', key: 'registered_by_name' },
  { title: 'Estado', key: 'status' },
  { title: 'Restante', key: 'remaining_minutes' },
  { title: 'Fin estimado', key: 'expected_ends_at' },
  { title: 'Total', key: 'total_amount' },
  { title: '% Chica', key: 'girl_percent' },
  { title: 'Chica', key: 'girl_amount' },
  { title: 'Casa', key: 'house_amount' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const statusColor = status => ({
  ACTIVE: 'warning',
  DUE: 'error',
  FINISHED: 'success',
  CANCELLED: 'secondary',
}[status] || 'default')

const canFinishItem = item => (item.status === 'ACTIVE' || item.status === 'DUE' || item.is_due)
  && can('room_services.finish')

const summaryCards = computed(() => {
  const s = summary.value || {}

  return [
    { title: 'Total piezas', color: 'warning', icon: 'ri-door-line', stats: String(s.count ?? 0), subtitle: 'Registros' },
    { title: 'Ingreso total', color: 'primary', icon: 'ri-money-dollar-circle-line', stats: `${formatMoney(s.total_amount)} BOB`, subtitle: 'Turno actual' },
  ]
})

const load = async () => {
  loading.value = true
  try {
    const data = await fetchCurrentShiftRoomServices()
    shift.value = data.shift
    summary.value = data.summary
    items.value = data.items ?? []
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const onFinish = async item => {
  if (!canFinishItem(item))
    return

  await run(keyFor(item.id, 'finish'), async () => {
    try {
      await finishRoomService(item.id)
      notify('Pieza terminada. Habitación disponible para nueva pieza.')
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
      title="Piezas"
      subtitle="Registro de piezas / habitaciones por chica."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Operación', disabled: true },
        { title: 'Servicios', to: { name: 'nightpos-services-room-services' } },
        { title: 'Piezas', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          v-if="can('room_services.create')"
          color="primary"
          prepend-icon="ri-add-line"
          :to="{ name: 'nightpos-services-room-services-create' }"
        >
          Registrar pieza
        </VBtn>
      </template>
    </NightPosPageHeader>

    <NightPosSectionTabs :tabs="serviceTabs" />

    <NightPosSseBanner
      :connected="sseConnected"
      :reconnecting="sseReconnecting"
    />

    <VAlert
      v-if="!loading && !shift"
      type="info"
      variant="tonal"
      class="mb-4"
    >
      Sin turno clasificado. Al registrar, el sistema asigna el turno automáticamente.
    </VAlert>
    <VAlert
      v-else-if="shift"
      type="info"
      variant="tonal"
      class="mb-4"
    >
      Turno: <strong>{{ shift.name }}</strong> · {{ shift.business_date }} · {{ shift.shift_type_label }}
    </VAlert>

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <template v-else>
      <VRow class="match-height mb-4">
        <VCol
          v-for="card in summaryCards"
          :key="card.title"
          cols="12"
          md="6"
        >
          <CardStatisticsVertical v-bind="card" />
        </VCol>
      </VRow>

      <VCard>
        <VCardTitle class="text-body-1">
          Historial del turno
        </VCardTitle>
        <VDataTable
          :headers="headers"
          :items="items"
          :items-per-page="15"
          class="text-no-wrap"
        >
          <template #item.status="{ item }">
            <VChip
              size="small"
              :color="statusColor(item.status)"
              variant="tonal"
            >
              {{ item.status_label || item.status }}
            </VChip>
          </template>
          <template #item.remaining_minutes="{ item }">
            <span v-if="item.status === 'ACTIVE' && !item.is_due">{{ item.remaining_minutes }} min</span>
            <VChip
              v-else-if="item.is_due"
              size="small"
              color="error"
              variant="tonal"
            >
              Vencida
            </VChip>
            <span v-else>—</span>
          </template>
          <template #item.total_amount="{ item }">
            <VChip
              size="small"
              color="warning"
              variant="tonal"
            >
              {{ formatMoney(item.total_amount) }}
            </VChip>
          </template>
          <template #item.girl_percent="{ item }">
            {{ item.girl_percent != null ? `${item.girl_percent}%` : '—' }}
          </template>
          <template #item.girl_amount="{ item }">
            {{ formatMoney(item.girl_amount) }}
          </template>
          <template #item.house_amount="{ item }">
            {{ formatMoney(item.house_amount) }}
          </template>
          <template #item.actions="{ item }">
            <VBtn
              v-if="canFinishItem(item)"
              size="small"
              variant="text"
              color="success"
              :loading="isLoading(keyFor(item.id, 'finish'))"
              :disabled="isLoading(keyFor(item.id, 'finish'))"
              @click="onFinish(item)"
            >
              Terminar
            </VBtn>
          </template>
        </VDataTable>
      </VCard>
    </template>
  </div>
</template>
