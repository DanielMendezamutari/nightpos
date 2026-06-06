<script setup>
import CleaningMobileHeader from '@/components/nightpos/cleaning/CleaningMobileHeader.vue'
import {
  checkCleaningRoomService,
  fetchCleaningDashboard,
  fetchCleaningShiftEarnings,
  finishCleaningRoomService,
  markCleaningRoomClean,
} from '@/api/cleaning'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useOnContextChange } from '@/composables/useOnContextChange'
import NightPosSseBanner from '@/components/nightpos/layout/NightPosSseBanner.vue'
import { useActionLoading } from '@/composables/useActionLoading'
import { useRoomDueAlerts } from '@/composables/useRoomDueAlerts'
import { useRoomOperationalEvents } from '@/composables/useRoomOperationalEvents'
import { getApiErrorMessage } from '@/services/http'
import '@/assets/styles/cleaning-mobile.scss'

definePage({
  meta: {
    layout: 'blank',
    permission: 'cleaning.dashboard',
  },
})

const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const { isLoading, run, keyFor } = useActionLoading()
const { handleDueItems, markItemReviewed } = useRoomDueAlerts()

const loading = ref(true)
const active = ref([])
const due = ref([])
const cleaning = ref([])
const finishedToday = ref([])
const earnings = ref(null)
let pollTimer = null

const kpiCards = computed(() => [
  { key: 'active', title: 'Activas', value: active.value.length, color: 'info', icon: 'ri-time-line' },
  { key: 'due', title: 'Tiempo cumplido', value: due.value.length, color: 'error', icon: 'ri-alarm-warning-line' },
  { key: 'cleaning', title: 'En limpieza', value: cleaning.value.length, color: 'warning', icon: 'ri-brush-line' },
  { key: 'finished', title: 'Terminadas hoy', value: finishedToday.value.length, color: 'success', icon: 'ri-check-line' },
])

const allItems = computed(() => [
  ...due.value.map(i => ({ ...i, section: 'due' })),
  ...active.value.map(i => ({ ...i, section: 'active' })),
  ...cleaning.value.map(i => ({ ...i, section: 'cleaning', is_room: true })),
  ...finishedToday.value.map(i => ({ ...i, section: 'finished' })),
])

