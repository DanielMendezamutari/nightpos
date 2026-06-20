<script setup>

import SettlementsCashBanner from '@/components/nightpos/settlements/SettlementsCashBanner.vue'
import CashMovementDialog from '@/components/nightpos/cash/CashMovementDialog.vue'
import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'

import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'

import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'

import { generateCurrentShiftSettlements } from '@/api/settlements'

import { useCurrentShiftSettlements } from '@/composables/useCurrentShiftSettlements'

import { useSettlementPendingSources } from '@/composables/useSettlementPendingSources'

import { useFilteredSettlementTabs } from '@/composables/useSettlementSectionTabs'

import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useServiceCashSession } from '@/composables/useServiceCashSession'

import { useOperationalEvents } from '@/composables/useOperationalEvents'
import NightPosSseBanner from '@/components/nightpos/layout/NightPosSseBanner.vue'

import { getApiErrorMessage } from '@/services/http'



definePage({ meta: { permission: 'settlements.access' } })



const settlementTabs = useFilteredSettlementTabs()

const { can } = useNightPosPermissions()

const { notify } = useNightPosNotify()

const { loading, shift, summary, context, sourcesSummary, reload } = useCurrentShiftSettlements()

const {

  loading: pendingLoading,

  error: pendingError,

  pendingSources,

  reload: reloadPendingSources,

} = useSettlementPendingSources()



const generating = ref(false)
const showCashMovement = ref(false)

const {
  cashSessionOpen,
  loadCashSession,
  showOpenCash,
} = useServiceCashSession()

const scopeLabel = computed(() => {
  if (context.value?.scope === 'my_cash_session') {
    return 'Mostrando liquidaciones de mi caja actual'
  }
  if (context.value?.scope === 'shift') {
    return 'Mostrando liquidaciones del turno'
  }

  return null
})



const summaryHasData = computed(() => {
  const s = summary.value || {}

  return Number(s.total_waiters ?? 0) > 0
    || Number(s.total_girls ?? 0) > 0
    || Number(s.total_cleaning ?? 0) > 0
    || Number(s.total_pending ?? 0) > 0
})

const settlementSummary = computed(() => context.value?.settlement_summary ?? null)

const hasPendingPayments = computed(() => (
  Number(settlementSummary.value?.generated_pending_count ?? 0) > 0
  || Number(summary.value?.total_pending ?? 0) > 0
))



const summaryCards = computed(() => {

  const s = summary.value || {}



  return [

    { title: 'Total garzones', color: 'primary', icon: 'ri-user-star-line', stats: `${s.total_waiters || '0.00'} BOB`, subtitle: 'Comisiones turno' },

    { title: 'Total chicas', color: 'secondary', icon: 'ri-women-line', stats: `${s.total_girls || '0.00'} BOB`, subtitle: 'Liquidación chicas' },

    { title: 'Total limpieza', color: 'success', icon: 'ri-brush-line', stats: `${s.total_cleaning || '0.00'} BOB`, subtitle: 'Personal limpieza' },

    { title: 'Consumos acompañante', color: 'success', icon: 'ri-glass-line', stats: `${s.total_consumption || '0.00'} BOB`, subtitle: 'CON_ACOMPANANTE' },

    { title: 'Manillas', color: 'info', icon: 'ri-gem-line', stats: `${s.total_bracelets || '0.00'} BOB`, subtitle: 'Registro manual' },

    { title: 'Piezas', color: 'warning', icon: 'ri-door-line', stats: `${s.total_pieces || '0.00'} BOB`, subtitle: 'Servicios habitación' },

    { title: 'Shows', color: 'error', icon: 'ri-mic-line', stats: `${s.total_shows || '0.00'} BOB`, subtitle: 'Shows registrados' },

    { title: 'Pendiente', color: 'warning', icon: 'ri-time-line', stats: `${s.total_pending || '0.00'} BOB`, subtitle: 'Por pagar' },

    { title: 'Pagado', color: 'success', icon: 'ri-check-double-line', stats: `${s.total_paid || '0.00'} BOB`, subtitle: 'Liquidado' },

  ]

})



const refreshAll = async () => {

  await Promise.all([reload(), reloadPendingSources()])

}



const onCashOpened = async () => {

  await refreshAll()

}



