import { computed, ref } from 'vue'
import { apiRequest } from '../services/api'

/**
 * Sucursal activa en pantallas de administracion: admin/manager usan site_id del usuario;
 * owner/super_admin eligen con ?site_id=.
 */
export function useBranchSiteScope(auth) {
  const sites = ref([])
  const sitePickerId = ref(null)

  const needsSitePicker = computed(() => ['owner', 'super_admin'].includes(auth.user.value?.role))

  function branchQuery() {
    if (needsSitePicker.value) {
      if (!sitePickerId.value) return ''
      return `?site_id=${sitePickerId.value}`
    }
    return ''
  }

  async function loadSites() {
    if (!needsSitePicker.value) return
    const payload = await apiRequest('/sites', {}, auth.token.value)
    sites.value = payload.data || []
    if (!sitePickerId.value && sites.value.length) {
      sitePickerId.value = sites.value[0].id
    }
  }

  async function initSiteScope() {
    if (needsSitePicker.value) {
      await loadSites()
    } else {
      sitePickerId.value = auth.user.value?.active_site_id ?? auth.user.value?.site_id ?? null
    }
  }

  /** Query para endpoints de reportes (sucursal + turno o rango fecha/hora). */
  function buildReportQuery(extra = {}) {
    const search = new URLSearchParams()
    if (needsSitePicker.value && sitePickerId.value) {
      search.set('site_id', String(sitePickerId.value))
    }
    const shiftRaw = extra.shift_turn_id
    if (shiftRaw != null && String(shiftRaw).trim() !== '') {
      search.set('shift_turn_id', String(shiftRaw))
      const q = search.toString()
      return q ? `?${q}` : ''
    }
    for (const [key, val] of Object.entries(extra)) {
      if (key === 'shift_turn_id') {
        continue
      }
      if (val != null && String(val).trim() !== '') {
        search.set(key, String(val))
      }
    }
    const q = search.toString()
    return q ? `?${q}` : ''
  }

  return { sites, sitePickerId, needsSitePicker, branchQuery, buildReportQuery, loadSites, initSiteScope }
}
