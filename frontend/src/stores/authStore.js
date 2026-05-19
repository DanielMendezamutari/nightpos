import { computed, ref } from 'vue'
import { apiRequest } from '../services/api'

/** localStorage: JWT access token (Authorization: Bearer …) entregado por POST /auth/login */
const TOKEN_KEY = 'nightpos_token'
const USER_KEY = 'nightpos_user'

const token = ref(localStorage.getItem(TOKEN_KEY) || '')
const user = ref(null)
const requiresOpenShift = ref(false)

try {
  const raw = localStorage.getItem(USER_KEY)
  if (raw) user.value = JSON.parse(raw)
} catch {
  user.value = null
}

const isAuthenticated = computed(() => !!token.value && !!user.value)
const isOwner = computed(() => user.value?.role === 'owner')
const canManageProducts = computed(() => ['admin', 'super_admin'].includes(user.value?.role))
const canManageUsers = computed(() => ['owner', 'super_admin', 'admin'].includes(user.value?.role))
const canManageBranches = computed(() => isOwner.value)
const canConfigureBranch = computed(() =>
  ['admin', 'super_admin', 'manager', 'owner'].includes(user.value?.role),
)
const accessibleSites = computed(() => user.value?.accessible_sites || [])
const activeSiteId = computed(() => user.value?.active_site_id ?? null)

/** Sucursal efectiva para API (activa, o principal, o única asignada). */
const resolvedActiveSiteId = computed(() => {
  const u = user.value
  if (!u) return null
  const sites = u.accessible_sites || []
  const active = u.active_site_id
  if (active != null && active !== '' && sites.some((s) => Number(s.id) === Number(active))) {
    return Number(active)
  }
  const home = u.site_id
  if (home != null && home !== '' && sites.some((s) => Number(s.id) === Number(home))) {
    return Number(home)
  }
  if (sites.length === 1) return Number(sites[0].id)
  if (active != null && active !== '') return Number(active)
  if (home != null && home !== '') return Number(home)
  return null
})

const activeSiteDisplay = computed(() => {
  const id = resolvedActiveSiteId.value
  if (id == null) return null
  const sites = user.value?.accessible_sites || []
  return sites.find((s) => Number(s.id) === Number(id)) || null
})

/** Mesero/cajero con varias sucursales debe elegir `active_site_id` antes de operar. */
const mustChooseSite = computed(() => {
  const u = user.value
  if (!u || !['waiter', 'cashier'].includes(u.role)) return false
  const sites = u.accessible_sites || []
  if (sites.length <= 1) return false
  const active = u.active_site_id
  if (active == null || active === '') return true
  return !sites.some((s) => Number(s.id) === Number(active))
})

async function login(email, password) {
  const payload = await apiRequest('/auth/login', {
    method: 'POST',
    body: JSON.stringify({ email, password }),
  })

  token.value = payload.data.token
  user.value = payload.data.user
  requiresOpenShift.value = !!payload.data.requires_open_shift
  localStorage.setItem(TOKEN_KEY, token.value)
  localStorage.setItem(USER_KEY, JSON.stringify(user.value))
}

async function loginWithPin(pin) {
  const payload = await apiRequest('/auth/login', {
    method: 'POST',
    body: JSON.stringify({ pin }),
  })

  token.value = payload.data.token
  user.value = payload.data.user
  requiresOpenShift.value = !!payload.data.requires_open_shift
  localStorage.setItem(TOKEN_KEY, token.value)
  localStorage.setItem(USER_KEY, JSON.stringify(user.value))
}

async function refreshMe() {
  if (!token.value) return
  const payload = await apiRequest('/auth/me', {}, token.value)
  user.value = payload.data
  localStorage.setItem(USER_KEY, JSON.stringify(user.value))
}

async function updateMyProfile(input) {
  if (!token.value) return
  const payload = await apiRequest('/auth/me', {
    method: 'PATCH',
    body: JSON.stringify(input),
  }, token.value)
  user.value = payload.data
  localStorage.setItem(USER_KEY, JSON.stringify(user.value))
}

async function setActiveSite(siteId) {
  if (!token.value) return
  const run = async () => {
    const payload = await apiRequest('/auth/active-site', {
      method: 'PATCH',
      body: JSON.stringify({ site_id: Number(siteId) }),
    }, token.value)
    user.value = payload.data.user
    if (typeof payload.data.requires_open_shift === 'boolean') {
      requiresOpenShift.value = payload.data.requires_open_shift
    }
    localStorage.setItem(USER_KEY, JSON.stringify(user.value))
  }
  try {
    await run()
  } catch (e) {
    const msg = e instanceof Error ? e.message : ''
    if (msg.includes('No tienes acceso')) {
      await refreshMe()
      await run()
      return
    }
    throw e
  }
}

function clearSessionLocal() {
  token.value = ''
  user.value = null
  requiresOpenShift.value = false
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
}

async function logout() {
  try {
    if (token.value) await apiRequest('/auth/logout', { method: 'POST' }, token.value)
  } catch {
    //
  } finally {
    clearSessionLocal()
  }
}

export function useAuthStore() {
  return {
    clearSessionLocal,
    token,
    user,
    isAuthenticated,
    isOwner,
    canManageProducts,
    canManageUsers,
    canManageBranches,
    canConfigureBranch,
    accessibleSites,
    activeSiteId,
    resolvedActiveSiteId,
    activeSiteDisplay,
    mustChooseSite,
    requiresOpenShift,
    login,
    loginWithPin,
    refreshMe,
    updateMyProfile,
    setActiveSite,
    logout,
  }
}
