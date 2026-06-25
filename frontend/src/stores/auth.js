import { defineStore } from 'pinia'
import { ability } from '@/plugins/casl/ability'
import api, { getApiErrorMessage, unwrapNightPosResponse } from '@/services/http'
import { useContextStore } from '@/stores/context'

const TOKEN_COOKIE = 'accessToken'
const USER_COOKIE = 'userData'
const TENANT_SLUG_COOKIE = 'tenantSlug'
const BRANCH_CODE_COOKIE = 'branchCode'
const TENANT_NAME_COOKIE = 'tenantName'
const BRANCH_NAME_COOKIE = 'branchName'
const LAST_OPERATOR_NAME_COOKIE = 'lastOperatorName'
/** Alineado con JWT_REFRESH_TTL (14 días) para permitir renovación silenciosa. */
const SESSION_COOKIE_MAX_AGE = 60 * 60 * 24 * 14

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: state => Boolean(state.token && state.user),
    permissions: state => state.user?.permissions ?? [],
    role: state => state.user?.role ?? null,
    staffRole: state => state.user?.staff_role ?? null,
    hasPermission: state => permission => {
      if (!permission)
        return true

      return (state.user?.permissions ?? []).includes(permission)
    },
  },

  actions: {
    syncAbilitiesFromUser(user) {
      if (!user?.permissions?.length) {
        ability.update([])

        return
      }

      const rules = user.permissions.map(slug => ({
        action: 'access',
        subject: slug,
      }))

      useCookie('userAbilityRules').value = rules
      ability.update(rules)
    },

    hydrateFromCookies() {
      this.token = useCookie(TOKEN_COOKIE).value || null
      this.user = useCookie(USER_COOKIE).value || null

      if ((this.token && !this.user) || (!this.token && this.user)) {
        this.clearSession()

        return
      }

      if (this.user)
        this.syncAbilitiesFromUser(this.user)
    },

    persistSession(token, user, tenantSlug, branchCode, tenantName = null, branchName = null) {
      this.token = token
      this.user = user

      const tokenCookie = useCookie(TOKEN_COOKIE, { maxAge: SESSION_COOKIE_MAX_AGE })
      const userCookie = useCookie(USER_COOKIE, { maxAge: SESSION_COOKIE_MAX_AGE })

      tokenCookie.value = token
      userCookie.value = user

      if (tenantSlug) {
        const slugCookie = useCookie(TENANT_SLUG_COOKIE, { maxAge: 60 * 60 * 24 * 30 })

        slugCookie.value = tenantSlug
      }

      if (branchCode) {
        const branchCookie = useCookie(BRANCH_CODE_COOKIE, { maxAge: 60 * 60 * 24 * 30 })

        branchCookie.value = branchCode
      }

      if (tenantName) {
        const nameCookie = useCookie(TENANT_NAME_COOKIE, { maxAge: 60 * 60 * 24 * 30 })

        nameCookie.value = tenantName
      }

      if (branchName) {
        const branchNameCookie = useCookie(BRANCH_NAME_COOKIE, { maxAge: 60 * 60 * 24 * 30 })

        branchNameCookie.value = branchName
      }

      if (user?.name) {
        const operatorCookie = useCookie(LAST_OPERATOR_NAME_COOKIE, { maxAge: 60 * 60 * 24 * 30 })

        operatorCookie.value = user.name
      }

      this.syncAbilitiesFromUser(user)
    },

    persistToken(token) {
      this.token = token
      useCookie(TOKEN_COOKIE, { maxAge: SESSION_COOKIE_MAX_AGE }).value = token
    },

    clearAuthOnly() {
      this.token = null
      this.user = null
      this.error = null
      useCookie(TOKEN_COOKIE).value = null
      useCookie(USER_COOKIE).value = null
      useCookie('userAbilityRules').value = null
      ability.update([])
    },

    clearSession() {
      this.token = null
      this.user = null
      useCookie(TOKEN_COOKIE).value = null
      useCookie(USER_COOKIE).value = null
      useCookie('userAbilityRules').value = null
      useCookie(TENANT_SLUG_COOKIE).value = null
      useCookie(BRANCH_CODE_COOKIE).value = null
      ability.update([])

      try {
        useContextStore().clearContext()
      }
      catch {
        // Pinia no inicializado
      }
    },

    async loginWithPin({ pin, tenantSlug, branchCode, tenantName, branchName }) {
      this.loading = true
      this.error = null

      try {
        const response = await api.post('/auth/login-pin', {
          pin,
          tenant_slug: tenantSlug,
          branch_code: branchCode,
        })

        const data = unwrapNightPosResponse(response)

        this.persistSession(data.token, data.user, tenantSlug, branchCode, tenantName, branchName)
        await useContextStore().applyContext({
          tenantSlug,
          branchCode,
          tenantName,
          branchName,
        })

        return data
      }
      catch (error) {
        this.error = getApiErrorMessage(error)
        throw error
      }
      finally {
        this.loading = false
      }
    },

    async loginWithPassword({ username, password, tenantSlug }) {
      this.loading = true
      this.error = null

      try {
        const normalizedUsername = username?.trim().toLowerCase() ?? ''
        const body = {
          username: normalizedUsername,
          password,
        }
        const slug = tenantSlug?.trim() || null
        const isPlatformUser = normalizedUsername === 'superadmin'

        if (slug && !isPlatformUser)
          body.tenant_slug = slug

        const response = await api.post('/auth/login-password', body)
        const data = unwrapNightPosResponse(response)

        if (data.user?.role === 'super_admin' || isPlatformUser) {
          useCookie(TENANT_SLUG_COOKIE).value = null
          useCookie(BRANCH_CODE_COOKIE).value = null
          useContextStore().clearContext()
          this.persistSession(data.token, data.user, null, null)
        }
        else {
          const branchCode = useCookie(BRANCH_CODE_COOKIE).value

          this.persistSession(data.token, data.user, slug, branchCode)
          await useContextStore().applyContext({ tenantSlug: slug, branchCode })
        }

        return data
      }
      catch (error) {
        this.error = getApiErrorMessage(error)
        throw error
      }
      finally {
        this.loading = false
      }
    },

    async fetchMe() {
      const response = await api.get('/auth/me')
      const data = unwrapNightPosResponse(response)

      this.user = data.user
      useCookie(USER_COOKIE, { maxAge: SESSION_COOKIE_MAX_AGE }).value = data.user
      this.syncAbilitiesFromUser(data.user)

      return data.user
    },

    async refreshSession() {
      const response = await api.post('/auth/refresh', null, { _skipAuthRefresh: true })
      const data = unwrapNightPosResponse(response)

      if (data?.token)
        this.persistToken(data.token)

      return data?.token ?? null
    },

    async logout() {
      try {
        await api.post('/auth/logout')
      }
      catch {
        // ignore
      }
      finally {
        this.clearSession()
      }
    },
  },
})
