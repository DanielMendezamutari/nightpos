<script setup>

import { fetchOrdersByScope } from '@/api/orders'

import { useOnContextChange } from '@/composables/useOnContextChange'

import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

import { formatMoney, orderStatusColor, orderStatusLabel } from '@/composables/useOrderHelpers'

import {

  ORDER_LIST_TABS,

  orderEmptyMessage,

  resolveOrderTab,

} from '@/composables/useOrderListTabs'

import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'



definePage({

  meta: {

    permission: 'orders.access',

  },

})



const { canAccessOrders } = useNightPosPermissions()

const router = useRouter()

const route = useRoute()



const orders = ref([])

const loading = ref(false)

const { notify } = useNightPosNotify()

const activeTab = ref(resolveOrderTab(route.query.tab))

const emptyMessage = computed(() => orderEmptyMessage(activeTab.value))



const loadOrders = async () => {

  loading.value = true



  try {

    const tab = ORDER_LIST_TABS.find(t => t.value === activeTab.value) ?? ORDER_LIST_TABS[0]



    orders.value = await fetchOrdersByScope(tab.scope)

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    loading.value = false

  }

}



const onTabChange = tab => {

  activeTab.value = tab

  router.replace({ query: { ...route.query, tab } })

  loadOrders()

}



watch(

  () => route.query.tab,

  tab => {

    const resolved = resolveOrderTab(tab)

    if (resolved !== activeTab.value) {

      activeTab.value = resolved

      loadOrders()

    }

  },

)



onMounted(loadOrders)

useOnContextChange(loadOrders)

</script>



<template>

  <div class="orders-page">

    <div class="orders-page__header mb-4">

      <div>

        <h4 class="text-h4 mb-1">

          Comandas

        </h4>

        <p class="mb-0 text-body-2">

          Comandas operativas de la sucursal actual.

        </p>

      </div>

      <VBtn

        v-if="canAccessOrders"

        color="primary"

        size="large"

        class="orders-page__new-btn"

        :to="{ name: 'nightpos-orders-new' }"

      >

        <VIcon

          icon="ri-add-line"

          start

        />

        Nueva comanda

      </VBtn>

    </div>



    <VTabs

      :model-value="activeTab"

      class="mb-4"

      @update:model-value="onTabChange"

    >

      <VTab

        v-for="tab in ORDER_LIST_TABS"

        :key="tab.value"

        :value="tab.value"

      >

        {{ tab.label }}

      </VTab>

    </VTabs>



    <VProgressLinear

      v-if="loading"

      indeterminate

      color="primary"

      class="mb-4"

    />



    <VAlert

      v-else-if="!orders.length"

      type="info"

      variant="tonal"

      class="mb-4"

    >

      {{ emptyMessage }}

      <template v-if="activeTab === 'operational_active' && canAccessOrders">

        Cree una nueva comanda para empezar.

      </template>

    </VAlert>



    <VRow v-else>

      <VCol

        v-for="order in orders"

        :key="order.id"

        cols="12"

        sm="6"

        lg="4"

      >

        <VCard

          class="order-card"

          @click="router.push({ name: 'nightpos-orders-id', params: { id: order.id } })"

        >

          <VCardText>

            <div class="d-flex justify-space-between align-start mb-2">

              <span class="text-h5 font-weight-bold">{{ order.order_number }}</span>

              <VChip

                :color="orderStatusColor(order.status)"

                size="small"

                label

              >

                {{ orderStatusLabel(order.status) }}

              </VChip>

            </div>

            <p class="mb-1 text-body-1">

              <VIcon

                icon="ri-table-line"

                size="18"

                class="me-1"

              />

              {{ order.table_label || 'Sin mesa' }}

            </p>

            <p

              v-if="order.waiter_name"

              class="mb-1 text-body-2 text-medium-emphasis"

            >

              Garzón: {{ order.waiter_name }}

            </p>

            <p class="mb-0 text-h6 text-primary">

              {{ formatMoney(order.total, order.currency) }}

            </p>

          </VCardText>

        </VCard>

      </VCol>

    </VRow>
</div>

</template>



<style scoped>

.orders-page__header {

  display: flex;

  flex-wrap: wrap;

  gap: 1rem;

  align-items: center;

  justify-content: space-between;

}



.orders-page__new-btn {

  min-block-size: 3rem;

  min-inline-size: 100%;

}



@media (min-width: 600px) {

  .orders-page__new-btn {

    min-inline-size: auto;

  }

}



.order-card {

  cursor: pointer;

  transition: box-shadow 0.2s ease;

}



.order-card:hover {

  box-shadow: 0 4px 18px rgb(0 0 0 / 12%);

}

</style>

