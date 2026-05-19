<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import AppLayout from '../components/AppLayout.vue'
import PdfPreviewModal from '../components/PdfPreviewModal.vue'
import { useAuthStore } from '../stores/authStore'
import { apiRequest } from '../services/api'
import { useNotificationStore } from '../stores/notificationStore'
import { usePdfPreview } from '../composables/usePdfPreview'

const auth = useAuthStore()
const notify = useNotificationStore()

const {
  pdfPreviewOpen,
  pdfPreviewLoading,
  pdfPreviewUrl,
  pdfPreviewTitle,
  openPdfPreview,
  closePdfPreview,
  downloadPdfPreview,
} = usePdfPreview(() => auth.token.value)
const message = ref('')
const overview = ref({
  total_branches: 0,
  active_branches: 0,
  suspended_branches: 0,
  monthly_revenue: 0,
})
const subscriptions = ref([])
const paymentsHistory = ref([])
const alerts = ref({
  critical_count: 0,
  warning_count: 0,
  critical: [],
  warning: [],
})
const loading = ref(false)
const dueFilter = ref('all')

const paymentForm = reactive({
  site_id: '',
  amount: 0,
  months_covered: 1,
  note: '',
})

const statusForm = reactive({
  site_id: '',
  status: 'active',
  reason: '',
})

const monthlyFeeForm = reactive({
  site_id: '',
  monthly_fee: 700,
})

const historySiteId = ref('')

const subscriptionOptions = computed(() =>
  subscriptions.value.map((row) => ({ value: String(row.site_id), label: `${row.name} (#${row.site_id})` }))
)

async function loadSaasData() {
  loading.value = true
  try {
    const query = dueFilter.value === 'all' ? '' : `?due_status=${dueFilter.value}`
    const [overviewResp, subscriptionsResp, alertsResp] = await Promise.all([
      apiRequest('/saas/overview', {}, auth.token.value),
      apiRequest(`/saas/subscriptions${query}`, {}, auth.token.value),
      apiRequest('/saas/alerts', {}, auth.token.value),
    ])
    overview.value = overviewResp.data
    subscriptions.value = subscriptionsResp.data || []
    alerts.value = alertsResp.data || alerts.value
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo cargar modulo SaaS.'
    notify.error(message.value)
  } finally {
    loading.value = false
  }
}

async function registerPayment() {
  try {
    await apiRequest(`/saas/subscriptions/${Number(paymentForm.site_id)}/payments`, {
      method: 'POST',
      body: JSON.stringify({
        amount: Number(paymentForm.amount),
        months_covered: Number(paymentForm.months_covered),
        note: paymentForm.note || null,
      }),
    }, auth.token.value)
    message.value = 'Pago SaaS registrado correctamente.'
    notify.success(message.value)
    Object.assign(paymentForm, { site_id: '', amount: 0, months_covered: 1, note: '' })
    await loadSaasData()
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo registrar el pago SaaS.'
    notify.error(message.value)
  }
}

async function updateStatus() {
  try {
    await apiRequest(`/saas/subscriptions/${Number(statusForm.site_id)}/status`, {
      method: 'PATCH',
      body: JSON.stringify({
        status: statusForm.status,
        reason: statusForm.reason || null,
      }),
    }, auth.token.value)
    message.value = 'Estado de sucursal actualizado.'
    notify.warning(message.value)
    Object.assign(statusForm, { site_id: '', status: 'active', reason: '' })
    await loadSaasData()
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo actualizar estado SaaS.'
    notify.error(message.value)
  }
}

async function updateMonthlyFee() {
  try {
    await apiRequest(`/saas/subscriptions/${Number(monthlyFeeForm.site_id)}/monthly-fee`, {
      method: 'PATCH',
      body: JSON.stringify({
        monthly_fee: Number(monthlyFeeForm.monthly_fee),
      }),
    }, auth.token.value)
    message.value = 'Mensualidad actualizada correctamente.'
    notify.success(message.value)
    await loadSaasData()
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo actualizar mensualidad.'
    notify.error(message.value)
  }
}

async function quickToggleStatus(row) {
  const nextStatus = row.status === 'active' ? 'suspended' : 'active'
  try {
    await apiRequest(`/saas/subscriptions/${Number(row.site_id)}/status`, {
      method: 'PATCH',
      body: JSON.stringify({
        status: nextStatus,
        reason: nextStatus === 'suspended' ? 'Corte rapido desde tablero SaaS' : null,
      }),
    }, auth.token.value)
    message.value = `Sucursal ${row.name} actualizada a ${nextStatus}.`
    if (nextStatus === 'suspended') notify.warning(message.value)
    else notify.success(message.value)
    await loadSaasData()
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo cambiar estado.'
    notify.error(message.value)
  }
}

