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
const rooms = ref([])
const kindOptions = ref([])
const editingId = ref(null)
const roomModalOpen = ref(false)

const fallbackKindOptions = [
  { value: 'main', label: 'Sala principal' },
  { value: 'dance_floor', label: 'Pista / pista de baile' },
  { value: 'vip', label: 'VIP / palcos' },
  { value: 'bar', label: 'Barra' },
  { value: 'terrace', label: 'Terraza / deck' },
  { value: 'lounge', label: 'Lounge / chill-out' },
  { value: 'box', label: 'Cabañas / boxes' },
  { value: 'smoking', label: 'Sector fumadores' },
  { value: 'staff', label: 'Personal / backstage' },
  { value: 'other', label: 'Otro' },
]

const presets = [
  { code: 'PRINCIPAL', name: 'Sala principal', kind: 'main', hint: 'Donde entra la mayoría' },
  { code: 'PISTA', name: 'Pista / baile', kind: 'dance_floor', hint: 'Zona de baile' },
  { code: 'VIP', name: 'VIP / palcos', kind: 'vip', hint: 'Mesas altas, palcos' },
  { code: 'BARRA', name: 'Barra', kind: 'bar', hint: 'Pedidos en barra' },
  { code: 'TERRAZA', name: 'Terraza', kind: 'terrace', hint: 'Aire libre / deck' },
  { code: 'LOUNGE', name: 'Lounge', kind: 'lounge', hint: 'Más tranquilo' },
  { code: 'BOXES', name: 'Cabañas / boxes', kind: 'box', hint: 'Privados cerrados' },
  { code: 'FUMADOR', name: 'Sector fumadores', kind: 'smoking', hint: 'Si aplica' },
]

const form = reactive({
  code: '',
  name: '',
  kind: 'main',
  floor_label: '',
  capacity_estimate: '',
  sort_order: '',
})

const effectiveKindOptions = computed(() =>
  kindOptions.value.length ? kindOptions.value : fallbackKindOptions,
)

const kindLabelMap = computed(() => {
  const m = {}
  for (const o of effectiveKindOptions.value) {
    m[o.value] = o.label
  }
  return m
})

function resetForm() {
  editingId.value = null
  form.code = ''
  form.name = ''
  form.kind = 'main'
  form.floor_label = ''
  form.capacity_estimate = ''
  form.sort_order = ''
}

function closeRoomModal() {
  roomModalOpen.value = false
  resetForm()
}

function openNewRoomModal() {
  resetForm()
  roomModalOpen.value = true
}

function startEdit(row) {
  editingId.value = row.id
  form.code = row.code
  form.name = row.name
  form.kind = row.kind
  form.floor_label = row.floor_label || ''
  form.capacity_estimate = row.capacity_estimate != null ? String(row.capacity_estimate) : ''
  form.sort_order = String(row.sort_order ?? '')
  roomModalOpen.value = true
}

function applyPreset(p) {
  if (!editingId.value) {
    form.code = p.code
    form.name = p.name
    form.kind = p.kind
    form.floor_label = ''
    form.capacity_estimate = ''
    form.sort_order = ''
  }
}

async function loadRooms() {
  const q = branchQuery()
  if (needsSitePicker.value && !sitePickerId.value) {
    rooms.value = []
    kindOptions.value = []
    return
  }
  loading.value = true
  try {
    const payload = await apiRequest(`/branch/rooms${q}`, {}, auth.token.value)
    rooms.value = payload.data?.rooms || []
    kindOptions.value = payload.data?.kind_options || []
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudieron cargar las salas.')
    rooms.value = []
  } finally {
    loading.value = false
  }
}

