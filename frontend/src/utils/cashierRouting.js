import {
  getRoleSlug,
  getStaffRole,
} from '@/utils/resolveHomeRoute'

const FULL_MENU_ROLES = new Set([
  'admin',
  'manager',
  'owner',
  'cashier_senior',
  'super_admin',
])

/** Rutas principales del shell cajera básica */
const CASHIER_SHELL_CORE_PREFIXES = [
  '/nightpos/cashier',
]

/** Rutas secundarias accesibles desde «Más» o flujos de corrección (permisos en guards) */
const CASHIER_SHELL_SECONDARY_PREFIXES = [
  '/nightpos/settlements',
  '/nightpos/sales',
  '/nightpos/shifts',
  '/nightpos/finance',
  '/nightpos/print',
  '/nightpos/orders',
  '/nightpos/products',
  '/nightpos/categories',
  '/nightpos/catalog',
  '/nightpos/services',
  '/nightpos/rooms',
  '/nightpos/settings',
  '/nightpos/shift-console',
  '/nightpos/staff',
]

/** Redirecciones desde rutas admin a equivalentes shell */
const CASHIER_SHELL_ROUTE_REDIRECTS = {
  'nightpos-shift-console': 'nightpos-cashier-orders',
  'nightpos-dashboard': 'nightpos-cashier-orders',
  'nightpos-cash': 'nightpos-cashier-caja',
  'nightpos-cash-direct-sale': 'nightpos-cashier-venta',
  'nightpos-services-room-services': 'nightpos-cashier-piezas',
  'nightpos-cashier-orders': null,
}

/**
 * Cajera básica: role cashier o staff CASHIER sin rol senior/admin.
 */
export function isBasicCashierStaff(user) {
  if (!user)
    return false

  const role = getRoleSlug(user)

  if (role && FULL_MENU_ROLES.has(role))
    return false

  const staff = getStaffRole(user)

  return role === 'cashier' || staff === 'CASHIER'
}

export function isCashierShellRoute(path) {
  if (typeof path !== 'string')
    return false

  return CASHIER_SHELL_CORE_PREFIXES.some(prefix => path.startsWith(prefix))
    || CASHIER_SHELL_SECONDARY_PREFIXES.some(prefix => path.startsWith(prefix))
}

/** Rutas permitidas para cajera básica (shell + secundarias + detalle comanda). */
export function isCashierShellAllowedPath(path) {
  return isCashierShellRoute(path)
}

export function resolveCashierShellRedirect(routeName) {
  if (!routeName || !(routeName in CASHIER_SHELL_ROUTE_REDIRECTS))
    return null

  return CASHIER_SHELL_ROUTE_REDIRECTS[routeName]
}

export function isCashierShellStaff(user) {
  return isBasicCashierStaff(user)
}
