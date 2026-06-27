import axios from 'axios'
import { getActivePinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { useContextStore } from '@/stores/context'

const API_TIMEOUT_MS = Number(import.meta.env.VITE_API_TIMEOUT_MS || 15000)
const REFRESH_BEFORE_EXPIRY_MS = 5 * 60 * 1000

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api/v1',
  timeout: API_TIMEOUT_MS,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

let refreshInFlight = null

export function isAuthEndpoint(url = '') {
  return url.includes('/auth/login-context/')
    || url.includes('/auth/login-')
    || url.includes('/auth/refresh')
    || url.includes('/auth/logout')
}

function responseLooksLikeHtml(error) {
  const contentType = String(error.response?.headers?.['content-type'] || '')
  const data = error.response?.data

  if (contentType.includes('text/html'))
    return true

  if (typeof data === 'string' && /<!DOCTYPE|<html/i.test(data))
    return true

  return false
}

function isNetworkOrResetError(error) {
  if (error.response)
    return false

  const code = error.code
  const message = error.message || ''

  return code === 'ERR_NETWORK'
    || code === 'ECONNRESET'
    || message === 'Network Error'
    || /connection reset|network error|unexpected end of json/i.test(message)
}

function decodeTokenExpiryMs(token) {
  try {
    const payload = JSON.parse(atob(token.split('.')[1]))
    if (!payload?.exp)
      return null

    return payload.exp * 1000
  }
  catch {
    return null
  }
}

function stabilityLog(event, detail = {}) {
  if (!import.meta.env.DEV || typeof window === 'undefined')
    return

  console.debug(`[nightpos:api] ${event}`, detail)

  if (window.__nightposStability?.pushApiEvent)
    window.__nightposStability.pushApiEvent(event, detail)
}

function redirectToLogin() {
  if (typeof window === 'undefined')
    return

  const onLoginPage = window.location.pathname.startsWith('/login')

  if (onLoginPage)
    return

  stabilityLog('logout_forced', { reason: 'session_expired' })

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

    stabilityLog('refresh_attempt')

    refreshInFlight = auth.refreshSession()
      .then(token => {
        if (token) {
          stabilityLog('refresh_success')
        }
        else {
          stabilityLog('refresh_fail', { reason: 'empty_token' })
        }

        return token
      })
      .catch(error => {
        stabilityLog('refresh_fail', {
          status: error.response?.status ?? null,
          code: error.response?.data?.data?.code ?? null,
        })

        return null
      })
      .finally(() => {
        refreshInFlight = null
      })
  }

  return refreshInFlight
}

/**
 * Clasifica errores HTTP/red para mensajes de usuario y diagnóstico.
 */
export function classifyApiError(error) {
  const status = error.response?.status
  const code = error.code
  const apiCode = error.response?.data?.data?.code
  const serverMessage = error.response?.data?.message

  if (code === 'ECONNABORTED' || /timeout/i.test(error.message || '')) {
    return {
      kind: 'timeout',
      userMessage: 'El servidor está tardando más de lo normal. Intente nuevamente en unos segundos.',
    }
  }

  if (isNetworkOrResetError(error)) {
    return {
      kind: 'network',
      userMessage: 'No se pudo conectar con el servidor. Verifique internet o hosting.',
    }
  }

  if (status === 404 && responseLooksLikeHtml(error)) {
    return {
      kind: 'api_routing',
      userMessage: 'La API no está respondiendo correctamente. Verifique configuración del hosting.',
    }
  }

  if (status === 401 || apiCode === 'token_expired' || apiCode === 'token_blacklisted') {
    return {
      kind: 'unauthorized',
      userMessage: serverMessage || 'Sesión expirada. Vuelva a iniciar sesión.',
    }
  }

  if (status === 403) {
    return {
      kind: 'forbidden',
      userMessage: serverMessage || 'No tiene permiso para esta acción.',
    }
  }

  if (status === 419) {
    return {
      kind: 'csrf',
      userMessage: 'Sesión CSRF expirada. Recargue la página e intente de nuevo.',
    }
  }

  if (
    apiCode === 'jwt_not_configured'
    || /key cannot be empty/i.test(serverMessage || '')
    || /jwt_secret/i.test(serverMessage || '')
  ) {
    return {
      kind: 'jwt_config',
      userMessage: 'Error de configuración del servidor. Contacte al administrador del sistema.',
    }
  }

  if (status >= 500) {
    return {
      kind: 'server',
      userMessage: serverMessage || 'El servidor no responde correctamente. Intente de nuevo en unos minutos.',
    }
  }

  if (serverMessage && status && status >= 400 && status < 500) {
    return {
      kind: 'client',
      userMessage: serverMessage,
    }
  }

  if (!error.response) {
    return {
      kind: 'no_response',
      userMessage: 'El servidor no responde. Verifique internet o hosting.',
    }
  }

  return {
    kind: 'unknown',
    userMessage: serverMessage || 'Error de comunicación con el servidor',
  }
}

api.interceptors.request.use(async config => {
  const accessToken = useCookie('accessToken').value

  if (accessToken) {
    config.headers.Authorization = `Bearer ${accessToken}`

    if (!config._skipAuthRefresh && !isAuthEndpoint(config.url || '')) {
      const expiryMs = decodeTokenExpiryMs(accessToken)
      if (expiryMs !== null && expiryMs - Date.now() < REFRESH_BEFORE_EXPIRY_MS)
        await tryRefreshSession()
    }
  }

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
    const classified = classifyApiError(error)

    stabilityLog('request_failed', {
      url: originalConfig?.url,
      status: status ?? null,
      kind: classified.kind,
    })

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

        return Promise.reject(error)
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
  const message = classifyApiError(error).userMessage

  if (import.meta.env.DEV && typeof window !== 'undefined' && window.__nightposStability?.setLastApiError)
    window.__nightposStability.setLastApiError(message)

  return message
}
