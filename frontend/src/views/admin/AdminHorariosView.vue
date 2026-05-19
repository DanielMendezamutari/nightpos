<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { apiRequest } from '../../services/api'
import { useAuthStore } from '../../stores/authStore'
import { useNotificationStore } from '../../stores/notificationStore'
import { useBranchSiteScope } from '../../composables/useBranchSiteScope'

const auth = useAuthStore()
const notify = useNotificationStore()
const { sites, sitePickerId, needsSitePicker, branchQuery, initSiteScope } = useBranchSiteScope(auth)

const loading = ref(false)
const saving = ref(false)

const mismoHorarioTodaLaSemana = ref(true)
const diaAbierto = ref(1)

/** @type {import('vue').Ref<Array<{ weekday: number, weekday_label: string, shifts: Array<{ label: string, opens_at: string, closes_at: string, crosses_midnight: boolean }> }>>} */
const weekdays = ref([])
const turnosCompartidos = ref([])

/** Aviso grande debajo del formulario: idle | ok | err */
const bannerEstado = ref('idle')
const bannerTexto = ref('')

function normalizarHora(v) {
  if (v == null || String(v).trim() === '') return ''
  const s = String(v).trim()
  const m = s.match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?/)
  if (!m) return s
  const h = Math.min(23, Math.max(0, parseInt(m[1], 10)))
  const min = Math.min(59, Math.max(0, parseInt(m[2], 10)))
  return `${String(h).padStart(2, '0')}:${String(min).padStart(2, '0')}`
}

function clonarTurnos(list) {
  return (list || []).map((s) => ({
    label: s.label ?? '',
    opens_at: s.opens_at ?? '',
    closes_at: s.closes_at ?? '',
    crosses_midnight: !!s.crosses_midnight,
  }))
}

function applyWeekdaysFromApi(rows) {
  weekdays.value = (rows || []).map((d) => ({
    weekday: d.weekday,
    weekday_label: d.weekday_label,
    shifts: clonarTurnos(d.shifts),
  }))
  sincronizarModoMismoHorario()
  if (weekdays.value.length === 7) {
    bannerEstado.value = 'idle'
    bannerTexto.value = 'Abajo está lo que hay guardado. Si cambias algo, pulsa Guardar.'
  }
}

function sincronizarModoMismoHorario() {
  if (!weekdays.value.length) return
  const refJson = JSON.stringify(weekdays.value[0].shifts)
  const todosIguales = weekdays.value.every((d) => JSON.stringify(d.shifts) === refJson)
  mismoHorarioTodaLaSemana.value = todosIguales
  turnosCompartidos.value = clonarTurnos(weekdays.value[0].shifts)
}

watch(mismoHorarioTodaLaSemana, (activo) => {
  if (activo && weekdays.value.length) {
    turnosCompartidos.value = clonarTurnos(weekdays.value[0].shifts)
  }
  if (!activo && weekdays.value.length && turnosCompartidos.value.length) {
    weekdays.value.forEach((d) => {
      d.shifts = clonarTurnos(turnosCompartidos.value)
    })
  }
})

const turnosQueEdito = computed(() => {
  if (mismoHorarioTodaLaSemana.value) {
    return turnosCompartidos.value
  }
  const dia = weekdays.value.find((d) => d.weekday === diaAbierto.value)
  return dia ? dia.shifts : []
})

function aplicarTurnosAlDiaAbierto(lista) {
  if (mismoHorarioTodaLaSemana.value) {
    turnosCompartidos.value = clonarTurnos(lista)
    return
  }
  const idx = weekdays.value.findIndex((d) => d.weekday === diaAbierto.value)
  if (idx >= 0) {
    weekdays.value[idx].shifts = clonarTurnos(lista)
  }
}

/** Texto amigable por turno (para la tabla resumen). */
function textoTurno(s, num) {
  const nom = (s.label && String(s.label).trim()) || `Turno ${num}`
  const sale = s.crosses_midnight ? `${s.closes_at} (ya es otro día)` : s.closes_at
  return `${nom}: ${s.opens_at} a ${sale}`
}

const tablaResumen = computed(() => {
  if (weekdays.value.length !== 7) return []
  return weekdays.value.map((d) => ({
    dia: d.weekday_label,
    vacio: d.shifts.length === 0,
    lineas: d.shifts.map((s, i) => textoTurno(s, i + 1)),
  }))
})

