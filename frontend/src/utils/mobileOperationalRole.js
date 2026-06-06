import { getStaffRole } from '@/utils/resolveHomeRoute'

const MOBILE_OPERATIONAL_STAFF_ROLES = new Set(['WAITER', 'CLEANING', 'GIRL'])

/**
 * Roles con experiencia móvil simplificada (garzón, limpieza, chica).
 * No deben ver el Theme Customizer ni cambiar layout de Materialize.
 */
export function isMobileOperationalRole(user) {
  const staff = getStaffRole(user)

  return staff != null && MOBILE_OPERATIONAL_STAFF_ROLES.has(staff)
}