const openCashMovement = async () => {
  await loadCashSession()
  if (!cashSessionOpen.value) {
    notify('Debe abrir caja para registrar movimientos.', 'warning')
    showOpenCash.value = true

    return
  }
  showCashMovement.value = true
}

const onMovementRegistered = async () => {
  await loadCashSession()
  await refreshAll()
}



const generate = async () => {

  if (!can('settlements.generate'))

    return



  generating.value = true



  try {

    const result = await generateCurrentShiftSettlements()

    if (result.created_items > 0) {
      notify(`Liquidaciones generadas (${result.created_items} líneas nuevas)`)
    }
    else if ((result.settlement_summary?.generated_pending_count ?? 0) > 0) {
      notify('No hay nuevas liquidaciones para generar. Tienes pagos pendientes en tu caja.', 'warning')
    }
    else {
      notify(
        result.context?.scope === 'my_cash_session'
          ? 'No hay liquidaciones nuevas para generar en tu caja actual.'
          : 'No hay liquidaciones nuevas para generar en este turno.',
        'info',
      )
    }

    await refreshAll()

  }

  catch (error) {

    if (import.meta.env.DEV) {

      console.error('[settlements/generate-current-shift]', error?.response?.status, error?.response?.data?.message ?? error)

    }

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    generating.value = false

  }

}

// ─── SSE real-time ──────────────────────────────────────────────────────────
const { on, start: startSse, stop: stopSse, connected: sseConnected, reconnecting: sseReconnecting } = useOperationalEvents()

let settlementDebounce = null
const debouncedRefresh = () => {
  clearTimeout(settlementDebounce)
  settlementDebounce = setTimeout(refreshAll, 600)
}

on('settlement.generated', debouncedRefresh)
on('settlement.paid', debouncedRefresh)
on('cash.movement.created', debouncedRefresh)

onMounted(() => { startSse() })
onUnmounted(() => { stopSse() })
// ─────────────────────────────────────────────────────────────────────────────

</script>



