<script setup>
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { closeShift, downloadShiftCsv, fetchCurrentShift, fetchShiftCloseCheck, fetchShiftSummary, printShiftClosure } from '@/api/shifts'
import { fetchCurrentShiftSettlements } from '@/api/settlements'
import { fetchProductReconciliation } from '@/api/reports'
import ProductReconciliationPanel from '@/components/nightpos/reports/ProductReconciliationPanel.vue'
import ComboBraceletSummaryPanel from '@/components/nightpos/reports/ComboBraceletSummaryPanel.vue'
import { useNightPosPrint } from '@/composables/useNightPosPrint'
import { useFilteredShiftTabs } from '@/composables/useShiftSectionTabs'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'shifts.close' } })

const shiftTabs = useFilteredShiftTabs()
const router = useRouter()
const { notify } = useNightPosNotify()
const { openPrintRoute } = useNightPosPrint()

const shift = ref(null)
const summary = ref(null)
const settlements = ref(null)
const closureCheck = ref(null)
const reconciliation = ref(null)
const loading = ref(true)
const saving = ref(false)
const shiftPrintLoading = ref(false)
const shiftReprintLoading = ref(false)
const closedShiftId = ref(null)
const showPendingConfirm = ref(false)
const form = ref({ counted_cash: '', notes: '' })
const refForm = ref()

const hasBlockers = computed(() => closureCheck.value?.blockers?.length > 0)
const hasWarnings = computed(() => closureCheck.value?.warnings?.length > 0)
const canClose    = computed(() => closureCheck.value?.can_close !== false)

const kpiCards = computed(() => {
  if (!summary.value)
    return []

  const s = summary.value.summary

  return [
    { title: 'Efectivo ventas', value: s.total_cash, icon: 'ri-money-dollar-circle-line', color: 'success' },
    { title: 'QR', value: s.total_qr, icon: 'ri-qr-code-line', color: 'info' },
    { title: 'Tarjeta', value: s.total_card, icon: 'ri-bank-card-line', color: 'primary' },
    { title: 'Total ventas', value: s.total_sales, icon: 'ri-shopping-bag-line', color: 'secondary' },
    { title: 'Ingresos manuales', value: s.total_manual_income, icon: 'ri-add-circle-line', color: 'success' },
    { title: 'Egresos manuales', value: s.total_manual_expense, icon: 'ri-indeterminate-circle-line', color: 'error' },
    { title: 'Efectivo esperado', value: s.expected_cash, icon: 'ri-calculator-line', color: 'warning' },
  ]
})

const settlementKpis = computed(() => {
  const s = settlements.value?.summary
  if (!s) return []

  return [
    {
      title: 'Garzones',
      value: `${s.total_waiters ?? '0.00'} BOB`,
      icon: 'ri-user-star-line',
      color: 'primary',
    },
    {
      title: 'Chicas',
      value: `${s.total_girls ?? '0.00'} BOB`,
      icon: 'ri-women-line',
      color: 'secondary',
    },
    {
      title: 'Limpieza',
      value: `${s.total_cleaning ?? '0.00'} BOB`,
      icon: 'ri-brush-line',
      color: 'success',
    },
    {
      title: 'Pendiente por pagar',
      value: `${s.total_pending ?? '0.00'} BOB`,
      icon: 'ri-time-line',
      color: Number(s.total_pending) > 0 ? 'error' : 'success',
    },
  ]
})

const hasPendingSettlements = computed(() => {
  const pending = Number(settlements.value?.summary?.total_pending ?? 0)

  return pending > 0
})

const settlementsGenerated = computed(() => {
  const s = settlements.value?.summary
  if (!s) return false
  const total = Number(s.total_waiters ?? 0) + Number(s.total_girls ?? 0) + Number(s.total_cleaning ?? 0)

  return total > 0
})

const load = async () => {
  loading.value = true
  try {
    shift.value = await fetchCurrentShift()
    if (shift.value?.id) {
      const [data, settlData, checkData, reconData] = await Promise.all([
        fetchShiftSummary(shift.value.id),
        fetchCurrentShiftSettlements().catch(() => null),
        fetchShiftCloseCheck().catch(() => null),
        fetchProductReconciliation({ officialShiftId: shift.value.id }).catch(() => null),
      ])

      summary.value = data
      settlements.value = settlData
      closureCheck.value = checkData
      reconciliation.value = reconData
      form.value.counted_cash = data.summary?.expected_cash ?? ''
    }
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const attemptClose = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid || !shift.value)
    return

  if (hasBlockers.value) {
    notify('No se puede cerrar: hay bloqueantes activos. Revisa la lista de verificación.', 'error')
    return
  }

  if (hasPendingSettlements.value) {
    showPendingConfirm.value = true

    return
  }

  await doClose()
}

