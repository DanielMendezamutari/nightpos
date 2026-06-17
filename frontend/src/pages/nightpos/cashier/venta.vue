<script setup>
import DirectSalePage from '@/pages/nightpos/cash/direct-sale.vue'
import CashierShell from '@/components/nightpos/cashier/CashierShell.vue'

definePage({
  meta: {
    permission: 'sales.direct_create',
    layout: 'blank',
  },
})
</script>

<template>
  <CashierShell active-tab="venta" :show-pending="false">
    <div class="cashier-shell__page">
      <DirectSalePage />
    </div>
  </CashierShell>
</template>

<style scoped lang="scss">
@use '@styles/cashier-shell';
</style>
