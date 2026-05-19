<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import CajaLiquidacionPanel from '../../components/caja/CajaLiquidacionPanel.vue'
import CajaModal from '../../components/caja/CajaModal.vue'
import PdfPreviewModal from '../../components/PdfPreviewModal.vue'
import { apiRequest } from '../../services/api'
import { useAuthStore } from '../../stores/authStore'
import { useNotificationStore } from '../../stores/notificationStore'
import { useCajaScope } from '../../composables/useCajaScope'
import { useCajaFormatters } from '../../composables/useCajaFormatters'
import { usePdfPreview } from '../../composables/usePdfPreview'

const TABS = ['apertura', 'meseros', 'arqueo', 'movimientos', 'chicas', 'liquidacion']

const CAJA_TABS = [
  { id: 'apertura', label: 'Apertura', title: 'Turno de caja' },
  { id: 'meseros', label: 'Meseros', title: 'Comisión y tipo de pago' },
  { id: 'arqueo', label: 'Arqueo', title: 'Conteo y cierre' },
  { id: 'movimientos', label: 'Movimientos', title: 'Entradas y salidas de efectivo' },
  { id: 'chicas', label: 'Chicas', title: 'Fichadas y pagos' },
  { id: 'liquidacion', label: 'Liquidación', title: 'Reporte del turno' },
]

const auth = useAuthStore()
const notify = useNotificationStore()
const route = useRoute()
const router = useRouter()

const { formatMoney, formatWhen } = useCajaFormatters()

const { needsSitePicker, sites, sitePickerId, branchQuery, initSiteScope, currentShift, refreshCurrentShift } =
  useCajaScope(auth)

const {
  pdfPreviewOpen,
  pdfPreviewLoading,
  pdfPreviewUrl,
  pdfPreviewTitle,
  openPdfPreview,
  closePdfPreview,
  downloadPdfPreview,
} = usePdfPreview(() => auth.token.value)

const booting = ref(true)
const switchingDaySite = ref(false)

const heroSummary = ref(null)
const summary = ref(null)
const closeForm = reactive({ closing_cash: 0 })

const movements = ref([])
const movementForm = reactive({ direction: 'out', amount: 0, notes: '' })

const openShiftForm = reactive({ period: 'night', opening_cash: 0 })

const history = ref([])
const selectedReportShiftId = ref(null)
const report = ref(null)
const loadingReport = ref(false)

const movementModalOpen = ref(false)
const openShiftModalOpen = ref(false)

const companionSessions = ref([])
const loadingSessions = ref(false)
const companions = ref([])
const ficharModalOpen = ref(false)
const ficharForm = reactive({ companion_id: null })
const newCompanionName = ref('')
const registeringCompanion = ref(false)
const registerChicaModalOpen = ref(false)
const settleModalOpen = ref(false)
const settleCtx = ref(null)
const settleForm = reactive({ amount: 0, notes: '' })

const branchWaitersPayload = ref(null)
const loadingBranchWaiters = ref(false)
const selectedMeseroId = ref(null)
const meseroForm = reactive({
  waiter_compensation_type: 'per_payment',
  waiter_commission_rate_pct: '',
})
const savingMesero = ref(false)
const meseroEditModalOpen = ref(false)

const branchWaitersList = computed(() => branchWaitersPayload.value?.waiters ?? [])
const selectedMeseroName = computed(() => {
  const id = selectedMeseroId.value
  if (!id) return ''
  const w = branchWaitersList.value.find((x) => Number(x.id) === Number(id))
  return w?.name ? String(w.name) : ''
})

const meseroModalTitle = computed(() =>
  selectedMeseroName.value ? `Mesero — ${selectedMeseroName.value}` : 'Mesero',
)

function queryTab() {
  const raw = route.query.tab
  const t = Array.isArray(raw) ? raw[0] : raw
  return typeof t === 'string' ? t : ''
}

const activeTab = computed(() => {
  const t = queryTab()
  return TABS.includes(t) ? t : 'apertura'
})

function goTab(tab) {
  if (!TABS.includes(tab)) return
  router.push({ name: 'caja-workspace', query: { ...route.query, tab } })
}

async function loadHeroSummary() {
  if (!currentShift.value?.id) {
    heroSummary.value = null
    return
  }
  try {
    const q = branchQuery()
    const payload = await apiRequest(`/shifts/${currentShift.value.id}/cash-summary${q}`, {}, auth.token.value)
    heroSummary.value = payload.data
  } catch {
    heroSummary.value = null
  }
}

async function loadSummary() {
  if (!currentShift.value?.id) {
    summary.value = null
    return
  }
  const q = branchQuery()
  const payload = await apiRequest(`/shifts/${currentShift.value.id}/cash-summary${q}`, {}, auth.token.value)
  summary.value = payload.data
  if (closeForm.closing_cash === 0 && payload.data?.expected_cash != null) {
    closeForm.closing_cash = payload.data.expected_cash
  }
}

async function loadMovements() {
  if (!currentShift.value?.id) {
    movements.value = []
    return
  }
  const q = branchQuery()
  const payload = await apiRequest(`/shifts/${currentShift.value.id}/cash-movements${q}`, {}, auth.token.value)
  movements.value = payload.data || []
}

async function loadHistory() {
  let path = '/shifts/history?limit=40'
  const q = branchQuery()
  if (q && q.startsWith('?')) {
    path += '&' + q.slice(1)
  }
  const payload = await apiRequest(path, {}, auth.token.value)
  history.value = payload.data || []
}

async function loadBranchWaiters() {
  loadingBranchWaiters.value = true
  try {
    const q = branchQuery()
    const payload = await apiRequest(`/branch/waiters${q}`, {}, auth.token.value)
    branchWaitersPayload.value = payload.data
    const sid = selectedMeseroId.value
    if (sid) {
      const w = branchWaitersList.value.find((x) => Number(x.id) === Number(sid))
      if (w) pickMesero(w)
      else selectedMeseroId.value = null
    }
  } catch (e) {
    branchWaitersPayload.value = null
    notify.error(e instanceof Error ? e.message : 'No se pudieron cargar los meseros.')
  } finally {
    loadingBranchWaiters.value = false
  }
}

function pickMesero(w) {
  selectedMeseroId.value = w.id
  meseroForm.waiter_compensation_type = w.waiter_compensation_type || 'per_payment'
  meseroForm.waiter_commission_rate_pct =
    w.waiter_commission_rate_pct != null && w.waiter_commission_rate_pct !== ''
      ? String(w.waiter_commission_rate_pct)
      : ''
}

