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
  error => {
    const status = error.response?.status

    if (typeof window !== 'undefined') {
      if (status === 401) {
        const onLoginPage = window.location.pathname.startsWith('/login')
        const isAuthLoginRequest = error.config?.url?.includes('/auth/login-')

        // En /login no limpiar sesión: applyContext puede llamar APIs tras persistSession.
        if (!isAuthLoginRequest && !onLoginPage) {
          if (getActivePinia())
            useAuthStore().clearSession()
          else {
            useCookie('accessToken').value = null
            useCookie('userData').value = null
          }
        }

        if (!onLoginPage && !isAuthLoginRequest) {
          const params = new URLSearchParams({ reason: 'session_expired' })

          window.location.assign(`/login?${params.toString()}`)
        }
      }

      // 403 por acción (ej. cobrar sin permiso): lo maneja cada vista vía getApiErrorMessage.
      // Rutas protegidas usan guards → not-authorized sin tumbar la sesión en dev.
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
  return error.response?.data?.message
    || error.message
    || 'Error de comunicación con el servidor'
}
