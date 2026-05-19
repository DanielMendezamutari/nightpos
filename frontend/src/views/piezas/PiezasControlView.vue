<script setup>
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue'
import PdfPreviewModal from '../../components/PdfPreviewModal.vue'
import { apiRequest } from '../../services/api'
import { useAuthStore } from '../../stores/authStore'
import { useNotificationStore } from '../../stores/notificationStore'
import { useBranchSiteScope } from '../../composables/useBranchSiteScope'
import { usePdfPreview } from '../../composables/usePdfPreview'

const auth = useAuthStore()
const notify = useNotificationStore()
const { branchQuery, initSiteScope } = useBranchSiteScope(auth)

const {
  pdfPreviewOpen,
  pdfPreviewLoading,
  pdfPreviewUrl,
  pdfPreviewTitle,
  openPdfPreview,
  closePdfPreview,
  downloadPdfPreview,
} = usePdfPreview(() => auth.token.value)

const loading = ref(false)
const services = ref([])
const companions = ref([])
const pendingAlerts = ref([])
const selectedService = ref(null)

const modals = reactive({ newService: false, quickCompanion: false, extend: false, pay: false })
const creatingCompanion = ref(false)
let alertsTimer = null
let audioContext = null

const createForm = reactive({
  room_label: '',
  companion_id: '',
  rate_per_hour: 1200,
  planned_minutes: 60,
  alert_before_minutes: 5,
  grace_minutes: 0,
  notes: '',
  payment_method: 'cash',
  payment_amount: 0,
})

const quickCompanion = reactive({ stage_name: '' })
const extendForm = reactive({ added_minutes: 30, notes: '' })
const payForm = reactive({ shift_turn_id: '', method: 'cash', amount: 0 })

const openCount = computed(() => services.value.filter((s) => s.status === 'open').length)
const closedCount = computed(() => services.value.filter((s) => s.status === 'closed').length)

function formatMoney(v) { return (Number(v) || 0).toLocaleString('es-AR', { maximumFractionDigits: 0 }) }
function badgeLabel(status) { return status === 'open' ? 'Abierta' : status === 'closed' ? 'Cerrada' : 'Cobrada' }
const alertedLocally = new Set()

function playAlertTone() {
  try {
    const Ctx = window.AudioContext || window.webkitAudioContext
    if (!Ctx) return
    if (!audioContext) audioContext = new Ctx()
    if (audioContext.state === 'suspended') {
      audioContext.resume().catch(() => {})
    }
    const nowTime = audioContext.currentTime
    const osc = audioContext.createOscillator()
    const gain = audioContext.createGain()
    osc.type = 'triangle'
    osc.frequency.setValueAtTime(880, nowTime)
    gain.gain.setValueAtTime(0.0001, nowTime)
    gain.gain.exponentialRampToValueAtTime(0.2, nowTime + 0.02)
    gain.gain.exponentialRampToValueAtTime(0.0001, nowTime + 0.45)
    osc.connect(gain)
    gain.connect(audioContext.destination)
    osc.start(nowTime)
    osc.stop(nowTime + 0.46)
  } catch (_) {
    // Browser may block autoplay audio.
  }
}

async function loadCompanions() {
  const payload = await apiRequest('/companions', {}, auth.token.value)
  companions.value = payload.data || []
}

async function loadServices() {
  const payload = await apiRequest('/room-services', {}, auth.token.value)
  services.value = payload.data || []

  const nowMs = Date.now()
  const localPending = services.value
    .filter((s) => s.status === 'open' && s.alert_at && !s.alert_notified_at)
    .filter((s) => {
      const ts = new Date(s.alert_at).getTime()
      return Number.isFinite(ts) && ts <= nowMs
    })
    .map((s) => ({
      service_id: s.id,
      room_label: s.room_label,
      companion_name: s.companion_name,
      planned_minutes: s.planned_minutes,
      alert_at: s.alert_at,
    }))

  if (localPending.length) {
    pendingAlerts.value = localPending
    for (const a of localPending) {
      if (!alertedLocally.has(a.service_id)) {
        alertedLocally.add(a.service_id)
        playAlertTone()
        notify.error(`Alerta pieza #${a.service_id}: enviar personal a puerta (${a.room_label || 'sin pieza'})`)
      }
    }
  }
}