function openMeseroEditor(w) {
  pickMesero(w)
  meseroEditModalOpen.value = true
}

function closeMeseroEditor() {
  meseroEditModalOpen.value = false
}

function meseroRowSubtitle(w) {
  const t = w.waiter_compensation_type || 'per_payment'
  if (t === 'payroll_monthly') return 'Nómina mensual'
  if (t === 'payroll_weekly') return 'Nómina semanal'
  if (w.waiter_commission_rate_pct != null && w.waiter_commission_rate_pct !== '') {
    return `Comisión ${w.waiter_commission_rate_pct}%`
  }
  const def = branchWaitersPayload.value?.default_commission_rate_pct
  return def != null ? `Comisión sucursal (${def}%)` : 'Comisión % sucursal'
}

async function saveMeseroCompensation() {
  if (!selectedMeseroId.value) {
    notify.error('Elegí un mesero de la lista.')
    return
  }
  savingMesero.value = true
  try {
    const q = branchQuery()
    const body = { waiter_compensation_type: meseroForm.waiter_compensation_type }
    if (meseroForm.waiter_compensation_type === 'per_payment') {
      const p = meseroForm.waiter_commission_rate_pct
      body.waiter_commission_rate_pct = p === '' || p == null ? null : Number(p)
    } else {
      body.waiter_commission_rate_pct = null
    }
    await apiRequest(
      `/branch/waiters/${selectedMeseroId.value}/compensation${q}`,
      { method: 'PATCH', body: JSON.stringify(body) },
      auth.token.value,
    )
    notify.success('Remuneración del mesero guardada.')
    meseroEditModalOpen.value = false
    await loadBranchWaiters()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo guardar.')
  } finally {
    savingMesero.value = false
  }
}

async function loadCompanionSessions() {
  if (!currentShift.value?.id) {
    companionSessions.value = []
    return
  }
  loadingSessions.value = true
  try {
    const q = branchQuery()
    const payload = await apiRequest(
      `/shifts/${currentShift.value.id}/companion-work-sessions${q}`,
      {},
      auth.token.value,
    )
    companionSessions.value = payload.data || []
  } catch (e) {
    companionSessions.value = []
    notify.error(e instanceof Error ? e.message : 'No se pudieron cargar las salidas de chicas.')
  } finally {
    loadingSessions.value = false
  }
}

async function loadCompanionsForFichar() {
  let path = '/companions'
  const q = branchQuery()
  if (q && q.startsWith('?')) {
    path += q
  }
  const payload = await apiRequest(path, {}, auth.token.value)
  companions.value = payload.data || []
}

async function openFicharModal() {
  if (!currentShift.value?.id) {
    notify.error('Abrí un turno de caja primero.')
    return
  }
  ficharForm.companion_id = null
  newCompanionName.value = ''
  try {
    await loadCompanionsForFichar()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar el listado de chicas.')
    return
  }
  ficharModalOpen.value = true
}

/**
 * @param {boolean} linkForFichar - si true, al registrar queda seleccionada en el modal de fichar; si false, solo alta y cierra el modal dedicado
 */
async function registerNewCompanion(linkForFichar = true) {
  const name = newCompanionName.value?.trim() || ''
  if (name.length < 2) {
    notify.error('Ingresá al menos 2 caracteres para el nombre artístico.')
    return
  }
  registeringCompanion.value = true
  try {
    let path = '/companions/quick-create'
    const q = branchQuery()
    if (q && q.startsWith('?')) {
      path += q
    }
    const payload = await apiRequest(
      path,
      { method: 'POST', body: JSON.stringify({ stage_name: name }) },
      auth.token.value,
    )
    const row = payload.data
    notify.success(row?.reused ? 'Chica encontrada; ya está en la lista.' : 'Chica registrada en esta sucursal.')
    newCompanionName.value = ''
    await loadCompanionsForFichar()
    if (linkForFichar && row?.id) {
      ficharForm.companion_id = Number(row.id)
    }
    if (!linkForFichar) {
      registerChicaModalOpen.value = false
    }
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo registrar.')
  } finally {
    registeringCompanion.value = false
  }
}

function openRegisterChicaModal() {
  if (!currentShift.value?.id) {
    notify.error('Abrí un turno de caja primero.')
    return
  }
  newCompanionName.value = ''
  registerChicaModalOpen.value = true
}

async function submitFichar() {
  if (!currentShift.value?.id || !ficharForm.companion_id) {
    notify.error('Elegí una chica.')
    return
  }
  try {
    const q = branchQuery()
    await apiRequest(
      `/shifts/${currentShift.value.id}/companion-work-sessions${q}`,
      {
        method: 'POST',
        body: JSON.stringify({ companion_id: Number(ficharForm.companion_id) }),
      },
      auth.token.value,
    )
    notify.success('Fichada: nueva salida activa.')
    ficharModalOpen.value = false
    await loadCompanionSessions()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo registrar la fichada.')
  }
}

function openSettleModal(row) {
  settleCtx.value = row
  const bal = Number(row.balance_due) || 0
  const sug = Number(row.suggested_payout_total)
  settleForm.amount = bal > 0 ? bal : (Number.isFinite(sug) && sug > 0 ? sug : 0)
  settleForm.notes = ''
  settleModalOpen.value = true
}

async function submitSettle() {
  const s = settleCtx.value
  if (!s?.id) return
  const amt = Number(settleForm.amount) || 0
  if (amt < 1) {
    notify.error('Ingresá un monto válido (mayor a 0).')
    return
  }
  try {
    const q = branchQuery()
    await apiRequest(
      `/companion-work-sessions/${s.id}/settle${q}`,
      {
        method: 'POST',
        body: JSON.stringify({
          amount: amt,
          notes: settleForm.notes?.trim() || null,
        }),
      },
      auth.token.value,
    )
    notify.success('Salida liquidada.')
    settleModalOpen.value = false
    settleCtx.value = null
    await loadCompanionSessions()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo registrar el pago.')
  }
}

const activeCompanionSessions = computed(() =>
  (companionSessions.value || []).filter((r) => r.status === 'active'),
)
const settledCompanionSessions = computed(() =>
  (companionSessions.value || []).filter((r) => r.status === 'settled'),
)

async function loadReport() {
  const id = selectedReportShiftId.value
  if (!id) {
    report.value = null
    return
  }
  loadingReport.value = true
  try {
    const q = branchQuery()
    const payload = await apiRequest(`/shifts/${id}/cashier-report${q}`, {}, auth.token.value)
    report.value = payload.data ?? null
  } catch (e) {
    report.value = null
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar el reporte.')
  } finally {
    loadingReport.value = false
  }
}

