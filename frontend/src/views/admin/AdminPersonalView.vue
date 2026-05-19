<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { apiRequest } from '../../services/api'
import { useAuthStore } from '../../stores/authStore'
import { useNotificationStore } from '../../stores/notificationStore'
import { useBranchSiteScope } from '../../composables/useBranchSiteScope'

const auth = useAuthStore()
const notify = useNotificationStore()
const { sites, sitePickerId, needsSitePicker, branchQuery, initSiteScope } = useBranchSiteScope(auth)

const loading = ref(false)
const saving = ref(false)
const search = ref('')
const contacts = ref([])
const activeType = ref('client')
const editingId = ref(null)
const contactModalOpen = ref(false)

const tabs = [
  { value: 'client', label: 'Personal cliente' },
  { value: 'companion', label: 'Personal chica' },
  { value: 'supplier', label: 'Personal proveedores' },
]

const form = reactive({
  display_name: '',
  phone: '',
  email: '',
  document_type: '',
  document_number: '',
  business_name: '',
  service_category: '',
  commission_percent: '',
  notes: '',
  is_active: true,
})

const showCommission = computed(() => activeType.value === 'companion')
const showSupplierFields = computed(() => activeType.value === 'supplier')

function resetForm() {
  editingId.value = null
  form.display_name = ''
  form.phone = ''
  form.email = ''
  form.document_type = ''
  form.document_number = ''
  form.business_name = ''
  form.service_category = ''
  form.commission_percent = ''
  form.notes = ''
  form.is_active = true
}

function closeContactModal() {
  contactModalOpen.value = false
  resetForm()
}

function openNewContact() {
  resetForm()
  contactModalOpen.value = true
}

function editRow(row) {
  editingId.value = row.id
  form.display_name = row.display_name || ''
  form.phone = row.phone || ''
  form.email = row.email || ''
  form.document_type = row.document_type || ''
  form.document_number = row.document_number || ''
  form.business_name = row.business_name || ''
  form.service_category = row.service_category || ''
  form.commission_percent = row.commission_percent ?? ''
  form.notes = row.notes || ''
  form.is_active = !!row.is_active
  contactModalOpen.value = true
}

async function loadContacts() {
  const q = branchQuery()
  if (needsSitePicker.value && !sitePickerId.value) {
    contacts.value = []
    return
  }
  loading.value = true
  try {
    const query = new URLSearchParams({ type: activeType.value })
    if (search.value.trim()) query.set('q', search.value.trim())
    const payload = await apiRequest(`/branch/contacts?${query.toString()}${q ? `&${q.slice(1)}` : ''}`, {}, auth.token.value)
    contacts.value = payload.data?.contacts || []
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo cargar personal.')
  } finally {
    loading.value = false
  }
}

async function saveContact() {
  const q = branchQuery()
  saving.value = true
  try {
    const body = {
      contact_type: activeType.value,
      display_name: form.display_name.trim(),
      phone: form.phone.trim() || null,
      email: form.email.trim() || null,
      document_type: form.document_type.trim() || null,
      document_number: form.document_number.trim() || null,
      business_name: form.business_name.trim() || null,
      service_category: form.service_category.trim() || null,
      commission_percent: form.commission_percent === '' ? null : Number(form.commission_percent),
      notes: form.notes.trim() || null,
      is_active: !!form.is_active,
    }
    if (editingId.value) {
      await apiRequest(`/branch/contacts/${editingId.value}${q}`, { method: 'PATCH', body: JSON.stringify(body) }, auth.token.value)
      notify.success('Registro actualizado.')
    } else {
      await apiRequest(`/branch/contacts${q}`, { method: 'POST', body: JSON.stringify(body) }, auth.token.value)
      notify.success('Registro creado.')
    }
    closeContactModal()
    await loadContacts()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo guardar.')
  } finally {
    saving.value = false
  }
}

async function removeContact(row) {
  const q = branchQuery()
  if (!window.confirm(`Eliminar ${row.display_name}?`)) return
  try {
    await apiRequest(`/branch/contacts/${row.id}${q}`, { method: 'DELETE' }, auth.token.value)
    notify.success('Registro eliminado.')
    if (editingId.value === row.id) closeContactModal()
    await loadContacts()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo eliminar.')
  }
}

onMounted(async () => {
  await initSiteScope()
  await loadContacts()
})

watch([activeType, sitePickerId], () => {
  closeContactModal()
  loadContacts()
})
</script>

