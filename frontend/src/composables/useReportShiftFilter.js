import { computed, ref } from 'vue'
import { apiRequest } from '../services/api'

/**
 * Filtros de reportes: turno de caja (shift_turn_id) o rango fecha/hora (from, to).
 * Si hay turno elegido, el backend ignora from/to.
 */
export function useReportShiftFilter(auth, branchScope) {
  const { initSiteScope, branchQuery, buildReportQuery } = branchScope

  const shiftTurns = ref([])
  const selectedShiftId = ref('')
  const dateFrom = ref('')
  const dateTo = ref('')

  const reportQs = computed(() => {
    if (selectedShiftId.value) {
      return buildReportQuery({ shift_turn_id: selectedShiftId.value })
    }
    return buildReportQuery({
      from: dateFrom.value || undefined,
      to: dateTo.value || undefined,
    })
  })

  async function loadShiftTurns() {
    await initSiteScope()
    const path = `/reports/shift-turns${branchQuery()}`
    const payload = await apiRequest(path, {}, auth.token.value)
    shiftTurns.value = payload.data || []
  }

  function shiftOptionLabel(s) {
    const o = new Date(s.opened_at)
    const c = s.closed_at ? new Date(s.closed_at) : null
    const fmt = (d) =>
      d.toLocaleString('es-AR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    const status = s.status === 'open' ? 'abierto' : 'cerrado'
    return `#${s.id} ${s.period} (${status}) · ${fmt(o)} — ${c ? fmt(c) : '…'}`
  }

  return {
    shiftTurns,
    selectedShiftId,
    dateFrom,
    dateTo,
    reportQs,
    loadShiftTurns,
    shiftOptionLabel,
  }
}