function differenceValue() {
  if (!summary.value) return 0
  return (Number(closeForm.closing_cash) || 0) - Number(summary.value.expected_cash || 0)
}

const movementTotals = computed(() => {
  let incoming = 0
  let outgoing = 0
  for (const m of movements.value) {
    if (m.direction === 'in') incoming += Number(m.amount) || 0
    else outgoing += Number(m.amount) || 0
  }
  return { incoming, outgoing, net: incoming - outgoing }
})

async function bootstrap() {
  booting.value = true
  try {
    await initSiteScope()
    if (!TABS.includes(queryTab())) {
      await router.replace({ query: { ...route.query, tab: 'apertura' } })
    }
    await refreshCurrentShift()
    await loadHeroSummary()

    const sq = Number(route.query.shift)
    if (Number.isFinite(sq)) {
      selectedReportShiftId.value = sq
    } else if (currentShift.value?.id) {
      selectedReportShiftId.value = currentShift.value.id
    }

    await loadHistory()
    if (!selectedReportShiftId.value && history.value.length) {
      selectedReportShiftId.value = history.value[0].id
    }

    if (activeTab.value === 'meseros') await loadBranchWaiters()
    if (activeTab.value === 'arqueo') await loadSummary()
    if (activeTab.value === 'movimientos') await loadMovements()
    if (activeTab.value === 'chicas') await loadCompanionSessions()
    if (activeTab.value === 'liquidacion') await loadReport()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar la caja.')
  } finally {
    booting.value = false
  }
}

async function onCashierWorksiteChange(event) {
  const siteId = Number(event.target.value || 0)
  if (!siteId || siteId === Number(auth.resolvedActiveSiteId.value)) return
  switchingDaySite.value = true
  try {
    await auth.setActiveSite(siteId)
    await bootstrap()
    notify.success('Sucursal actualizada.')
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cambiar de sucursal.')
    event.target.value = String(auth.resolvedActiveSiteId.value || '')
  } finally {
    switchingDaySite.value = false
  }
}

async function openShift() {
  if (currentShift.value?.id) {
    notify.error('Ya hay un turno abierto.')
    return
  }
  try {
    const siteId = needsSitePicker.value
      ? Number(sitePickerId.value)
      : Number(auth.user.value?.active_site_id ?? auth.user.value?.site_id)
    if (!siteId) {
      notify.error('Seleccioná una sucursal válida.')
      return
    }
    const q = branchQuery()
    const payload = await apiRequest(
      `/shifts/open${q}`,
      {
        method: 'POST',
        body: JSON.stringify({
          cashier_user_id: Number(auth.user.value?.id),
          site_id: siteId,
          period: openShiftForm.period,
          opening_cash: Number(openShiftForm.opening_cash) || 0,
        }),
      },
      auth.token.value,
    )
    notify.success(`Turno abierto #${payload.data.shift_turn_id}.`)
    auth.requiresOpenShift.value = false
    openShiftModalOpen.value = false
    await refreshCurrentShift()
    await loadHeroSummary()
    selectedReportShiftId.value = currentShift.value?.id ?? selectedReportShiftId.value
    await loadHistory()
    await loadReport()
    await loadCompanionSessions()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo abrir el turno.')
  }
}

async function closeShift() {
  if (!currentShift.value?.id) {
    notify.error('No hay turno abierto.')
    return
  }
  const closedShiftId = currentShift.value.id
  try {
    const q = branchQuery()
    const payload = await apiRequest(
      `/shifts/${closedShiftId}/close${q}`,
      {
        method: 'POST',
        body: JSON.stringify({ closing_cash: Number(closeForm.closing_cash) || 0 }),
      },
      auth.token.value,
    )
    const diff = payload.data?.difference ?? 0
    notify.success(diff === 0 ? 'Turno cerrado sin diferencia.' : `Turno cerrado. Diferencia: ${formatMoney(diff)}.`)
    closeForm.closing_cash = 0
    summary.value = null
    await refreshCurrentShift()
    await loadHeroSummary()
    await loadHistory()
    if (selectedReportShiftId.value) await loadReport()
    try {
      await openPdfPreview(
        `/shifts/${closedShiftId}/pdf${q}`,
        `Informe ERP · cierre turno #${closedShiftId}`,
      )
    } catch (pdfErr) {
      notify.error(pdfErr instanceof Error ? pdfErr.message : 'Turno cerrado, pero no se pudo abrir el PDF.')
      closePdfPreview()
    }
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cerrar el turno.')
  }
}

async function submitMovement() {
  if (!currentShift.value?.id) {
    notify.error('No hay turno abierto.')
    return
  }
  try {
    const q = branchQuery()
    await apiRequest(
      `/shifts/${currentShift.value.id}/cash-movements${q}`,
      {
        method: 'POST',
        body: JSON.stringify({
          direction: movementForm.direction,
          amount: Number(movementForm.amount) || 0,
          notes: movementForm.notes?.trim() || null,
        }),
      },
      auth.token.value,
    )
    notify.success('Movimiento registrado.')
    movementForm.amount = 0
    movementForm.notes = ''
    movementModalOpen.value = false
    await loadMovements()
    await loadHeroSummary()
    if (activeTab.value === 'arqueo') await loadSummary()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo registrar el movimiento.')
  }
}

async function openShiftCashPdf() {
  const id = currentShift.value?.id
  if (!id) return
  try {
    await openPdfPreview(`/shifts/${id}/pdf${branchQuery()}`, `Turno de caja #${id}`)
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar el PDF.')
    closePdfPreview()
  }
}

async function downloadShiftPdfFromModal() {
  try {
    await downloadPdfPreview('turno-caja.pdf')
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo descargar.')
  }
}

function openMovementModal() {
  if (!currentShift.value?.id) {
    notify.error('Primero abrí un turno.')
    return
  }
  movementModalOpen.value = true
}

async function onSitePickerUpdate(id) {
  sitePickerId.value = id
  await bootstrap()
}

async function onSelectedReportShift(id) {
  selectedReportShiftId.value = id
  await loadReport()
}

watch(
  () => activeTab.value,
  async (tab) => {
    if (booting.value) return
    if (tab === 'meseros') await loadBranchWaiters()
    if (tab === 'arqueo') await loadSummary()
    if (tab === 'movimientos') await loadMovements()
    if (tab === 'chicas') await loadCompanionSessions()
    if (tab === 'liquidacion') {
      await loadHistory()
      if (!selectedReportShiftId.value && history.value.length) {
        selectedReportShiftId.value = history.value[0].id
      }
      await loadReport()
    }
  },
)