async function loadHours() {
  const q = branchQuery()
  if (needsSitePicker.value && !sitePickerId.value) return
  loading.value = true
  bannerEstado.value = 'idle'
  bannerTexto.value = 'Cargando…'
  try {
    const payload = await apiRequest(`/branch/operating-hours${q}`, {}, auth.token.value)
    applyWeekdaysFromApi(payload.data?.weekdays)
  } catch (error) {
    bannerEstado.value = 'err'
    bannerTexto.value = error instanceof Error ? error.message : 'No se pudo cargar.'
    notify.error(bannerTexto.value)
  } finally {
    loading.value = false
  }
}

function mapShiftParaApi(s) {
  return {
    label: s.label?.trim() ? s.label.trim() : null,
    opens_at: normalizarHora(s.opens_at),
    closes_at: normalizarHora(s.closes_at),
    crosses_midnight: !!s.crosses_midnight,
  }
}

function buildPayload() {
  if (mismoHorarioTodaLaSemana.value) {
    const shifts = turnosCompartidos.value.map((s) => mapShiftParaApi(s))
    return weekdays.value.map((d) => ({
      weekday: d.weekday,
      shifts: shifts.map((x) => ({ ...x })),
    }))
  }
  return weekdays.value.map((d) => ({
    weekday: d.weekday,
    shifts: d.shifts.map((s) => mapShiftParaApi(s)),
  }))
}

function validarAntesDeGuardar() {
  const bloques = mismoHorarioTodaLaSemana.value ? [turnosCompartidos.value] : weekdays.value.map((d) => d.shifts)
  const nombresDias = mismoHorarioTodaLaSemana.value
    ? ['esta semana']
    : weekdays.value.map((d) => d.weekday_label)

  for (let i = 0; i < bloques.length; i++) {
    const shifts = bloques[i]
    const nombre = nombresDias[i] || 'este día'
    for (const s of shifts) {
      if (!s.opens_at || !s.closes_at) {
        const t = `Falta hora de entrada o de salida (${nombre}).`
        notify.warning(t)
        return t
      }
    }
  }
  return null
}

const datosListos = computed(() => weekdays.value.length === 7 && !loading.value)

async function saveHours() {
  const q = branchQuery()
  if (needsSitePicker.value && !sitePickerId.value) {
    const t = 'Primero elige la sucursal arriba.'
    bannerEstado.value = 'err'
    bannerTexto.value = t
    notify.warning(t)
    return
  }
  if (weekdays.value.length !== 7) {
    const t = 'Espera a que cargue la pagina.'
    bannerEstado.value = 'err'
    bannerTexto.value = t
    notify.warning(t)
    return
  }
  const errVal = validarAntesDeGuardar()
  if (errVal) {
    bannerEstado.value = 'err'
    bannerTexto.value = errVal
    return
  }

  saving.value = true
  bannerEstado.value = 'idle'
  bannerTexto.value = 'Guardando…'
  try {
    const payload = await apiRequest(
      `/branch/operating-hours${q}`,
      { method: 'PUT', body: JSON.stringify({ weekdays: buildPayload() }) },
      auth.token.value,
    )
    applyWeekdaysFromApi(payload.data?.weekdays)
    bannerEstado.value = 'ok'
    bannerTexto.value = 'Listo. Se guardó bien. Mira la tabla de abajo.'
    notify.success('Guardado.')
  } catch (error) {
    const msg = error instanceof Error ? error.message : 'No se pudo guardar.'
    bannerEstado.value = 'err'
    bannerTexto.value = msg
    notify.error(msg)
  } finally {
    saving.value = false
  }
}

function addTurno() {
  const nuevo = {
    label: '',
    opens_at: '09:00',
    closes_at: '17:00',
    crosses_midnight: false,
  }
  if (mismoHorarioTodaLaSemana.value) {
    turnosCompartidos.value = [...turnosCompartidos.value, nuevo]
  } else {
    const idx = weekdays.value.findIndex((d) => d.weekday === diaAbierto.value)
    if (idx >= 0) {
      weekdays.value[idx].shifts = [...weekdays.value[idx].shifts, nuevo]
    }
  }
}

function removeTurno(shiftIndex) {
  if (mismoHorarioTodaLaSemana.value) {
    turnosCompartidos.value = turnosCompartidos.value.filter((_, i) => i !== shiftIndex)
  } else {
    const idx = weekdays.value.findIndex((d) => d.weekday === diaAbierto.value)
    if (idx >= 0) {
      weekdays.value[idx].shifts = weekdays.value[idx].shifts.filter((_, i) => i !== shiftIndex)
    }
  }
}

