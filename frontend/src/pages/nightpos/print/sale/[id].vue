<script setup>
import PrintableSaleTicket from '@/components/nightpos/print/PrintableSaleTicket.vue'
import { fetchSale } from '@/api/sales'
import { useNightPosPrint } from '@/composables/useNightPosPrint'

definePage({
  meta: {
    layout: 'blank',
    permission: 'sales.list',
  },
})

const route = useRoute()
const { triggerAutoPrint } = useNightPosPrint()
const sale = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    sale.value = await fetchSale(Number(route.params.id))
  }
  finally {
    loading.value = false
    triggerAutoPrint()
  }
})
</script>

<template>
  <PrintableSaleTicket
    :sale="sale"
    :loading="loading"
    :cashier-name="sale?.cashier_name || ''"
    :waiter-name="sale?.waiter_name || ''"
  />
</template>
