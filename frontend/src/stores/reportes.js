import { defineStore } from 'pinia'
import { $api } from '@/utils/api'

export const useReportesStore = defineStore('reportes', {
  state: () => ({
    resumenVentas: {
      total_ventas: 0,
      total_transacciones: 0,
      ticket_promedio: 0,
      total_facturado: 0,
      total_recibos: 0,
      desglose_pagos: { efectivo: 0, qr: 0, tarjeta: 0, otros: 0 },
      ventas_por_dia: [],
    },
    topProductos: [],
    rendimientoMeseros: [],
    balanceFinanciero: {
      total_ingresos_ventas: 0,
      total_gastos_caja: 0,
      total_compras_insumos: 0,
      utilidad_operativa_bruta: 0,
    },
    loading: false,
    error: null,
  }),

  actions: {
    async fetchTodosReportes(fechaDesde, fechaHasta) {
      this.loading = true
      this.error = null
      try {
        const params = `?fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}`
        const [resumen, top, meseros, balance] = await Promise.all([
          $api(`/api/v1/reportes/resumen-ventas${params}`),
          $api(`/api/v1/reportes/top-productos${params}`),
          $api(`/api/v1/reportes/rendimiento-meseros${params}`),
          $api(`/api/v1/reportes/balance-financiero${params}`),
        ])

        if (resumen.success) this.resumenVentas = resumen.data
        if (top.success) this.topProductos = top.data
        if (meseros.success) this.rendimientoMeseros = meseros.data
        if (balance.success) this.balanceFinanciero = balance.data
      } catch (err) {
        this.error = err.message || 'Error al cargar reportes'
      } finally {
        this.loading = false
      }
    },
  },
})