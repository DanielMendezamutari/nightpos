import { defineStore } from 'pinia'
import { $api } from '@/utils/api'

export const useSalonMesaStore = defineStore('salonMesa', {
  state: () => ({
    salones: [],
    activeSalonId: null,
    mesas: [],
    selectedMesa: null,
    selectedMesaDetails: null,
    filterStatus: 'TODAS',
    searchQuery: '',
    loading: false,
    error: null,
  }),

  getters: {
    activeSalon: state => state.salones.find(s => s.id === state.activeSalonId) || null,

    filteredMesas: state => {
      let result = state.mesas

      if (state.filterStatus !== 'TODAS') {
        result = result.filter(m => m.estado === state.filterStatus)
      }

      if (state.searchQuery.trim()) {
        const q = state.searchQuery.toLowerCase().trim()
        result = result.filter(m => 
          m.codigo.toLowerCase().includes(q) || 
          m.nombre.toLowerCase().includes(q) ||
          (m.mesero_nombre && m.mesero_nombre.toLowerCase().includes(q))
        )
      }

      return result
    },

    totalesResumen: state => {
      const total = state.mesas.length
      const libres = state.mesas.filter(m => m.estado === 'LIBRE').length
      const ocupadas = state.mesas.filter(m => m.estado === 'OCUPADA').length
      const precuenta = state.mesas.filter(m => m.estado === 'PRECUENTA').length
      const consumoTotal = state.mesas.reduce((acc, m) => acc + (parseFloat(m.total_consumo) || 0), 0)

      return { total, libres, ocupadas, precuenta, consumoTotal }
    },
  },

  actions: {
    async fetchSalones() {
      this.loading = true
      this.error = null
      try {
        const response = await $api('/api/v1/salones')
        if (response.success && response.data) {
          this.salones = response.data
          if (!this.activeSalonId && this.salones.length > 0) {
            this.activeSalonId = this.salones[0].id
          }
          if (this.activeSalonId) {
            await this.fetchMesas(this.activeSalonId)
          }
        }
      } catch (err) {
        this.error = err.message || 'Error al cargar salones'
      } finally {
        this.loading = false
      }
    },

    async fetchMesas(salonId) {
      this.activeSalonId = salonId
      this.loading = true
      this.error = null
      try {
        const response = await $api(`/api/v1/salones/${salonId}/mesas`)
        if (response.success && response.data) {
          this.mesas = response.data
        }
      } catch (err) {
        this.error = err.message || 'Error al cargar mesas del salón'
      } finally {
        this.loading = false
      }
    },

    async fetchMesaDetails(mesaId) {
      try {
        const response = await $api(`/api/v1/mesas/${mesaId}`)
        if (response.success && response.data) {
          this.selectedMesaDetails = response.data
          return response.data
        }
      } catch (err) {
        console.error('Error fetching mesa details:', err)
      }
      return null
    },

    async abrirMesa(mesaId, payload) {
      this.loading = true
      try {
        const response = await $api(`/api/v1/mesas/${mesaId}/abrir`, {
          method: 'POST',
          body: payload,
        })
        if (response.success) {
          await this.fetchSalones()
          await this.fetchMesas(this.activeSalonId)
          return { success: true }
        }
        return { success: false, message: response.message }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message }
      } finally {
        this.loading = false
      }
    },

    async cambiarMesa(mesaOrigenId, mesaDestinoId) {
      this.loading = true
      try {
        const response = await $api(`/api/v1/mesas/${mesaOrigenId}/cambiar`, {
          method: 'POST',
          body: { mesa_destino_id: mesaDestinoId },
        })
        if (response.success) {
          await this.fetchSalones()
          await this.fetchMesas(this.activeSalonId)
          return { success: true }
        }
        return { success: false, message: response.message }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message }
      } finally {
        this.loading = false
      }
    },

    async solicitarPrecuenta(mesaId) {
      try {
        const response = await $api(`/api/v1/mesas/${mesaId}/precuenta`, {
          method: 'POST',
        })
        if (response.success) {
          await this.fetchSalones()
          await this.fetchMesas(this.activeSalonId)
          if (this.selectedMesa?.id === mesaId) {
            await this.fetchMesaDetails(mesaId)
          }
          return { success: true }
        }
        return { success: false, message: response.message }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message }
      }
    },

    async liberarMesa(mesaId) {
      try {
        const response = await $api(`/api/v1/mesas/${mesaId}/liberar`, {
          method: 'POST',
        })
        if (response.success) {
          await this.fetchSalones()
          await this.fetchMesas(this.activeSalonId)
          this.selectedMesa = null
          this.selectedMesaDetails = null
          return { success: true }
        }
        return { success: false, message: response.message }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message }
      }
    },
  },
})