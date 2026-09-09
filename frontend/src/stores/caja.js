import { defineStore } from 'pinia'
import { $api } from '@/utils/api'

export const useCajaStore = defineStore('caja', {
  state: () => ({
    turnoActivo: null,
    movimientos: [],
    facturas: [],
    loading: false,
    error: null,
  }),

  getters: {
    isTurnoAbierto: state => !!state.turnoActivo && state.turnoActivo.estado === 'ABIERTO',
    efectivoEsperado: state => state.turnoActivo?.efectivo_esperado || 0,
    totalVentas: state => state.turnoActivo?.total_ventas || 0,
  },

  actions: {
    async fetchTurnoActivo() {
      this.loading = true
      this.error = null
      try {
        const res = await $api('/api/v1/caja/turno-activo', { method: 'GET' })
        this.turnoActivo = res.data || null
        return this.turnoActivo
      } catch (err) {
        this.error = err.message || 'Error al obtener turno activo'
        this.turnoActivo = null
        return null
      } finally {
        this.loading = false
      }
    },

    async abrirTurno(payload) {
      this.loading = true
      this.error = null
      try {
        const res = await $api('/api/v1/caja/abrir-turno', {
          method: 'POST',
          body: payload,
        })
        await this.fetchTurnoActivo()
        return { success: true, data: res.data }
      } catch (err) {
        this.error = err.data?.message || err.message || 'Error al abrir turno'
        return { success: false, message: this.error }
      } finally {
        this.loading = false
      }
    },

    async cerrarTurno(payload) {
      this.loading = true
      this.error = null
      try {
        const res = await $api('/api/v1/caja/cerrar-turno', {
          method: 'POST',
          body: payload,
        })
        this.turnoActivo = null
        return { success: true, data: res.data }
      } catch (err) {
        this.error = err.data?.message || err.message || 'Error al cerrar turno'
        return { success: false, message: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchMovimientos(turnoId = null) {
      try {
        const url = turnoId ? `/api/v1/caja/movimientos?turno_id=${turnoId}` : '/api/v1/caja/movimientos'
        const res = await $api(url, { method: 'GET' })
        this.movimientos = res.data || []
        return this.movimientos
      } catch (err) {
        return []
      }
    },

    async registrarMovimiento(payload) {
      this.loading = true
      try {
        const res = await $api('/api/v1/caja/movimientos', {
          method: 'POST',
          body: payload,
        })
        await this.fetchMovimientos()
        await this.fetchTurnoActivo()
        return { success: true, data: res.data }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message || 'Error al registrar movimiento' }
      } finally {
        this.loading = false
      }
    },

    async cobrarYFacturar(mesaId, payload) {
      this.loading = true
      try {
        const res = await $api(`/api/v1/mesas/${mesaId}/cobrar-facturar`, {
          method: 'POST',
          body: payload,
        })
        await this.fetchTurnoActivo()
        return { success: true, data: res.data }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message || 'Error al cobrar y facturar' }
      } finally {
        this.loading = false
      }
    },

    async fetchFacturas(turnoId = null) {
      try {
        const url = turnoId ? `/api/v1/facturas?turno_id=${turnoId}` : '/api/v1/facturas'
        const res = await $api(url, { method: 'GET' })
        this.facturas = res.data || []
        return this.facturas
      } catch (err) {
        return []
      }
    },

    async anularFactura(id, motivo) {
      try {
        const res = await $api(`/api/v1/facturas/${id}/anular`, {
          method: 'POST',
          body: { motivo },
        })
        await this.fetchFacturas()
        await this.fetchTurnoActivo()
        return { success: true, data: res.data }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message || 'Error al anular factura' }
      }
    },
  },
})