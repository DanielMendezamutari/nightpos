<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { checkRoomService, fetchRoomControlOverview, finishRoomService } from '@/api/roomServices'
import { markRoomClean } from '@/api/rooms'
import { fetchUnreadNotificationCount } from '@/api/notifications'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { useFilteredServiceTabs } from '@/composables/useServiceSectionTabs'
import NightPosSseBanner from '@/components/nightpos/layout/NightPosSseBanner.vue'
import { useActionLoading } from '@/composables/useActionLoading'
import { useRoomDueAlerts } from '@/composables/useRoomDueAlerts'
import { useRoomOperationalEvents } from '@/composables/useRoomOperationalEvents'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'room_services.cleaning_view' } })

const serviceTabs = useFilteredServiceTabs()
const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const { isLoading, run, keyFor } = useActionLoading()
const { silenced, handleDueItems, markItemReviewed, toggleSilence } = useRoomDueAlerts()

const loading = ref(true)
const active = ref([])
const due = ref([])
const cleaning = ref([])
const finishedToday = ref([])
const unreadCount = ref(0)
let pollTimer = null

const serviceHeaders = [
  { title: 'Habitación', key: 'room_label' },
  { title: 'Chica', key: 'girl_name' },
  { title: 'Inicio', key: 'started_at' },
  { title: 'Fin estimado', key: 'expected_ends_at' },
  { title: 'Estado', key: 'status' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const cleaningHeaders = [
  { title: 'Código', key: 'code' },
  { title: 'Nombre', key: 'name' },
  { title: 'Finalizó', key: 'last_finished_at' },
  { title: 'Min. pendiente', key: 'minutes_since_finish' },
  { title: 'Acción', key: 'actions', sortable: false },
]

const statusColor = (item) => {
  if (item.is_due || item.status === 'DUE')
    return 'error'

  return item.status === 'FINISHED' ? 'success' : 'warning'
}

const load = async () => {
  try {
    const data = await fetchRoomControlOverview()
    const previousDueIds = due.value.map(i => i.id)

    active.value = data.active ?? []
    due.value = data.due ?? []
    cleaning.value = data.cleaning ?? []
    finishedToday.value = data.finished_today ?? []

    const newDue = due.value.filter(i => !previousDueIds.includes(i.id))

    if (newDue.length)
      handleDueItems(newDue)

    const countData = await fetchUnreadNotificationCount()

    unreadCount.value = countData.unread_count ?? 0
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const refresh = async () => {
  await load()
}

const onCheck = async item => {
  await run(keyFor(item.id, 'check'), async () => {
    try {
      await checkRoomService(item.id)
      markItemReviewed(item.id)
      notify('Pieza marcada como revisada')
      await refresh()
    }
    catch (error) {
      notify(getApiErrorMessage(error), 'error')
    }
  })
}

const onFinish = async item => {
  await run(keyFor(item.id, 'finish'), async () => {
    try {
      await finishRoomService(item.id)
      markItemReviewed(item.id)
      notify('Servicio finalizado. Habitación en limpieza.')
      await refresh()
    }
    catch (error) {
      notify(getApiErrorMessage(error), 'error')
    }
  })
}

const onMarkClean = async item => {
  if (!can('rooms.clean'))
    return

  await run(keyFor(item.id, 'clean'), async () => {
    try {
      await markRoomClean(item.id)
      notify('Habitación marcada como limpia')
      await refresh()
    }
    catch (error) {
      notify(getApiErrorMessage(error), 'error')
    }
  })
}

const { connected: sseConnected, reconnecting: sseReconnecting } = useRoomOperationalEvents(refresh)

onMounted(() => {
  load()
  pollTimer = setInterval(refresh, 30000)
})

onUnmounted(() => {
  if (pollTimer)
    clearInterval(pollTimer)
})

useOnContextChange(refresh)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Control de piezas"
      subtitle="Vista operativa: activas, tiempo cumplido, en limpieza y terminadas."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Servicios', to: { name: 'nightpos-services-bracelets' } },
        { title: 'Control de piezas', disabled: true },
      ]"
    >
      <template #actions>
        <VBadge
          v-if="due.length"
          :content="due.length"
          color="error"
          class="me-2"
        >
          <VChip
            color="error"
            variant="tonal"
          >
            Tiempo cumplido
          </VChip>
        </VBadge>
        <VBtn
          variant="tonal"
          :prepend-icon="silenced ? 'ri-volume-mute-line' : 'ri-volume-up-line'"
          @click="toggleSilence"
        >
          {{ silenced ? 'Silenciado' : 'Silenciar' }}
        </VBtn>
      </template>
    </NightPosPageHeader>

    <NightPosSectionTabs :tabs="serviceTabs" />

    <NightPosSseBanner
      :connected="sseConnected"
      :reconnecting="sseReconnecting"
    />

    <VAlert
      v-if="due.length"
      type="error"
      variant="tonal"
      class="mb-4"
      prominent
    >
      Hay <strong>{{ due.length }}</strong> pieza(s) con tiempo cumplido. Revise la puerta antes de finalizar.
      <span
        v-if="unreadCount"
        class="ms-2"
      >({{ unreadCount }} notificación(es) sin leer)</span>
    </VAlert>

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <template v-else>
      <VRow class="mb-4">
        <VCol
          cols="12"
          sm="6"
          md="3"
        >
          <CardStatisticsVertical
            title="Activas"
            :stats="String(active.length)"
            subtitle="En curso"
            color="info"
            icon="ri-time-line"
          />
        </VCol>
        <VCol
          cols="12"
          sm="6"
          md="3"
        >
          <CardStatisticsVertical
            title="Tiempo cumplido"
            :stats="String(due.length)"
            subtitle="Revisar puerta"
            color="error"
            icon="ri-alarm-warning-line"
          />
        </VCol>
        <VCol
          cols="12"
          sm="6"
          md="3"
        >
          <CardStatisticsVertical
            title="En limpieza"
            :stats="String(cleaning.length)"
            subtitle="Pendientes"
            color="warning"
            icon="ri-brush-line"
          />
        </VCol>
        <VCol
          cols="12"
          sm="6"
          md="3"
        >
          <CardStatisticsVertical
            title="Terminadas"
            :stats="String(finishedToday.length)"
            subtitle="Hoy"
            color="success"
            icon="ri-check-line"
          />
        </VCol>
      </VRow>

      <VCard
        v-if="due.length"
        class="mb-4"
        color="error"
        variant="tonal"
      >
        <VCardTitle>Tiempo cumplido — atención inmediata</VCardTitle>
        <VDataTable
          :headers="serviceHeaders"
          :items="due"
          :items-per-page="10"
          class="text-no-wrap"
        >
          <template #item.status="{ item }">
            <VChip
              size="small"
              :color="statusColor(item)"
              variant="tonal"
            >
              Tiempo cumplido
            </VChip>
          </template>
          <template #item.actions="{ item }">
            <VBtn
              v-if="can('room_services.check')"
              size="small"
              variant="text"
              :loading="isLoading(keyFor(item.id, 'check'))"
              :disabled="isLoading(keyFor(item.id, 'check'))"
              @click="onCheck(item)"
            >
              Revisado / tocar puerta
            </VBtn>
            <VBtn
              v-if="can('room_services.finish')"
              size="small"
              color="success"
              variant="text"
              :loading="isLoading(keyFor(item.id, 'finish'))"
              :disabled="isLoading(keyFor(item.id, 'finish'))"
              @click="onFinish(item)"
            >
              Finalizar servicio
            </VBtn>
          </template>
        </VDataTable>
      </VCard>

      <VCard
        v-if="cleaning.length"
        class="mb-4"
        color="warning"
        variant="tonal"
      >
        <VCardTitle>En limpieza</VCardTitle>
        <VDataTable
          :headers="cleaningHeaders"
          :items="cleaning"
          :items-per-page="10"
          class="text-no-wrap"
        >
          <template #item.actions="{ item }">
            <VBtn
              v-if="can('rooms.clean')"
              size="small"
              color="success"
              variant="text"
              :loading="isLoading(keyFor(item.id, 'clean'))"
              :disabled="isLoading(keyFor(item.id, 'clean'))"
              @click="onMarkClean(item)"
            >
              Marcar limpia
            </VBtn>
          </template>
        </VDataTable>
      </VCard>

      <VCard class="mb-4">
        <VCardTitle>Activas</VCardTitle>
        <VDataTable
          :headers="serviceHeaders"
          :items="active"
          :items-per-page="10"
          class="text-no-wrap"
        >
          <template #item.status="{ item }">
            <VChip
              size="small"
              color="info"
              variant="tonal"
            >
              Activa
            </VChip>
          </template>
        </VDataTable>
      </VCard>

      <VCard>
        <VCardTitle>Terminadas hoy</VCardTitle>
        <VDataTable
          :headers="serviceHeaders"
          :items="finishedToday"
          :items-per-page="10"
          class="text-no-wrap"
        >
          <template #item.status="{ item }">
            <VChip
              size="small"
              :color="statusColor(item)"
              variant="tonal"
            >
              Terminada
            </VChip>
          </template>
        </VDataTable>
      </VCard>
    </template>
  </div>
</template>
