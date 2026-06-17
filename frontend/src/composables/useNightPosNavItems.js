import { storeToRefs } from 'pinia'

import { can } from '@layouts/plugins/casl'

import { useAuthStore } from '@/stores/auth'

import { useContextStore } from '@/stores/context'

import nightposNavigation from '@/navigation/vertical/nightpos-r4'
import { isGirlStaff, isWaiterStaff } from '@/utils/waiterRouting'
import { isBasicCashierStaff } from '@/utils/cashierRouting'



function canSeeItem(item, ctx) {

  if (item.requiresSuperAdmin && !ctx.isSuperAdmin)

    return false



  if (item.requiresOperationalContext && ctx.isSuperAdmin && !ctx.hasOperationalContext)

    return false



  if (item.hideForSuperAdminGlobal && ctx.isSuperAdmin && !ctx.hasOperationalContext)

    return false



  if (item.action && item.subject && !can(item.action, item.subject))

    return false



  return true

}



function filterNavTree(items, ctx) {

  const result = []

  let pendingHeading = null



  for (const item of items) {

    if (item.heading) {

      pendingHeading = item

      continue

    }



    let processed = null



    if (item.children?.length) {

      if (!canSeeItem(item, ctx))

        continue



      const children = filterNavTree(item.children, ctx)

      if (children.length)

        processed = { ...item, children }

    }

    else if (canSeeItem(item, ctx)) {

      processed = item

    }



    if (processed) {

      if (pendingHeading) {

        result.push(pendingHeading)

        pendingHeading = null

      }

      result.push(processed)

    }

  }



  return result

}



/**

 * Menú vertical NightPOS filtrado por rol, permisos y contexto operativo (reactivo).

 */

export function useNightPosNavItems() {

  const auth = useAuthStore()

  const context = useContextStore()

  const { hasOperationalContext } = storeToRefs(context)



  const navContext = computed(() => ({

    isSuperAdmin: auth.role === 'super_admin',

    hasOperationalContext: hasOperationalContext.value,

  }))



  const navItems = computed(() => {
    if (isWaiterStaff(auth.user) || isGirlStaff(auth.user) || isBasicCashierStaff(auth.user))
      return []

    return filterNavTree(nightposNavigation, navContext.value)
  })



  const navMenuKey = computed(() => context.contextKey)



  return { navItems, navContext, navMenuKey }

}

