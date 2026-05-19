<script setup>
import { onMounted, reactive, ref } from 'vue'
import PdfPreviewModal from '../components/PdfPreviewModal.vue'
import { apiFormPost, apiRequest } from '../services/api'
import { useAuthStore } from '../stores/authStore'
import { useNotificationStore } from '../stores/notificationStore'
import { useBranchSiteScope } from '../composables/useBranchSiteScope'
import { usePdfPreview } from '../composables/usePdfPreview'

const auth = useAuthStore()
const notify = useNotificationStore()
const { sites, sitePickerId, needsSitePicker, branchQuery, initSiteScope } = useBranchSiteScope(auth)

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
const saving = ref(false)
const profileModalOpen = ref(false)

const readOnly = reactive({
  code: '',
  name: '',
})

const form = reactive({
  legal_document_type: '',
  legal_document_number: '',
  legal_name: '',
  branch_address: '',
  branch_phone: '',
  branch_email: '',
  economic_activity: '',
  authorization_date: '',
  authorization_resolution: '',
  manager_document_type: '',
  manager_document_number: '',
  manager_full_name: '',
  currency_code: 'BOB',
  ticket_series_start: 1,
  boleta_series_start: 1,
  factura_series_start: 1,
})

const logoUrl = ref('')
const logoFile = ref(null)

function applyProfileData(data) {
  readOnly.code = data.code ?? ''
  readOnly.name = data.name ?? ''
  form.legal_document_type = data.legal_document_type ?? ''
  form.legal_document_number = data.legal_document_number ?? ''
  form.legal_name = data.legal_name ?? ''
  form.branch_address = data.branch_address ?? ''
  form.branch_phone = data.branch_phone ?? ''
  form.branch_email = data.branch_email ?? ''
  form.economic_activity = data.economic_activity ?? ''
  form.authorization_date = data.authorization_date ?? ''
  form.authorization_resolution = data.authorization_resolution ?? ''
  form.manager_document_type = data.manager_document_type ?? ''
  form.manager_document_number = data.manager_document_number ?? ''
  form.manager_full_name = data.manager_full_name ?? ''
  form.currency_code = data.currency_code ?? 'BOB'
  form.ticket_series_start = data.ticket_series_start ?? 1
  form.boleta_series_start = data.boleta_series_start ?? 1
  form.factura_series_start = data.factura_series_start ?? 1
  logoUrl.value = data.logo_url ?? ''
}

async function loadProfile() {
  const q = branchQuery()
  if (needsSitePicker.value && !sitePickerId.value) {
    return
  }
  loading.value = true
  try {
    const payload = await apiRequest(`/branch/profile${q}`, {}, auth.token.value)
    applyProfileData(payload.data || {})
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudo cargar la sucursal.')
  } finally {
    loading.value = false
  }
}

async function saveProfile() {
  const q = branchQuery()
  if (needsSitePicker.value && !sitePickerId.value) {
    notify.warning('Selecciona una sucursal.')
    return
  }
  saving.value = true
  try {
    const body = {
      legal_document_type: form.legal_document_type || null,
      legal_document_number: form.legal_document_number || null,
      legal_name: form.legal_name || null,
      branch_address: form.branch_address || null,
      branch_phone: form.branch_phone || null,
      branch_email: form.branch_email || null,
      economic_activity: form.economic_activity || null,
      authorization_date: form.authorization_date || null,
      authorization_resolution: form.authorization_resolution || null,
      manager_document_type: form.manager_document_type || null,
      manager_document_number: form.manager_document_number || null,
      manager_full_name: form.manager_full_name || null,
      currency_code: form.currency_code,
      ticket_series_start: Number(form.ticket_series_start),
      boleta_series_start: Number(form.boleta_series_start),
      factura_series_start: Number(form.factura_series_start),
    }
    const payload = await apiRequest(`/branch/profile${q}`, {
      method: 'PATCH',
      body: JSON.stringify(body),
    }, auth.token.value)
    applyProfileData(payload.data || {})
    notify.success('Configuracion de sucursal guardada.')
    profileModalOpen.value = false
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudo guardar.')
  } finally {
    saving.value = false
  }
}

function openProfileModal() {
  profileModalOpen.value = true
}

async function cancelProfileEdit() {
  await loadProfile()
  profileModalOpen.value = false
}

async function uploadLogo() {
  if (!logoFile.value) {
    notify.warning('Elige un archivo de imagen.')
    return
  }
  const q = branchQuery()
  if (needsSitePicker.value && !sitePickerId.value) {
    notify.warning('Selecciona una sucursal.')
    return
  }
  const fd = new FormData()
  fd.append('logo', logoFile.value)
  try {
    const payload = await apiFormPost(`/branch/logo${q}`, fd, auth.token.value)
    applyProfileData(payload.data || {})
    logoFile.value = null
    notify.success('Logo actualizado.')
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudo subir el logo.')
  }
}

