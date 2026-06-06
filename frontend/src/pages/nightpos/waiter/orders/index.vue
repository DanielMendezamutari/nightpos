<script setup>
import WaiterBottomNav from '@/components/nightpos/waiter/WaiterBottomNav.vue'
import WaiterMobileHeader from '@/components/nightpos/waiter/WaiterMobileHeader.vue'
import WaiterOrderActions from '@/components/nightpos/waiter/WaiterOrderActions.vue'
import WaiterOrderCard from '@/components/nightpos/waiter/WaiterOrderCard.vue'
import { fetchWaiterOrders } from '@/api/waiter'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    layout: 'blank',
    permission: 'waiter.orders',
  },
})

const route = useRoute()
const { notify } = useNightPosNotify()

const scope = computed(() => route.query.scope || 'active')
const loading = ref(false)
const orders = ref([])

const scopeTitle = computed(() => ({
  active: 'Activas',
  open: 'Abiertas',
  sent_to_bar: 'En barra',
  pending_charge: 'Pendientes cobro',
}[scope.value] || 'Comandas'))

const load = async () => {
  loading.value = true
  try {
    orders.value = await fetchWaiterOrders(scope.value)
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

watch(scope, load)
onMounted(load)
</script>

<template>
  <div class="waiter-shell">
    <WaiterMobileHeader
      :title="scopeTitle"
      show-back
    />

    <VContainer class="py-4 px-4">
      <VProgressLinear
        v-if="loading"
        indeterminate
        class="mb-4"
      />
      <VAlert
        v-else-if="!orders.length"
        type="info"
        variant="tonal"
      >
        No hay comandas en esta lista.
      </VAlert>
      <WaiterOrderCard
        v-for="order in orders"
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
</style>
