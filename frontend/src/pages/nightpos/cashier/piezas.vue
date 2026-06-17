<script setup>
import CashierShell from '@/components/nightpos/cashier/CashierShell.vue'
import RoomServicesPage from '@/pages/nightpos/services/room-services/index.vue'

definePage({
  meta: {
    permissions: ['room_services.access', 'rooms.access'],
    layout: 'blank',
  },
})
</script>

<template>
  <CashierShell active-tab="piezas" :show-pending="false">
    <div class="cashier-shell__page">
      <RoomServicesPage />
    </div>
  </CashierShell>
</template>

<style scoped lang="scss">
@use '@styles/cashier-shell';
</style>