function aplicarModelo(tipo) {
  let tpl = []
  if (tipo === '3x8') {
    tpl = [
      { label: 'Mañana', opens_at: '00:00', closes_at: '08:00', crosses_midnight: false },
      { label: 'Tarde', opens_at: '08:00', closes_at: '16:00', crosses_midnight: false },
      { label: 'Noche', opens_at: '16:00', closes_at: '00:00', crosses_midnight: true },
    ]
  } else if (tipo === '2x12') {
    tpl = [
      { label: 'Dia', opens_at: '09:00', closes_at: '21:00', crosses_midnight: false },
      { label: 'Noche', opens_at: '21:00', closes_at: '09:00', crosses_midnight: true },
    ]
  } else if (tipo === '24h') {
    tpl = [{ label: '24 horas', opens_at: '21:00', closes_at: '21:00', crosses_midnight: true }]
  } else if (tipo === 'finde') {
    mismoHorarioTodaLaSemana.value = false
    weekdays.value.forEach((d) => {
      if ([5, 6, 7].includes(d.weekday)) {
        d.shifts = clonarTurnos([{ label: 'Noche', opens_at: '22:00', closes_at: '05:00', crosses_midnight: true }])
      } else {
        d.shifts = []
      }
    })
    diaAbierto.value = 5
    bannerEstado.value = 'idle'
    bannerTexto.value = 'Revisa y pulsa Guardar para registrar.'
    return
  } else if (tipo === 'vacio') {
    tpl = []
  }
  if (mismoHorarioTodaLaSemana.value) {
    turnosCompartidos.value = clonarTurnos(tpl)
  } else {
    aplicarTurnosAlDiaAbierto(tpl)
  }
  bannerEstado.value = 'idle'
  bannerTexto.value = 'Cambiaste el modelo. Pulsa Guardar para que quede guardado.'
}

const resumenDia = computed(() => {
  if (!weekdays.value.length) return []
  return weekdays.value.map((d) => ({
    label: d.weekday_label,
    texto: d.shifts.length === 0 ? 'Sin turno' : `${d.shifts.length} turno(s)`,
  }))
})

onMounted(async () => {
  await initSiteScope()
  await loadHours()
})
</script>

