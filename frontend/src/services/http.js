import axios from 'axios'
import { getActivePinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { useContextStore } from '@/stores/context'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api/v1',
  timeout: 15000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

let refreshInFlight = null

function isAuthEndpoint(url = '') {
  return url.includes('/auth/login-')
    || url.includes('/auth/refresh')
    || url.includes('/auth/logout')
}

function redirectToLogin() {
  if (typeof window === 'undefined')
    return

  const onLoginPage = window.location.pathname.startsWith('/login')

  if (onLoginPage)
    return

  const params = new URLSearchParams({ reason: 'session_expired' })

  window.location.assign(`/login?${params.toString()}`)
}

function clearClientSession() {
  if (getActivePinia())
    useAuthStore().clearSession()
  else {
    useCookie('accessToken').value = null
    useCookie('userData').value = null
  }
}

async function tryRefreshSession() {
  if (!getActivePinia())
    return null

  if (!refreshInFlight) {
    const auth = useAuthStore()

    refreshInFlight = auth.refreshSession()
      .catch(() => null)
      .finally(() => {
        refreshInFlight = null
      })
  }

  return refreshInFlight
}

api.interceptors.request.use(config => {
  const accessToken = useCookie('accessToken').value

  if (accessToken)
    config.headers.Authorization = `Bearer ${accessToken}`

  if (getActivePinia()) {
    const ctx = useContextStore()

    if (ctx.branchCode)
      config.headers['X-Branch-Code'] = ctx.branchCode

    if (ctx.tenantSlug)
      config.headers['X-Tenant-Slug'] = ctx.tenantSlug
  }
  else {
    const branchCode = useCookie('branchCode').value
    const tenantSlug = useCookie('tenantSlug').value

    if (branchCode)
      config.headers['X-Branch-Code'] = branchCode

    if (tenantSlug)
      config.headers['X-Tenant-Slug'] = tenantSlug
  }

  return config
})

api.interceptors.response.use(
  response => response,
  async error => {
    const status = error.response?.status
    const originalConfig = error.config

    if (typeof window !== 'undefined' && status === 401 && originalConfig) {
      const onLoginPage = window.location.pathname.startsWith('/login')
      const skipRefresh = originalConfig._skipAuthRefresh
      const isLoginRequest = isAuthEndpoint(originalConfig.url)

      if (!isLoginRequest && !onLoginPage && !skipRefresh && !originalConfig._retried) {
        originalConfig._retried = true

        const newToken = await tryRefreshSession()

        if (newToken) {
          originalConfig.headers.Authorization = `Bearer ${newToken}`

          return api.request(originalConfig)
        }
      }

      if (!isLoginRequest && !onLoginPage) {
        clearClientSession()
        redirectToLogin()
      }
    }

    return Promise.reject(error)
  },
)

export default api

/**
 * Extrae payload estándar NightPOS { success, message, data, errors }.
 */
export function unwrapNightPosResponse(response) {
  const body = response.data

  if (body && body.success === false)
    throw new Error(body.message || 'Error en la operación')

  return body?.data ?? body
}

export function getApiErrorMessage(error) {
  const message = error.response?.data?.message
    || error.message
    || 'Error de comunicación con el servidor'

  if (import.meta.env.DEV && typeof window !== 'undefined' && window.__nightposStability?.setLastApiError)
    window.__nightposStability.setLastApiError(message)

  return message
}
