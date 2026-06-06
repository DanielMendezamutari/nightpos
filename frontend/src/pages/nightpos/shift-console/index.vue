<script setup>
import CardStatisticsVertical from '@core/components/cards/CardStatisticsVertical.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchShiftConsoleCurrent } from '@/api/shiftConsole'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { useRoomDueAlerts } from '@/composables/useRoomDueAlerts'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import NightPosSseBanner from '@/components/nightpos/layout/NightPosSseBanner.vue'
import { useOperationalEvents } from '@/composables/useOperationalEvents'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'shift_console.access' } })

const { notify } = useNightPosNotify()
const { handleDueItems } = useRoomDueAlerts()
const { canDirectSale, canChargeOrders, canAccessCash, canAccessSettlements } = useNightPosPermissions()
const router = useRouter()

const loading = ref(true)
const data = ref(null)
let pollTimer = null
const lastDueCount = ref(0)

const cards = computed(() => {
  const c = data.value?.cards ?? {}
  const shift = data.value?.shift

  return [
    {
      title: 'Turno actual',
      color: c.shift_open ? 'info' : 'secondary',
      icon: 'ri-time-line',
      stats: shift ? shift.shift_type_label : 'Sin turno',
      subtitle: shift ? `${shift.name} · ${shift.business_date}` : 'Abrirá con la primera operación',
    },
    {
      title: 'Caja',
      color: c.cash_open ? 'success' : 'secondary',
      icon: 'ri-cash-line',
      stats: c.cash_open ? 'Abierta' : 'Cerrada',
      subtitle: data.value?.cash_session?.cashier_name ?? 'Sin sesión',
      to: 'nightpos-cash',
    },
    {
      title: 'Comandas activas',
      color: 'primary',
      icon: 'ri-restaurant-line',
      stats: String(c.active_orders ?? c.open_orders ?? 0),
      subtitle: `Abiertas: ${c.open_orders ?? 0} · En barra: ${c.sent_to_bar_orders ?? 0}`,
      to: { name: 'nightpos-orders', query: { tab: 'operational_active' } },
    },
    {
      title: 'En barra',
      color: 'info',
      icon: 'ri-goblet-line',
      stats: String(c.sent_to_bar_orders ?? 0),
      subtitle: 'Enviadas a preparación',
      to: { name: 'nightpos-orders', query: { tab: 'sent_to_bar' } },
    },
    ...(Number(c.pending_charge_orders ?? 0) > 0 ? [{
      title: 'Pendientes cobro',
      color: 'warning',
      icon: 'ri-bank-card-line',
      stats: String(c.pending_charge_orders),
      subtitle: 'Listas para caja',
      to: { name: 'nightpos-orders', query: { tab: 'pending_charge' } },
    }] : []),
    {
      title: 'Habitaciones ocupadas',
      color: 'warning',
      icon: 'ri-user-line',
      stats: String(c.occupied_rooms ?? 0),
      subtitle: 'Con pieza activa',
      to: 'nightpos-rooms-list',
    },
    {
      title: 'En limpieza',
      color: 'info',
      icon: 'ri-brush-line',
      stats: String(c.cleaning_rooms ?? 0),
      subtitle: 'Pendientes de liberar',
      to: 'nightpos-rooms-cleaning',
    },
    {
      title: 'Piezas vencidas',
      color: c.due_room_services > 0 ? 'error' : 'success',
      icon: 'ri-alarm-warning-line',
      stats: String(c.due_room_services ?? 0),
      subtitle: 'Requieren revisión',
      to: 'nightpos-services-room-control',
      badge: c.due_room_services > 0,
    },
    {
      title: 'Liquidaciones pend.',
      color: 'warning',
      icon: 'ri-wallet-3-line',
      stats: String(c.pending_settlements ?? 0),
      subtitle: `${data.value?.settlements_summary?.pending_amount ?? '0.00'} BOB`,
      to: 'nightpos-settlements',
    },
  ]
})

