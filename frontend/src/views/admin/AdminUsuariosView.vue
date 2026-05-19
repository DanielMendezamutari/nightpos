<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useAuthStore } from '../../stores/authStore'
import { apiRequest } from '../../services/api'
import { useNotificationStore } from '../../stores/notificationStore'
import { useBranchSiteScope } from '../../composables/useBranchSiteScope'

const auth = useAuthStore()
const notify = useNotificationStore()
const { sites, sitePickerId, needsSitePicker, initSiteScope } = useBranchSiteScope(auth)

const loadingUsers = ref(false)
const saving = ref(false)
const users = ref([])
const showModal = ref(false)
const mode = ref('create') // create|edit
const editingId = ref(null)

const form = reactive({
  name: '',
  email: '',
  password: 'password123',
  new_password: '',
  pin_code: '',
  role: 'cashier',
  default_site_id: null,
  site_ids: [],
  waiter_compensation_type: 'per_payment',
  waiter_commission_rate_pct: '',
})

const siteOptions = computed(() => {
  if (sites.value?.length) return sites.value
  if (auth.accessibleSites?.value?.length) return auth.accessibleSites.value
  const sid = auth.user.value?.active_site_id ?? auth.user.value?.site_id
  return sid ? [{ id: sid, code: `ID-${sid}`, name: 'Sucursal actual' }] : []
})

/** Owner / super admin: pueden dar acceso a varias sucursales (rotación garzón/cajera). */
const canAssignMultiSite = computed(() => ['owner', 'super_admin'].includes(auth.user.value?.role))

const staffRolesWithMultiSite = ['cashier', 'waiter', 'manager']

const showMultiSiteAccess = computed(
  () => canAssignMultiSite.value && staffRolesWithMultiSite.includes(form.role),
)

const showWaiterPercentField = computed(
  () => form.role === 'waiter' && form.waiter_compensation_type === 'per_payment',
)

function waiterPayLabel(type) {
  if (type === 'payroll_monthly') return 'Mensual / nómina'
  if (type === 'payroll_weekly') return 'Semanal / nómina'
  return 'Comisión por cobro'
}

function waiterPayCell(u) {
  if (u.role !== 'waiter') return '—'
  const t = u.waiter_compensation_type || 'per_payment'
  if (t !== 'per_payment') return waiterPayLabel(t)
  if (u.waiter_commission_rate_pct != null && u.waiter_commission_rate_pct !== '') {
    return `${u.waiter_commission_rate_pct}% propio`
  }
  return 'Comisión % sucursal'
}

function openCreate() {
  mode.value = 'create'
  editingId.value = null
  form.name = ''
  form.email = ''
  form.password = 'password123'
  form.new_password = ''
  form.pin_code = ''
  form.role = 'cashier'
  form.waiter_compensation_type = 'per_payment'
  form.waiter_commission_rate_pct = ''
  form.default_site_id = needsSitePicker.value ? Number(sitePickerId.value || 0) || null : auth.user.value?.active_site_id ?? auth.user.value?.site_id ?? null
  form.site_ids = form.default_site_id ? [form.default_site_id] : []
  showModal.value = true
}