async function loadAlerts() {
  const payload = await apiRequest('/room-services/alerts', {}, auth.token.value)
  pendingAlerts.value = payload.data || []
  for (const a of pendingAlerts.value) {
    if (!alertedLocally.has(a.service_id)) {
      alertedLocally.add(a.service_id)
      playAlertTone()
      notify.error(`Alerta pieza #${a.service_id}: enviar personal a puerta (${a.room_label || 'sin pieza'})`)
    }
  }
}

async function bootstrap() {
  loading.value = true
  try {
    await initSiteScope()
    await Promise.all([loadServices(), loadCompanions(), loadAlerts()])
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar módulo de piezas.')
  } finally {
    loading.value = false
  }
}

function openModal(name) { modals[name] = true }
function closeModal(name) { modals[name] = false }

async function acknowledgeAlert(serviceId) {
  try {
    await apiRequest(`/room-services/${serviceId}/alerts/ack`, { method: 'POST', body: JSON.stringify({}) }, auth.token.value)
    pendingAlerts.value = pendingAlerts.value.filter((a) => Number(a.service_id) !== Number(serviceId))
    services.value = services.value.map((s) =>
      Number(s.id) === Number(serviceId) ? { ...s, alert_notified_at: new Date().toISOString() } : s,
    )
    notify.success(`Alerta de pieza #${serviceId} atendida.`)
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo marcar alerta como atendida.')
  }
}

function openExtendModal(service) {
  selectedService.value = service
  extendForm.added_minutes = 30
  extendForm.notes = ''
  openModal('extend')
}

function openPayModal(service) {
  selectedService.value = service
  payForm.shift_turn_id = service?.shift_turn_id || ''
  payForm.method = 'cash'
  payForm.amount = Number(service?.balance_due || 0)
  openModal('pay')
}

async function createCompanionQuick() {
  const stageName = quickCompanion.stage_name.trim()
  if (!stageName) return notify.error('Ingresa nombre artístico/alias.')

  creatingCompanion.value = true
  try {
    const payload = await apiRequest('/companions/quick-create', {
      method: 'POST',
      body: JSON.stringify({ stage_name: stageName }),
    }, auth.token.value)

    await loadCompanions()
    createForm.companion_id = payload.data.id
    quickCompanion.stage_name = ''
    closeModal('quickCompanion')
    openModal('newService')
    notify.success('Chica lista para seleccionar.')
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo crear chica rápida.')
  } finally {
    creatingCompanion.value = false
  }
}

async function openService() {
  if (!createForm.companion_id) return notify.error('Selecciona una chica.')
  if (!createForm.payment_amount || Number(createForm.payment_amount) < 1) return notify.error('Ingresa monto de pago anticipado.')

  try {
    await apiRequest('/room-services', {
      method: 'POST',
      body: JSON.stringify({
        room_label: createForm.room_label || null,
        companion_id: Number(createForm.companion_id),
        rate_per_hour: Number(createForm.rate_per_hour),
        planned_minutes: Number(createForm.planned_minutes) || null,
        alert_before_minutes: Number(createForm.alert_before_minutes) || 5,
        grace_minutes: Number(createForm.grace_minutes) || 0,
        notes: createForm.notes || null,
        payment_method: createForm.payment_method,
        payment_amount: Number(createForm.payment_amount),
      }),
    }, auth.token.value)

    createForm.room_label = ''
    createForm.notes = ''
    createForm.planned_minutes = 60
    createForm.alert_before_minutes = 5
    closeModal('newService')
    notify.success('Pieza abierta con pago anticipado.')
    await loadServices()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo abrir pieza.')
  }
}

async function extendService() {
  if (!selectedService.value?.id) return
  try {
    await apiRequest(`/room-services/${selectedService.value.id}/extend`, {
      method: 'POST',
      body: JSON.stringify({ added_minutes: Number(extendForm.added_minutes), notes: extendForm.notes || null }),
    }, auth.token.value)
    closeModal('extend')
    notify.success('Tiempo extendido.')
    await loadServices()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo extender.')
  }
}

