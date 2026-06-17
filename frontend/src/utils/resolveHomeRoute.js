import { readAuthSessionFromCookies, readContextFromCookies } from '@/utils/authSession'
import { isBasicCashierStaff } from '@/utils/cashierRouting'

export function getStaffRole(user) {
  return user?.staff_role ?? user?.staffRole ?? null
}

export function getRoleSlug(user) {
  return user?.role ?? null
}

export function isGirlStaff(user) {
  return getStaffRole(user) === 'GIRL'
}

export function isWaiterStaff(user) {
  return getStaffRole(user) === 'WAITER'
}

export function isCleaningStaff(user) {
  return getStaffRole(user) === 'CLEANING'
}

export function hasPermission(user, permission) {
  if (!permission)
    return true

  return (user?.permissions ?? []).includes(permission)
}

/**
 * Ruta de inicio según rol, staff_role, contexto y permisos efectivos.
 * @returns {{ name: string } | { name: 'login' }}
 */
export function resolveHomeRoute(user, context = {}) {
  if (!user)
    return { name: 'login' }

  const permissions = user.permissions ?? []
  const can = slug => !slug || permissions.includes(slug)
  const tenantSlug = context.tenantSlug ?? null
  const role = getRoleSlug(user)
  const staff = getStaffRole(user)

  if (isGirlStaff(user)) {
    if (can('girl.dashboard'))
      return { name: 'nightpos-girl' }

    return { name: 'login' }
  }

  if (isWaiterStaff(user)) {
    if (can('waiter.dashboard'))
      return { name: 'nightpos-waiter' }

    return { name: 'login' }
  }

  if (isCleaningStaff(user)) {
    if (can('cleaning.dashboard'))
      return { name: 'nightpos-cleaning' }

    return { name: 'login' }
  }

  if (role === 'super_admin') {
    if (!tenantSlug) {
      if (can('admin.tenants.list'))
        return { name: 'nightpos-platform-tenants' }

      return { name: 'nightpos-platform-dashboard' }
    }

    return { name: 'nightpos-dashboard' }
  }

  if (isBasicCashierStaff(user)) {
    if (can('sales.charge'))
      return { name: 'nightpos-cashier-orders' }

    if (can('sales.direct_create'))
      return { name: 'nightpos-cashier-venta' }

    if (can('cash.access'))
      return { name: 'nightpos-cashier-caja' }

    return { name: 'nightpos-cashier-more' }
  }

  if (['admin', 'manager', 'owner', 'cashier_senior'].includes(role)) {
    return { name: 'nightpos-dashboard' }
  }

  if (can('shift_console.access'))
    return { name: 'nightpos-shift-console' }

  if (can('cash.access'))
    return { name: 'nightpos-cash' }

  if (can('waiter.dashboard'))
    return { name: 'nightpos-waiter' }

  if (can('admin.tenants.list') && !tenantSlug)
    return { name: 'nightpos-platform-tenants' }

  return { name: 'nightpos-dashboard' }
}

export function resolveHomeRouteName(user, context = {}) {
  return resolveHomeRoute(user, context).name
}

/** @deprecated Usar resolveHomeRouteName */
export function defaultHomeRouteName(user, context = {}) {
  return resolveHomeRouteName(user, context)
}

export function isWaiterOnlyRoute(path) {
  return typeof path === 'string' && path.startsWith('/nightpos/waiter')
}

export function isCleaningOnlyRoute(path) {
  return typeof path === 'string' && path.startsWith('/nightpos/cleaning')
}

export function isGirlOnlyRoute(path) {
  return typeof path === 'string' && path.startsWith('/nightpos/girl')
}

export function isIndexRoute(to) {
  return to.name === 'index' || to.path === '/'
}

export function isUserHomeRoute(to, user, context = {}) {
  if (!user)
    return false

  return to.name === resolveHomeRouteName(user, context)
}

/**
 * Sesión + contexto para redirects/guards sin depender solo de Pinia.
 */
export function readGuardSession() {
  const session = readAuthSessionFromCookies()
  const context = readContextFromCookies()

  return {
    isLoggedIn: session.isLoggedIn,
    user: session.user,
    permissions: session.user?.permissions ?? [],
    context,
    authRef: session.user,
  }
}
