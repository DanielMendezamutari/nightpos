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
const savingLimitUserId = ref(null)
const batchModalOpen = ref(false)
const assigningTableId = ref(null)
const filterText = ref('')
const rooms = ref([])
const tables = ref([])
const waiters = ref([])

const assignModalOpen = ref(false)
const assignTable = ref(null)
/** 'none' | string id */
const assignChoice = ref('none')

const form = reactive({
  site_room_id: '',
  prefix: 'M',
  quantity: 10,
  start_number: 1,
  seats: 4,
})

const roomsOptions = computed(() => [{ id: '', name: 'Sin sala (general)' }, ...rooms.value])

const branchLabel = computed(() => {
  if (needsSitePicker.value && sitePickerId.value && sites.value?.length) {
    const s = sites.value.find((x) => Number(x.id) === Number(sitePickerId.value))
    return s ? `${s.code} — ${s.name}` : ''
  }
  const u = auth.user.value
  const list = u?.accessible_sites || []
  const id = u?.active_site_id ?? u?.site_id
  const hit = list.find((x) => Number(x.id) === Number(id))
  return hit ? `${hit.code} — ${hit.name}` : 'tu sucursal'
})

const filteredTables = computed(() => {
  const q = filterText.value.trim().toLowerCase()
  if (!q) return tables.value
  return tables.value.filter(
    (t) =>
      String(t.code).toLowerCase().includes(q) ||
      String(t.room_name || '').toLowerCase().includes(q),
  )
})

const tablesByRoom = computed(() => {
  const map = new Map()
  for (const t of filteredTables.value) {
    const key = t.room_name || 'Sin sala'
    if (!map.has(key)) map.set(key, [])
    map.get(key).push(t)
  }
  return [...map.entries()].sort(([a], [b]) => a.localeCompare(b, 'es'))
})

const stats = computed(() => {
  const total = tables.value.length
  const sin = tables.value.filter((t) => !t.assigned_waiter_user_id).length
  return { total, sin }
})

function waiterNameForTable(row) {
  if (row.assigned_waiter_name) return row.assigned_waiter_name
  if (!row.assigned_waiter_user_id) return null
  const w = waiters.value.find((x) => Number(x.id) === Number(row.assigned_waiter_user_id))
  return w?.name || 'Garzón'
}

function openBatchModal() {
  batchModalOpen.value = true
}

function closeBatchModal() {
  batchModalOpen.value = false
}

function openAssignModal(row) {
  assignTable.value = row
  assignChoice.value = row.assigned_waiter_user_id ? String(row.assigned_waiter_user_id) : 'none'
  assignModalOpen.value = true
}

function closeAssignModal() {
  assignModalOpen.value = false
  assignTable.value = null
  assignChoice.value = 'none'
}

async function loadData() {
  const q = branchQuery()
  if (needsSitePicker.value && !sitePickerId.value) {
    rooms.value = []
    tables.value = []
    waiters.value = []
    return
  }
  loading.value = true
  try {
    const payload = await apiRequest(`/branch/tables${q}`, {}, auth.token.value)
    rooms.value = payload.data?.rooms || []
    tables.value = payload.data?.tables || []
    waiters.value = payload.data?.waiters || []
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudieron cargar las mesas.')
  } finally {
    loading.value = false
  }
}

async function createBatch() {
  const q = branchQuery()
  if (needsSitePicker.value && !sitePickerId.value) {
    notify.warning('Elegí sucursal primero.')
    return
  }
  saving.value = true
  try {
    const payload = await apiRequest(
      `/branch/tables${q}`,
      {
        method: 'POST',
        body: JSON.stringify({
          site_room_id: form.site_room_id === '' ? null : Number(form.site_room_id),
          prefix: String(form.prefix || '').trim(),
          quantity: Number(form.quantity),
          start_number: Number(form.start_number),
          seats: Number(form.seats),
        }),
      },
      auth.token.value,
    )
    notify.success(`Listo: ${payload.data?.created_count || 0} mesas nuevas.`)
    closeBatchModal()
    await loadData()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo crear el lote.')
  } finally {
    saving.value = false
  }
}

async function removeTable(row, ev) {
  ev?.stopPropagation?.()
  const ok = window.confirm(`¿Eliminar la mesa ${row.code}?`)
  if (!ok) return
  const q = branchQuery()
  try {
    await apiRequest(`/branch/tables/${row.id}${q}`, { method: 'DELETE' }, auth.token.value)
    notify.success('Mesa eliminada.')
    await loadData()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo borrar.')
  }
}

