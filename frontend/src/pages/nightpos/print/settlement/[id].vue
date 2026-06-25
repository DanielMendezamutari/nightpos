<script setup>
import { fetchSettlement } from '@/api/settlements'
import PrintableSettlementTicket from '@/components/nightpos/print/PrintableSettlementTicket.vue'
import { useNightPosPrint } from '@/composables/useNightPosPrint'

definePage({
  meta: {
    layout: 'blank',
    permission: 'settlements.access',
  },
})

const route = useRoute()
const { triggerAutoPrint } = useNightPosPrint()
const loading = ref(true)
const settlement = ref(null)
const adjustments = ref([])

onMounted(async () => {
  try {
    const data = await fetchSettlement(Number(route.params.id))
    settlement.value = data?.settlement ?? null
    adjustments.value = data?.adjustments ?? []
  }
  finally {
    loading.value = false
    triggerAutoPrint()
  }
})
</script>

<template>
  <PrintableSettlementTicket
    :settlement="settlement"
    :adjustments="adjustments"
    :loading="loading"
    :is-reprint="Boolean(settlement?.print_count)"
    :reprint-number="settlement?.print_count || null"
    :reprinted-at="settlement?.last_printed_at"
  />
</template>
