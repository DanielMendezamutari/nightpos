import { defineStore } from 'pinia'
import api, { getApiErrorMessage, unwrapNightPosResponse } from '@/services/http'
import { useAuthStore } from '@/stores/auth'
import { useContextStore } from '@/stores/context'

export const useOperationalStore = defineStore('operational', {
  state: () => ({
    tenant: null,
    branch: null,
    branches: [],
    loading: false,
    error: null,
  }),

  actions: {
    async loadTenantCurrent() {
      try {
        const response = await api.get('/tenant/current')
        const data = unwrapNightPosResponse(response)

        this.tenant = data.tenant ?? data

        return this.tenant
      }
      catch (error) {
        this.error = getApiErrorMessage(error)
        throw error
      }
    },

    async loadBranchCurrent() {
      try {
        const response = await api.get('/branches/current')
        const data = unwrapNightPosResponse(response)

        this.branch = data.branch ?? data

        return this.branch
      }
      catch (error) {
        this.branch = null

        return null
      }
    },

    async loadAvailableBranches() {
      const response = await api.get('/branches/available')
      const data = unwrapNightPosResponse(response)

      this.branches = data.branches ?? []

      return this.branches
    },

    async refreshContext() {
      this.loading = true
      this.error = null

      try {
        const auth = useAuthStore()
        const context = useContextStore()
        const isGlobalSuperAdmin = auth.role === 'super_admin'
          && !context.tenantSlug

        if (isGlobalSuperAdmin) {
          this.tenant = null
          this.branch = null
          this.branches = []

          return
        }

        await this.loadTenantCurrent()
        await this.loadAvailableBranches()
        await this.loadBranchCurrent()
      }
      catch (error) {
        this.error = getApiErrorMessage(error)
      }
      finally {
        this.loading = false
      }
    },
  },
})