const doClose = async () => {
  showPendingConfirm.value = false
  saving.value = true
  try {
    const result = await closeShift(shift.value.id, {
      counted_cash: Number(form.value.counted_cash),
      notes: form.value.notes?.trim() || null,
    })
    closedShiftId.value = shift.value.id
    if (result?.shift)
      shift.value = result.shift
    notify('Turno cerrado')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

const printClosureThermal = async ({ reprint = false } = {}) => {
  const shiftId = closedShiftId.value ?? shift.value?.id
  if (!shiftId)
    return

  const loadingRef = reprint ? shiftReprintLoading : shiftPrintLoading
  loadingRef.value = true
  try {
    const result = await printShiftClosure(shiftId, { reprint })
    if (result?.print_warning)
      notify(result.print_warning, 'warning')
    else
      notify(reprint ? 'Cierre reenviado a impresora.' : 'Cierre enviado a impresora.')
  }
  catch (error) {
    notify(getApiErrorMessage(error) || 'No se pudo imprimir por agente. Use la vista del navegador.', 'error')
    if (shiftId)
      openPrintRoute({ name: 'nightpos-print-shift-id', params: { id: shiftId } })
  }
  finally {
    loadingRef.value = false
  }
}

const goToHistory = async () => {
  await router.push({ name: 'nightpos-shifts-history' })
}

const save = attemptClose

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Cierre de turno"
      subtitle="Resumen operativo y arqueo de efectivo del turno abierto."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Turnos', to: { name: 'nightpos-shifts' } },
        { title: 'Cierre', disabled: true },
      ]"
    >
      <template
        v-if="shift?.id"
        #actions
      >
        <VBtn
          variant="tonal"
          prepend-icon="ri-printer-line"
          class="me-2"
          @click="openPrintRoute({ name: 'nightpos-print-shift-id', params: { id: shift.id } })"
        >
          Vista imprimible
        </VBtn>
        <VBtn
          v-if="closedShiftId"
          variant="tonal"
          prepend-icon="ri-printer-2-line"
          class="me-2"
          :loading="shiftPrintLoading"
          @click="printClosureThermal()"
        >
          Imprimir cierre
        </VBtn>
        <VBtn
          v-if="closedShiftId"
          variant="tonal"
          prepend-icon="ri-printer-line"
          class="me-2"
          :loading="shiftReprintLoading"
          @click="printClosureThermal({ reprint: true })"
        >
          Reimprimir cierre
        </VBtn>
        <VBtn
          variant="tonal"
          prepend-icon="ri-file-download-line"
          @click="downloadShiftCsv(shift.id)"
        >
          Exportar CSV
        </VBtn>
      </template>
    </NightPosPageHeader>
    <NightPosSectionTabs :tabs="shiftTabs" />

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VAlert
      v-if="closedShiftId"
      type="success"
      variant="tonal"
      class="mb-4"
    >
      <div class="mb-3">
        Turno cerrado. Puede imprimir el reporte por agente térmico o usar la vista del navegador.
      </div>
      <div class="d-flex flex-wrap gap-2">
        <VBtn
          size="small"
          variant="tonal"
          prepend-icon="ri-printer-2-line"
          :loading="shiftPrintLoading"
          @click="printClosureThermal()"
        >
          Imprimir cierre
        </VBtn>
        <VBtn
          size="small"
          variant="tonal"
          prepend-icon="ri-printer-line"
          :loading="shiftReprintLoading"
          @click="printClosureThermal({ reprint: true })"
        >
          Reimprimir cierre
        </VBtn>
        <VBtn
          size="small"
          variant="text"
          prepend-icon="ri-file-text-line"
          @click="openPrintRoute({ name: 'nightpos-print-shift-id', params: { id: closedShiftId } })"
        >
          Vista imprimible
        </VBtn>
        <VBtn
          size="small"
          variant="text"
          @click="goToHistory"
        >
          Ir al historial
        </VBtn>
      </div>
    </VAlert>

    <VAlert
      v-else-if="!shift"
      type="warning"
      variant="tonal"
    >
      No hay turno abierto para cerrar.
    </VAlert>

    <template v-else>

      <!-- ─── Verificación de cierre ─── -->
      <template v-if="closureCheck">
        <!-- Bloqueantes -->
        <VAlert
          v-for="blocker in closureCheck.blockers"
          :key="blocker.code"
          type="error"
          variant="tonal"
          class="mb-2"
          :title="`🚫 Bloqueante: ${blocker.message}`"
        />
        <!-- Advertencias -->
        <VAlert
          v-for="warning in closureCheck.warnings"
          :key="warning.code"
          type="warning"
          variant="tonal"
          class="mb-2"
          :title="`⚠️ ${warning.message}`"
        />
        <VAlert
          v-if="!hasBlockers && !hasWarnings"
          type="success"
          variant="tonal"
          class="mb-4"
          title="✅ Todo en orden — puedes cerrar el turno."
        />
      </template>

      <!-- KPIs ventas/caja -->
      <VRow class="mb-4">
        <VCol
          v-for="card in kpiCards"
          :key="card.title"
          cols="12"
          sm="6"
          md="4"
          lg="3"
        >
          <VCard>
            <VCardText class="d-flex align-center gap-3">
              <VAvatar
                :color="card.color"
                variant="tonal"
                rounded
              >
                <VIcon :icon="card.icon" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis">
                  {{ card.title }}
                </div>
                <div class="text-h6">
                  {{ card.value }}
                </div>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <!-- Bloque liquidaciones -->
      <VCard class="mb-4">
        <VCardTitle class="d-flex align-center justify-space-between flex-wrap gap-2 pt-4 px-4">
          <span>
            <VIcon
              icon="ri-group-line"
              class="me-2"
            />
            Pagos al personal
          </span>
          <VBtn
            size="small"
            variant="tonal"
            color="primary"
            prepend-icon="ri-arrow-right-line"
            :to="{ name: 'nightpos-settlements' }"
          >
            Ver liquidaciones
          </VBtn>
        </VCardTitle>

        <VCardText>
          <VAlert
            v-if="!settlementsGenerated"
            type="warning"
            variant="tonal"
            density="compact"
            class="mb-4"
          >
            Las liquidaciones aún no fueron generadas — los totales de abajo muestran 0.
            <VBtn
              size="small"
              variant="text"
              color="warning"
              class="ms-2"
              :to="{ name: 'nightpos-settlements' }"
            >
              Generar ahora
            </VBtn>
          </VAlert>

          <VAlert
            v-else-if="hasPendingSettlements"
            type="error"
            variant="tonal"
            density="compact"
            class="mb-4"
          >
            Hay liquidaciones <strong>pendientes de pago</strong>.
            Se recomienda pagarlas antes de cerrar el turno.
          </VAlert>

          <VRow>
            <VCol
              v-for="kpi in settlementKpis"
              :key="kpi.title"
              cols="6"
              md="3"
            >
              <VCard variant="tonal">
                <VCardText class="d-flex align-center gap-2 pa-3">
                  <VAvatar
                    :color="kpi.color"
                    variant="tonal"
                    size="36"
                    rounded
                  >
                    <VIcon
                      :icon="kpi.icon"
                      size="18"
                    />
                  </VAvatar>
                  <div>
                    <div class="text-caption text-medium-emphasis">
                      {{ kpi.title }}
                    </div>
                    <div class="text-subtitle-2 font-weight-bold">
                      {{ kpi.value }}
                    </div>
                  </div>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

      <!-- Conciliación de productos -->
      <VCard v-if="reconciliation" class="mb-4">
        <VCardTitle class="pt-4 px-4">
          <VIcon icon="ri-goblet-line" class="me-2" />
          Conciliación de productos
        </VCardTitle>
        <VCardText>
          <ComboBraceletSummaryPanel
            v-if="summary?.combo_bracelets?.total_bracelet_units"
            :summary="summary.combo_bracelets"
            compact
            class="mb-4"
          />

          <ProductReconciliationPanel
            :data="reconciliation"
            title=""
            :show-sold="false"
          />
        </VCardText>
      </VCard>

      <!-- Formulario cierre -->
      <VCard>
        <VCardText>
          <VForm
            ref="refForm"
            @submit.prevent="save"
          >
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.counted_cash"
                  label="Efectivo contado"
                  type="number"
                  min="0"
                  step="0.01"
                  :rules="[v => v !== '' && v !== null || 'Requerido']"
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="form.notes"
                  label="Notas de cierre"
                  rows="2"
                />
              </VCol>
            </VRow>
            <NightPosFormActions
              :saving="saving"
              :save-disabled="hasBlockers"
              save-label="Cerrar turno"
              :cancel-to="{ name: 'nightpos-shifts-current' }"
              @save="save"
            />
          </VForm>
        </VCardText>
      </VCard>
    </template>

    <!-- Confirmación liquidaciones pendientes -->
    <VDialog
      v-model="showPendingConfirm"
      max-width="460"
    >
      <VCard title="Liquidaciones pendientes">
        <VCardText>
          <VAlert
            type="warning"
            variant="tonal"
            class="mb-4"
          >
            Hay liquidaciones pendientes de pago por
            <strong>{{ settlements?.summary?.total_pending }} BOB</strong>.
            ¿Desea cerrar el turno de todas formas?
          </VAlert>
          <p class="text-body-2">
            Se recomienda pagar al personal antes de cerrar.
            Puede hacerlo en <strong>Finanzas → Liquidaciones</strong>.
          </p>
        </VCardText>
        <VCardActions>
          <VBtn
            variant="text"
            :to="{ name: 'nightpos-settlements' }"
          >
            Ir a liquidaciones
          </VBtn>
          <VSpacer />
          <VBtn
            color="error"
            :loading="saving"
            @click="doClose"
          >
            Cerrar de todas formas
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
</div>
</template>
