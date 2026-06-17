<script setup>
import CashierAccountSection from '@/components/nightpos/cashier/CashierAccountSection.vue'
import { useCashierAccount } from '@/composables/useCashierAccount'

defineProps({
  cashSessionOpen: { type: Boolean, default: false },
  pendingCount: { type: Number, default: 0 },
  pendingTotalBob: { type: String, default: '0.00' },
  sseConnected: { type: Boolean, default: false },
  sseReconnecting: { type: Boolean, default: false },
  showPending: { type: Boolean, default: true },
})

const { displayName } = useCashierAccount()
const showAccountMenu = ref(false)
</script>

<template>
  <div class="cashier-status-bar d-flex flex-wrap align-center gap-2 py-2 px-3">
    <VChip
      :color="cashSessionOpen ? 'success' : 'warning'"
      variant="tonal"
      size="small"
      prepend-icon="ri-safe-2-line"
    >
      {{ cashSessionOpen ? 'Caja abierta' : 'Caja cerrada' }}
    </VChip>

    <VChip
      v-if="showPending"
      color="primary"
      variant="tonal"
      size="small"
      prepend-icon="ri-bank-card-line"
    >
      {{ pendingCount }} pend. · {{ pendingTotalBob }} Bs
    </VChip>

    <VChip
      v-if="sseReconnecting"
      color="warning"
      variant="tonal"
      size="small"
      prepend-icon="ri-refresh-line"
    >
      Reconectando…
    </VChip>
    <VChip
      v-else-if="sseConnected"
      color="success"
      variant="tonal"
      size="small"
      prepend-icon="ri-wifi-line"
    >
      En vivo
    </VChip>
    <VChip
      v-else
      color="secondary"
      variant="tonal"
      size="small"
      prepend-icon="ri-wifi-off-line"
    >
      Sin tiempo real
    </VChip>

    <VSpacer class="d-none d-md-flex" />

    <VMenu
      v-model="showAccountMenu"
      location="bottom end"
      class="d-none d-md-block"
    >
      <template #activator="{ props: menuProps }">
        <VBtn
          v-bind="menuProps"
          variant="text"
          size="small"
          class="cashier-status-bar__account d-none d-md-flex"
          append-icon="ri-arrow-down-s-line"
          prepend-icon="ri-user-3-line"
        >
          {{ displayName }}
        </VBtn>
      </template>

      <VCard min-width="240">
        <CashierAccountSection variant="compact" />
      </VCard>
    </VMenu>
  </div>
</template>

<style scoped>
.cashier-status-bar {
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  background: rgb(var(--v-theme-surface));
}

.cashier-status-bar__account {
  text-transform: none;
  font-weight: 500;
}
</style>
