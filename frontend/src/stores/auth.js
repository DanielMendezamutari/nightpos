import { defineStore } from 'pinia'
import { $api } from '@/utils/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: typeof window !== 'undefined' ? localStorage.getItem('accessToken') : null,
    user: typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('userData') || 'null') : null,
    tenantSlug: typeof window !== 'undefined' ? (localStorage.getItem('riberresto_tenant_slug') || 'casa-demo') : 'casa-demo',
    branchCode: typeof window !== 'undefined' ? (localStorage.getItem('riberresto_branch_code') || 'CENTRO') : 'CENTRO',
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: state => !!state.token,
    userName: state => state.user?.name || 'Invitado',
    userRole: state => state.user?.role || 'cajero',
  },

  actions: {
    setSession(token, user, tenantSlug, branchCode) {
      this.token = token
      this.user = user
      this.tenantSlug = tenantSlug
      this.branchCode = branchCode

      if (typeof window !== 'undefined') {
        localStorage.setItem('accessToken', token)
        localStorage.setItem('userData', JSON.stringify(user))
        localStorage.setItem('riberresto_tenant_slug', tenantSlug)
        localStorage.setItem('riberresto_branch_code', branchCode)
        useCookie('accessToken').value = token
      }
    },

    async loginWithPin(pin, tenantSlug = null, branchCode = null) {
      this.loading = true
      this.error = null

      const tenant = tenantSlug || this.tenantSlug
      const branch = branchCode || this.branchCode

      try {
        const response = await $api('/api/v1/auth/login-pin', {
          method: 'POST',
          body: {
            pin: String(pin).trim(),
            tenant_slug: tenant,
            branch_code: branch,
          },
        })

        if (response.success && response.data?.token) {
          this.setSession(response.data.token, response.data.user, tenant, branch)
          return { success: true, user: response.data.user }
        }

        const msg = response.message || 'Error al autenticar con PIN'
        this.error = msg
        return { success: false, message: msg }
      } catch (err) {
        const msg = err.data?.message || err.message || 'Error de conexion con el servidor'
        this.error = msg
        return { success: false, message: msg }
      } finally {
        this.loading = false
      }
    },

    async loginWithPassword(username, password, tenantSlug = null) {
      this.loading = true
      this.error = null

      const tenant = tenantSlug || this.tenantSlug

      try {
        const response = await $api('/api/v1/auth/login-password', {
          method: 'POST',
          body: {
            username: String(username).trim(),
            password: String(password),
            tenant_slug: tenant,
          },
        })

        if (response.success && response.data?.token) {
          this.setSession(response.data.token, response.data.user, tenant, this.branchCode)
          return { success: true, user: response.data.user }
        }

        const msg = response.message || 'Credenciales invalidas'
        this.error = msg
        return { success: false, message: msg }
      } catch (err) {
        const msg = err.data?.message || err.message || 'Error al iniciar sesion'
        this.error = msg
        return { success: false, message: msg }
      } finally {
        this.loading = false
      }
    },

    logout() {
      this.token = null
      this.user = null
      if (typeof window !== 'undefined') {
        localStorage.removeItem('accessToken')
        localStorage.removeItem('userData')
        useCookie('accessToken').value = null
        window.location.href = '/login'
      }
    },
  },
})
