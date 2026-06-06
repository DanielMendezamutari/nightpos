<script setup>
import WaiterBottomNav from '@/components/nightpos/waiter/WaiterBottomNav.vue'
import WaiterKpiCard from '@/components/nightpos/waiter/WaiterKpiCard.vue'
import WaiterMobileHeader from '@/components/nightpos/waiter/WaiterMobileHeader.vue'
import WaiterOrderActions from '@/components/nightpos/waiter/WaiterOrderActions.vue'
import WaiterOrderCard from '@/components/nightpos/waiter/WaiterOrderCard.vue'
import { fetchWaiterDashboard } from '@/api/waiter'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    layout: 'blank',
    permission: 'waiter.dashboard',
  },
})

const router = useRouter()
const { notify } = useNightPosNotify()
const loading = ref(true)
const dashboard = ref({ cards: {}, recent_orders: [] })

const kpiCards = computed(() => [
  {
    key: 'new',
    title: 'Nueva comanda',
    subtitle: 'Abrir mesa',
    value: '',
    icon: 'ri-add-circle-line',
    color: 'primary',
    highlight: true,
    to: { name: 'nightpos-waiter-orders-new' },
  },
  {
    key: 'open',
    title: 'Abiertas',
    subtitle: 'Sin enviar',
    value: dashboard.value.cards?.open_orders ?? 0,
    icon: 'ri-file-list-3-line',
    color: 'info',
    scope: 'open',
  },
  {
    key: 'bar',
    title: 'En barra',
    subtitle: 'Preparación',
    value: dashboard.value.cards?.sent_to_bar ?? 0,
    icon: 'ri-goblet-line',
    color: 'warning',
    scope: 'sent_to_bar',
  },
  ...(Number(dashboard.value.cards?.pending_charge ?? 0) > 0
    ? [{
        key: 'charge',
        title: 'Pendientes cobro',
        subtitle: 'Listas en barra',
        value: dashboard.value.cards.pending_charge,
        icon: 'ri-money-dollar-circle-line',
        color: 'success',
        scope: 'pending_charge',
      }]
    : []),
])

const load = async () => {
  loading.value = true
  try {
    dashboard.value = await fetchWaiterDashboard()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const onKpiClick = card => {
  if (card.to)
    router.push(card.to)
  else if (card.scope)
    router.push({ name: 'nightpos-waiter-orders', query: { scope: card.scope } })
}

onMounted(load)
</script>

<template>
  <div class="waiter-shell">
    <WaiterMobileHeader />

    <VContainer class="py-4 px-4">
      <VProgressLinear
        v-if="loading"
        indeterminate
        class="mb-4"
      />

      <div class="waiter-kpi-grid mb-6">
        <WaiterKpiCard
          v-for="card in kpiCards"
          :key="card.key"
          :title="card.title"
          :subtitle="card.subtitle"
          :value="card.value"
          :icon="card.icon"
          :color="card.color"
          :highlight="card.highlight"
          @click="onKpiClick(card)"
        />
      </div>

      <h2 class="text-subtitle-1 font-weight-bold mb-3">
        Recientes
      </h2>

      <VAlert
        v-if="!loading && !dashboard.recent_orders?.length"
        type="info"
        variant="tonal"
      >
        Abre una comanda para empezar.
      </VAlert>

      <WaiterOrderCard
        v-for="order in dashboard.recent_orders"
        :key="order.id"
        :order="order"
      >
        <WaiterOrderActions :order="order" />
      </WaiterOrderCard>
    </VContainer>

    <WaiterBottomNav />
</div>
</template>

<style scoped lang="scss">
@use '@styles/waiter-mobile';

.waiter-kpi-grid {
  display: grid;
  gap: 12px;
  grid-template-columns: 1fr;
}

@media (min-width: 400px) {
  .waiter-kpi-grid {
    grid-template-columns: 1fr 1fr;
  }

  .waiter-kpi-grid > :first-child {
    grid-column: 1 / -1;
  }
}
</style>