const load = async () => {
  try {
    const previousDueIds = due.value.map(i => i.id)
    const [dashboard, earningsData] = await Promise.all([
      fetchCleaningDashboard(),
      fetchCleaningShiftEarnings().catch(() => ({ earnings: null })),
    ])

    active.value = dashboard.active ?? []
    due.value = dashboard.due ?? []
    cleaning.value = dashboard.cleaning ?? []
    finishedToday.value = dashboard.finished_today ?? []
    earnings.value = earningsData.earnings ?? null

    const newDue = due.value.filter(i => !previousDueIds.includes(i.id))
    if (newDue.length)
      handleDueItems(newDue)
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const onCheck = async item => {
  await run(keyFor(item.id, 'check'), async () => {
    try {
      await checkCleaningRoomService(item.id)
      markItemReviewed(item.id)
      notify('Pieza marcada como revisada')
      await load()
    }
    catch (error) {
      notify(getApiErrorMessage(error), 'error')
    }
  })
}

const onFinish = async item => {
  await run(keyFor(item.id, 'finish'), async () => {
    try {
      await finishCleaningRoomService(item.id)
      markItemReviewed(item.id)
      notify('Servicio finalizado')
      await load()
    }
    catch (error) {
      notify(getApiErrorMessage(error), 'error')
    }
  })
}

const onMarkClean = async item => {
  await run(keyFor(item.id, 'clean'), async () => {
    try {
      await markCleaningRoomClean(item.id)
      notify('Habitación marcada como limpia')
      await load()
    }
    catch (error) {
      notify(getApiErrorMessage(error), 'error')
    }
  })
}

const statusLabel = item => {
  if (item.is_room)
    return 'En limpieza'

  if (item.section === 'due' || item.status === 'DUE')
    return 'Tiempo cumplido'

  if (item.status === 'FINISHED')
    return 'Terminada'

  return 'Activa'
}

const { connected: sseConnected, reconnecting: sseReconnecting } = useRoomOperationalEvents(load)

onMounted(() => {
  load()
  pollTimer = setInterval(load, 30000)
})

onUnmounted(() => {
  if (pollTimer)
    clearInterval(pollTimer)
})

useOnContextChange(load)
</script>

<template>
  <div class="cleaning-shell">
    <NightPosSseBanner
      class="ma-2 mb-0"
      :connected="sseConnected"
      :reconnecting="sseReconnecting"
    />
    <CleaningMobileHeader title="Control de piezas" />

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-2"
    />

    <div
      v-else
      class="px-4"
    >
      <VCard
        v-if="earnings"
        class="cleaning-card mb-4"
        color="success"
        variant="tonal"
      >
        <VCardTitle class="text-subtitle-1">
          Mi pago del turno
        </VCardTitle>
        <VCardText>
          <div class="d-flex flex-wrap gap-4">
            <div>
              <div class="text-caption">
                Base
              </div>
              <div class="text-h6">
                {{ earnings.base_amount }} Bs
              </div>
            </div>
            <div>
              <div class="text-caption">
                Piezas limpias
              </div>
              <div class="text-h6">
                {{ earnings.rooms_cleaned }}
              </div>
            </div>
            <div>
              <div class="text-caption">
                Pago por pieza
              </div>
              <div class="text-h6">
                {{ earnings.room_amount }} Bs
              </div>
            </div>
            <div>
              <div class="text-caption">
                Total piezas
              </div>
              <div class="text-h6">
                {{ earnings.rooms_total }} Bs
              </div>
            </div>
            <div>
              <div class="text-caption">
                Total acumulado
              </div>
              <div class="text-h6 font-weight-bold">
                {{ earnings.total_accumulated }} Bs
              </div>
            </div>
          </div>
        </VCardText>
      </VCard>

      <div class="cleaning-kpi-grid mb-4">
        <VCard
          v-for="card in kpiCards"
          :key="card.key"
          class="cleaning-card"
          :color="card.color"
          variant="tonal"
        >
          <VCardText class="py-3">
            <div class="d-flex align-center gap-2">
              <VIcon :icon="card.icon" />
              <div>
                <div class="text-caption">
                  {{ card.title }}
                </div>
                <div class="text-h5 font-weight-bold">
                  {{ card.value }}
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>
      </div>

      <VAlert
        v-if="due.length"
        type="error"
        variant="tonal"
        class="mb-4"
        prominent
      >
        {{ due.length }} pieza(s) con tiempo cumplido. Revise la puerta.
      </VAlert>

      <div
        v-if="!allItems.length"
        class="text-center text-medium-emphasis py-8"
      >
        Sin piezas pendientes en este momento.
      </div>

      <VCard
        v-for="item in allItems"
        :key="`${item.section}-${item.id}`"
        class="cleaning-card mb-3"
        :color="item.section === 'due' ? 'error' : undefined"
        :variant="item.section === 'due' ? 'tonal' : 'elevated'"
      >
        <VCardText>
          <div class="d-flex justify-space-between align-start mb-2">
            <div>
              <div class="text-h6">
                {{ item.is_room ? item.code : (item.room_label || item.room_number || 'Pieza') }}
              </div>
              <div
                v-if="!item.is_room && item.girl_name"
                class="text-body-2"
              >
                {{ item.girl_name }}
              </div>
            </div>
            <VChip
              size="small"
              :color="item.section === 'due' ? 'error' : item.section === 'cleaning' ? 'warning' : 'info'"
              variant="tonal"
            >
              {{ statusLabel(item) }}
            </VChip>
          </div>

          <template v-if="!item.is_room">
            <div class="text-caption text-medium-emphasis mb-2">
              <div v-if="item.started_at">
                Inicio: {{ item.started_at }}
              </div>
              <div v-if="item.expected_ends_at">
                Fin estimado: {{ item.expected_ends_at }}
              </div>
              <div v-if="item.remaining_minutes != null && item.section === 'active'">
                Restante: {{ item.remaining_minutes }} min
              </div>
            </div>

            <div
              v-if="item.section === 'due'"
              class="d-flex flex-wrap gap-2"
            >
              <VBtn
                v-if="can('cleaning.check')"
                size="small"
                variant="tonal"
                :loading="isLoading(keyFor(item.id, 'check'))"
                :disabled="isLoading(keyFor(item.id, 'check'))"
                @click="onCheck(item)"
              >
                Tocar puerta / Revisado
              </VBtn>
              <VBtn
                v-if="can('cleaning.finish')"
                size="small"
                color="success"
                :loading="isLoading(keyFor(item.id, 'finish'))"
                :disabled="isLoading(keyFor(item.id, 'finish'))"
                @click="onFinish(item)"
              >
                Finalizar servicio
              </VBtn>
            </div>
          </template>

          <template v-else>
            <div class="text-caption text-medium-emphasis mb-2">
              <div v-if="item.last_finished_at">
                Finalizó: {{ item.last_finished_at }}
              </div>
            </div>
            <VBtn
              v-if="can('cleaning.mark_clean')"
              size="small"
              color="success"
              block
              :loading="isLoading(keyFor(item.id, 'clean'))"
              :disabled="isLoading(keyFor(item.id, 'clean'))"
              @click="onMarkClean(item)"
            >
              Marcar limpia
            </VBtn>
          </template>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>
