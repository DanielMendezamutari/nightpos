<script setup>
import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'
import CashierBottomNav from '@/components/nightpos/cashier/CashierBottomNav.vue'
import CashierDesktopNav from '@/components/nightpos/cashier/CashierDesktopNav.vue'
import CashierStatusBar from '@/components/nightpos/cashier/CashierStatusBar.vue'
import InstallPwaBanner from '@/components/nightpos/layout/InstallPwaBanner.vue'
import OfflineBanner from '@/components/nightpos/layout/OfflineBanner.vue'
import { useCashierShell } from '@/composables/useCashierShell'

defineProps({
  activeTab: {
    type: String,
    default: 'cobrar',
  },
  showPending: {
    type: Boolean,
    default: true,
  },
})

const {
  cashSessionOpen,
  pendingCount,
  pendingTotalBob,
  showOpenCash,
  refresh,
  openCashDialog,
  sseConnected,
  sseReconnecting,
} = useCashierShell()

const onCashOpened = async () => {
  await refresh()
}
</script>

<template>
  <div class="cashier-shell">
    <CashierStatusBar
      :cash-session-open="cashSessionOpen"
      :pending-count="pendingCount"
      :pending-total-bob="pendingTotalBob"
      :sse-connected="sseConnected"
      :sse-reconnecting="sseReconnecting"
      :show-pending="showPending"
    />

    <VAlert
      v-if="!cashSessionOpen"
      type="warning"
      variant="tonal"
      density="compact"
      class="ma-3 mb-0"
      prominent
    >
      <div class="d-flex flex-wrap align-center justify-space-between gap-2 w-100">
        <span class="font-weight-medium">Caja cerrada — abra caja para cobrar y registrar movimientos.</span>
        <VBtn
          color="warning"
          variant="flat"
          size="small"
          prepend-icon="ri-lock-unlock-line"
          @click="openCashDialog"
        >
          Abrir caja
        </VBtn>
      </div>
    </VAlert>

    <CashierDesktopNav :active-tab="activeTab" />

    <VContainer class="cashier-shell__content px-4">
      <InstallPwaBanner
        context="cashier"
        class="mb-2"
      />
      <OfflineBanner class="mb-2" />
      <slot />
    </VContainer>

    <CashierBottomNav :active-tab="activeTab" />

    <QuickOpenCashDialog
      v-model="showOpenCash"
      @opened="onCashOpened"
    />
  </div>
</template>

<style scoped lang="scss">
@use '@styles/cashier-shell';
</style>
