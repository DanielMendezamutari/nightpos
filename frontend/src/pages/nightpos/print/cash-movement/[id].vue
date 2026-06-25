<script setup>
import { fetchCashMovement } from '@/api/cash'
import PrintableCashMovementTicket from '@/components/nightpos/print/PrintableCashMovementTicket.vue'
import { useNightPosPrint } from '@/composables/useNightPosPrint'

definePage({
  meta: {
    layout: 'blank',
    permission: 'cash.access',
  },
})

const route = useRoute()
const { triggerAutoPrint } = useNightPosPrint()
const loading = ref(true)
const movement = ref(null)
const cashierName = ref('')
const branchName = ref('')

onMounted(async () => {
  try {
    const data = await fetchCashMovement(Number(route.params.id))
    movement.value = data?.movement ?? null
    cashierName.value = data?.cashier_name ?? ''
    branchName.value = data?.branch_name ?? ''
  }
  finally {
    loading.value = false
    triggerAutoPrint()
  }
})
</script>

<template>
  <PrintableCashMovementTicket
    :movement="movement"
    :cashier-name="cashierName"
    :branch-name="branchName"
    :loading="loading"
  />
</template>
