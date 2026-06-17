<script setup>
import { fetchOrderPrecheck } from '@/api/orders'
import PrintablePrecheckTicket from '@/components/nightpos/print/PrintablePrecheckTicket.vue'
import { useNightPosPrint } from '@/composables/useNightPosPrint'

definePage({
  meta: {
    permission: 'orders.access',
    layout: 'blank',
  },
})

const route = useRoute()
const { triggerAutoPrint } = useNightPosPrint()
const loading = ref(true)
const precheck = ref(null)

onMounted(async () => {
  try {
    precheck.value = await fetchOrderPrecheck(route.params.id)
  }
  finally {
    loading.value = false
    triggerAutoPrint()
  }
})
</script>

<template>
  <PrintablePrecheckTicket
    :precheck="precheck"
    :loading="loading"
  />
</template>