<template>
  <div class="horarios-min-head">
    <h2>Horarios</h2>
    <p>Entrada, salida y una casilla si la salida es al día siguiente. Luego <strong>Guardar</strong>.</p>
  </div>

  <section class="panel horarios-panel">
    <div v-if="needsSitePicker" class="form-grid branch-site-picker">
      <label>
        Sucursal
        <select v-model.number="sitePickerId" @change="loadHours">
          <option v-for="s in sites" :key="s.id" :value="s.id">{{ s.code }} — {{ s.name }}</option>
        </select>
      </label>
    </div>

    <p class="horarios-subtle">Atajos (luego Guardar):</p>
    <div class="preset-cards">
      <button type="button" class="preset-card" @click="aplicarModelo('3x8')">
        <span class="preset-card-title">3 turnos de 8 h</span>
        <span class="preset-card-desc">Cubre todo el dia.</span>
      </button>
      <button type="button" class="preset-card" @click="aplicarModelo('2x12')">
        <span class="preset-card-title">2 turnos de 12 h</span>
        <span class="preset-card-desc">Dia y noche.</span>
      </button>
      <button type="button" class="preset-card" @click="aplicarModelo('24h')">
        <span class="preset-card-title">1 turno 24 h</span>
        <span class="preset-card-desc">Ej. 21:00 a 21:00 otro dia.</span>
      </button>
      <button type="button" class="preset-card preset-card-muted" @click="aplicarModelo('vacio')">
        <span class="preset-card-title">Vacio</span>
        <span class="preset-card-desc">Armar a mano.</span>
      </button>
      <button type="button" class="preset-card preset-card-muted" @click="aplicarModelo('finde')">
        <span class="preset-card-title">Vie–Dom noche</span>
        <span class="preset-card-desc">Resto sin turno.</span>
      </button>
    </div>

    <div class="mismo-horario-row">
      <label class="mismo-horario-label">
        <input v-model="mismoHorarioTodaLaSemana" type="checkbox" />
        <span>
          <strong>Igual todos los dias</strong>
          <small>Desmarca si un dia es distinto.</small>
        </span>
      </label>
    </div>

    <div v-if="!mismoHorarioTodaLaSemana" class="dia-tabs">
      <p class="dia-tabs-hint">Día a editar:</p>
      <div class="dia-tabs-btns">
        <button
          v-for="d in weekdays"
          :key="d.weekday"
          type="button"
          class="dia-tab"
          :class="{ 'dia-tab-active': diaAbierto === d.weekday }"
          @click="diaAbierto = d.weekday"
        >
          {{ d.weekday_label.slice(0, 3) }}
        </button>
      </div>
      <ul class="resumen-semana">
        <li v-for="r in resumenDia" :key="r.label"><span>{{ r.label }}</span><span>{{ r.texto }}</span></li>
      </ul>
    </div>

    <div class="turnos-editor">
      <div class="turnos-editor-head">
        <h4>{{ mismoHorarioTodaLaSemana ? 'Turnos (toda la semana)' : `Turnos del ${weekdays.find((x) => x.weekday === diaAbierto)?.weekday_label || ''}` }}</h4>
        <button type="button" class="primary-btn primary-btn-sm" @click="addTurno">+ Turno</button>
      </div>

      <p v-if="!turnosQueEdito.length" class="sin-turnos-msg">Sin turnos. Usa un atajo o + Turno.</p>

      <ul v-else class="turno-cards">
        <li v-for="(row, shiftIndex) in turnosQueEdito" :key="shiftIndex" class="turno-card">
          <div class="turno-card-top">
            <span class="turno-num">#{{ shiftIndex + 1 }}</span>
            <button type="button" class="ghost-btn ghost-btn-sm" @click="removeTurno(shiftIndex)">Quitar</button>
          </div>
          <label class="turno-field">
            Nombre (opcional)
            <input v-model="row.label" type="text" placeholder="Ej. Noche" class="turno-input" />
          </label>
          <div class="turno-horas">
            <label class="turno-field">
              Entrada
              <input v-model="row.opens_at" type="time" class="turno-input turno-time" />
            </label>
            <label class="turno-field">
              Salida
              <input v-model="row.closes_at" type="time" class="turno-input turno-time" />
            </label>
          </div>
          <label class="turno-check-row">
            <input v-model="row.crosses_midnight" type="checkbox" />
            <span>
              <strong>Salida es al dia siguiente</strong>
              <small>Ej. entra 21:00 y sale 07:00 del otro dia.</small>
            </span>
          </label>
        </li>
      </ul>
    </div>

    <div
      v-if="weekdays.length === 7"
      class="horarios-save-banner"
      :class="{
        'horarios-save-banner--ok': bannerEstado === 'ok',
        'horarios-save-banner--err': bannerEstado === 'err',
        'horarios-save-banner--idle': bannerEstado === 'idle',
      }"
      role="status"
    >
      {{ bannerTexto }}
    </div>

    <div class="hours-footer-actions">
      <button
        type="button"
        class="primary-btn horarios-btn-guardar"
        :disabled="saving || loading || !datosListos"
        @click="saveHours"
      >
        {{ saving ? 'Guardando…' : 'Guardar' }}
      </button>
    </div>
    <p v-if="!datosListos && !loading" class="save-hint-wait">Espera… cargando.</p>
  </section>

  <section v-if="tablaResumen.length" class="panel horarios-resumen-panel">
    <div class="panel-head">
      <h3>Tu semana (guardado)</h3>
      <span>Lo mismo que está en el sistema</span>
    </div>
    <div class="table-wrap">
      <table class="horarios-tabla-resumen">
        <thead>
          <tr>
            <th scope="col">Día</th>
            <th scope="col">Horarios</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="fila in tablaResumen" :key="fila.dia">
            <td>{{ fila.dia }}</td>
            <td>
              <template v-if="fila.vacio">
                <span class="horarios-sin-turno">— Sin turnos —</span>
              </template>
              <ul v-else>
                <li v-for="(linea, i) in fila.lineas" :key="i">{{ linea }}</li>
              </ul>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<style scoped>
.horarios-subtle {
  margin: 0 0 8px;
  font-size: 0.85rem;
  color: #8fa7e0;
}

.horarios-panel {
  margin-top: 8px;
}

.horarios-resumen-panel {
  margin-top: 14px;
}

.horarios-sin-turno {
  color: #8fa7e0;
  font-style: italic;
}

