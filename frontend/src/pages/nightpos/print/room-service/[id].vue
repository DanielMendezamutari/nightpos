<script setup>
import { fetchRoomService } from '@/api/roomServices'
import PrintableRoomServiceTicket from '@/components/nightpos/print/PrintableRoomServiceTicket.vue'
import { useNightPosPrint } from '@/composables/useNightPosPrint'

definePage({
  meta: {
    layout: 'blank',
    permission: 'room_services.access',
  },
})

const route = useRoute()
const { triggerAutoPrint } = useNightPosPrint()
const loading = ref(true)
const roomService = ref(null)

onMounted(async () => {
  try {
    const data = await fetchRoomService(Number(route.params.id))
    roomService.value = data?.room_service ?? data
  }
  finally {
    loading.value = false
    triggerAutoPrint()
  }
})
</script>

<template>
  <PrintableRoomServiceTicket
    :room-service="roomService"
    :loading="loading"
  />
</template>