const orderHeaders = [
  { title: 'Nº', key: 'order_number' },
  { title: 'Mesa', key: 'table_label' },
  { title: 'Estado', key: 'status' },
  { title: 'Total', key: 'total', align: 'end' },
]

const alertColor = severity => {
  if (severity === 'warning')
    return 'warning'
  if (severity === 'error')
    return 'error'

  return 'info'
}

const load = async () => {
  try {
    const payload = await fetchShiftConsoleCurrent()
    const dueCount = payload?.cards?.due_room_services ?? 0

    if (dueCount > lastDueCount.value && lastDueCount.value > 0) {
      handleDueItems([{ id: `due-${dueCount}` }])
    }

    lastDueCount.value = dueCount
    data.value = payload
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const refresh = async () => {
  loading.value = true
  await load()
}

// ─── SSE real-time ──────────────────────────────────────────────────────────
const { on, start: startSse, stop: stopSse, connected: sseConnected, reconnecting: sseReconnecting } = useOperationalEvents()

let consoleDebounce = null
const debouncedLoad = () => {
  clearTimeout(consoleDebounce)
  consoleDebounce = setTimeout(load, 600)
}

on('order.created', debouncedLoad)
on('order.billed', debouncedLoad)
on('order.cancelled', debouncedLoad)
on('sale.created', debouncedLoad)
on('direct_sale.created', debouncedLoad)
on('cash.movement.created', debouncedLoad)
on('cash.session.opened', debouncedLoad)
on('cash.session.closed', debouncedLoad)
on('settlement.generated', debouncedLoad)
on('settlement.paid', debouncedLoad)
on('room_service.created', debouncedLoad)
on('room_service.finished', debouncedLoad)
// ─────────────────────────────────────────────────────────────────────────────

onMounted(async () => {
  await load()
  pollTimer = setInterval(load, 30000)
  startSse()
})

onUnmounted(() => {
  if (pollTimer)
    clearInterval(pollTimer)
  stopSse()
})

useOnContextChange(refresh)
</script>

<template>
  <div class="shift-console">
    <NightPosPageHeader
      title="Consola de turno"
      subtitle="Vista operativa del turno actual: caja, comandas, habitaciones, servicios y alertas."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Operación', disabled: true },
        { title: 'Consola de turno', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          variant="tonal"
          prepend-icon="ri-refresh-line"
          :loading="loading"
          @click="refresh"
        >
          Actualizar
        </VBtn>
      </template>
    </NightPosPageHeader>

    <NightPosSseBanner
      :connected="sseConnected"
      :reconnecting="sseReconnecting"
    />

    <VProgressLinear
      v-if="loading && !data"
      indeterminate
      class="mb-4"
    />

    <template v-if="data">
      <!-- Accesos rápidos operativos -->
      <VCard class="mb-4">
        <VCardText class="py-3">
          <div class="text-overline text-medium-emphasis mb-2">
            Accesos rápidos
          </div>
          <div class="d-flex flex-wrap gap-2">
            <VBtn
              v-if="canChargeOrders"
              color="primary"
              variant="elevated"
              prepend-icon="ri-bank-card-line"
              :to="{ name: 'nightpos-cashier-orders' }"
            >
              Cobrar comandas
            </VBtn>
            <VBtn
              v-if="canDirectSale"
              color="success"
              variant="elevated"
              prepend-icon="ri-shopping-cart-line"
              :to="{ name: 'nightpos-cash-direct-sale' }"
            >
              Venta directa
            </VBtn>
            <VBtn
              v-if="canAccessCash"
              variant="tonal"
              prepend-icon="ri-safe-2-line"
              :to="{ name: 'nightpos-cash' }"
            >
              Mi caja
            </VBtn>
            <VBtn
              variant="tonal"
              prepend-icon="ri-service-line"
              :to="{ name: 'nightpos-services-bracelets' }"
            >
              Servicios
            </VBtn>
            <VBtn
              variant="tonal"
              prepend-icon="ri-building-2-line"
              :to="{ name: 'nightpos-rooms-list' }"
            >
              Habitaciones
            </VBtn>
            <VBtn
              v-if="canAccessSettlements"
              variant="tonal"
              prepend-icon="ri-wallet-3-line"
              :to="{ name: 'nightpos-settlements' }"
            >
              Liquidaciones
            </VBtn>
          </div>
        </VCardText>
      </VCard>

      <VRow class="mb-4">
        <VCol
          v-for="card in cards"
          :key="card.title"
          cols="12"
          sm="6"
          md="4"
          lg="3"
        >
          <RouterLink
            v-if="card.to"
            :to="typeof card.to === 'string' ? { name: card.to } : card.to"
            class="text-decoration-none"
          >
            <VBadge
              :model-value="card.badge"
              color="error"
              dot
              floating
            >
              <CardStatisticsVertical v-bind="card" />
            </VBadge>
          </RouterLink>
          <CardStatisticsVertical
            v-else
            v-bind="card"
          />
        </VCol>
      </VRow>

      <VRow>
        <VCol
          cols="12"
          lg="6"
        >
          <VCard class="mb-4">
            <VCardTitle class="d-flex align-center gap-2">
              <VIcon icon="ri-cash-line" />
              Caja
            </VCardTitle>
            <VCardText>
              <VAlert
                v-if="!data.cash_session"
                type="info"
                variant="tonal"
                class="mb-3"
              >
                No hay caja abierta para su usuario.
                <VBtn
                  class="ms-2"
                  size="small"
                  variant="tonal"
                  :to="{ name: 'nightpos-cash' }"
                >
                  Ir a caja
                </VBtn>
              </VAlert>
              <template v-else>
                <div class="text-body-2 mb-2">
                  Cajera: <strong>{{ data.cash_session.cashier_name }}</strong>
                </div>
                <VChip
                  class="me-2 mb-2"
                  color="success"
                  size="small"
                >
                  Apertura: {{ data.cash_totals.opening_amount }} BOB
                </VChip>
                <VChip
                  class="me-2 mb-2"
                  size="small"
                >
                  Efectivo: {{ data.cash_totals.cash }} BOB
                </VChip>
                <VChip
                  class="me-2 mb-2"
                  size="small"
                >
                  QR: {{ data.cash_totals.qr }} BOB
                </VChip>
                <VChip
                  class="me-2 mb-2"
                  size="small"
                >
                  Tarjeta: {{ data.cash_totals.card }} BOB
                </VChip>
                <div class="text-h6 mt-2">
                  Total cobrado: {{ data.cash_totals.total_collected }} BOB
                </div>
                <div class="text-caption">
                  Esperado en caja: {{ data.cash_totals.expected_amount }} BOB
                </div>
              </template>
              <div
                v-if="data.open_cash_sessions?.length"
                class="mt-4"
              >
                <div class="text-subtitle-2 mb-2">
                  Cajas abiertas en sucursal
                </div>
                <VChip
                  v-for="session in data.open_cash_sessions"
                  :key="session.id"
                  class="me-2 mb-2"
                  :color="session.is_current_user ? 'success' : 'warning'"
                  size="small"
                >
                  {{ session.cashier_name }} · esp. {{ session.expected_amount ?? '—' }} BOB
                </VChip>
              </div>
            </VCardText>
          </VCard>

          <VCard class="mb-4">
            <VCardTitle class="d-flex align-center gap-2">
              <VIcon icon="ri-hotel-bed-line" />
              Habitaciones
            </VCardTitle>
            <VCardText>
              <div class="d-flex flex-wrap gap-2">
                <VChip color="success">
                  Disponibles: {{ data.rooms_summary.available ?? 0 }}
                </VChip>
                <VChip color="warning">
                  Ocupadas: {{ data.rooms_summary.occupied ?? 0 }}
                </VChip>
                <VChip color="info">
                  Limpieza: {{ data.rooms_summary.cleaning ?? 0 }}
                </VChip>
                <VChip color="error">
                  Mantenimiento: {{ data.rooms_summary.maintenance ?? 0 }}
                </VChip>
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <VCol
          cols="12"
          lg="6"
        >
          <VCard class="mb-4">
            <VCardTitle class="d-flex align-center gap-2">
              <VIcon icon="ri-alarm-warning-line" />
              Alertas
            </VCardTitle>
            <VCardText>
              <VAlert
                v-if="!data.alerts?.length"
                type="success"
                variant="tonal"
              >
                Sin alertas operativas en este momento.
              </VAlert>
              <VAlert
                v-for="(alert, idx) in data.alerts"
                :key="idx"
                :type="alertColor(alert.severity)"
                variant="tonal"
                class="mb-2"
              >
                {{ alert.message }}
                <div
                  v-if="alert.type === 'room_services_due'"
                  class="mt-2"
                >
                  <VBtn
                    size="small"
                    color="error"
                    variant="tonal"
                    :to="{ name: 'nightpos-services-room-control' }"
                  >
                    Control de piezas
                  </VBtn>
                </div>
              </VAlert>
            </VCardText>
          </VCard>

          <VCard class="mb-4">
            <VCardTitle class="d-flex align-center gap-2">
              <VIcon icon="ri-gem-line" />
              Servicios del turno
            </VCardTitle>
            <VCardText>
              <VChip class="me-2 mb-2">
                Manillas: {{ data.services_summary.bracelets_count }} ({{ data.services_summary.bracelets_total }} BOB)
              </VChip>
              <VChip class="me-2 mb-2">
                Piezas activas: {{ data.services_summary.active_room_services_count }}
              </VChip>
              <VChip class="mb-2">
                Shows: {{ data.services_summary.shows_count }} ({{ data.services_summary.shows_total }} BOB)
              </VChip>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <VCard>
        <VCardTitle class="d-flex align-center gap-2">
          <VIcon icon="ri-restaurant-line" />
          Comandas
        </VCardTitle>
        <VCardText>
          <VTabs>
            <VTab value="open">
              Abiertas ({{ data.orders_summary.counts.open }})
            </VTab>
            <VTab value="bar">
              En barra ({{ data.orders_summary.counts.sent_to_bar }})
            </VTab>
            <VTab value="charge">
              Pend. cobro ({{ data.orders_summary.counts.pending_charge }})
            </VTab>
            <VTab value="billed">
              Cobradas recientes
            </VTab>
          </VTabs>
          <VWindow class="mt-4">
            <VWindowItem value="open">
              <VDataTable
                :headers="orderHeaders"
                :items="data.orders_summary.open"
                density="compact"
                :items-per-page="5"
                @click:row="(_, { item }) => router.push({ name: 'nightpos-orders-id', params: { id: item.id } })"
              />
            </VWindowItem>
            <VWindowItem value="bar">
              <VDataTable
                :headers="orderHeaders"
                :items="data.orders_summary.sent_to_bar"
                density="compact"
                :items-per-page="5"
                @click:row="(_, { item }) => router.push({ name: 'nightpos-orders-id', params: { id: item.id } })"
              />
            </VWindowItem>
            <VWindowItem value="charge">
              <VDataTable
                :headers="orderHeaders"
                :items="data.orders_summary.pending_charge"
                density="compact"
                :items-per-page="5"
                @click:row="(_, { item }) => router.push({ name: 'nightpos-orders-id', params: { id: item.id } })"
              />
            </VWindowItem>
            <VWindowItem value="billed">
              <VDataTable
                :headers="orderHeaders"
                :items="data.orders_summary.recent_billed"
                density="compact"
                :items-per-page="5"
              />
            </VWindowItem>
          </VWindow>
        </VCardText>
      </VCard>
    </template>
  </div>
</template>

<style scoped>
.shift-console :deep(.v-data-table tbody tr) {
  cursor: pointer;
}
</style>