.preset-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 10px;
  margin-bottom: 16px;
}

.preset-card {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 4px;
  padding: 12px;
  border-radius: 12px;
  border: 1px solid rgba(142, 168, 245, 0.28);
  background: rgba(18, 28, 58, 0.65);
  color: inherit;
  cursor: pointer;
  text-align: left;
  transition: border-color 0.15s, background 0.15s;
  font-family: inherit;
}

.preset-card:hover {
  border-color: rgba(113, 215, 255, 0.45);
  background: rgba(30, 48, 100, 0.55);
}

.preset-card-muted {
  opacity: 0.92;
}

.preset-card-title {
  font-weight: 700;
  font-size: 0.88rem;
  color: #f4f8ff;
}

.preset-card-desc {
  font-size: 0.76rem;
  line-height: 1.3;
  color: #a8bcee;
}

.mismo-horario-row {
  margin-bottom: 16px;
  padding: 10px 12px;
  border-radius: 12px;
  background: rgba(92, 129, 255, 0.12);
  border: 1px solid rgba(142, 168, 245, 0.2);
}

.mismo-horario-label {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  cursor: pointer;
  margin: 0;
}

.mismo-horario-label input {
  margin-top: 3px;
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.mismo-horario-label small {
  display: block;
  margin-top: 2px;
  font-weight: 500;
  color: #9eb4ea;
  font-size: 0.8rem;
}

.dia-tabs {
  margin-bottom: 14px;
}

.dia-tabs-hint {
  margin: 0 0 6px;
  font-size: 0.82rem;
  color: #a8bcee;
}

.dia-tabs-btns {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-bottom: 8px;
}

.dia-tab {
  padding: 7px 10px;
  border-radius: 8px;
  border: 1px solid rgba(145, 175, 255, 0.3);
  background: rgba(19, 33, 72, 0.5);
  color: #dbe6ff;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.8rem;
  font-family: inherit;
}

.dia-tab-active {
  background: rgba(92, 129, 255, 0.35);
  border-color: rgba(113, 215, 255, 0.5);
  color: #fff;
}

.resumen-semana {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 2px;
  font-size: 0.75rem;
  color: #8fa7e0;
}

.resumen-semana li {
  display: flex;
  justify-content: space-between;
  max-width: 280px;
}

.turnos-editor-head {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 12px;
}

.turnos-editor-head h4 {
  margin: 0;
  font-size: 0.95rem;
  color: #f2f7ff;
}

.primary-btn-sm {
  padding: 8px 14px;
  font-size: 0.85rem;
}

.sin-turnos-msg {
  margin: 0 0 14px;
  color: #97ace4;
  font-size: 0.88rem;
}

.turno-cards {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 12px;
}

.turno-card {
  padding: 14px;
  border-radius: 12px;
  border: 1px solid rgba(142, 168, 245, 0.22);
  background: rgba(12, 18, 42, 0.5);
}

.turno-card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.turno-num {
  font-weight: 700;
  color: #9dd8ff;
  font-size: 0.85rem;
}

.turno-field {
  display: grid;
  gap: 5px;
  font-size: 0.78rem;
  color: #a8bcee;
  margin-bottom: 10px;
}

.turno-horas {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

@media (max-width: 520px) {
  .turno-horas {
    grid-template-columns: 1fr;
  }
}

.turno-input {
  border: 1px solid rgba(142, 168, 245, 0.26);
  background: rgba(23, 35, 76, 0.7);
  color: #edf3ff;
  padding: 10px 12px;
  border-radius: 10px;
  font-family: inherit;
}

.turno-time {
  min-height: 42px;
}

.turno-check-row {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  margin: 0;
  padding-top: 8px;
  border-top: 1px solid rgba(142, 168, 245, 0.12);
  cursor: pointer;
}

.turno-check-row input {
  margin-top: 3px;
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.turno-check-row small {
  display: block;
  margin-top: 2px;
  font-weight: 500;
  color: #8fa7e0;
  font-size: 0.76rem;
  line-height: 1.3;
}

.ghost-btn-sm {
  padding: 6px 10px;
  font-size: 0.8rem;
}

.branch-site-picker {
  margin-bottom: 14px;
}

.hours-footer-actions {
  margin-top: 16px;
  display: flex;
  justify-content: flex-start;
}

.save-hint-wait {
  margin: 10px 0 0;
  font-size: 0.85rem;
  color: #e0b45c;
}
</style>
