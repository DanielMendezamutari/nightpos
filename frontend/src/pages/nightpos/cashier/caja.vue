<script setup>
import CashPage from '@/pages/nightpos/cash/index.vue'
import CashierShell from '@/components/nightpos/cashier/CashierShell.vue'

definePage({
  meta: {
    permission: 'cash.access',
    layout: 'blank',
  },
})
</script>

<template>
  <CashierShell active-tab="caja" :show-pending="false">
    <div class="cashier-shell__page">
      <CashPage />
    </div>
  </CashierShell>
</template>

<style scoped lang="scss">
@use '@styles/cashier-shell';
</style>