async function closeService(service) {
  try {
    const payload = await apiRequest(`/room-services/${service.id}/close`, { method: 'POST', body: JSON.stringify({}) }, auth.token.value)
    const due = Number(payload?.data?.balance_due || 0)
    if (due > 0) {
      notify.info(`Pieza cerrada. Saldo pendiente: ${formatMoney(due)}`)
    } else {
      notify.success('Pieza cerrada y cubierta por pago anticipado.')
    }
    await loadServices()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cerrar pieza.')
  }
}

async function payService() {
  if (!selectedService.value?.id) return
  if (!payForm.shift_turn_id || !payForm.amount) return notify.error('Completa turno y monto.')

  try {
    await apiRequest(`/room-services/${selectedService.value.id}/pay`, {
      method: 'POST',
      body: JSON.stringify({ shift_turn_id: Number(payForm.shift_turn_id), method: payForm.method, amount: Number(payForm.amount) }),
    }, auth.token.value)
    closeModal('pay')
    notify.success('Pago adicional registrado.')
    await loadServices()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo registrar pago adicional.')
  }
}

async function openRoomServicePdf(serviceId) {
  try {
    await openPdfPreview(`/room-services/${serviceId}/pdf${branchQuery()}`, `Pieza / servicio #${serviceId}`)
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar el PDF.')
    closePdfPreview()
  }
}

async function downloadRoomPdfFromModal() {
  try {
    await downloadPdfPreview('pieza-servicio.pdf')
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo descargar el PDF.')
  }
}

onMounted(() => {
  bootstrap()
  alertsTimer = setInterval(() => {
    Promise.all([loadAlerts(), loadServices()]).catch(() => {})
  }, 5000)
})
onUnmounted(() => {
  if (alertsTimer) clearInterval(alertsTimer)
  alertsTimer = null
})
</script>