watch(
  () => route.query.shift,
  async (q) => {
    const n = Number(q)
    if (q !== undefined && q !== null && q !== '' && Number.isFinite(n)) {
      selectedReportShiftId.value = n
      if (activeTab.value === 'liquidacion') await loadReport()
    }
  },
)

onMounted(bootstrap)
</script>

<template>
  <section class="caja-spa">
    <!-- Cabecera compacta tipo SPA -->
    <header class="caja-spa__top">
      <div class="caja-spa__title-row">
        <h1 class="caja-spa__h1">Caja</h1>
        <span v-if="booting" class="caja-spa__status caja-spa__status--muted">Cargando…</span>
        <template v-else-if="currentShift">
          <span class="caja-spa__status">
            <strong>#{{ currentShift.id }}</strong>
            · {{ currentShift.period === 'day' ? 'Día' : 'Noche' }}
            · {{ formatWhen(currentShift.opened_at) }}
          </span>
          <span v-if="heroSummary" class="caja-spa__pill">
            Cajón <strong>{{ formatMoney(heroSummary.expected_cash) }}</strong>
          </span>
        </template>
        <span v-else class="caja-spa__status caja-spa__status--warn">Sin turno</span>
      </div>
      <div class="caja-spa__quick">
        <button v-if="!currentShift" type="button" class="primary-btn caja-spa__quick-btn" @click="openShiftModalOpen = true">
          Abrir turno
        </button>
        <template v-else>
          <button type="button" class="ghost-btn caja-spa__quick-btn" title="Salidas y fichadas" @click="goTab('chicas')">
            Chicas
          </button>
          <button type="button" class="ghost-btn caja-spa__quick-btn" title="Registrar chica en sucursal" @click="openRegisterChicaModal">
            Alta chica
          </button>
          <button type="button" class="ghost-btn caja-spa__quick-btn" @click="openMovementModal">Movimiento</button>
          <button type="button" class="ghost-btn caja-spa__quick-btn" @click="openShiftCashPdf">PDF</button>
          <button type="button" class="ghost-btn caja-spa__quick-btn" @click="goTab('meseros')">Meseros</button>
        </template>
      </div>
    </header>

    <div v-if="currentShift && heroSummary" class="caja-spa__metrics">
      <span>Total cobrado <strong>{{ formatMoney(heroSummary.payments_all_methods_total) }}</strong></span>
      <span class="caja-spa__dot" aria-hidden="true">·</span>
      <span>Chicas <strong>{{ formatMoney(heroSummary.companion_payouts_total) }}</strong></span>
      <span class="caja-spa__dot" aria-hidden="true">·</span>
      <span>QR <strong>{{ formatMoney(heroSummary.payment_totals?.qr) }}</strong></span>
      <span class="caja-spa__metrics-hint" title="Efectivo físico en cajón: ventas efectivo + ingresos manuales − retiros y pagos chicas (QR/tarjeta no suman al arqueo físico)">
        Efectivo cajón ≠ total cobrado
      </span>
    </div>

    <div
      v-if="auth.user.value?.role === 'cashier' && auth.accessibleSites.value.length > 1"
      class="caja-spa__scope caja-spa__scope--inline"
    >
      <label class="caja-spa__scope-label">
        Sucursal
        <select
          class="caja-spa__scope-select"
          :value="String(auth.resolvedActiveSiteId.value || '')"
          :disabled="switchingDaySite || booting"
          @change="onCashierWorksiteChange"
        >
          <option v-for="s in auth.accessibleSites.value" :key="s.id" :value="String(s.id)">
            {{ s.code }} — {{ s.name }}
          </option>
        </select>
      </label>
    </div>

    <div v-if="needsSitePicker" class="caja-spa__scope caja-spa__scope--inline">
      <label class="caja-spa__scope-label">
        Sucursal (admin)
        <select v-model.number="sitePickerId" class="caja-spa__scope-select" @change="bootstrap">
          <option v-for="s in sites" :key="s.id" :value="s.id">{{ s.code }} — {{ s.name }}</option>
        </select>
      </label>
    </div>

    <nav class="caja-spa__tabs" aria-label="Secciones de caja">
      <button
        v-for="t in CAJA_TABS"
        :key="t.id"
        type="button"
        :class="['caja-spa__tab', { 'caja-spa__tab--active': activeTab === t.id }]"
        :title="t.title"
        @click="goTab(t.id)"
      >
        {{ t.label }}
      </button>
    </nav>

    <div class="caja-spa__stage">
    <!-- Apertura -->
    <div v-show="activeTab === 'apertura'" class="caja-panel caja-spa__panel">
      <p class="caja-panel__hint">Efectivo inicial y período. Movimientos: botón arriba o pestaña Movimientos.</p>

      <div v-if="currentShift" class="panel-muted caja-open-info">
        <p class="caja-open-title">Turno vigente</p>
        <p><strong>ID:</strong> #{{ currentShift.id }}</p>
        <p><strong>Efectivo inicial:</strong> {{ formatMoney(currentShift.opening_cash) }}</p>
      </div>

      <form class="maint-field-grid" @submit.prevent="openShift">
        <div class="field-block">
          <span>Período</span>
          <select v-model="openShiftForm.period" :disabled="!!currentShift">
            <option value="day">Día</option>
            <option value="night">Noche</option>
          </select>
        </div>
        <div class="field-block">
          <span>Efectivo inicial</span>
          <input v-model.number="openShiftForm.opening_cash" type="number" min="0" required :disabled="!!currentShift" />
        </div>
        <div class="maint-form-actions field-block--full">
          <button type="submit" class="primary-btn" :disabled="!!currentShift">Abrir turno en esta pantalla</button>
        </div>
      </form>
    </div>

    <!-- Meseros: lista; edición en modal -->
    <div v-show="activeTab === 'meseros'" class="caja-panel caja-spa__panel caja-meseros-wrap">
      <p class="caja-panel__hint">Tocá un mesero para editar comisión o nómina.</p>

      <div class="caja-meseros caja-meseros--solo">
        <div class="caja-meseros__list" aria-label="Lista de meseros">
          <div v-if="loadingBranchWaiters" class="panel-muted caja-meseros__loading">Cargando…</div>
          <template v-else-if="branchWaitersList.length">
            <p v-if="branchWaitersPayload?.default_commission_rate_pct != null" class="caja-meseros__default">
              % base sucursal: <strong>{{ branchWaitersPayload.default_commission_rate_pct }}%</strong>
              <span class="caja-meseros__default-hint">(si el mesero no tiene % propio)</span>
            </p>
            <ul class="caja-meseros__ul">
              <li v-for="w in branchWaitersList" :key="w.id">
                <button type="button" class="caja-meseros__pick" @click="openMeseroEditor(w)">
                  <span class="caja-meseros__pick-name">{{ w.name }}</span>
                  <span class="caja-meseros__pick-sub">{{ meseroRowSubtitle(w) }}</span>
                </button>
              </li>
            </ul>
          </template>
          <div v-else class="admin-empty-card">
            <p>No hay meseros en esta sucursal</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Arqueo -->
    <div v-show="activeTab === 'arqueo'" class="caja-panel caja-spa__panel">
      <p class="caja-panel__hint">Contá el efectivo físico y cerrá el turno.</p>

      <div v-if="!booting && !currentShift" class="admin-empty-card">
        <p>No hay turno abierto</p>
        <button type="button" class="ghost-btn" @click="goTab('apertura')">Ir a apertura</button>
      </div>

      <template v-else-if="summary">
        <div class="caja-quick-actions">
          <button type="button" class="ghost-btn" @click="openShiftCashPdf">Ver PDF</button>
          <button type="button" class="ghost-btn" @click="goTab('liquidacion')">Ver liquidación del turno</button>
        </div>
        <div class="panel-muted caja-summary">
          <p><span>Total cobrado (todos los medios)</span> <strong>{{ formatMoney(summary.payments_all_methods_total) }}</strong></p>
          <p>
            <span>Desglose cobranzas</span>
            <strong
              >Ef. {{ formatMoney(summary.cash_from_sales) }} · QR
              {{ formatMoney(summary.payment_totals?.qr) }} · Tarj. {{ formatMoney(summary.payment_totals?.card) }}</strong
            >
          </p>
          <p><span>Efectivo inicial</span> <strong>{{ formatMoney(summary.opening_cash) }}</strong></p>
          <p><span>Ventas efectivo</span> <strong>{{ formatMoney(summary.cash_from_sales) }}</strong></p>
          <p><span>Ingresos en caja</span> <strong>+ {{ formatMoney(summary.drawer_in) }}</strong></p>
          <p><span>Retiros + pagos chicas (egresos)</span> <strong>- {{ formatMoney(summary.drawer_out) }}</strong></p>
          <p>
            <span>Acumulado liquidado a chicas</span> <strong>{{ formatMoney(summary.companion_payouts_total) }}</strong>
          </p>
          <p class="caja-expected"><span>Efectivo esperado en cajón</span> <strong>{{ formatMoney(summary.expected_cash) }}</strong></p>
        </div>

        <form class="maint-field-grid" @submit.prevent="closeShift">
          <div class="field-block field-block--full">
            <span>Efectivo contado</span>
            <input v-model.number="closeForm.closing_cash" type="number" min="0" required />
          </div>
          <div class="field-block field-block--full">
            <span>Diferencia previa</span>
            <p class="caja-diff">{{ formatMoney(differenceValue()) }}</p>
          </div>
          <div class="maint-form-actions field-block--full">
            <button type="submit" class="primary-btn danger-outline">Cerrar turno</button>
          </div>
        </form>
      </template>
    </div>

    <!-- Movimientos -->
    <div v-show="activeTab === 'movimientos'" class="caja-panel caja-spa__panel">
      <p class="caja-panel__hint">Entradas y salidas del cajón (atajo arriba: Movimiento).</p>

      <div v-if="!booting && !currentShift" class="admin-empty-card">
        <p>No hay turno abierto</p>
        <button type="button" class="ghost-btn" @click="goTab('apertura')">Ir a apertura</button>
      </div>

      <template v-else-if="!booting">
        <p class="caja-inline-action">
          <button type="button" class="secondary-btn" @click="openMovementModal">Registrar movimiento</button>
        </p>
        <div class="panel-muted caja-resumen">
          <p><span>Entradas</span> <strong>+ {{ formatMoney(movementTotals.incoming) }}</strong></p>
          <p><span>Salidas</span> <strong>- {{ formatMoney(movementTotals.outgoing) }}</strong></p>
          <p>
            <span>Neto</span>
            <strong>{{ movementTotals.net >= 0 ? '+' : '' }}{{ formatMoney(movementTotals.net) }}</strong>
          </p>
        </div>

        <div v-if="!movements.length" class="admin-empty-card">
          <p>Sin movimientos en este turno</p>
        </div>
        <div v-else class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Cuándo</th>
                <th>Tipo</th>
                <th class="num">Monto</th>
                <th>Detalle</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in movements" :key="m.id">
                <td>{{ formatWhen(m.created_at) }}</td>
                <td>{{ m.direction === 'in' ? 'Entrada' : 'Salida' }}</td>
                <td class="num">{{ formatMoney(m.amount) }}</td>
                <td>{{ m.notes || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </div>

    <!-- Salidas / fichada chicas -->
    <div v-show="activeTab === 'chicas'" class="caja-panel caja-spa__panel">
      <p class="caja-panel__hint">Fichá salidas, pagá al irse. Alta de chica nueva desde acá o desde el flujo de registro.</p>
      <details class="caja-details">
        <summary class="caja-details__sum">Cómo funcionan fichada y liquidación</summary>
        <div class="caja-details__body">
          <p>
            El mesero no ve si una salida ya se pagó: en caja ves el sugerido y liquidás al irse. Registrá chica nueva si no
            está en el sistema. Si no hay salida activa, fichá. Manillas y piezas desde la fichada hasta el pago cuentan en
            ese cierre; si vuelve a trabajar, fichá de nuevo.
          </p>
        </div>
      </details>

      <div v-if="!booting && !currentShift" class="admin-empty-card">
        <p>No hay turno abierto</p>
        <button type="button" class="ghost-btn" @click="goTab('apertura')">Ir a apertura</button>
      </div>

      <template v-else-if="!booting">
        <div class="caja-chicas-actions">
          <button type="button" class="primary-btn" @click="openFicharModal">Fichar salida</button>
          <button type="button" class="secondary-btn caja-btn-register" @click="openRegisterChicaModal">Alta chica</button>
        </div>

        <div v-if="loadingSessions" class="panel-muted">Cargando…</div>

        <template v-else>
          <h4 class="caja-subhead">En caja ahora — a pagar</h4>
          <div v-if="!activeCompanionSessions.length" class="admin-empty-card">
            <p>Nadie con salida activa</p>
            <small>Tocá “Fichar salida” cuando la chica se presenta o vuelve a trabajar.</small>
          </div>
          <div v-else class="caja-chica-cards">
            <article v-for="s in activeCompanionSessions" :key="s.id" class="caja-chica-card">
              <div class="caja-chica-card-top">
                <span class="caja-chica-name">{{ s.stage_name }}</span>
                <span class="caja-chica-badge">Activa</span>
              </div>
              <p class="caja-chica-when">Desde {{ formatWhen(s.started_at) }}</p>
              <p class="caja-chica-amount">
                A pagar (sugerido)
                <strong>{{
                  s.suggested_payout_total != null ? formatMoney(s.suggested_payout_total) : 'Config. % en sistema'
                }}</strong>
              </p>
              <p v-if="s.suggested_payout_total != null" class="caja-chica-balance">
                Saldo sugerido: <strong>{{ formatMoney(s.balance_due) }}</strong>
              </p>
              <p class="caja-chica-detail">
                Manillas {{ formatMoney(s.manilla_subtotal) }} · Piezas {{ formatMoney(s.pieza_subtotal) }} ({{ s.pieza_count }} svc)
              </p>
              <button type="button" class="primary-btn caja-chica-pay" @click="openSettleModal(s)">Pagar e irse</button>
            </article>
          </div>

          <h4 class="caja-subhead caja-subhead--spaced">Ya liquidadas (este turno)</h4>
          <div v-if="!settledCompanionSessions.length" class="admin-empty-card">
            <p>Sin salidas cerradas aún</p>
          </div>
          <div v-else class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Chica</th>
                  <th>Desde</th>
                  <th class="num">Sugerido</th>
                  <th class="num">Pagado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="s in settledCompanionSessions" :key="s.id">
                  <td>{{ s.stage_name }}</td>
                  <td>{{ formatWhen(s.started_at) }}</td>
                  <td class="num">
                    {{ s.suggested_payout_total != null ? formatMoney(s.suggested_payout_total) : '—' }}
                  </td>
                  <td class="num">{{ formatMoney(s.paid_out_total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
      </template>
    </div>

    <!-- Liquidación -->
    <div v-show="activeTab === 'liquidacion'" class="caja-panel caja-spa__panel">
      <CajaLiquidacionPanel
        :report="report"
        :loading-report="loadingReport"
        :booting="booting"
        :needs-site-picker="needsSitePicker"
        :sites="sites"
        :site-picker-id="sitePickerId"
        :history="history"
        :selected-shift-id="selectedReportShiftId"
        :current-shift="currentShift"
        @update:site-picker-id="onSitePickerUpdate"
        @update:selected-shift-id="onSelectedReportShift"
        @go-arqueo="goTab('arqueo')"
      />
    </div>
    </div>

    <!-- Modal remuneración mesero -->
    <CajaModal
      :open="meseroEditModalOpen"
      :title="meseroModalTitle"
      size="md"
      @close="closeMeseroEditor"
    >
      <form v-if="selectedMeseroId" class="caja-meseros__form" @submit.prevent="saveMeseroCompensation">
        <label class="caja-meseros__field">
          <span>Tipo de pago</span>
          <select v-model="meseroForm.waiter_compensation_type">
            <option value="per_payment">Comisión por cobro (ventas)</option>
            <option value="payroll_monthly">Sueldo mensual / fijo</option>
            <option value="payroll_weekly">Pago semanal / fijo</option>
          </select>
        </label>
        <label v-if="meseroForm.waiter_compensation_type === 'per_payment'" class="caja-meseros__field">
          <span>Comisión (%)</span>
          <input
            v-model="meseroForm.waiter_commission_rate_pct"
            type="number"
            min="0"
            max="100"
            step="0.25"
            placeholder="Vacío = % sucursal"
          />
          <small class="caja-meseros__hint">Vacío usa el % general de la sucursal.</small>
        </label>
        <div class="caja-meseros__actions caja-meseros__actions--row">
          <button type="button" class="ghost-btn" @click="closeMeseroEditor">Cancelar</button>
          <button type="submit" class="primary-btn" :disabled="savingMesero">
            {{ savingMesero ? 'Guardando…' : 'Guardar' }}
          </button>
        </div>
      </form>
    </CajaModal>

    <!-- Modal movimiento -->
    <CajaModal :open="movementModalOpen" title="Movimiento de caja" size="md" @close="movementModalOpen = false">
      <form class="maint-field-grid" @submit.prevent="submitMovement">
        <div class="field-block">
          <span>Tipo</span>
          <select v-model="movementForm.direction">
            <option value="out">Salida</option>
            <option value="in">Entrada</option>
          </select>
        </div>
        <div class="field-block">
          <span>Monto</span>
          <input v-model.number="movementForm.amount" type="number" min="1" required />
        </div>
        <div class="field-block field-block--full">
          <span>Detalle</span>
          <input v-model="movementForm.notes" type="text" maxlength="400" placeholder="Motivo" />
        </div>
        <div class="maint-form-actions field-block--full">
          <button type="submit" class="primary-btn">Guardar</button>
        </div>
      </form>
    </CajaModal>

    <!-- Modal abrir turno (acceso rápido) -->
    <CajaModal :open="openShiftModalOpen" title="Abrir turno de caja" size="md" @close="openShiftModalOpen = false">
      <form class="maint-field-grid" @submit.prevent="openShift">
        <div class="field-block field-block--full">
          <span>Período</span>
          <select v-model="openShiftForm.period">
            <option value="day">Día</option>
            <option value="night">Noche</option>
          </select>
        </div>
        <div class="field-block field-block--full">
          <span>Efectivo inicial</span>
          <input v-model.number="openShiftForm.opening_cash" type="number" min="0" required />
        </div>
        <div class="maint-form-actions field-block--full">
          <button type="submit" class="primary-btn">Confirmar apertura</button>
        </div>
      </form>
    </CajaModal>

    <CajaModal
      :open="registerChicaModalOpen"
      title="Registrar chica en esta sucursal"
      size="md"
      @close="registerChicaModalOpen = false"
    >
      <p class="field-hint" style="margin: 0 0 0.75rem">
        Nombre artístico como lo van a usar en el POS. No hace falta fichar salida todavía: con esto ya aparece en la lista
        para el mesero.
      </p>
      <form class="maint-field-grid" @submit.prevent="registerNewCompanion(false)">
        <div class="field-block field-block--full">
          <span>Nombre artístico</span>
          <input
            v-model="newCompanionName"
            type="text"
            maxlength="120"
            placeholder="Ej. Luna, Candy…"
            :disabled="registeringCompanion"
            required
          />
        </div>
        <div class="maint-form-actions field-block--full">
          <button type="submit" class="primary-btn" :disabled="registeringCompanion">Guardar en sucursal</button>
        </div>
      </form>
    </CajaModal>

    <CajaModal :open="ficharModalOpen" title="Fichar salida de chica" size="md" @close="ficharModalOpen = false">
      <p class="field-hint" style="margin: 0 0 0.75rem">
        A partir de ahora, manillas y piezas de esta chica en este turno quedan en esta salida hasta que liquidés.
      </p>
      <form class="maint-field-grid" @submit.prevent="submitFichar">
        <div class="field-block field-block--full">
          <span>Chica</span>
          <select v-model.number="ficharForm.companion_id" required>
            <option :value="null" disabled>Elegir…</option>
            <option v-for="c in companions" :key="c.id" :value="c.id">{{ c.stage_name }}</option>
          </select>
        </div>
        <div class="field-block field-block--full caja-register-inline">
          <span>¿No está en la lista?</span>
          <div class="caja-register-row">
            <input
              v-model="newCompanionName"
              type="text"
              maxlength="120"
              placeholder="Nombre artístico"
              :disabled="registeringCompanion"
              @keydown.enter.prevent="() => registerNewCompanion(true)"
            />
            <button
              type="button"
              class="secondary-btn"
              :disabled="registeringCompanion"
              @click="registerNewCompanion(true)"
            >
              Registrar en sucursal
            </button>
          </div>
          <small class="field-hint">Queda como contacto “chica” de la sucursal y ya puede ficharse.</small>
        </div>
        <div class="maint-form-actions field-block--full">
          <button type="submit" class="primary-btn">Confirmar fichada</button>
        </div>
      </form>
    </CajaModal>

    <CajaModal
      :open="settleModalOpen"
      title="Pago a la chica (sale de la noche)"
      size="md"
      @close="settleModalOpen = false"
    >
      <p v-if="settleCtx" class="field-hint" style="margin: 0 0 0.75rem">
        {{ settleCtx.stage_name }} — se cierra esta salida; si vuelve, fichala de nuevo.
      </p>
      <form class="maint-field-grid" @submit.prevent="submitSettle">
        <div class="field-block field-block--full">
          <span>Monto que entregás (efectivo u otro acuerdo)</span>
          <input v-model.number="settleForm.amount" type="number" min="1" required />
        </div>
        <div class="field-block field-block--full">
          <span>Nota (opcional)</span>
          <input v-model="settleForm.notes" type="text" maxlength="400" placeholder="Ej. mixto, se llevó propina aparte" />
        </div>
        <div class="maint-form-actions field-block--full">
          <button type="submit" class="primary-btn">Confirmar pago y cierre de salida</button>
        </div>
      </form>
    </CajaModal>

    <PdfPreviewModal
      :open="pdfPreviewOpen"
      :title="pdfPreviewTitle"
      :loading="pdfPreviewLoading"
      :src="pdfPreviewUrl"
      iframe-title="Vista previa turno de caja"
      @close="closePdfPreview"
      @download="downloadShiftPdfFromModal"
    />
  </section>
</template>

<style scoped>
.caja-spa {
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
  padding: 0 0 2rem;
}

.caja-spa__top {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem 1rem;
  margin-bottom: 0.65rem;
}

.caja-spa__title-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem 0.85rem;
  min-width: 0;
}

.caja-spa__h1 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.caja-spa__status {
  font-size: 0.82rem;
  color: var(--color-muted, #a4b8ec);
}

.caja-spa__status--muted {
  opacity: 0.85;
}

.caja-spa__status--warn {
  color: rgba(255, 200, 140, 0.95);
  font-weight: 600;
}

.caja-spa__pill {
  font-size: 0.8rem;
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  background: rgba(60, 100, 200, 0.22);
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.28));
}

.caja-spa__quick {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  align-items: center;
}

.caja-spa__quick-btn {
  padding: 0.35rem 0.65rem;
  font-size: 0.85rem;
}

.caja-spa__metrics {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.35rem 0.5rem;
  font-size: 0.78rem;
  color: var(--color-muted, #97ace4);
  margin-bottom: 0.75rem;
  padding: 0.4rem 0;
  border-bottom: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.12));
}

.caja-spa__dot {
  opacity: 0.5;
}

.caja-spa__metrics-hint {
  margin-left: auto;
  font-size: 0.72rem;
  opacity: 0.85;
  cursor: help;
}

@media (max-width: 640px) {
  .caja-spa__metrics-hint {
    width: 100%;
    margin-left: 0;
  }
}

.caja-spa__scope {
  margin-bottom: 0.65rem;
}

.caja-spa__scope--inline {
  max-width: 22rem;
}

.caja-spa__scope-label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.78rem;
  color: var(--color-muted, #97ace4);
}

.caja-spa__scope-select {
  padding: 0.4rem 0.5rem;
  border-radius: 8px;
  font: inherit;
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.28));
  background: rgba(0, 0, 0, 0.15);
  color: inherit;
}

