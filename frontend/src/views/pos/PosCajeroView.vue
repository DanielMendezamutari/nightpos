<script setup>
import { onMounted, reactive, ref } from 'vue'
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
const orders = ref([])
const selectedOrder = ref(null)
const detail = ref(null)
const payForm = reactive({ method: 'cash', amount: 0 })

function formatMoney(v) { return (Number(v) || 0).toLocaleString('es-AR', { maximumFractionDigits: 0 }) }

async function bootstrap() {
  loading.value = true
  try {
    await initSiteScope()
    const payload = await apiRequest('/pos/orders', {}, auth.token.value)
    orders.value = payload.data || []
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar cola de cobros.')
  } finally { loading.value = false }
}

async function loadOrder(orderId) {
  selectedOrder.value = Number(orderId)
  try {
    const payload = await apiRequest(`/pos/orders/${selectedOrder.value}`, {}, auth.token.value)
    detail.value = payload.data
    payForm.amount = (payload.data?.items || []).reduce((acc, i) => acc + (Number(i.subtotal) || 0), 0)
  } catch (e) { notify.error(e instanceof Error ? e.message : 'No se pudo cargar orden.') }
}

async function payOrder() {
  if (!detail.value?.order) return notify.error('Selecciona una orden.')
  try {
    await apiRequest('/payments', {
      method: 'POST',
      body: JSON.stringify({
        order_id: detail.value.order.id,
        shift_turn_id: detail.value.order.shift_turn_id,
        method: payForm.method,
        amount: Number(payForm.amount) || 0,
      }),
    }, auth.token.value)
    notify.success('Pago confirmado. Comisión de garzón registrada.')
    detail.value = null
    selectedOrder.value = null
    await bootstrap()
  } catch (e) { notify.error(e instanceof Error ? e.message : 'No se pudo cobrar la orden.') }
}

async function openOrderPdf(orderId) {
  try {
    await openPdfPreview(`/pos/orders/${orderId}/pdf${branchQuery()}`, `Orden POS #${orderId}`)
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar el PDF.')
    closePdfPreview()
  }
}

async function downloadOrderPdfFromModal() {
  try {
    await downloadPdfPreview('orden-pos.pdf')
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo descargar el PDF.')
  }
}

onMounted(bootstrap)
</script>

<template>
  <section class="panel">
    <div class="panel-head"><h3>POS Cajero</h3><span>{{ loading ? 'Cargando…' : `${orders.length} pendientes de cobro` }}</span></div>
    <div class="content-grid">
      <article class="panel panel-muted">
        <div class="panel-head"><h4>Cola de cobro</h4></div>
        <div class="table-wrap" v-if="orders.length"><table class="data-table"><thead><tr><th>Orden</th><th>Mesa</th><th>Total</th><th></th></tr></thead><tbody>
          <tr v-for="o in orders" :key="o.id"><td>#{{ o.id }}</td><td>{{ o.table_code || '—' }}</td><td>{{ formatMoney(o.subtotal) }}</td><td><button type="button" class="ghost-btn" @click="loadOrder(o.id)">Cobrar</button></td></tr>
        </tbody></table></div>
        <div v-else class="admin-empty-card"><p>Sin órdenes pendientes.</p></div>
      </article>
      <article class="panel panel-muted" v-if="detail?.order">
        <div class="panel-head">
          <h4>Cobro orden #{{ detail.order.id }}</h4>
          <button type="button" class="ghost-btn" @click="openOrderPdf(detail.order.id)">Ver PDF</button>
        </div>
        <div class="table-wrap" v-if="detail.items?.length"><table class="data-table"><thead><tr><th>Producto</th><th>Cant.</th><th>Subtotal</th></tr></thead><tbody>
          <tr v-for="it in detail.items" :key="it.id"><td>{{ it.product_name }}</td><td>{{ it.quantity }}</td><td>{{ formatMoney(it.subtotal) }}</td></tr>
        </tbody></table></div>
        <form class="maint-field-grid" @submit.prevent="payOrder">
          <div class="field-block"><span>Medio</span><select v-model="payForm.method"><option value="cash">Efectivo</option><option value="qr">QR</option><option value="card">Tarjeta</option></select></div>
          <div class="field-block"><span>Monto</span><input v-model.number="payForm.amount" type="number" min="1" required /></div>
          <div class="maint-form-actions field-block--full"><button class="primary-btn" type="submit">Confirmar pago</button></div>
        </form>
      </article>
    </div>

    <PdfPreviewModal
      :open="pdfPreviewOpen"
      :title="pdfPreviewTitle"
      :loading="pdfPreviewLoading"
      :src="pdfPreviewUrl"
      iframe-title="Vista previa orden POS"
      @close="closePdfPreview"
      @download="downloadOrderPdfFromModal"
    />
  </section>
</template>

<style scoped>
.panel-head {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}
</style>