function openEdit(row) {
  mode.value = 'edit'
  editingId.value = row.id
  form.name = row.name || ''
  form.email = row.email || ''
  form.password = ''
  form.new_password = ''
  form.pin_code = row.pin_code || ''
  form.role = row.role || 'cashier'
  form.waiter_compensation_type = row.waiter_compensation_type || 'per_payment'
  form.waiter_commission_rate_pct =
    row.waiter_commission_rate_pct != null && row.waiter_commission_rate_pct !== '' ? String(row.waiter_commission_rate_pct) : ''
  form.default_site_id = row.site_id || null
  const ids = Array.isArray(row.site_ids) && row.site_ids.length
    ? row.site_ids.map((v) => Number(v)).filter(Boolean)
    : row.site_id
      ? [Number(row.site_id)]
      : []
  form.site_ids = [...new Set(ids)]
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

function syncDefaultSite() {
  const defaultId = Number(form.default_site_id || 0) || null
  if (!defaultId) return
  if (!form.site_ids.includes(defaultId)) {
    form.site_ids = [defaultId, ...form.site_ids]
  }
}

async function loadUsers() {
  loadingUsers.value = true
  try {
    let path = '/users'
    if (needsSitePicker.value && sitePickerId.value) {
      path += `?site_id=${sitePickerId.value}`
    }
    const payload = await apiRequest(path, {}, auth.token.value)
    users.value = payload.data || []
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudo cargar usuarios.')
  } finally {
    loadingUsers.value = false
  }
}

async function submitModal() {
  saving.value = true
  try {
    const defaultSiteId =
      form.default_site_id ||
      (needsSitePicker.value ? Number(sitePickerId.value || 0) || null : auth.user.value?.active_site_id ?? auth.user.value?.site_id ?? null)
    const siteIds = [...new Set((form.site_ids || []).map((v) => Number(v)).filter(Boolean))]
    if (defaultSiteId && !siteIds.includes(defaultSiteId)) {
      siteIds.push(defaultSiteId)
    }

    if (canAssignMultiSite.value && staffRolesWithMultiSite.includes(form.role) && !siteIds.length) {
      notify.error('Marcá al menos una sucursal donde pueda trabajar.')
      return
    }

    const body = {
      name: form.name,
      email: form.email,
      pin_code: form.pin_code || null,
      role: form.role,
      default_site_id: defaultSiteId,
      site_id: defaultSiteId,
      site_ids: siteIds,
    }
    if (form.role === 'waiter') {
      body.waiter_compensation_type = form.waiter_compensation_type || 'per_payment'
      if (form.waiter_compensation_type === 'per_payment') {
        const p = form.waiter_commission_rate_pct
        body.waiter_commission_rate_pct = p === '' || p == null ? null : Number(p)
      } else {
        body.waiter_commission_rate_pct = null
      }
    }

    if (mode.value === 'create') {
      body.password = form.password || 'password123'
      await apiRequest('/users', { method: 'POST', body: JSON.stringify(body) }, auth.token.value)
      notify.success('Usuario creado.')
    } else {
      if (form.new_password?.trim()) {
        body.password = form.new_password.trim()
      }
      await apiRequest(`/users/${editingId.value}`, { method: 'PATCH', body: JSON.stringify(body) }, auth.token.value)
      notify.success('Usuario actualizado.')
    }

    showModal.value = false
    await loadUsers()
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudo guardar.')
  } finally {
    saving.value = false
  }
}

async function removeUser(row) {
  if (!window.confirm(`Borrar usuario ${row.name}?`)) return
  try {
    await apiRequest(`/users/${row.id}`, { method: 'DELETE' }, auth.token.value)
    notify.success('Usuario eliminado.')
    await loadUsers()
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudo borrar.')
  }
}

onMounted(async () => {
  await initSiteScope()
  await loadUsers()
})

watch(sitePickerId, () => {
  if (needsSitePicker.value) loadUsers()
})

watch(() => form.default_site_id, syncDefaultSite)
</script>

<template>
  <div class="admin-page-head">
    <h2>Usuarios</h2>
    <p>Gestion completa de usuarios por sucursal.</p>
  </div>

  <section class="panel">
    <div class="panel-head">
      <h3>Usuarios</h3>
      <span>{{ loadingUsers ? 'Cargando...' : 'Equipo por sucursal' }}</span>
    </div>

    <div v-if="needsSitePicker" class="form-grid picker-inline">
      <label>
        Sucursal
        <select v-model.number="sitePickerId">
          <option v-for="s in siteOptions" :key="s.id" :value="s.id">{{ s.code }} - {{ s.name }}</option>
        </select>
      </label>
    </div>

    <div class="maint-products-toolbar">
      <button type="button" class="primary-btn" @click="openCreate">Nuevo usuario</button>
      <span class="maint-products-count">{{ loadingUsers ? '…' : `${users.length} usuarios` }}</span>
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Mesero / %</th>
            <th>Sucursal</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.id">
            <td>{{ u.name }}</td>
            <td>{{ u.email }}</td>
            <td>{{ u.role }}</td>
            <td class="cell-muted">{{ waiterPayCell(u) }}</td>
            <td>
              <template v-if="u.site_access_label">{{ u.site_access_label }}</template>
              <template v-else-if="u.site_code">{{ u.site_code }} - {{ u.site_name }}</template>
              <template v-else>—</template>
            </td>
            <td class="row-actions">
              <button type="button" class="ghost-btn ghost-btn-sm" @click="openEdit(u)">Editar</button>
              <button type="button" class="ghost-btn ghost-btn-sm btn-danger" @click="removeUser(u)">Borrar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <div v-if="showModal" class="maint-product-modal-overlay" @click.self="closeModal">
    <article class="panel maint-user-modal-card" @click.stop>
      <div class="panel-head">
        <h3>{{ mode === 'create' ? 'Crear usuario' : 'Editar usuario' }}</h3>
        <button type="button" class="ghost-btn" @click="closeModal">Cerrar</button>
      </div>
      <p class="modal-lead">{{ mode === 'create' ? 'Nuevo registro del equipo' : 'Actualizar datos del usuario' }}</p>

      <form class="modal-form-grid" @submit.prevent="submitModal">
        <label>
          Nombre
          <input v-model="form.name" required />
        </label>
        <label>
          Correo
          <input v-model="form.email" type="email" required />
        </label>
        <label>
          PIN numerico
          <input v-model="form.pin_code" type="text" inputmode="numeric" maxlength="8" placeholder="Opcional" />
        </label>
        <label v-if="mode === 'create'">
          Contrasena inicial
          <input v-model="form.password" type="text" required />
        </label>
        <label>
          Rol
          <select v-model="form.role">
            <option v-if="auth.isOwner.value" value="owner">Owner</option>
            <option v-if="auth.isOwner.value" value="admin">Admin</option>
            <option v-if="auth.isOwner.value" value="super_admin">Super Admin</option>
            <option value="manager">Encargada</option>
            <option value="cashier">Cajera</option>
            <option value="waiter">Garzon</option>
          </select>
        </label>
        <label v-if="form.role === 'waiter'" class="modal-field-full">
          Remuneración del mesero
          <select v-model="form.waiter_compensation_type">
            <option value="per_payment">Comisión por cobro (usa % del sistema en cada pago)</option>
            <option value="payroll_monthly">Mensualizado / fijo mensual (sin comisión por ticket)</option>
            <option value="payroll_weekly">Pago semanal / fijo (sin comisión por ticket)</option>
          </select>
          <small class="modal-help"
            >Los mensualizados o semanales no generan líneas de comisión al cobrar; el sueldo se liquida fuera del POS.</small
          >
        </label>
        <label v-if="showWaiterPercentField" class="modal-field-full waiter-pct-field">
          <span class="modal-field-label">Comisión por cobro (%)</span>
          <input
            v-model="form.waiter_commission_rate_pct"
            type="number"
            min="0"
            max="100"
            step="0.25"
            placeholder="Vacío = usar % de la sucursal (sistema)"
          />
          <small class="modal-help">
            Solo si el mesero cobra comisión por ticket. Dejá vacío para usar el porcentaje global de sucursal; o cargá un valor
            propio (ej. 12,5%) para este garzón.
          </small>
        </label>
        <label v-if="mode === 'edit'">
          Nueva contraseña (opcional)
          <input v-model="form.new_password" type="text" placeholder="Dejar en blanco para no cambiar" />
        </label>

        <div class="modal-divider"></div>

        <label>
          Sucursal principal
          <select v-model.number="form.default_site_id">
            <option v-for="s in siteOptions" :key="s.id" :value="s.id">{{ s.code }} - {{ s.name }}</option>
          </select>
          <small class="modal-help">Casa o sede principal del usuario (reportes y datos base).</small>
        </label>

        <div v-if="showMultiSiteAccess" class="modal-field-full site-access-checks">
          <span class="modal-field-label">¿En qué sucursales puede trabajar?</span>
          <p class="modal-help">
            Marca todas las que apliquen. El garzón o la cajera eligen en cuál operan al iniciar sesión (barra superior o pantalla de caja).
          </p>
          <div class="site-check-grid">
            <label v-for="s in siteOptions" :key="`acc-${s.id}`" class="site-check">
              <input v-model="form.site_ids" type="checkbox" :value="Number(s.id)" />
              <span>{{ s.code }} — {{ s.name }}</span>
            </label>
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="ghost-btn" @click="closeModal">Cancelar</button>
          <button type="submit" class="primary-btn" :disabled="saving">{{ saving ? 'Guardando...' : 'Guardar' }}</button>
        </div>
      </form>
    </article>
  </div>
</template>

<style scoped>
.maint-products-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem 1rem;
  margin-bottom: 10px;
}
.maint-products-count {
  font-size: 0.9rem;
  color: var(--color-muted, #666);
}
.picker-inline {
  margin-bottom: 10px;
}
.row-actions {
  white-space: nowrap;
  text-align: right;
}
.ghost-btn-sm {
  padding: 6px 10px;
  font-size: 0.8rem;
}
.btn-danger {
  color: #ff9e9e;
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
.maint-user-modal-card {
  width: 100%;
  max-width: 42rem;
  max-height: 92vh;
  overflow: auto;
}
.modal-lead {
  margin: 0 1rem 1rem;
  font-size: 0.88rem;
  color: var(--color-muted, #a8bcee);
}
.modal-form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  padding: 0 1rem 1rem;
}
.modal-form-grid label {
  display: grid;
  gap: 6px;
}
.modal-divider {
  grid-column: 1 / -1;
  height: 1px;
  background: rgba(142, 168, 245, 0.2);
  margin: 2px 0;
}
.modal-field-full {
  grid-column: 1 / -1;
}
.modal-field-label {
  font-size: 0.88rem;
  font-weight: 700;
}
.site-access-checks .modal-help {
  margin: 4px 0 10px;
}
.site-check-grid {
  display: grid;
  gap: 8px;
  margin-top: 6px;
}
.site-check {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.88rem;
  cursor: pointer;
}
.site-check input {
  width: 1.05rem;
  height: 1.05rem;
  accent-color: #5c81ff;
}
.modal-help {
  font-size: 0.78rem;
  color: var(--color-muted, #9ab0e4);
}
.modal-actions {
  grid-column: 1 / -1;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 4px;
}
.waiter-pct-field input {
  max-width: 20rem;
  width: 100%;
}
.cell-muted {
  font-size: 0.86rem;
  color: var(--color-muted, #9ab0e4);
  line-height: 1.35;
}
@media (max-width: 720px) {
  .modal-form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