.caja-spa__tabs {
  display: flex;
  flex-wrap: nowrap;
  gap: 0.15rem;
  overflow-x: auto;
  padding-bottom: 0.15rem;
  margin-bottom: 0.85rem;
  border-bottom: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.18));
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
}

.caja-spa__tab {
  flex: 0 0 auto;
  padding: 0.45rem 0.65rem;
  margin-bottom: -1px;
  border: none;
  border-bottom: 2px solid transparent;
  background: transparent;
  color: var(--color-muted, #9eb4e8);
  font: inherit;
  font-size: 0.82rem;
  font-weight: 500;
  cursor: pointer;
  white-space: nowrap;
  transition: color 0.12s, border-color 0.12s;
}

.caja-spa__tab:hover {
  color: var(--color-text, #e8eeff);
}

.caja-spa__tab--active {
  color: var(--color-text, #e8eeff);
  border-bottom-color: rgba(130, 170, 255, 0.75);
  font-weight: 600;
}

.caja-spa__stage {
  min-width: 0;
}

.caja-spa__panel {
  border-radius: 12px;
  padding: 0.85rem 1rem 1rem;
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.16));
  background: rgba(12, 22, 52, 0.2);
}

.caja-panel__hint {
  margin: 0 0 0.85rem;
  font-size: 0.8rem;
  color: var(--color-muted, #97ace4);
  line-height: 1.4;
}

.caja-details {
  margin: 0 0 1rem;
  font-size: 0.82rem;
}

.caja-details__sum {
  cursor: pointer;
  color: var(--color-muted, #8ea8e8);
  user-select: none;
}

.caja-details__sum:hover {
  color: var(--color-text, #e8eeff);
}

.caja-details__body {
  margin-top: 0.5rem;
  padding: 0.6rem 0.75rem;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.12);
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.12));
  color: var(--color-muted, #a4b8ec);
  line-height: 1.45;
}

.caja-details__body p {
  margin: 0;
}

.caja-register-inline {
  padding-top: 0.35rem;
  border-top: 1px dashed var(--border-subtle, rgba(142, 168, 245, 0.25));
}

.caja-register-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  align-items: center;
  margin-top: 0.35rem;
}

.caja-register-row input {
  flex: 1;
  min-width: 10rem;
}

.secondary-btn {
  padding: 0.45rem 0.85rem;
  border-radius: 10px;
  font: inherit;
  cursor: pointer;
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.35));
  background: rgba(80, 120, 255, 0.15);
  color: inherit;
}

