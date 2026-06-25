<script setup>
import PrintableOrderTicket from '@/components/nightpos/print/PrintableOrderTicket.vue'
import { fetchOrder } from '@/api/orders'
import { useNightPosPrint } from '@/composables/useNightPosPrint'

definePage({
  meta: {
    layout: 'blank',
    permission: 'orders.access',
  },
})

const route = useRoute()
const { triggerAutoPrint } = useNightPosPrint()
const order = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    order.value = await fetchOrder(Number(route.params.id))
  }
  finally {
    loading.value = false
    triggerAutoPrint()
  }
})
</script>

<template>
  <PrintableOrderTicket
    :order="order"
    :loading="loading"
    :waiter-name="order?.waiter_name || ''"
    :service-area-name="order?.service_area_name || ''"
  />
</template>
