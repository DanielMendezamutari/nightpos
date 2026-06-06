<script setup>
import { fetchGirlShiftEarnings } from '@/api/girl'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    layout: 'blank',
    permission: 'girl.dashboard',
  },
})

const { notify } = useNightPosNotify()
const loading = ref(true)
const earnings = ref(null)

const formatMoney = value => {
  const num = Number(value)

  if (Number.isNaN(num))
    return `${value ?? '0.00'} BOB`

  return `${num.toFixed(2)} BOB`
}

const incomeRows = computed(() => {
  if (!earnings.value)
    return []

  return [
    { key: 'consumption', label: 'Consumos con acompañante', value: earnings.value.consumption_total, icon: 'ri-goblet-line', color: 'primary' },
    { key: 'bracelets', label: 'Manillas', value: earnings.value.bracelets_total, icon: 'ri-vip-crown-line', color: 'info' },
    { key: 'rooms', label: 'Piezas', value: earnings.value.rooms_total, icon: 'ri-hotel-bed-line', color: 'warning' },
    { key: 'shows', label: 'Shows', value: earnings.value.shows_total, icon: 'ri-music-2-line', color: 'secondary' },
  ]
})

const load = async () => {
  loading.value = true

  try {
    earnings.value = await fetchGirlShiftEarnings()
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
  <div class="girl-shell">
    <VAppBar
      flat
      color="primary"
      density="comfortable"
    >
      <VAppBarTitle>Mis ingresos</VAppBarTitle>
    </VAppBar>

    <VContainer class="py-4 px-4">
      <p class="text-body-2 text-medium-emphasis mb-4">
        Resumen de tu turno actual. Solo lectura.
      </p>

      <VProgressLinear
        v-if="loading"
        indeterminate
        class="mb-4"
      />

      <template v-else-if="earnings">
        <VRow class="mb-4">
          <VCol
            v-for="row in incomeRows"
            :key="row.key"
            cols="12"
            sm="6"
          >
            <VCard variant="tonal">
              <VCardText>
                <div class="d-flex align-center gap-2 mb-2">
                  <VIcon
                    :icon="row.icon"
                    :color="row.color"
                  />
                  <span class="text-body-2">{{ row.label }}</span>
                </div>
                <div class="text-h5 font-weight-bold">
                  {{ formatMoney(row.value) }}
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>

        <VCard
          color="warning"
          variant="tonal"
          class="mb-4"
        >
          <VCardText>
            <div class="text-body-2 mb-1">
              Total pendiente
            </div>
            <div class="text-h4 font-weight-bold">
              {{ formatMoney(earnings.total_pending) }}
            </div>
          </VCardText>
        </VCard>

        <VCard
          color="success"
          variant="tonal"
        >
          <VCardText>
            <div class="text-body-2 mb-1">
              Total pagado
            </div>
            <div class="text-h4 font-weight-bold">
              {{ formatMoney(earnings.total_paid) }}
            </div>
          </VCardText>
        </VCard>
      </template>

      <VAlert
        v-else
        type="info"
        variant="tonal"
      >
        Sin datos de ingresos para el turno actual.
      </VAlert>
    </VContainer>
</div>
</template>

<style scoped>
.girl-shell {
  min-height: 100vh;
  background: rgb(var(--v-theme-surface));
}
</style>