.caja-panel {
  min-height: 8rem;
}

.caja-open-info {
  border-radius: 12px;
  padding: 0.9rem 1rem;
  margin-bottom: 1rem;
}

.caja-open-title {
  font-weight: 700;
  margin: 0 0 0.35rem;
}

.caja-summary {
  border-radius: 12px;
  padding: 0.9rem 1rem;
  margin-bottom: 1rem;
  display: grid;
  gap: 0.35rem;
}

.caja-summary p {
  margin: 0;
  display: flex;
  justify-content: space-between;
  gap: 1rem;
}

.caja-summary span {
  color: var(--color-muted, #97ace4);
}

.caja-expected {
  padding-top: 0.35rem;
  margin-top: 0.25rem;
  border-top: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.2));
}

.caja-diff {
  margin: 0;
  font-weight: 700;
}

.danger-outline {
  border: 1px solid rgba(255, 120, 120, 0.45);
}

.caja-quick-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.caja-resumen {
  border-radius: 12px;
  padding: 0.9rem 1rem;
  margin: 1rem 0;
  display: grid;
  gap: 0.35rem;
}

.caja-resumen p {
  margin: 0;
  display: flex;
  justify-content: space-between;
}

.caja-resumen span {
  color: var(--color-muted, #97ace4);
}

.caja-chicas-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem;
  align-items: center;
  margin-bottom: 0.5rem;
}