<template>
  <section class="panel">
    <div class="panel-head"><h3>Piezas por tiempo</h3><span>{{ loading ? 'Cargando...' : `${services.length} servicios` }}</span></div>
    <p class="maint-tab-intro">La pieza se paga antes de entrar (anticipo obligatorio). Si queda saldo al cerrar, se regulariza en caja.</p>

    <div class="pieces-summary">
      <div class="pieces-kpi"><strong>{{ openCount }}</strong><span>Abiertas</span></div>
      <div class="pieces-kpi"><strong>{{ closedCount }}</strong><span>Cerradas con saldo</span></div>
      <div class="pieces-actions">
        <button class="primary-btn" type="button" @click="openModal('newService')">Nueva pieza</button>
        <button class="ghost-btn" type="button" @click="openModal('quickCompanion')">Alta rápida chica</button>
      </div>
    </div>

    <article v-if="pendingAlerts.length" class="panel panel-muted alerts-box">
      <div class="panel-head"><h4>Alertas para cajera</h4><span>{{ pendingAlerts.length }} pendientes</span></div>
      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th>Pieza</th><th>Chica</th><th>Min planificados</th><th>Hora alerta</th><th></th></tr></thead>
          <tbody>
            <tr v-for="a in pendingAlerts" :key="a.service_id">
              <td>#{{ a.service_id }} {{ a.room_label || '—' }}</td>
              <td>{{ a.companion_name || '—' }}</td>
              <td>{{ a.planned_minutes || '—' }}</td>
              <td>{{ a.alert_at || '—' }}</td>
              <td class="table-actions"><button type="button" class="primary-btn primary-btn-sm" @click="acknowledgeAlert(a.service_id)">Atendido</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </article>

    <div class="table-wrap" v-if="services.length">
      <table class="data-table">
        <thead><tr><th>ID</th><th>Pieza</th><th>Chica</th><th>Subtotal</th><th>Pagado</th><th>Saldo</th><th>Estado</th><th class="table-actions">Acciones</th></tr></thead>
        <tbody>
          <tr v-for="s in services" :key="s.id">
            <td>#{{ s.id }}</td><td>{{ s.room_label || '—' }}</td><td>{{ s.companion_name || '—' }}</td><td>{{ formatMoney(s.subtotal) }}</td><td>{{ formatMoney(s.paid_total) }}</td><td>{{ formatMoney(s.balance_due) }}</td>
            <td><span class="status-pill" :data-status="s.status">{{ badgeLabel(s.status) }}</span></td>
            <td class="table-actions row-buttons">
              <button type="button" class="ghost-btn ghost-btn-sm" @click="openRoomServicePdf(s.id)">PDF</button>
              <button type="button" class="ghost-btn ghost-btn-sm" :disabled="s.status !== 'open'" @click="openExtendModal(s)">Extender</button>
              <button type="button" class="ghost-btn ghost-btn-sm" :disabled="s.status !== 'open'" @click="closeService(s)">Cerrar</button>
              <button type="button" class="primary-btn primary-btn-sm" :disabled="s.status !== 'closed' || Number(s.balance_due || 0) <= 0" @click="openPayModal(s)">Saldo</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else class="admin-empty-card"><p>No hay piezas registradas todavía.</p></div>

    <PdfPreviewModal
      :open="pdfPreviewOpen"
      :title="pdfPreviewTitle"
      :loading="pdfPreviewLoading"
      :src="pdfPreviewUrl"
      iframe-title="Vista previa pieza / servicio"
      @close="closePdfPreview"
      @download="downloadRoomPdfFromModal"
    />
  </section>

  <div v-if="modals.newService" class="maint-product-modal-overlay" @click.self="closeModal('newService')">
    <article class="panel pieces-modal-card" @click.stop>
      <div class="panel-head"><h3>Nueva pieza</h3><button type="button" class="ghost-btn" @click="closeModal('newService')">Cerrar</button></div>
      <form class="pieces-modal-form" @submit.prevent="openService">
        <label>Pieza<input v-model="createForm.room_label" type="text" maxlength="60" placeholder="Ej. Privado 2" /></label>
        <label>Chica<select v-model.number="createForm.companion_id" required><option value="" disabled>Seleccionar...</option><option v-for="c in companions" :key="c.id" :value="c.id">{{ c.stage_name }} (#{{ c.id }})</option></select></label>
        <label>Tarifa por hora<input v-model.number="createForm.rate_per_hour" type="number" min="1" required /></label>
        <label>Minutos planificados<input v-model.number="createForm.planned_minutes" type="number" min="1" max="1440" required /></label>
        <label>Alertar antes (min)<input v-model.number="createForm.alert_before_minutes" type="number" min="1" max="120" required /></label>
        <label>Tolerancia (min)<input v-model.number="createForm.grace_minutes" type="number" min="0" /></label>
        <label>Pago anticipado - medio<select v-model="createForm.payment_method"><option value="cash">Efectivo</option><option value="qr">QR</option><option value="card">Tarjeta</option></select></label>
        <label>Pago anticipado - monto<input v-model.number="createForm.payment_amount" type="number" min="1" required /></label>
        <label class="span-2">Notas<input v-model="createForm.notes" type="text" maxlength="400" /></label>
        <div class="modal-actions span-2"><button type="button" class="ghost-btn" @click="closeModal('newService')">Cancelar</button><button type="submit" class="primary-btn">Abrir pieza</button></div>
      </form>
    </article>
  </div>

  <div v-if="modals.quickCompanion" class="maint-product-modal-overlay" @click.self="closeModal('quickCompanion')">
    <article class="panel pieces-modal-card" @click.stop>
      <div class="panel-head"><h3>Alta rápida chica</h3><button type="button" class="ghost-btn" @click="closeModal('quickCompanion')">Cerrar</button></div>
      <form class="pieces-modal-form" @submit.prevent="createCompanionQuick">
        <label class="span-2">Nombre artístico / alias<input v-model="quickCompanion.stage_name" type="text" required maxlength="120" placeholder="Ej. Luna" /></label>
        <div class="modal-actions span-2"><button type="button" class="ghost-btn" @click="closeModal('quickCompanion')">Cancelar</button><button type="submit" class="primary-btn" :disabled="creatingCompanion">{{ creatingCompanion ? 'Guardando...' : 'Guardar' }}</button></div>
      </form>
    </article>
  </div>

  <div v-if="modals.extend && selectedService" class="maint-product-modal-overlay" @click.self="closeModal('extend')">
    <article class="panel pieces-modal-card" @click.stop>
      <div class="panel-head"><h3>Extender pieza #{{ selectedService.id }}</h3><button type="button" class="ghost-btn" @click="closeModal('extend')">Cerrar</button></div>
      <form class="pieces-modal-form" @submit.prevent="extendService">
        <label>Minutos a agregar<input v-model.number="extendForm.added_minutes" type="number" min="1" required /></label>
        <label class="span-2">Nota<input v-model="extendForm.notes" type="text" maxlength="300" /></label>
        <div class="modal-actions span-2"><button type="button" class="ghost-btn" @click="closeModal('extend')">Cancelar</button><button type="submit" class="primary-btn">Confirmar extensión</button></div>
      </form>
    </article>
  </div>

  <div v-if="modals.pay && selectedService" class="maint-product-modal-overlay" @click.self="closeModal('pay')">
    <article class="panel pieces-modal-card" @click.stop>
      <div class="panel-head"><h3>Registrar saldo pieza #{{ selectedService.id }}</h3><button type="button" class="ghost-btn" @click="closeModal('pay')">Cerrar</button></div>
      <form class="pieces-modal-form" @submit.prevent="payService">
        <label>Turno de caja<input v-model.number="payForm.shift_turn_id" type="number" min="1" required /></label>
        <label>Medio de pago<select v-model="payForm.method"><option value="cash">Efectivo</option><option value="qr">QR</option><option value="card">Tarjeta</option></select></label>
        <label class="span-2">Monto saldo<input v-model.number="payForm.amount" type="number" min="1" required /></label>
        <div class="modal-actions span-2"><button type="button" class="ghost-btn" @click="closeModal('pay')">Cancelar</button><button type="submit" class="primary-btn">Confirmar pago</button></div>
      </form>
    </article>
  </div>
</template>

<style scoped>
.maint-product-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1200;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.58);
  backdrop-filter: blur(2px);
}

