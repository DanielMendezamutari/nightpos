<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchCurrentShiftShows } from '@/api/shows'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { useFilteredServiceTabs } from '@/composables/useServiceSectionTabs'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { formatMoney } from '@/composables/useOrderHelpers'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'shows.access' } })

const serviceTabs = useFilteredServiceTabs()
const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const loading = ref(true)
const shift = ref(null)
const summary = ref(null)
const items = ref([])

const headers = [
  { title: 'Chica', key: 'girl_name' },
  { title: 'Tipo', key: 'show_type_label' },
  { title: 'Precio', key: 'unit_price' },
  { title: 'Total', key: 'total_amount' },
  { title: 'Hora', key: 'registered_at' },
  { title: 'Registró', key: 'registered_by_name' },
]

const summaryCards = computed(() => {
  const s = summary.value || {}

  return [
    { title: 'Total shows', color: 'error', icon: 'ri-mic-line', stats: String(s.count ?? 0), subtitle: 'Registros' },
    { title: 'Ingreso total', color: 'primary', icon: 'ri-money-dollar-circle-line', stats: `${formatMoney(s.total_amount)} BOB`, subtitle: 'Turno actual' },
  ]
})

const load = async () => {
  loading.value = true
  try {
    const data = await fetchCurrentShiftShows()
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

onMounted(load)
useOnContextChange(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Shows"
      subtitle="Registro de shows por chica y tipo."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Operación', disabled: true },
        { title: 'Servicios', to: { name: 'nightpos-services-shows' } },
        { title: 'Shows', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          v-if="can('shows.create')"
          color="primary"
          prepend-icon="ri-add-line"
          :to="{ name: 'nightpos-services-shows-create' }"
        >
          Registrar show
        </VBtn>
      </template>
    </NightPosPageHeader>

    <NightPosSectionTabs :tabs="serviceTabs" />

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
          <template #item.show_type_label="{ item }">
            <VChip
              size="small"
              color="error"
              variant="tonal"
            >
              {{ item.show_type_label }}
            </VChip>
          </template>
          <template #item.total_amount="{ item }">
            {{ formatMoney(item.total_amount) }}
          </template>
        </VDataTable>
      </VCard>
    </template>
  </div>
</template>
