import { parse, serialize } from 'cookie-es'
import { defineStore } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { useOperationalStore } from '@/stores/operational'

export const TENANT_SLUG_COOKIE = 'tenantSlug'
export const BRANCH_CODE_COOKIE = 'branchCode'
const COOKIE_MAX_AGE = 60 * 60 * 24 * 30
const COOKIE_OPTS = { path: '/', maxAge: COOKIE_MAX_AGE }

function readCookie(name) {
  if (typeof document === 'undefined')
    return null

  const cookies = parse(document.cookie)

  return cookies[name] ?? null
}

function writeCookie(name, value) {
  if (typeof document === 'undefined')
    return

  document.cookie = serialize(name, value ?? null, value ? COOKIE_OPTS : { ...COOKIE_OPTS, maxAge: -1 })
}

/**
 * Contexto operativo (empresa/sucursal). Fuente de verdad en Pinia; cookies solo persisten.
 */
export const useContextStore = defineStore('nightposContext', {
  state: () => ({
    tenantSlug: null,
    branchCode: null,
    /** Incrementa al cambiar contexto; usar en watch para invalidar datos de módulos. */
    version: 0,
  }),

  getters: {
    hasTenantContext: state => Boolean(state.tenantSlug),
    hasBranchContext: state => Boolean(state.branchCode),
    hasFullContext: state => Boolean(state.tenantSlug && state.branchCode),

    hasOperationalContext(state) {
      const auth = useAuthStore()
      const isSuperAdmin = auth.role === 'super_admin'

      if (isSuperAdmin)
        return Boolean(state.tenantSlug)

      return Boolean(state.tenantSlug || auth.user?.tenant_id)
    },

    needsBranchSelection: state => Boolean(state.tenantSlug && !state.branchCode),

    contextKey: state => `${state.tenantSlug ?? ''}|${state.branchCode ?? ''}|${state.version}`,
  },

  actions: {
    hydrateFromCookies() {
      this.tenantSlug = readCookie(TENANT_SLUG_COOKIE)
      this.branchCode = readCookie(BRANCH_CODE_COOKIE)
    },

    persistToCookies() {
      writeCookie(TENANT_SLUG_COOKIE, this.tenantSlug)
      writeCookie(BRANCH_CODE_COOKIE, this.branchCode)
      // Mantener refs singleton de useCookie alineados (axios legacy / plugins).
      try {
        const tenantRef = useCookie(TENANT_SLUG_COOKIE, COOKIE_OPTS)
        const branchRef = useCookie(BRANCH_CODE_COOKIE, COOKIE_OPTS)

        tenantRef.value = this.tenantSlug
        branchRef.value = this.branchCode
      }
      catch {
        // SSR o entorno sin document
      }
    },

    bumpVersion() {
      this.version += 1
    },

    setTenant(slug) {
      const normalized = slug?.trim() || null

      this.tenantSlug = normalized
      if (!normalized)
        this.branchCode = null

      this.persistToCookies()
      this.bumpVersion()
    },

    setBranch(code) {
      this.branchCode = code?.trim() || null
      this.persistToCookies()
      this.bumpVersion()
    },

    async applyContext({ tenantSlug, branchCode }) {
      const normalizedTenant = tenantSlug?.trim() || null

      this.tenantSlug = normalizedTenant
      if (!normalizedTenant)
        this.branchCode = null
      else if (branchCode !== undefined)
        this.branchCode = branchCode?.trim() || null

      this.persistToCookies()
      this.bumpVersion()
      await this.refreshOperationalContext()
    },

    async refreshOperationalContext() {
      const operational = useOperationalStore()

      await operational.refreshContext()
    },

    async clearContext() {
      this.tenantSlug = null
      this.branchCode = null
      this.persistToCookies()
      this.bumpVersion()

      const operational = useOperationalStore()

      operational.tenant = null
      operational.branch = null
      operational.branches = []
    },

    /** Tras cambio de empresa/sucursal: refrescar tenant/branch y subir versión. */
    async changeContext({ tenantSlug, branchCode }) {
      await this.applyContext({ tenantSlug, branchCode })
    },
  },
})