<template>
  <div class="admin-page-head">
    <h2>Personal</h2>
    <p>Gestiona personal cliente, personal chica y personal proveedores por sucursal.</p>
  </div>

  <div v-if="needsSitePicker" class="form-grid branch-site-picker panel-inner">
    <label>
      Sucursal
      <select v-model.number="sitePickerId">
        <option v-for="s in sites" :key="s.id" :value="s.id">{{ s.code }} - {{ s.name }}</option>
      </select>
    </label>
  </div>

  <section class="panel">
    <div class="tabs-line">
      <button
        v-for="t in tabs"
        :key="t.value"
        type="button"
        class="dia-tab"
        :class="{ 'dia-tab-active': activeType === t.value }"
        @click="activeType = t.value"
      >
        {{ t.label }}
      </button>
    </div>

    <div class="maint-products-toolbar">
      <button type="button" class="primary-btn" @click="openNewContact">Nuevo registro</button>
      <span class="maint-products-count">{{ tabs.find((t) => t.value === activeType)?.label }}</span>
    </div>

    <div class="panel-head panel-head--inline">
      <h3>Listado</h3>
      <input v-model="search" class="personal-search" placeholder="Buscar..." @input="loadContacts" />
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Telefono</th>
            <th>Documento</th>
            <th>Estado</th>
            <th class="table-actions">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in contacts" :key="row.id" :class="{ 'row-editing': editingId === row.id && contactModalOpen }">
            <td>{{ row.display_name }}</td>
            <td>{{ row.phone || '-' }}</td>
            <td>{{ row.document_type || '' }} {{ row.document_number || '' }}</td>
            <td>{{ row.is_active ? 'Activo' : 'Inactivo' }}</td>
            <td class="table-actions">
              <button type="button" class="ghost-btn ghost-btn-sm" @click="editRow(row)">Editar</button>
              <button type="button" class="ghost-btn ghost-btn-sm" @click="removeContact(row)">Borrar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-if="!loading && !contacts.length" class="admin-hint">Sin registros en esta seccion.</p>
  </section>

  <div v-if="contactModalOpen" class="maint-product-modal-overlay" @click.self="closeContactModal">
    <article class="panel maint-contact-modal-card" @click.stop>
      <div class="panel-head">
        <h3>{{ editingId ? 'Editar registro' : 'Nuevo registro' }}</h3>
        <button type="button" class="ghost-btn" @click="closeContactModal">Cerrar</button>
      </div>
      <p class="modal-lead">{{ tabs.find((t) => t.value === activeType)?.label }}</p>
      <form class="personal-modal-form" @submit.prevent="saveContact">
        <input v-model="form.display_name" placeholder="Nombre / Alias" required maxlength="140" />
        <input v-model="form.phone" placeholder="Telefono" maxlength="40" />
        <input v-model="form.email" placeholder="Correo" maxlength="140" />
        <input v-model="form.document_type" placeholder="Tipo doc" maxlength="20" />
        <input v-model="form.document_number" placeholder="Numero doc" maxlength="40" />
        <input v-if="showSupplierFields" v-model="form.business_name" placeholder="Razon social" maxlength="160" />
        <input v-if="showSupplierFields" v-model="form.service_category" placeholder="Categoria servicio" maxlength="80" />
        <input
          v-if="showCommission"
          v-model.number="form.commission_percent"
          type="number"
          min="0"
          max="100"
          step="0.01"
          placeholder="% comision"
        />
        <input v-model="form.notes" class="span-notes" placeholder="Notas" maxlength="1500" />
        <label class="check-inline span-notes">
          <input v-model="form.is_active" type="checkbox" />
          Activo
        </label>
        <div class="row-actions maint-modal-actions">
          <button type="button" class="ghost-btn" @click="closeContactModal">Cancelar</button>
          <button type="submit" class="primary-btn" :disabled="saving">{{ saving ? 'Guardando...' : 'Guardar' }}</button>
        </div>
      </form>
    </article>
  </div>
</template>

<style scoped>
.branch-site-picker {
  margin-bottom: 12px;
  padding: 12px;
  border-radius: 12px;
  background: rgba(92, 129, 255, 0.08);
  border: 1px solid rgba(142, 168, 245, 0.2);
}
.panel-inner {
  margin-bottom: 12px;
}
.tabs-line {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 12px;
}
.maint-products-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem 1rem;
  margin-bottom: 1rem;
}
.maint-products-count {
  font-size: 0.9rem;
  color: var(--color-muted, #666);
}
.panel-head--inline {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}
.personal-search {
  max-width: 220px;
  min-height: 38px;
}
.modal-lead {
  margin: 0 0 1rem;
  font-size: 0.88rem;
  color: var(--color-muted, #a8bcee);
}
.personal-modal-form {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
  padding: 0 1rem 1rem;
}
@media (max-width: 640px) {
  .personal-modal-form {
    grid-template-columns: 1fr;
  }
}
.span-notes {
  grid-column: 1 / -1;
}
.row-actions {
  display: flex;
  gap: 10px;
  align-items: center;
  justify-content: flex-end;
}
.maint-modal-actions {
  margin-top: 0.5rem;
}
.check-inline {
  display: flex;
  align-items: center;
  gap: 8px;
}
.ghost-btn-sm {
  padding: 6px 10px;
  font-size: 0.8rem;
}
.table-actions {
  text-align: right;
  white-space: nowrap;
}
.row-editing {
  outline: 1px solid rgba(113, 215, 255, 0.45);
  background: rgba(92, 129, 255, 0.08);
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
.maint-contact-modal-card {
  width: 100%;
  max-width: 36rem;
  max-height: 92vh;
  overflow: auto;
}
</style>