async function submitForm() {
  const q = branchQuery()
  if (needsSitePicker.value && !sitePickerId.value) {
    notify.warning('Elegí sucursal arriba.')
    return
  }
  saving.value = true
  try {
    const body = {
      name: form.name.trim(),
      kind: form.kind,
      floor_label: form.floor_label.trim() || null,
      capacity_estimate:
        form.capacity_estimate === '' || form.capacity_estimate == null
          ? null
          : Number(form.capacity_estimate),
      sort_order:
        form.sort_order === '' || form.sort_order == null ? undefined : Number(form.sort_order),
    }

    if (!editingId.value) {
      body.code = form.code.trim()
      await apiRequest(`/branch/rooms${q}`, { method: 'POST', body: JSON.stringify(body) }, auth.token.value)
      notify.success('Sala creada.')
    } else {
      await apiRequest(
        `/branch/rooms/${editingId.value}${q}`,
        { method: 'PATCH', body: JSON.stringify(body) },
        auth.token.value,
      )
      notify.success('Sala actualizada.')
    }
    closeRoomModal()
    await loadRooms()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo guardar.')
  } finally {
    saving.value = false
  }
}

async function removeRoom(row) {
  const q = branchQuery()
  const ok = window.confirm(`¿Borrar la sala «${row.name}» (${row.code})?`)
  if (!ok) return
  try {
    await apiRequest(`/branch/rooms/${row.id}${q}`, { method: 'DELETE' }, auth.token.value)
    notify.success('Sala borrada.')
    if (editingId.value === row.id) closeRoomModal()
    await loadRooms()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo borrar.')
  }
}

onMounted(async () => {
  await initSiteScope()
  await loadRooms()
})

watch(sitePickerId, () => {
  loadRooms()
})
</script>

<template>
  <div class="admin-page-head">
    <h2>Salas</h2>
    <p>
      En boliches se divide el local en <strong>ambientes</strong>: pista, VIP, barra, terraza, cabañas. Cada sala tiene un
      <strong>código corto</strong> (ej. VIP, PISTA) para mesas y comandas.
    </p>
  </div>

  <div v-if="needsSitePicker" class="form-grid branch-site-picker panel-inner">
    <label>
      Sucursal
      <select v-model.number="sitePickerId">
        <option v-for="s in sites" :key="s.id" :value="s.id">{{ s.code }} — {{ s.name }}</option>
      </select>
    </label>
  </div>

  <section class="panel">
    <div class="maint-products-toolbar">
      <button type="button" class="primary-btn" @click="openNewRoomModal">Nueva sala</button>
      <span class="maint-products-count">{{ loading ? '…' : `${rooms.length} salas` }}</span>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Orden</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Piso</th>
            <th>Aforo</th>
            <th class="admin-sala-row-actions">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rooms" :key="row.id" :class="{ 'row-editing': editingId === row.id && roomModalOpen }">
            <td>{{ row.sort_order }}</td>
            <td><code class="admin-sala-code">{{ row.code }}</code></td>
            <td>{{ row.name }}</td>
            <td>{{ row.kind_label || kindLabelMap[row.kind] || row.kind }}</td>
            <td>{{ row.floor_label || '—' }}</td>
            <td>{{ row.capacity_estimate ?? '—' }}</td>
            <td class="admin-sala-row-actions">
              <button type="button" class="ghost-btn ghost-btn-sm" @click="startEdit(row)">Editar</button>
              <button type="button" class="ghost-btn ghost-btn-sm admin-sala-btn-del" @click="removeRoom(row)">
                Borrar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-if="!loading && !rooms.length" class="admin-hint">Todavía no hay salas. Usá <strong>Nueva sala</strong>.</p>
  </section>

  <div v-if="roomModalOpen" class="maint-product-modal-overlay" @click.self="closeRoomModal">
    <article class="panel maint-sala-modal-card" @click.stop>
      <div class="panel-head">
        <h3>{{ editingId ? 'Editar sala' : 'Nueva sala' }}</h3>
        <button type="button" class="ghost-btn" @click="closeRoomModal">Cerrar</button>
      </div>
      <p class="modal-lead">
        {{ editingId ? 'El código no se puede cambiar.' : 'Código en mayúsculas, único por sucursal.' }}
      </p>

      <div v-if="!editingId" class="admin-salas-presets">
        <span class="presets-title">Atajos típicos</span>
        <div class="preset-cards salas-preset-cards">
          <button v-for="p in presets" :key="p.code" type="button" class="preset-card" @click="applyPreset(p)">
            <span class="preset-card-title">{{ p.name }}</span>
            <span class="preset-card-desc">{{ p.hint }}</span>
          </button>
        </div>
      </div>

      <form class="form-grid admin-sala-form" @submit.prevent="submitForm">
        <label class="admin-sala-field">
          Código (mesa / comanda)
          <input v-model="form.code" type="text" maxlength="32" required :disabled="!!editingId" placeholder="VIP" />
        </label>
        <label class="admin-sala-field">
          Nombre visible
          <input v-model="form.name" type="text" maxlength="120" required placeholder="VIP / Palcos norte" />
        </label>
        <label class="admin-sala-field">
          Tipo de ambiente
          <select v-model="form.kind">
            <option v-for="o in effectiveKindOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
          </select>
        </label>
        <label class="admin-sala-field">
          Piso (opcional)
          <input v-model="form.floor_label" type="text" maxlength="20" placeholder="PB, 1, Sótano" />
        </label>
        <label class="admin-sala-field">
          Aforo aprox. (opcional)
          <input v-model="form.capacity_estimate" type="number" min="0" max="65535" placeholder="120" />
        </label>
        <label class="admin-sala-field">
          Orden en lista
          <input v-model="form.sort_order" type="number" min="0" max="65535" placeholder="Auto si vacío al crear" />
        </label>
        <div class="admin-sala-actions maint-modal-actions">
          <button type="button" class="ghost-btn" @click="closeRoomModal">Cancelar</button>
          <button type="submit" class="primary-btn" :disabled="saving">
            {{ saving ? 'Guardando…' : editingId ? 'Guardar cambios' : 'Añadir sala' }}
          </button>
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