<template>

  <div>

    <NightPosSseBanner
      :connected="sseConnected"
      :reconnecting="sseReconnecting"
    />

    <NightPosPageHeader

      title="Liquidaciones"

      subtitle="Resumen del turno oficial: comisiones, chicas y limpieza."

      :breadcrumbs="[

        { title: 'NightPOS', disabled: true },

        { title: 'Finanzas', disabled: true },

        { title: 'Liquidaciones', disabled: true },

      ]"

    >

      <template #actions>
        <VBtn
          v-if="can('cash.access')"
          variant="tonal"
          prepend-icon="ri-exchange-dollar-line"
          class="me-2"
          @click="openCashMovement"
        >
          Registrar movimiento
        </VBtn>

        <VBtn

          v-if="can('settlements.generate')"

          color="primary"

          prepend-icon="ri-refresh-line"

          :loading="generating"
          :disabled="generating"

          @click="generate"

        >

          Generar liquidaciones del turno actual

        </VBtn>

      </template>

    </NightPosPageHeader>



    <NightPosSectionTabs :tabs="settlementTabs" />



    <SettlementsCashBanner @cash-opened="onCashOpened" />



    <VAlert

      v-if="!loading && !shift"

      type="info"

      variant="tonal"

      class="mb-4"

    >

      No hay turno abierto para liquidaciones. Abra un turno o opere caja/comandas para iniciar el turno actual.

    </VAlert>



    <VAlert

      v-else-if="shift"

      type="info"

      variant="tonal"

      class="mb-4"

    >

      <div class="text-body-2">

        <strong>Turno actual</strong>

        · ID {{ shift.id }}

        · {{ shift.shift_type_label || (shift.shift_type === 'DAY' ? 'Día' : 'Noche') }}

        · {{ shift.business_date }}

        <span v-if="context?.branch_id"> · Sucursal #{{ context.branch_id }}</span>

      </div>

      <div

        v-if="context"

        class="text-caption text-medium-emphasis mt-1"

      >

        <span v-if="scopeLabel">{{ scopeLabel }} · </span>

        Caja #{{ context.cash_session_id ?? '—' }} · Cajera #{{ context.cashier_user_id ?? '—' }}

      </div>

    </VAlert>



    <VAlert

      v-if="shift && hasPendingPayments && !loading"

      type="warning"

      variant="tonal"

      class="mb-4"

    >

      Hay liquidaciones pendientes de pago por

      <strong>{{ settlementSummary?.generated_pending_amount ?? summary?.total_pending }} BOB</strong>.

      Vaya a Garzones, Chicas o Limpieza para pagarlas.

    </VAlert>



    <VAlert

      v-if="shift && !summaryHasData && !loading && !hasPendingPayments"

      type="warning"

      variant="tonal"

      class="mb-4"

    >

      <span v-if="context?.scope === 'my_cash_session'">
        No tienes liquidaciones pendientes en tu caja.
      </span>
      <span v-else>
        No hay liquidaciones pendientes para este turno.
      </span>

      <span v-if="sourcesSummary">

        Fuentes: ventas {{ sourcesSummary.sales ?? 0 }}, manillas {{ sourcesSummary.bracelets ?? 0 }}, piezas {{ sourcesSummary.rooms ?? 0 }}.

      </span>

    </VAlert>



    <VAlert

      v-if="pendingError"

      type="warning"

      variant="tonal"

      class="mb-4"

    >

      No se pudieron cargar las fuentes pendientes: {{ pendingError }}.

      El resumen de liquidaciones sigue disponible.

    </VAlert>



    <VAlert

      v-if="pendingSources?.waiters_without_commission?.length"

      type="warning"

      variant="tonal"

      class="mb-4"

    >

      <template v-if="pendingSources.waiters_without_commission.some(w => w.name)">

        Garzones sin porcentaje de comisión configurado:

        <strong>{{ pendingSources.waiters_without_commission.map(w => w.name).filter(Boolean).join(', ') }}</strong>.

        Revise el personal antes de generar liquidaciones.

      </template>

      <template v-else>

        Hay <strong>{{ pendingSources.waiters_without_commission_count ?? pendingSources.waiters_without_commission.length }}</strong>

        garzón(es) sin porcentaje de comisión configurado.

        Contacte al administrador antes de generar liquidaciones.

      </template>

    </VAlert>



    <VAlert

      v-if="pendingSources?.girls_without_commission_flag?.length"

      type="warning"

      variant="tonal"

      class="mb-4"

    >

      <template v-if="pendingSources.girls_without_commission_flag.some(g => g.name)">

        Chicas sin flag de comisión activo:

        <strong>{{ pendingSources.girls_without_commission_flag.map(g => g.name).filter(Boolean).join(', ') }}</strong>.

        No bloquea la generación, pero puede afectar pagos a chicas.

      </template>

      <template v-else>

        Hay <strong>{{ pendingSources.girls_without_commission_flag_count ?? pendingSources.girls_without_commission_flag.length }}</strong>

        chica(s) sin flag de comisión activo.

        Contacte al administrador si aplica.

      </template>

    </VAlert>



    <VAlert

      v-if="pendingSources?.active_room_services_count > 0"

      type="warning"

      variant="tonal"

      class="mb-4"

    >

      Hay <strong>{{ pendingSources.active_room_services_count }}</strong> pieza(s) activa(s) que aún no entran a liquidación hasta finalizarse.

      <div class="mt-2">

        <VBtn

          size="small"

          variant="tonal"

          color="warning"

          :to="{ name: 'nightpos-services-room-control' }"

        >

          Ver control de piezas

        </VBtn>

      </div>

    </VAlert>



    <VAlert

      v-if="!loading && shift && !summaryHasData && !hasPendingPayments"

      type="warning"

      variant="tonal"

      class="mb-4"

    >

      Las liquidaciones del turno aún no se han generado — todos los totales son 0.

      Pulse <strong>«Generar liquidaciones del turno actual»</strong> para calcular comisiones, chicas y limpieza.

    </VAlert>



    <VProgressLinear

      v-if="loading"

      indeterminate

      class="mb-4"

    />



    <VRow

      v-else

      class="match-height"

    >

      <VCol

        v-for="card in summaryCards"

        :key="card.title"

        cols="12"

        sm="6"

        md="4"

        lg="3"

      >

        <CardStatisticsVertical v-bind="card" />

      </VCol>

    </VRow>



    <p

      v-if="pendingLoading"

      class="text-caption text-medium-emphasis mt-2"

    >

      Actualizando fuentes pendientes…

    </p>

    <CashMovementDialog
      v-model="showCashMovement"
      @registered="onMovementRegistered"
    />

    <QuickOpenCashDialog
      v-model="showOpenCash"
      @opened="onCashOpened"
    />

  </div>

</template>

