import { parse, serialize } from 'cookie-es'
import { destr } from 'destr'

const AUTH_COOKIE_NAMES = [
  'accessToken',
  'userData',
  'userAbilityRules',
  'tenantSlug',
  'branchCode',
]

function decodeCookieValue(value) {
  if (!value)
    return null

  try {
    return destr(decodeURIComponent(value))
  }
  catch {
    return null
  }
}

export function isValidAuthUser(user) {
  return Boolean(user && typeof user === 'object' && user.id != null)
}

export function isSessionCorrupt(token, user) {
  if ((token && !user) || (!token && user))
    return true

  if (token && user && !isValidAuthUser(user))
    return true

  return false
}

/**
 * Limpia cookies de sesión sin Pinia (guards / redirects tempranos).
 */
export function clearAuthCookies() {
  if (typeof document === 'undefined')
    return

  for (const name of AUTH_COOKIE_NAMES) {
    document.cookie = serialize(name, '', { path: '/', maxAge: -1 })
  }
}

/**
 * Lectura de sesión sin Pinia (seguro en redirects y antes de app.use(pinia)).
 */
export function readAuthSessionFromCookies() {
  if (typeof document === 'undefined')
    return { isLoggedIn: false, token: null, user: null, corrupt: false }

  const cookies = parse(document.cookie)
  const token = decodeCookieValue(cookies.accessToken)
  const user = decodeCookieValue(cookies.userData)
  const corrupt = isSessionCorrupt(token, user)
  const isLoggedIn = !corrupt && Boolean(token && user)

  return { isLoggedIn, token, user: isLoggedIn ? user : null, corrupt }
}

/**
 * Contexto operativo desde cookies (sin Pinia).
 */
export function readContextFromCookies() {
  if (typeof document === 'undefined')
    return { tenantSlug: null, branchCode: null }

  const cookies = parse(document.cookie)

  const tenantSlug = decodeCookieValue(cookies.tenantSlug)
  const branchCode = decodeCookieValue(cookies.branchCode)

  return {
    tenantSlug: typeof tenantSlug === 'string' ? tenantSlug : null,
    branchCode: typeof branchCode === 'string' ? branchCode : null,
  }
}