.modal-lead {
  margin: 0 0 1rem;
  font-size: 0.88rem;
  color: var(--color-muted, #a8bcee);
}

.admin-salas-presets {
  margin-bottom: 1rem;
}

.presets-title {
  display: block;
  font-size: 0.82rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: var(--muted, #a8bcee);
}

.salas-preset-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 8px;
}

.preset-cards .preset-card {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 4px;
  padding: 10px;
  border-radius: 10px;
  border: 1px solid rgba(142, 168, 245, 0.28);
  background: rgba(18, 28, 58, 0.65);
  color: inherit;
  cursor: pointer;
  text-align: left;
  font-family: inherit;
}

.preset-cards .preset-card:hover {
  border-color: rgba(113, 215, 255, 0.45);
}

.preset-card-title {
  font-weight: 700;
  font-size: 0.85rem;
}

.preset-card-desc {
  font-size: 0.74rem;
  line-height: 1.3;
  color: #a8bcee;
}

.admin-sala-form {
  align-items: end;
}

.admin-sala-field {
  display: grid;
  gap: 6px;
  margin: 0;
  font-size: 0.85rem;
  color: var(--muted, #a8bcee);
}

.admin-sala-field input,
.admin-sala-field select {
  min-height: 42px;
}

.admin-sala-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
  justify-content: flex-end;
  grid-column: 1 / -1;
}

.maint-modal-actions {
  margin-top: 0.5rem;
}

.ghost-btn-sm {
  padding: 6px 10px;
  font-size: 0.8rem;
}

.admin-sala-code {
  font-size: 0.85rem;
  padding: 2px 6px;
  border-radius: 6px;
  background: rgba(0, 0, 0, 0.2);
}

.row-editing {
  outline: 1px solid rgba(113, 215, 255, 0.45);
  background: rgba(92, 129, 255, 0.08);
}

.admin-sala-btn-del {
  color: #ff9e9e;
}

.admin-sala-row-actions {
  white-space: nowrap;
  text-align: right;
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

.maint-sala-modal-card {
  width: 100%;
  max-width: 40rem;
  max-height: 92vh;
  overflow: auto;
}
</style>