.caja-btn-register {
  font-weight: 600;
}

.caja-register-hint {
  margin: 0 0 1rem;
  font-size: 0.82rem;
  color: var(--color-muted, #97ace4);
  line-height: 1.45;
}

.caja-inline-action {
  margin: 0 0 0.75rem;
}

.table-wrap {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.2));
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.data-table th,
.data-table td {
  padding: 0.5rem 0.65rem;
  text-align: left;
  border-bottom: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.15));
}

.data-table .num {
  text-align: right;
}

.caja-subhead {
  margin: 0 0 0.65rem;
  font-size: 1rem;
  font-weight: 600;
}

.caja-subhead--spaced {
  margin-top: 1.25rem;
}

.caja-chica-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(17rem, 1fr));
  gap: 0.75rem;
}

.caja-chica-card {
  border-radius: 14px;
  padding: 1rem;
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.35));
  background: var(--panel-muted-bg, rgba(25, 40, 85, 0.4));
}

.caja-chica-card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.35rem;
}

.caja-chica-name {
  font-weight: 700;
  font-size: 1.05rem;
}

.caja-chica-badge {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 0.2rem 0.5rem;
  border-radius: 999px;
  background: rgba(120, 200, 140, 0.25);
  border: 1px solid rgba(160, 230, 180, 0.35);
}

