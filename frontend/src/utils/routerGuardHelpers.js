/** Rutas accesibles sin sesión (path exacto o prefijo de alias Materialize). */
export const PUBLIC_ROUTE_PATHS = [
  '/login',
  '/not-authorized',
  '/pages/authentication/login-v1',
  '/pages/authentication/login-v2',
  '/pages/misc/not-authorized',
  '/404',
  '/500',
]

export function isPublicRoute(to) {
  if (to.meta?.public)
    return true

  const path = to.path

  return PUBLIC_ROUTE_PATHS.some(publicPath =>
    path === publicPath || path.startsWith(`${publicPath}/`),
  )
}

export function isLoginRoute(to) {
  return to.name === 'login' || to.path === '/login'
}

function normalizeQuery(query = {}) {
  return Object.keys(query)
    .sort()
    .reduce((acc, key) => {
      const value = query[key]

      if (value !== undefined && value !== null && value !== '')
        acc[key] = String(value)

      return acc
    }, {})
}

function queriesEqual(a = {}, b = {}) {
  const left = normalizeQuery(a)
  const right = normalizeQuery(b)

  return JSON.stringify(left) === JSON.stringify(right)
}

/**
 * Redirige solo si el destino difiere de la navegación actual (evita bucles).
 */
export function redirectIfDifferent(router, to, location) {
  if (!location)
    return undefined

  const resolved = router.resolve(location)

  if (resolved.fullPath === to.fullPath)
    return undefined

  if (resolved.path === to.path && resolved.name === to.name && queriesEqual(resolved.query, to.query))
    return undefined

  return location
}
