import { getActivePinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { useContextStore } from '@/stores/context'
import {
  clearAuthCookies,
  readAuthSessionFromCookies,
  readContextFromCookies,
} from '@/utils/authSession'
import {
  isCashierShellAllowedPath,
  isBasicCashierStaff,
  resolveCashierShellRedirect,
} from '@/utils/cashierRouting'
import {
  isCleaningOnlyRoute,
  isCleaningStaff,
  isGirlOnlyRoute,
  isGirlStaff,
  isIndexRoute,
  isUserHomeRoute,
  isWaiterOnlyRoute,
  isWaiterStaff,
  resolveHomeRoute,
} from '@/utils/resolveHomeRoute'
import {
  isLoginRoute,
  isPublicRoute,
  redirectIfDifferent,
} from '@/utils/routerGuardHelpers'

function purgeCorruptSession() {
  if (getActivePinia()) {
    useAuthStore().clearSession()

    return
  }

  clearAuthCookies()
}

function loadGuardSession() {
  if (getActivePinia()) {
    const authStore = useAuthStore()
    const contextStore = useContextStore()

    authStore.hydrateFromCookies()
    contextStore.hydrateFromCookies()

    if (!authStore.isAuthenticated) {
      return {
        isLoggedIn: false,
        user: null,
        permissions: [],
        context: {
          tenantSlug: contextStore.tenantSlug,
          branchCode: contextStore.branchCode,
        },
      }
    }

    return {
      isLoggedIn: true,
      user: authStore.user,
      permissions: authStore.permissions,
      context: {
        tenantSlug: contextStore.tenantSlug,
        branchCode: contextStore.branchCode,
      },
    }
  }

  const session = readAuthSessionFromCookies()
  const context = readContextFromCookies()

  if (session.corrupt) {
    purgeCorruptSession()

    return {
      isLoggedIn: false,
      user: null,
      permissions: [],
      context,
    }
  }

  return {
    isLoggedIn: session.isLoggedIn,
    user: session.user,
    permissions: session.user?.permissions ?? [],
    context,
  }
}

function handleLoginRoute(router, to, session) {
  if (!session.isLoggedIn || !session.user)
    return undefined

  const home = resolveHomeRoute(session.user, session.context)

  if (home.name === 'login') {
    purgeCorruptSession()

    return undefined
  }

  return redirectIfDifferent(router, to, home)
}

export const setupGuards = router => {
  router.beforeEach(to => {
    if (isPublicRoute(to)) {
      if (isLoginRoute(to))
        return handleLoginRoute(router, to, loadGuardSession())

      return undefined
    }

    const session = loadGuardSession()
    const girlUser = session.user && isGirlStaff(session.user)
    const waiterUser = session.user && isWaiterStaff(session.user)
    const cleaningUser = session.user && isCleaningStaff(session.user)
    const basicCashier = session.user && isBasicCashierStaff(session.user)

    if (!session.isLoggedIn) {
      if (isIndexRoute(to))
        return undefined

      return redirectIfDifferent(router, to, {
        name: 'login',
        query: {
          redirect: to.fullPath !== '/' ? to.fullPath : undefined,
        },
      })
    }

    if (isIndexRoute(to))
      return redirectIfDifferent(router, to, resolveHomeRoute(session.user, session.context))

    if (girlUser && !isGirlOnlyRoute(to.path) && to.name !== 'not-authorized') {
      return redirectIfDifferent(router, to, { name: 'nightpos-girl' })
    }

    if (waiterUser && to.name === 'nightpos-waiter-orders-new') {
      return redirectIfDifferent(router, to, { name: 'nightpos-waiter' })
    }

    if (waiterUser && !isWaiterOnlyRoute(to.path) && to.name !== 'not-authorized') {
      return redirectIfDifferent(router, to, { name: 'nightpos-waiter' })
    }

    if (cleaningUser && !isCleaningOnlyRoute(to.path) && to.name !== 'not-authorized') {
      return redirectIfDifferent(router, to, { name: 'nightpos-cleaning' })
    }

    if (basicCashier) {
      const shellRedirect = resolveCashierShellRedirect(to.name)

      if (shellRedirect)
        return redirectIfDifferent(router, to, { name: shellRedirect })

      if (!isCashierShellAllowedPath(to.path) && to.name !== 'not-authorized') {
        return redirectIfDifferent(router, to, { name: 'nightpos-cashier-orders' })
      }
    }

    const requiredPermissions = to.meta.permissions
    const requiredPermission = to.meta.permission

    const lacksPermission = Array.isArray(requiredPermissions) && requiredPermissions.length > 0
      ? !requiredPermissions.some(permission => session.permissions.includes(permission))
      : requiredPermission && !session.permissions.includes(requiredPermission)

    if (lacksPermission) {
      if (isUserHomeRoute(to, session.user, session.context)) {
        purgeCorruptSession()

        return redirectIfDifferent(router, to, {
          name: 'login',
          query: { reason: 'session_refresh' },
        })
      }

      return redirectIfDifferent(router, to, { name: 'not-authorized' })
    }

    return undefined
  })
}