async function saveWaiterLimit(waiter) {
  const q = branchQuery()
  savingLimitUserId.value = waiter.id
  try {
    await apiRequest(
      `/branch/waiters/${waiter.id}/table-limit${q}`,
      { method: 'PATCH', body: JSON.stringify({ max_active_tables: Number(waiter.max_active_tables) }) },
      auth.token.value,
    )
    notify.success(`Límite actualizado (${waiter.name}).`)
    await loadData()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo guardar.')
  } finally {
    savingLimitUserId.value = null
  }
}

function waiterPickDisabled(w, row) {
  const current = Number(row?.assigned_waiter_user_id || 0)
  if (Number(w.id) === current) return false
  const max = Number(w.max_active_tables ?? 5)
  return Number(w.assigned_tables_count) >= max
}

function waiterSubtitle(w) {
  return `${w.assigned_tables_count} de ${w.max_active_tables ?? 5} mesas`
}

async function confirmAssign() {
  const row = assignTable.value
  if (!row) return
  const q = branchQuery()
  const picked = assignChoice.value === 'none' ? 0 : Number(assignChoice.value)
  const previous = Number(row.assigned_waiter_user_id || 0)
  if (picked === previous) {
    closeAssignModal()
    return
  }

  assigningTableId.value = row.id
  try {
    if (!picked) {
      await apiRequest(`/branch/tables/${row.id}/assign${q}`, { method: 'DELETE' }, auth.token.value)
      notify.success(`${row.code}: sin garzón.`)
    } else {
      await apiRequest(
        `/branch/tables/${row.id}/assign${q}`,
        { method: 'POST', body: JSON.stringify({ waiter_user_id: picked }) },
        auth.token.value,
      )
      const name = waiters.value.find((w) => Number(w.id) === picked)?.name || ''
      notify.success(`${row.code} → ${name}`)
    }
    closeAssignModal()
    await loadData()
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo guardar.')
  } finally {
    assigningTableId.value = null
  }
}

onMounted(async () => {
  await initSiteScope()
  await loadData()
})

watch(sitePickerId, () => {
  loadData()
})
</script>

