import { defineStore } from 'pinia'
import { $api } from '@/utils/api'

export const useClientesStore = defineStore('clientes', {
  state: () => ({
    clientes: [],
    clienteActual: null,
    loading: false,
    error: null,
  }),

  actions: {
    async fetchClientes(params = {}) {
      this.loading = true
      this.error = null
      try {
        const queryParams = new URLSearchParams()
        if (params.search) queryParams.append('search', params.search)
        if (params.solo_deudores) queryParams.append('solo_deudores', '1')
        if (params.cumpleaneros_mes) queryParams.append('cumpleaneros_mes', params.cumpleaneros_mes)

        const qs = queryParams.toString()
        const url = qs ? `/api/v1/clientes?${qs}` : '/api/v1/clientes'
        const res = await $api(url, { method: 'GET' })
        this.clientes = res.data || []
        return this.clientes
      } catch (err) {
        this.error = err.message || 'Error al cargar clientes'
        this.clientes = []
        return []
      } finally {
        this.loading = false
      }
    },

    async fetchClienteDetalle(id) {
      this.loading = true
      try {
        const res = await $api(`/api/v1/clientes/${id}`, { method: 'GET' })
        this.clienteActual = res.data || null
        return this.clienteActual
      } catch (err) {
        return null
      } finally {
        this.loading = false
      }
    },

    async crearCliente(payload) {
      this.loading = true
      try {
        const res = await $api('/api/v1/clientes', {
          method: 'POST',
          body: payload,
        })
        await this.fetchClientes()
        return { success: true, data: res.data }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message || 'Error al crear cliente' }
      } finally {
        this.loading = false
      }
    },

    async actualizarCliente(id, payload) {
      this.loading = true
      try {
        const res = await $api(`/api/v1/clientes/${id}`, {
          method: 'PUT',
          body: payload,
        })
        await this.fetchClientes()
        return { success: true, data: res.data }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message || 'Error al actualizar cliente' }
      } finally {
        this.loading = false
      }
    },

    async eliminarCliente(id) {
      try {
        const res = await $api(`/api/v1/clientes/${id}`, { method: 'DELETE' })
        await this.fetchClientes()
        return { success: true }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message || 'Error al eliminar cliente' }
      }
    },

    async registrarAbono(id, payload) {
      this.loading = true
      try {
        const res = await $api(`/api/v1/clientes/${id}/abono`, {
          method: 'POST',
          body: payload,
        })
        await this.fetchClientes()
        return { success: true, data: res.data }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message || 'Error al registrar abono' }
      } finally {
        this.loading = false
      }
    },

    async registrarAnticipo(id, payload) {
      this.loading = true
      try {
        const res = await $api(`/api/v1/clientes/${id}/anticipos`, {
          method: 'POST',
          body: payload,
        })
        await this.fetchClientes()
        return { success: true, data: res.data }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message || 'Error al registrar anticipo' }
      } finally {
        this.loading = false
      }
    },

    async fetchAnticiposDisponibles(id) {
      try {
        const res = await $api(`/api/v1/clientes/${id}/anticipos-disponibles`, { method: 'GET' })
        return res.data || []
      } catch (err) {
        return []
      }
    },
  },
})