async function openBranchProfilePdf() {
  const q = branchQuery()
  if (needsSitePicker.value && !sitePickerId.value) {
    notify.warning('Selecciona una sucursal.')
    return
  }
  try {
    await openPdfPreview(`/branch/profile/pdf${q}`, 'Datos de sucursal')
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo generar el PDF.')
    closePdfPreview()
  }
}

async function downloadBranchPdfFromModal() {
  try {
    await downloadPdfPreview(`sucursal-${readOnly.code || 'datos'}.pdf`)
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo descargar el PDF.')
  }
}

onMounted(async () => {
  await initSiteScope()
  await loadProfile()
})
</script>

<template>
  <div class="admin-page-head">
    <h2>Mi sucursal</h2>
    <p>Identidad legal, correlativos de documentos y logo. El codigo y nombre comercial los define el dueno al crear la sucursal.</p>
  </div>

  <div class="branch-layout-grid">
    <section class="panel branch-panel-main">
      <div class="panel-head">
        <h3>Datos fiscales y operativos</h3>
        <span>{{ loading ? 'Cargando...' : 'Resumen de la sucursal activa' }}</span>
      </div>

      <div v-if="needsSitePicker" class="form-grid branch-site-picker">
        <label>
          Sucursal a configurar
          <select v-model.number="sitePickerId" @change="loadProfile">
            <option v-for="s in sites" :key="s.id" :value="s.id">
              {{ s.code }} — {{ s.name }}
            </option>
          </select>
        </label>
      </div>

      <div class="branch-readonly-grid">
        <p><strong>Codigo sucursal</strong><span>{{ readOnly.code || '—' }}</span></p>
        <p><strong>Nombre comercial</strong><span>{{ readOnly.name || '—' }}</span></p>
      </div>

      <div class="branch-summary-card">
        <p><strong>Razón social</strong><span>{{ form.legal_name || '—' }}</span></p>
        <p><strong>Teléfono</strong><span>{{ form.branch_phone || '—' }}</span></p>
        <p><strong>Moneda</strong><span>{{ form.currency_code || '—' }}</span></p>
        <p><strong>Encargado/a</strong><span>{{ form.manager_full_name || '—' }}</span></p>
      </div>

      <div class="maint-products-toolbar branch-profile-toolbar">
        <button type="button" class="ghost-btn" @click="openBranchProfilePdf">Ver PDF sucursal</button>
        <button type="button" class="primary-btn" @click="openProfileModal">Editar datos fiscales</button>
      </div>
    </section>

    <section class="panel branch-panel-aside">
      <div class="panel-head">
        <h3>Logo de sucursal</h3>
        <span>PNG o JPG, max. 2 MB</span>
      </div>
      <div class="branch-logo-aside">
        <div v-if="logoUrl" class="branch-logo-preview branch-logo-preview-lg">
          <img :src="logoUrl" alt="Logo sucursal" />
        </div>
        <p v-else class="branch-logo-empty">Sin logo cargado.</p>
        <div class="branch-logo-upload form-grid">
          <input type="file" accept="image/*" @change="(e) => (logoFile = e.target.files?.[0] || null)" />
          <button type="button" class="ghost-btn" @click="uploadLogo">Subir logo</button>
        </div>
      </div>
    </section>
  </div>

  <div v-if="profileModalOpen" class="maint-product-modal-overlay" @click.self="cancelProfileEdit">
    <article class="panel branch-profile-modal-card" @click.stop>
      <div class="panel-head">
        <h3>Editar datos fiscales y series</h3>
        <button type="button" class="ghost-btn" @click="cancelProfileEdit">Cerrar</button>
      </div>
      <form class="form-grid branch-form" @submit.prevent="saveProfile">
        <h4 class="branch-section-title">Razon social y ubicacion</h4>
        <label>
          Tipo de documento (empresa)
          <input v-model="form.legal_document_type" placeholder="Ej. NIT, RUC" />
        </label>
        <label>
          Numero de documento (empresa)
          <input v-model="form.legal_document_number" />
        </label>
        <label class="span-2">
          Razon social
          <input v-model="form.legal_name" />
        </label>
        <label class="span-2">
          Direccion de sucursal
          <textarea v-model="form.branch_address" rows="2" class="branch-textarea"></textarea>
        </label>
        <label>
          Telefono
          <input v-model="form.branch_phone" />
        </label>
        <label>
          Correo de sucursal
          <input v-model="form.branch_email" type="email" />
        </label>
        <label class="span-2">
          Actividad economica
          <input v-model="form.economic_activity" />
        </label>

        <h4 class="branch-section-title">Autorizacion y series de documentos</h4>
        <label>
          Fecha de autorizacion
          <input v-model="form.authorization_date" type="date" />
        </label>
        <label>
          Numero de resolucion / autorizacion
          <input v-model="form.authorization_resolution" />
        </label>
        <label>
          Inicio correlativo tickets
          <input v-model.number="form.ticket_series_start" type="number" min="1" />
        </label>
        <label>
          Inicio correlativo boletas
          <input v-model.number="form.boleta_series_start" type="number" min="1" />
        </label>
        <label>
          Inicio correlativo facturas
          <input v-model.number="form.factura_series_start" type="number" min="1" />
        </label>

        <h4 class="branch-section-title">Encargado(a)</h4>
        <label>
          Tipo de documento
          <input v-model="form.manager_document_type" placeholder="Ej. CI, CE" />
        </label>
        <label>
          Numero de documento
          <input v-model="form.manager_document_number" />
        </label>
        <label class="span-2">
          Nombre completo
          <input v-model="form.manager_full_name" />
        </label>

        <h4 class="branch-section-title">Moneda</h4>
        <label>
          Tipo de moneda
          <select v-model="form.currency_code">
            <option value="BOB">BOB — Boliviano</option>
            <option value="USD">USD — Dolar</option>
            <option value="PEN">PEN — Sol</option>
            <option value="EUR">EUR — Euro</option>
          </select>
        </label>

        <div class="branch-actions span-2 maint-modal-actions-end">
          <button type="button" class="ghost-btn" @click="cancelProfileEdit">Cancelar</button>
          <button type="submit" class="primary-btn" :disabled="saving">{{ saving ? 'Guardando...' : 'Guardar' }}</button>
        </div>
      </form>
    </article>
  </div>

  <PdfPreviewModal
    :open="pdfPreviewOpen"
    :title="pdfPreviewTitle"
    :loading="pdfPreviewLoading"
    :src="pdfPreviewUrl"
    iframe-title="Vista previa datos sucursal"
    @close="closePdfPreview"
    @download="downloadBranchPdfFromModal"
  />