<template>
  <div class="admin-page-head mesas-head">
    <h2>Mesas</h2>
    <p class="mesas-sub">
      Tocá una mesa y elegí quién la atiende. El mesero solo ve en su teléfono las mesas que le des acá.
    </p>
  </div>

  <div v-if="needsSitePicker" class="mesas-topbar panel-inner">
    <label class="mesas-field-inline">
      <span class="mesas-field-label">Sucursal</span>
      <select v-model.number="sitePickerId" class="mesas-select-lg">
        <option v-for="s in sites" :key="s.id" :value="s.id">{{ s.code }} — {{ s.name }}</option>
      </select>
    </label>
  </div>

  <header class="mesas-toolbar">
    <div class="mesas-toolbar-stats" aria-live="polite">
      <span class="mesas-chip">{{ loading ? '…' : `${stats.total} mesas` }}</span>
      <span v-if="stats.sin > 0" class="mesas-chip mesas-chip-warn">{{ stats.sin }} sin garzón</span>
      <span v-else-if="!loading && stats.total" class="mesas-chip mesas-chip-ok">Todas asignadas</span>
    </div>
    <div class="mesas-toolbar-actions">
      <input
        v-model="filterText"
        type="search"
        class="mesas-search"
        placeholder="Buscar mesa…"
        autocomplete="off"
      />
      <button type="button" class="primary-btn" @click="openBatchModal">+ Agregar mesas</button>
    </div>
  </header>
  <p class="mesas-branch-hint">Configurando: <strong>{{ branchLabel }}</strong></p>

  <div v-if="loading" class="mesas-loading">Cargando mesas…</div>

  <div v-else-if="!tables.length" class="panel mesas-empty">
    <p class="mesas-empty-title">Todavía no hay mesas en esta sucursal</p>
    <p class="mesas-empty-text">Creá varias de una vez con el botón <strong>Agregar mesas</strong> (por ejemplo 10 mesas código M1, M2…).</p>
    <button type="button" class="primary-btn" @click="openBatchModal">Agregar mesas</button>
  </div>

  <div v-else-if="!filteredTables.length" class="panel mesas-empty">
    <p>No hay mesas que coincidan con «{{ filterText }}».</p>
    <button type="button" class="ghost-btn" @click="filterText = ''">Limpiar búsqueda</button>
  </div>

  <div v-else class="mesas-rooms">
    <section v-for="[roomName, roomTables] in tablesByRoom" :key="roomName" class="mesas-room-block">
      <h3 class="mesas-room-title">{{ roomName }}</h3>
      <div class="mesas-card-grid">
        <button
          v-for="row in roomTables"
          :key="row.id"
          type="button"
          class="mesa-card"
          :class="{
            'mesa-card--open': assignModalOpen && assignTable?.id === row.id,
            'mesa-card--busy': assigningTableId === row.id,
            'mesa-card--free': !row.assigned_waiter_user_id,
          }"
          :disabled="assigningTableId === row.id"
          @click="openAssignModal(row)"
        >
          <span class="mesa-card-code">{{ row.code }}</span>
          <span class="mesa-card-meta">{{ row.seats }} lugares</span>
          <span class="mesa-card-waiter" :class="{ 'mesa-card-waiter--empty': !row.assigned_waiter_user_id }">
            {{ waiterNameForTable(row) || 'Sin garzón — tocá para elegir' }}
          </span>
          <span
            class="mesa-card-delete"
            title="Eliminar mesa"
            @click.stop="removeTable(row, $event)"
          >×</span>
        </button>
      </div>
    </section>
  </div>

  <p v-if="!loading && tables.length && !waiters.length" class="mesas-warn-banner">
    No hay <strong>garzones</strong> cargados en esta sucursal. Crealos en <em>Administración → Personal o Usuarios</em>.
  </p>

  <!-- Modal: elegir garzón -->
  <div v-if="assignModalOpen && assignTable" class="mesas-overlay" @click.self="closeAssignModal">
    <div class="mesas-modal" role="dialog" aria-labelledby="assign-mesa-title">
      <h3 id="assign-mesa-title" class="mesas-modal-title">Mesa {{ assignTable.code }}</h3>
      <p class="mesas-modal-sub">{{ assignTable.room_name || 'Sin sala' }} · {{ assignTable.seats }} lugares</p>

      <div class="mesas-radio-list">
        <label class="mesas-radio-row">
          <input v-model="assignChoice" type="radio" name="waiter-pick" value="none" />
          <span>
            <strong>Sin garzón</strong>
            <small>Nadie la atiende por ahora</small>
          </span>
        </label>
        <label
          v-for="w in waiters"
          :key="w.id"
          class="mesas-radio-row"
          :class="{ 'mesas-radio-row--disabled': waiterPickDisabled(w, assignTable) }"
        >
          <input
            v-model="assignChoice"
            type="radio"
            name="waiter-pick"
            :value="String(w.id)"
            :disabled="waiterPickDisabled(w, assignTable)"
          />
          <span>
            <strong>{{ w.name }}</strong>
            <small>{{ waiterSubtitle(w) }}</small>
          </span>
        </label>
      </div>

      <p v-if="!waiters.length" class="mesas-modal-note">No hay garzones. Agregá usuarios con rol Garzón primero.</p>

      <div class="mesas-modal-actions">
        <button type="button" class="ghost-btn" @click="closeAssignModal">Cancelar</button>
        <button
          type="button"
          class="primary-btn"
          :disabled="assigningTableId != null"
          @click="confirmAssign"
        >
          {{ assigningTableId != null ? 'Guardando…' : 'Guardar' }}
        </button>
      </div>
    </div>
  </div>

  <!-- Modal: lote -->
  <div v-if="batchModalOpen" class="mesas-overlay" @click.self="closeBatchModal">
    <article class="mesas-modal mesas-modal--wide" @click.stop>
      <div class="mesas-modal-head">
        <h3>Agregar varias mesas</h3>
        <button type="button" class="ghost-btn" @click="closeBatchModal">Cerrar</button>
      </div>
      <p class="mesas-modal-lead">Ejemplo: prefijo <strong>M</strong>, cantidad <strong>10</strong>, desde <strong>1</strong> → M1…M10.</p>
      <form class="mesas-batch-form" @submit.prevent="createBatch">
        <label>
          Sala
          <select v-model="form.site_room_id" class="mesas-select-lg">
            <option v-for="room in roomsOptions" :key="room.id || 'general'" :value="room.id">{{ room.name }}</option>
          </select>
        </label>
        <label>
          Prefijo del código
          <input v-model="form.prefix" maxlength="16" required placeholder="M" />
        </label>
        <label>
          Cantidad
          <input v-model.number="form.quantity" type="number" min="1" max="200" required />
        </label>
        <label>
          Número inicial
          <input v-model.number="form.start_number" type="number" min="1" max="10000" required />
        </label>
        <label>
          Lugares por mesa
          <input v-model.number="form.seats" type="number" min="1" max="50" required />
        </label>
        <div class="mesas-modal-actions">
          <button type="button" class="ghost-btn" @click="closeBatchModal">Cancelar</button>
          <button type="submit" class="primary-btn" :disabled="saving">{{ saving ? 'Creando…' : 'Crear mesas' }}</button>
        </div>
      </form>
    </article>
  </div>

  <details class="panel mesas-more">
    <summary>Límites: cuántas mesas puede tener cada garzón</summary>
    <p class="mesas-more-hint">Por defecto 5. Subí el número si un garzón puede cubrir más mesas.</p>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Garzón</th>
            <th>Ahora</th>
            <th>Máximo</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="w in waiters" :key="w.id">
            <td>{{ w.name }}</td>
            <td>{{ w.assigned_tables_count }}</td>
            <td><input v-model.number="w.max_active_tables" type="number" min="1" max="30" class="mesas-limit-inp" /></td>
            <td>
              <button
                type="button"
                class="ghost-btn ghost-btn-sm"
                :disabled="savingLimitUserId === w.id"
                @click="saveWaiterLimit(w)"
              >
                {{ savingLimitUserId === w.id ? '…' : 'Guardar' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </details>
</template>

<style scoped>
.mesas-head .mesas-sub {
  max-width: 40rem;
  line-height: 1.45;
}

.mesas-topbar {
  margin-bottom: 12px;
  padding: 12px 14px;
  border-radius: 12px;
  background: rgba(92, 129, 255, 0.08);
  border: 1px solid rgba(142, 168, 245, 0.22);
}

.mesas-field-inline {
  display: grid;
  gap: 6px;
}

.mesas-field-label {
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  opacity: 0.85;
}

.mesas-select-lg {
  max-width: 22rem;
  padding: 10px 12px;
  border-radius: 10px;
  font: inherit;
}

.mesas-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 6px;
}

.mesas-toolbar-stats {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.mesas-chip {
  font-size: 0.82rem;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(92, 129, 255, 0.2);
  border: 1px solid rgba(142, 168, 245, 0.35);
}

.mesas-chip-warn {
  background: rgba(255, 185, 40, 0.15);
  border-color: rgba(255, 185, 40, 0.4);
}

.mesas-chip-ok {
  background: rgba(80, 200, 120, 0.12);
  border-color: rgba(80, 200, 120, 0.35);
}

.mesas-toolbar-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
}

.mesas-search {
  min-width: 160px;
  padding: 8px 12px;
  border-radius: 10px;
  font: inherit;
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.28));
  background: var(--panel-muted-bg, rgba(25, 40, 85, 0.35));
  color: inherit;
}

