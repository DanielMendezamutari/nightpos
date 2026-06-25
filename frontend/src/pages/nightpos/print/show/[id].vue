<script setup>
import { fetchShow } from '@/api/shows'
import PrintableShowTicket from '@/components/nightpos/print/PrintableShowTicket.vue'
import { useNightPosPrint } from '@/composables/useNightPosPrint'

definePage({
  meta: {
    layout: 'blank',
    permission: 'shows.access',
  },
})

const route = useRoute()
const { triggerAutoPrint } = useNightPosPrint()
const loading = ref(true)
const show = ref(null)

onMounted(async () => {
  try {
    const data = await fetchShow(Number(route.params.id))
    show.value = data?.show ?? data
  }
  finally {
    loading.value = false
    triggerAutoPrint()
  }
})
</script>

<template>
  <PrintableShowTicket
    :show="show"
    :loading="loading"
  />
</template>
