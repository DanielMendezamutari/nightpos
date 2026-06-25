<script setup>
import PrintableShiftClosureReport from '@/components/nightpos/print/PrintableShiftClosureReport.vue'
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
const loading = ref(true)

const ticketWidth = computed(() => route.query.width === '58' ? '58mm' : '80mm')

onMounted(async () => {
  try {
    const shiftId = Number(route.params.id)
    data.value = await fetchShiftSummary(shiftId)
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
    :managerial="data?.managerial"
    :tenant-name="data?.shift?.tenant_name || ''"
    :width="ticketWidth"
    :loading="loading"
  />
</template>
