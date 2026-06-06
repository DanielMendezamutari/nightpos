<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchCurrentShiftBracelets } from '@/api/bracelets'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { useFilteredServiceTabs } from '@/composables/useServiceSectionTabs'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { formatMoney } from '@/composables/useOrderHelpers'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'bracelets.access' } })

const serviceTabs = useFilteredServiceTabs()
const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const router = useRouter()

const loading = ref(true)
const shift = ref(null)
const summary = ref(null)
const items = ref([])

const headers = [
  { title: 'Chica', key: 'girl_name' },
  { title: 'Cantidad', key: 'quantity' },
  { title: 'Precio', key: 'unit_price' },
  { title: 'Total', key: 'total_amount' },
  { title: 'Hora', key: 'registered_at' },
  { title: 'Registró', key: 'registered_by_name' },
]

const summaryCards = computed(() => {
  const s = summary.value || {}

  return [
    { title: 'Total turno', color: 'primary', icon: 'ri-money-dollar-circle-line', stats: `${formatMoney(s.total_amount)} BOB`, subtitle: 'Ingreso manillas' },
    { title: 'Cantidad', color: 'info', icon: 'ri-stack-line', stats: String(s.quantity ?? 0), subtitle: 'Unidades' },
    { title: 'Promedio', color: 'secondary', icon: 'ri-bar-chart-line', stats: `${formatMoney(s.average)} BOB`, subtitle: 'Por registro' },
  ]
})

const load = async () => {
  loading.value = true
  try {
    const data = await fetchCurrentShiftBracelets()
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
      title="Manillas"
      subtitle="Consumo/pago asignado a chica (sin habitación ni control de tiempo)."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Operación', disabled: true },
        { title: 'Servicios', to: { name: 'nightpos-services-bracelets' } },
        { title: 'Manillas', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          v-if="can('bracelets.create')"
          color="primary"
          prepend-icon="ri-add-line"
          :to="{ name: 'nightpos-services-bracelets-create' }"
        >
          Registrar manillas
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
      <VChip
        v-if="shift.auto_created"
        size="x-small"
        color="info"
        variant="tonal"
        class="ms-2"
      >
        Auto
      </VChip>
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
          md="4"
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
          <template #item.unit_price="{ item }">
            {{ formatMoney(item.unit_price) }}
          </template>
          <template #item.total_amount="{ item }">
            <VChip
              size="small"
              color="primary"
              variant="tonal"
            >
              {{ formatMoney(item.total_amount) }}
            </VChip>
          </template>
        </VDataTable>
      </VCard>
    </template>
  </div>
</template>
