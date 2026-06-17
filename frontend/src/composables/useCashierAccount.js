import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useContextStore } from '@/stores/context'

const ROLE_LABELS = {
  cashier: 'Cajera',
  cashier_senior: 'Cajera senior',
  admin: 'Administrador',
  manager: 'Gerente',
  owner: 'Dueño',
  super_admin: 'Superadmin',
}

const STAFF_ROLE_LABELS = {
  CASHIER: 'Cajera',
  WAITER: 'Garzón',
  GIRL: 'Chica',
  CLEANING: 'Limpieza',
}

function resolveRoleLabel(role, staffRole) {
  if (role && ROLE_LABELS[role])
    return ROLE_LABELS[role]

  if (staffRole && STAFF_ROLE_LABELS[staffRole])
    return STAFF_ROLE_LABELS[staffRole]

  return role || staffRole || 'Usuario'
}

/**
 * Cuenta actual del shell cajera: identidad, sucursal y cierre de sesión.
 */
export function useCashierAccount() {
  const auth = useAuthStore()
  const context = useContextStore()
  const router = useRouter()

  const displayName = computed(() => auth.user?.name || auth.user?.username || 'Usuario')

  const roleLabel = computed(() => resolveRoleLabel(auth.user?.role, auth.user?.staff_role))

  const branchLabel = computed(() => {
    if (context.branchName)
      return context.branchName

    if (context.branchCode)
      return context.branchCode

    const branchNameCookie = useCookie('branchName').value
    const branchCodeCookie = useCookie('branchCode').value

    return branchNameCookie || branchCodeCookie || '—'
  })

  const performLogout = async () => {
    await auth.logout()
    await router.replace({ name: 'login' })
  }

  return {
    displayName,
    roleLabel,
    branchLabel,
    logout: performLogout,
    switchAccount: performLogout,
  }
}