.mesas-branch-hint {
  margin: 0 0 16px;
  font-size: 0.86rem;
  color: var(--color-muted, #97ace4);
}

.mesas-loading {
  padding: 2rem;
  text-align: center;
  color: var(--color-muted, #97ace4);
}

.mesas-empty {
  padding: 1.5rem;
  text-align: center;
  max-width: 28rem;
  margin-bottom: 16px;
}

.mesas-empty-title {
  font-weight: 800;
  margin: 0 0 8px;
  font-size: 1.05rem;
}

.mesas-empty-text {
  margin: 0 0 16px;
  font-size: 0.9rem;
  line-height: 1.45;
  color: var(--color-muted, #97ace4);
}

.mesas-warn-banner {
  margin: 12px 0;
  padding: 12px 14px;
  border-radius: 12px;
  background: rgba(255, 185, 40, 0.12);
  border: 1px solid rgba(255, 185, 40, 0.35);
  font-size: 0.88rem;
  line-height: 1.4;
}

.mesas-room-block {
  margin-bottom: 1.25rem;
}

.mesas-room-title {
  margin: 0 0 10px;
  font-size: 0.92rem;
  font-weight: 800;
  opacity: 0.92;
}

.mesas-card-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(158px, 1fr));
  gap: 10px;
}

.mesa-card {
  position: relative;
  display: grid;
  gap: 4px;
  padding: 14px 12px 12px;
  text-align: left;
  border-radius: 14px;
  border: 1px solid var(--border-subtle, rgba(142, 168, 245, 0.28));
  background: var(--panel-muted-bg, rgba(25, 40, 85, 0.35));
  color: inherit;
  font: inherit;
  cursor: pointer;
  transition: border-color 0.15s, box-shadow 0.15s, transform 0.1s;
}

.mesa-card:hover:not(:disabled) {
  border-color: rgba(113, 215, 255, 0.45);
  box-shadow: 0 6px 20px rgba(4, 12, 40, 0.25);
}

.mesa-card:disabled {
  opacity: 0.65;
  cursor: wait;
}

.mesa-card--open {
  outline: 2px solid rgba(113, 215, 255, 0.55);
}

.mesa-card--free {
  border-style: dashed;
  opacity: 0.95;
}

.mesa-card-code {
  font-weight: 800;
  font-size: 1.25rem;
  letter-spacing: 0.02em;
}

.mesa-card-meta {
  font-size: 0.75rem;
  opacity: 0.8;
}

.mesa-card-waiter {
  font-size: 0.8rem;
  font-weight: 600;
  margin-top: 4px;
  line-height: 1.25;
  color: #b8ceff;
}

:root[data-theme='light'] .mesa-card-waiter {
  color: #3d4f7a;
}

.mesa-card-waiter--empty {
  font-weight: 500;
  opacity: 0.75;
  font-style: italic;
}

.mesa-card-delete {
  position: absolute;
  top: 6px;
  right: 8px;
  width: 26px;
  height: 26px;
  display: grid;
  place-items: center;
  border-radius: 8px;
  font-size: 1.2rem;
  line-height: 1;
  opacity: 0.45;
  border: none;
  background: transparent;
  color: inherit;
  cursor: pointer;
}

.mesa-card-delete:hover {
  opacity: 1;
  background: rgba(255, 100, 100, 0.2);
}

.mesas-overlay {
  position: fixed;
  inset: 0;
  z-index: 80;
  display: grid;
  place-items: center;
  padding: 16px;
  background: rgba(6, 10, 24, 0.65);
  backdrop-filter: blur(4px);
}

.mesas-modal {
  width: 100%;
  max-width: 420px;
  max-height: 90vh;
  overflow: auto;
  padding: 20px 20px 18px;
  border-radius: 16px;
  background: rgba(11, 21, 47, 0.98);
  border: 1px solid rgba(142, 168, 245, 0.3);
  box-shadow: 0 24px 48px rgba(0, 0, 0, 0.45);
}

:root[data-theme='light'] .mesas-modal {
  background: #f6f8ff;
  border-color: rgba(100, 120, 180, 0.35);
  box-shadow: 0 20px 40px rgba(40, 60, 120, 0.18);
}

.mesas-modal--wide {
  max-width: 440px;
}

.mesas-modal-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 8px;
}

