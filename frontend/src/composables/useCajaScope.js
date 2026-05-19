import { ref } from 'vue'
import { apiRequest } from '../services/api'
import { useBranchSiteScope } from './useBranchSiteScope'

export function useCajaScope(auth) {
  const { sites, sitePickerId, needsSitePicker, branchQuery, initSiteScope } = useBranchSiteScope(auth)
  const currentShift = ref(null)

  async function refreshCurrentShift() {
    const q = branchQuery()
    const res = await apiRequest(`/shifts/current${q}`, {}, auth.token.value)
    currentShift.value = res.data ?? null
    return currentShift.value
  }

  return {
    sites,
    sitePickerId,
    needsSitePicker,
    branchQuery,
    initSiteScope,
    currentShift,
    refreshCurrentShift,
  }
}
