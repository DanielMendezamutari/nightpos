import { storeToRefs } from 'pinia'

import { useAuthStore } from '@/stores/auth'

import { useContextStore } from '@/stores/context'

import { useOperationalStore } from '@/stores/operational'



/**

 * Contexto operativo (empresa/sucursal) — Pinia como fuente de verdad.

 */

export function usePlatformContext() {

  const auth = useAuthStore()

  const operational = useOperationalStore()

  const context = useContextStore()



  const {

    tenantSlug,

    branchCode,

    hasTenantContext,

    hasFullContext,

    hasOperationalContext,

    needsBranchSelection,

  } = storeToRefs(context)



  const isSuperAdmin = computed(() => auth.role === 'super_admin')



  const contextLabel = computed(() => {

    if (!tenantSlug.value)

      return 'Sin empresa'



    const tenant = operational.tenant?.name || tenantSlug.value

    const branch = operational.branch?.code || branchCode.value



    return branch ? `${tenant} · ${branch}` : tenant

  })



  async function applyContext({ tenantSlug: slug, branchCode: code }) {

    await context.applyContext({ tenantSlug: slug, branchCode: code })

  }



  async function clearContext() {

    await context.clearContext()

  }



  return {

    isSuperAdmin,

    tenantSlug,

    branchCode,

    hasTenantContext,

    hasFullContext,

    hasOperationalContext,

    needsBranchSelection,

    contextLabel,

    contextVersion: computed(() => context.version),

    applyContext,

    clearContext,

  }

}