async function loadPaymentsHistory(siteId) {
  try {
    const payload = await apiRequest(`/saas/subscriptions/${Number(siteId)}/payments`, {}, auth.token.value)
    paymentsHistory.value = payload.data || []
  } catch (error) {
    message.value = error instanceof Error ? error.message : 'No se pudo cargar historial de pagos.'
    notify.error(message.value)
  }
}

async function openSaasPaymentsPdf(siteId) {
  if (!siteId) return
  try {
    await openPdfPreview(
      `/saas/subscriptions/${Number(siteId)}/payments/pdf`,
      `Pagos SaaS · sucursal #${siteId}`,
    )
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo generar el PDF de pagos.')
    closePdfPreview()
  }
}

async function downloadSaasPaymentsPdfFromModal() {
  try {
    await downloadPdfPreview('saas-pagos.pdf')
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo descargar el PDF.')
  }
}

function exportPaymentsCsv(siteId) {
  if (!siteId) return
  const apiBase = import.meta.env.VITE_API_BASE_URL || 'http://nightpos.test/api'
  const url = `${apiBase}/saas/subscriptions/${Number(siteId)}/payments/export`
  fetch(url, {
    headers: {
      Authorization: `Bearer ${auth.token.value}`,
      Accept: 'text/csv',
    },
  })
    .then((resp) => resp.blob())
    .then((blob) => {
      const blobUrl = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = blobUrl
      a.download = `saas_pagos_sucursal_${siteId}.csv`
      a.click()
      window.URL.revokeObjectURL(blobUrl)
      notify.info('CSV exportado correctamente.')
    })
}

function applyQuickPayment(row) {
  paymentForm.site_id = String(row.site_id)
  paymentForm.amount = Number(row.monthly_fee)
  paymentForm.months_covered = 1
  paymentForm.note = 'Cobro rapido desde tablero'
}

onMounted(loadSaasData)
</script>

