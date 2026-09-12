import { defineStore } from 'pinia'
import { $api } from '@/utils/api'

export const useCajaStore = defineStore('caja', {
  state: () => ({
    turnoActivo: null,
    movimientos: [],
    facturas: [],
    tiposGastos: [],
    gastos: [],
    gastosSummary: { total_gastos: 0, total_efectivo: 0, total_otros: 0, cantidad: 0 },
    ultimoArqueo: null,
    reporteCierreZ: null,
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
        const isSinMesa = typeof mesaId === 'string' && mesaId.startsWith('sin_mesa_')
        const parsedVisitaId = isSinMesa ? parseInt(mesaId.replace('sin_mesa_', '')) : (payload.visita_id || null)
        const url = parsedVisitaId
          ? `/api/v1/visitas/${parsedVisitaId}/cobrar-facturar`
          : `/api/v1/mesas/${mesaId}/cobrar-facturar`

        const res = await $api(url, {
          method: 'POST',
          body: {
            ...payload,
            visita_id: parsedVisitaId,
          },
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

    // QR Payment Actions
    async generarPagoQr(payload) {
      try {
        const res = await $api('/api/v1/pagos/qr/generar', {
          method: 'POST',
          body: payload,
        })
        return { success: true, data: res.data }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message || 'Error al generar código QR' }
      }
    },

    async consultarEstadoQr(codigo) {
      try {
        const res = await $api(`/api/v1/pagos/qr/estado/${codigo}`, {
          method: 'GET',
        })
        return { success: true, data: res.data }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message || 'Error al consultar estado QR' }
      }
    },

    async simularPagoQr(codigo) {
      try {
        const res = await $api(`/api/v1/pagos/qr/simular/${codigo}`, {
          method: 'POST',
        })
        return { success: true, data: res.data, message: res.message }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message || 'Error al simular pago QR' }
      }
    },
    async fetchTiposGastos() {
      try {
        const res = await $api('/api/v1/caja/gastos/tipos', { method: 'GET' })
        if (res.success && res.data) {
          this.tiposGastos = res.data
        }
        return this.tiposGastos
      } catch (err) {
        console.error('Error cargando tipos de gastos:', err)
        return []
      }
    },

    async fetchGastos(turnoId = null) {
      try {
        const url = turnoId ? `/api/v1/caja/gastos?turno_id=${turnoId}` : '/api/v1/caja/gastos'
        const res = await $api(url, { method: 'GET' })
        if (res.success && res.data) {
          this.gastos = res.data
          if (res.summary) {
            this.gastosSummary = res.summary
          }
        }
        return this.gastos
      } catch (err) {
        console.error('Error cargando gastos:', err)
        return []
      }
    },

    async registrarGasto(payload) {
      this.loading = true
      try {
        const res = await $api('/api/v1/caja/gastos', {
          method: 'POST',
          body: payload,
        })
        if (res.success) {
          await this.fetchTurnoActivo()
          await this.fetchGastos()
        }
        return res
      } catch (err) {
        throw err
      } finally {
        this.loading = false
      }
    },

    async anularGasto(id) {
      try {
        const res = await $api(`/api/v1/caja/gastos/${id}`, { method: 'DELETE' })
        if (res.success) {
          await this.fetchTurnoActivo()
          await this.fetchGastos()
        }
        return res
      } catch (err) {
        throw err
      }
    },

    async realizarArqueoCiego(payload) {
      this.loading = true
      try {
        const res = await $api('/api/v1/caja/arqueo-ciego', {
          method: 'POST',
          body: payload,
        })
        if (res.success) {
          this.ultimoArqueo = res.data
          if (res.data.turno_cerrado) {
            this.turnoActivo = null
            await this.fetchReporteCierreZ(payload.turno_id)
          }
        }
        return res
      } catch (err) {
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchReporteCierreZ(turnoId) {
      try {
        const res = await $api(`/api/v1/caja/reporte-cierre/${turnoId}`, { method: 'GET' })
        if (res.success && res.data) {
          this.reporteCierreZ = res.data
        }
        return this.reporteCierreZ
      } catch (err) {
        console.error('Error cargando reporte Z:', err)
        return null
      }
    },
  },
})
