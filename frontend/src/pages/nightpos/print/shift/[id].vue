<script setup>
import PrintableShiftClosureReport from '@/components/nightpos/print/PrintableShiftClosureReport.vue'
import { fetchProductReconciliation } from '@/api/reports'
import { fetchShiftSummary } from '@/api/shifts'
import { useNightPosPrint } from '@/composables/useNightPosPrint'

definePage({
  meta: {
    layout: 'blank',
    permission: 'shifts.list',
  },
})

const route = useRoute()
const { triggerAutoPrint } = useNightPosPrint()
const data = ref(null)
const reconciliation = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const shiftId = Number(route.params.id)

    data.value = await fetchShiftSummary(shiftId)
    reconciliation.value = await fetchProductReconciliation({ officialShiftId: shiftId }).catch(() => null)
  }
  finally {
    loading.value = false
    triggerAutoPrint()
  }
})
</script>

<template>
  <PrintableShiftClosureReport
    :shift="data?.shift"
    :summary="data?.summary"
    :reconciliation="reconciliation"
    :loading="loading"
  />
</template>
