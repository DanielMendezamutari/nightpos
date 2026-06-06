<script setup>
import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'
import { useServiceCashSession } from '@/composables/useServiceCashSession'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { formatMoney } from '@/composables/useOrderHelpers'

defineProps({
  emphasizePayRequirement: { type: Boolean, default: false },
})

const emit = defineEmits(['cash-opened'])

const { can } = useNightPosPermissions()
const {
  cashSession,
  cashSessionOpen,
  showOpenCash,
  loadingCash,
  onCashOpened,
} = useServiceCashSession()

const openCashDialog = () => {
  showOpenCash.value = true
}

const handleCashOpened = async session => {
  await onCashOpened(session)
  emit('cash-opened', session)
}

defineExpose({
  cashSessionOpen,
  cashSession,
  loadingCash,
  openCashDialog,
  reload: onCashOpened,
})
</script>

<template>
  <VProgressLinear
    v-if="loadingCash"
    indeterminate
    class="mb-4"
  />

  <VAlert
    v-else-if="cashSessionOpen && cashSession"
    type="success"
    variant="tonal"
    class="mb-4"
  >
    <div class="d-flex flex-wrap align-center justify-space-between gap-2">
      <div>
        <strong>Caja abierta</strong>
        <span class="text-medium-emphasis ms-2">
          Fondo inicial {{ formatMoney(cashSession.opening_amount) }} BOB
          <template v-if="cashSession.expected_amount != null">
            · Esperado {{ formatMoney(cashSession.expected_amount) }} BOB
          </template>
        </span>
      </div>
      <VBtn
        v-if="can('cash.access')"
        size="small"
        variant="text"
        :to="{ name: 'nightpos-cash' }"
      >
        Ir a caja
      </VBtn>
    </div>
  </VAlert>

  <VAlert
    v-else
    :type="emphasizePayRequirement ? 'warning' : 'info'"
    variant="tonal"
    class="mb-4"
    :prominent="emphasizePayRequirement"
  >
    <template v-if="emphasizePayRequirement">
      Debe abrir caja para pagar liquidaciones (genera egreso de caja).
    </template>
    <template v-else>
      Sin caja abierta para su usuario. Ver liquidaciones no requiere caja; pagar sí.
    </template>
    <div
      v-if="can('admin.cash_sessions.list')"
      class="text-caption mt-2"
    >
      Para pagar se requiere tu caja abierta. Para revisar otras cajas usa
      <RouterLink :to="{ name: 'nightpos-finance-cash-sessions' }">
        Fiscalización de cajas
      </RouterLink>.
    </div>
    <VBtn
      v-if="can('cash.access')"
      class="ms-0 ms-sm-2 mt-2 mt-sm-0"
      size="small"
      color="primary"
      @click="openCashDialog"
    >
      Abrir caja ahora
    </VBtn>
  </VAlert>

  <QuickOpenCashDialog
    v-model="showOpenCash"
    @opened="handleCashOpened"
  />
</template>