.caja-chica-when {
  margin: 0 0 0.5rem;
  font-size: 0.82rem;
  color: var(--color-muted, #97ace4);
}

.caja-chica-amount {
  margin: 0 0 0.25rem;
  font-size: 0.9rem;
}

.caja-chica-amount strong {
  display: block;
  font-size: 1.35rem;
  margin-top: 0.15rem;
}

.caja-chica-balance {
  margin: 0 0 0.5rem;
  font-size: 0.88rem;
}

.caja-chica-detail {
  margin: 0 0 0.75rem;
  font-size: 0.8rem;
  color: var(--color-muted, #97ace4);
}

.caja-chica-pay {
  width: 100%;
}

.caja-meseros-wrap {
  padding-bottom: 0.25rem;
}

.caja-meseros--solo {
  max-width: 100%;
}

.caja-meseros__list {
  border-radius: 12px;
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.22));
  background: rgba(15, 28, 62, 0.35);
  padding: 0.65rem 0.5rem;
  max-height: min(65vh, 24rem);
  overflow: auto;
}

.caja-meseros__default {
  margin: 0 0 0.5rem;
  padding: 0.35rem 0.45rem;
  font-size: 0.78rem;
  color: var(--color-muted, #97ace4);
  border-bottom: 1px dashed var(--border-subtle, rgba(142, 168, 245, 0.2));
}

.caja-meseros__default-hint {
  display: block;
  margin-top: 0.15rem;
  font-size: 0.72rem;
  opacity: 0.9;
}

.caja-meseros__ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.caja-meseros__pick {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.15rem;
  text-align: left;
  padding: 0.55rem 0.6rem;
  margin-bottom: 0.35rem;
  border-radius: 10px;
  border: 1px solid transparent;
  background: rgba(30, 48, 110, 0.35);
  color: inherit;
  font: inherit;
  cursor: pointer;
  transition:
    background 0.12s,
    border-color 0.12s;
}

.caja-meseros__pick:hover {
  border-color: rgba(130, 170, 255, 0.35);
  background: rgba(55, 85, 180, 0.28);
}

.caja-meseros__pick-name {
  font-weight: 600;
  font-size: 0.95rem;
}

.caja-meseros__pick-sub {
  font-size: 0.75rem;
  color: var(--color-muted, #9eb4e8);
  line-height: 1.3;
}

.caja-meseros__form {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  max-width: 100%;
}

.caja-meseros__field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  font-size: 0.88rem;
}

.caja-meseros__field span:first-child {
  color: var(--color-muted, #a4b8ec);
  font-weight: 600;
}

.caja-meseros__field select,
.caja-meseros__field input {
  padding: 0.5rem 0.6rem;
  border-radius: 10px;
  font: inherit;
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.35));
  background: rgba(0, 0, 0, 0.2);
  color: inherit;
}

.caja-meseros__hint {
  font-size: 0.76rem;
  color: var(--color-muted, #97ace4);
  line-height: 1.35;
}

.caja-meseros__actions {
  margin-top: 0.25rem;
}

.caja-meseros__actions--row {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.caja-meseros__loading {
  padding: 0.75rem;
  font-size: 0.88rem;
}
</style>