.mesas-modal-head h3 {
  margin: 0;
  font-size: 1.1rem;
}

.mesas-modal-title {
  margin: 0;
  font-size: 1.35rem;
  font-weight: 800;
}

.mesas-modal-sub {
  margin: 4px 0 16px;
  font-size: 0.88rem;
  color: var(--color-muted, #97ace4);
}

.mesas-modal-lead {
  margin: 0 0 14px;
  font-size: 0.88rem;
  color: var(--color-muted, #97ace4);
  line-height: 1.4;
}

.mesas-radio-list {
  display: grid;
  gap: 8px;
  margin-bottom: 16px;
}

.mesas-radio-row {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 12px;
  border-radius: 12px;
  border: 1px solid rgba(142, 168, 245, 0.22);
  cursor: pointer;
  background: rgba(25, 40, 85, 0.25);
}

:root[data-theme='light'] .mesas-radio-row {
  background: rgba(230, 238, 255, 0.6);
}

.mesas-radio-row:has(input:checked) {
  border-color: rgba(113, 215, 255, 0.55);
  background: rgba(92, 129, 255, 0.18);
}

.mesas-radio-row--disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.mesas-radio-row input {
  margin-top: 4px;
  width: 1.1rem;
  height: 1.1rem;
  accent-color: #5c81ff;
}

.mesas-radio-row strong {
  display: block;
  font-size: 0.95rem;
}

.mesas-radio-row small {
  display: block;
  margin-top: 2px;
  font-size: 0.78rem;
  opacity: 0.85;
}

.mesas-modal-note {
  font-size: 0.86rem;
  color: #ffb528;
  margin: 0 0 12px;
}

.mesas-modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  flex-wrap: wrap;
}

.mesas-batch-form {
  display: grid;
  gap: 12px;
}

.mesas-batch-form label {
  display: grid;
  gap: 6px;
  font-size: 0.86rem;
}

.mesas-batch-form input,
.mesas-batch-form select {
  padding: 8px 10px;
  border-radius: 10px;
  font: inherit;
}

.mesas-more {
  margin-top: 20px;
  padding: 12px 14px;
}

.mesas-more summary {
  cursor: pointer;
  font-weight: 700;
  font-size: 0.9rem;
}

.mesas-more-hint {
  font-size: 0.82rem;
  color: var(--color-muted, #97ace4);
  margin: 8px 0 12px;
}

.mesas-limit-inp {
  width: 4rem;
  padding: 6px 8px;
  border-radius: 8px;
}

.ghost-btn-sm {
  padding: 6px 10px;
  font-size: 0.78rem;
}
</style>