</template>

<style scoped>
.branch-site-picker {
  margin-bottom: 14px;
}

.branch-readonly-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 18px;
  padding: 12px;
  border-radius: 10px;
  background: rgba(25, 40, 85, 0.25);
}

.branch-readonly-grid p {
  margin: 0;
  display: grid;
  gap: 4px;
  font-size: 0.86rem;
  color: #9eb4ea;
}

.branch-readonly-grid span {
  color: #f0f5ff;
  font-weight: 600;
}

.branch-summary-card {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 16px;
  padding: 12px;
  border-radius: 10px;
  background: rgba(18, 28, 58, 0.45);
  border: 1px solid rgba(142, 168, 245, 0.15);
}

.branch-summary-card p {
  margin: 0;
  display: grid;
  gap: 4px;
  font-size: 0.84rem;
  color: #9eb4ea;
}

.branch-summary-card span {
  color: #f0f5ff;
  font-weight: 500;
}

.branch-profile-toolbar {
  margin-bottom: 0;
}

.maint-products-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem 1rem;
}

.maint-product-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 60;
  padding: 1rem;
}

.branch-profile-modal-card {
  width: 100%;
  max-width: 44rem;
  max-height: 92vh;
  overflow: auto;
}

.maint-modal-actions-end {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  flex-wrap: wrap;
}

@media (max-width: 640px) {
  .branch-summary-card {
    grid-template-columns: 1fr;
  }
}

.branch-section-title {
  grid-column: 1 / -1;
  margin: 18px 0 4px;
  font-size: 0.95rem;
  color: #b6c8f7;
}

.branch-section-title:first-of-type {
  margin-top: 0;
}

.branch-form {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

@media (max-width: 720px) {
  .branch-form {
    grid-template-columns: 1fr;
  }
}

.span-2 {
  grid-column: 1 / -1;
}

.branch-textarea {
  border: 1px solid rgba(142, 168, 245, 0.26);
  background: rgba(23, 35, 76, 0.7);
  color: #edf3ff;
  padding: 10px 12px;
  border-radius: 10px;
  outline: none;
  font-family: inherit;
  resize: vertical;
}

.branch-actions {
  margin-top: 8px;
}

.branch-layout-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(260px, 340px);
  gap: 18px;
  align-items: start;
}

@media (max-width: 960px) {
  .branch-layout-grid {
    grid-template-columns: 1fr;
  }
}

.branch-panel-main {
  min-width: 0;
}

.branch-panel-aside {
  position: sticky;
  top: 12px;
}

@media (max-width: 960px) {
  .branch-panel-aside {
    position: static;
  }
}

.branch-logo-aside {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.branch-logo-preview-lg {
  width: 100%;
  max-width: 220px;
  height: 160px;
  margin: 0 auto;
}

.branch-logo-preview {
  width: 120px;
  height: 120px;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid rgba(145, 175, 255, 0.2);
  background: rgba(12, 18, 42, 0.6);
}

.branch-logo-preview img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.branch-logo-empty {
  margin: 0;
  color: #97ace4;
  font-size: 0.9rem;
}

.branch-logo-upload {
  flex: 1;
  min-width: 200px;
}
</style>