.pieces-summary { display: grid; grid-template-columns: repeat(2, minmax(0, 140px)) 1fr; gap: 0.75rem; margin: 1rem 0; align-items: stretch; }
.pieces-kpi { border: 1px solid var(--color-border-soft, #ccd4e7); border-radius: 10px; padding: 0.6rem 0.7rem; display: grid; gap: 0.15rem; }
.pieces-kpi strong { font-size: 1.1rem; }
.pieces-kpi span { font-size: 0.78rem; color: var(--color-muted, #7b8497); }
.pieces-actions { display: flex; gap: 0.5rem; justify-content: flex-end; align-items: center; }
.status-pill { display: inline-flex; align-items: center; justify-content: center; min-width: 82px; padding: 0.2rem 0.45rem; border-radius: 999px; font-size: 0.75rem; border: 1px solid transparent; }
.status-pill[data-status='open'] { color: #0d7a41; background: rgba(30,180,90,0.12); border-color: rgba(30,180,90,0.35); }
.status-pill[data-status='closed'] { color: #9a6300; background: rgba(255,185,40,0.16); border-color: rgba(255,185,40,0.35); }
.status-pill[data-status='paid'] { color: #1866d3; background: rgba(73,136,255,0.14); border-color: rgba(73,136,255,0.35); }
.row-buttons { display: flex; gap: 0.35rem; justify-content: flex-end; }
.primary-btn-sm { padding: 0.35rem 0.6rem; font-size: 0.78rem; }
.pieces-modal-card {
  width: min(620px, 94vw);
  max-height: 90vh;
  overflow: auto;
  position: relative;
  z-index: 1201;
}
.pieces-modal-form { display: grid; grid-template-columns: 1fr 1fr; gap: 0.7rem; padding: 0 1rem 1rem; }
.pieces-modal-form label { display: grid; gap: 0.3rem; font-size: 0.86rem; }
.span-2 { grid-column: 1 / -1; }
.modal-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }
@media (max-width: 840px) {
  .pieces-summary { grid-template-columns: 1fr 1fr; }
  .pieces-actions { grid-column: 1 / -1; justify-content: flex-start; }
  .pieces-modal-form { grid-template-columns: 1fr; }
  .span-2 { grid-column: 1; }
}
</style>