<template>
  <AppLayout>
    <p v-if="message" class="info-text">{{ message }}</p>

    <section class="stats-grid">
      <article class="stat-card">
        <p class="stat-label">Sucursales totales</p>
        <h3 class="stat-value">{{ overview.total_branches }}</h3>
        <span class="stat-trend">Registradas</span>
      </article>
      <article class="stat-card">
        <p class="stat-label">Sucursales activas</p>
        <h3 class="stat-value">{{ overview.active_branches }}</h3>
        <span class="stat-trend">En servicio</span>
      </article>
      <article class="stat-card">
        <p class="stat-label">Sucursales suspendidas</p>
        <h3 class="stat-value">{{ overview.suspended_branches }}</h3>
        <span class="stat-trend">Cortadas</span>
      </article>
      <article class="stat-card">
        <p class="stat-label">Ingreso mensual SaaS</p>
        <h3 class="stat-value">Bs {{ overview.monthly_revenue }}</h3>
        <span class="stat-trend">Cobrado este mes</span>
      </article>
    </section>

    <section class="content-grid">
      <article class="panel">
        <div class="panel-head"><h3>Semaforo de riesgo SaaS</h3><span>Accion inmediata</span></div>
        <div class="modules-list">
          <div class="module-item">
            <div>
              <h4>Criticas (rojo)</h4>
              <p>Sucursales suspendidas o vencidas que debes cobrar/reactivar hoy.</p>
            </div>
            <strong>{{ alerts.critical_count }}</strong>
          </div>
          <div class="module-item">
            <div>
              <h4>Por vencer (amarillo)</h4>
              <p>Sucursales que vencen en 5 dias o menos.</p>
            </div>
            <strong>{{ alerts.warning_count }}</strong>
          </div>
        </div>
      </article>
      <article class="panel">
        <div class="panel-head"><h3>Flujo recomendado</h3><span>Paso a paso</span></div>
        <ul class="activity-list">
          <li><time>1</time><p>Revisa sucursales en rojo y usa "Reactivar" tras registrar pago.</p></li>
          <li><time>2</time><p>Cobra las amarillas con "Cobro rapido 1 mes".</p></li>
          <li><time>3</time><p>Actualiza mensualidad cuando cambie tu plan comercial.</p></li>
        </ul>
      </article>
    </section>

    <section class="content-grid">
      <article class="panel">
        <div class="panel-head"><h3>Suscripciones por sucursal</h3><span>{{ loading ? 'Cargando...' : `${subscriptions.length} registros` }}</span></div>
        <div class="form-grid" style="margin-bottom: 10px;">
          <select v-model="dueFilter" @change="loadSaasData">
            <option value="all">Todas</option>
            <option value="ok">Al dia (verde)</option>
            <option value="warning">Por vencer (amarillo)</option>
            <option value="overdue">Vencidas/cortadas (rojo)</option>
          </select>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th><th>Sucursal</th><th>Mensualidad</th><th>Estado</th><th>Ultimo pago</th><th>Vence</th><th>Accion rapida</th><th>Cobro rapido</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in subscriptions" :key="row.site_id">
                <td>{{ row.site_id }}</td>
                <td>{{ row.name }}</td>
                <td>Bs {{ row.monthly_fee }}</td>
                <td>
                  <span :class="['status-pill', row.due_status]">
                    {{ row.status }} ({{ row.due_status }})
                  </span>
                </td>
                <td>{{ row.last_paid_at || '-' }}</td>
                <td>{{ row.next_due_at || '-' }}</td>
                <td>
                  <button class="ghost-btn" @click="quickToggleStatus(row)">
                    {{ row.status === 'active' ? 'Cortar hoy' : 'Reactivar' }}
                  </button>
                </td>
                <td><button class="ghost-btn" @click="applyQuickPayment(row)">1 mes</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>

      <article class="panel">
        <div class="panel-head"><h3>Registrar pago SaaS</h3><span>Owner</span></div>
        <form class="form-grid" @submit.prevent="registerPayment">
          <select v-model="paymentForm.site_id" required>
            <option disabled value="">Selecciona sucursal</option>
            <option v-for="opt in subscriptionOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
          <input v-model="paymentForm.amount" type="number" min="1" placeholder="Monto cobrado" required />
          <input v-model="paymentForm.months_covered" type="number" min="1" placeholder="Meses cubiertos" required />
          <input v-model="paymentForm.note" placeholder="Nota (opcional)" />
          <button class="primary-btn" type="submit">Registrar pago</button>
        </form>
      </article>
    </section>

    <section class="content-grid">
      <article class="panel">
        <div class="panel-head"><h3>Cortar o reactivar sucursal</h3><span>Control de servicio</span></div>
        <form class="form-grid" @submit.prevent="updateStatus">
          <select v-model="statusForm.site_id" required>
            <option disabled value="">Selecciona sucursal</option>
            <option v-for="opt in subscriptionOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
          <select v-model="statusForm.status">
            <option value="active">Activar servicio</option>
            <option value="suspended">Suspender servicio</option>
          </select>
          <input v-model="statusForm.reason" placeholder="Motivo (ej: mensualidad vencida)" />
          <button class="primary-btn" type="submit">Actualizar estado</button>
        </form>
      </article>

      <article class="panel">
        <div class="panel-head"><h3>Historial y exportacion</h3><span>Por sucursal</span></div>
        <div class="form-grid">
          <select v-model="historySiteId" @change="loadPaymentsHistory(historySiteId)">
            <option disabled value="">Selecciona sucursal para historial</option>
            <option v-for="opt in subscriptionOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
          <button class="ghost-btn" type="button" @click="openSaasPaymentsPdf(historySiteId)">Ver PDF</button>
          <button class="ghost-btn" type="button" @click="exportPaymentsCsv(historySiteId)">Exportar CSV</button>
        </div>
        <div class="panel-head" style="margin-top: 14px;"><h3>Editar mensualidad</h3><span>Control comercial</span></div>
        <form class="form-grid" @submit.prevent="updateMonthlyFee">
          <select v-model="monthlyFeeForm.site_id" required>
            <option disabled value="">Selecciona sucursal</option>
            <option v-for="opt in subscriptionOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
          <input v-model="monthlyFeeForm.monthly_fee" type="number" min="1" placeholder="Nueva mensualidad" required />
          <button class="primary-btn" type="submit">Guardar mensualidad</button>
        </form>
        <div class="table-wrap" style="margin-top: 12px;">
          <table class="data-table">
            <thead><tr><th>Fecha</th><th>Monto</th><th>Meses</th><th>Nota</th></tr></thead>
            <tbody>
              <tr v-for="row in paymentsHistory" :key="row.id">
                <td>{{ row.paid_at }}</td>
                <td>Bs {{ row.amount }}</td>
                <td>{{ row.months_covered }}</td>
                <td>{{ row.note || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>
    </section>

    <PdfPreviewModal
      :open="pdfPreviewOpen"
      :title="pdfPreviewTitle"
      :loading="pdfPreviewLoading"
      :src="pdfPreviewUrl"
      iframe-title="Vista previa pagos SaaS"
      @close="closePdfPreview"
      @download="downloadSaasPaymentsPdfFromModal"
    />
  </AppLayout>
</template>
