import { defineStore } from 'pinia'
import { $api } from '@/utils/api'

export const useInventarioStore = defineStore('inventario', {
  state: () => ({
    almacenes: [],
    insumos: [],
    compras: [],
    proveedores: [],
    kardex: [],
    recetaActual: null,
    loading: false,
    error: null,
  }),

  getters: {
    insumosBajoStock: (state) => state.insumos.filter(i => Number(i.stock_actual) <= Number(i.stock_minimo)),
    totalDeudaProveedores: (state) => state.proveedores.reduce((acc, p) => acc + Number(p.saldo_deuda || 0), 0),
  },

  actions: {
    // ALMACENES
    async fetchAlmacenes() {
      try {
        const { data } = await $api.get('/almacenes')
        this.almacenes = data
        return data
      } catch (err) {
        console.error('Error al cargar almacenes:', err)
      }
    },

    async guardarAlmacen(payload) {
      if (payload.id) {
        const { data } = await $api.put(`/almacenes/${payload.id}`, payload)
        await this.fetchAlmacenes()
        return data
      } else {
        const { data } = await $api.post('/almacenes', payload)
        await this.fetchAlmacenes()
        return data
      }
    },

    // INSUMOS
    async fetchInsumos(params = {}) {
      this.loading = true
      try {
        const { data } = await $api.get('/insumos', { params })
        this.insumos = data
        return data
      } catch (err) {
        console.error('Error al cargar insumos:', err)
      } finally {
        this.loading = false
      }
    },

    async guardarInsumo(payload) {
      if (payload.id) {
        const { data } = await $api.put(`/insumos/${payload.id}`, payload)
        await this.fetchInsumos()
        return data
      } else {
        const { data } = await $api.post('/insumos', payload)
        await this.fetchInsumos()
        return data
      }
    },

    async ajustarStock(insumoId, payload) {
      const { data } = await $api.post(`/insumos/${insumoId}/ajuste`, payload)
      await this.fetchInsumos()
      return data
    },

    // COMPRAS
    async fetchCompras() {
      try {
        const { data } = await $api.get('/compras')
        this.compras = data
        return data
      } catch (err) {
        console.error('Error al cargar compras:', err)
      }
    },

    async registrarCompra(payload) {
      const { data } = await $api.post('/compras', payload)
      await this.fetchCompras()
      await this.fetchInsumos()
      await this.fetchProveedores()
      return data
    },

    // PROVEEDORES
    async fetchProveedores(params = {}) {
      try {
        const { data } = await $api.get('/proveedores', { params })
        this.proveedores = data
        return data
      } catch (err) {
        console.error('Error al cargar proveedores:', err)
      }
    },

    async guardarProveedor(payload) {
      if (payload.id) {
        const { data } = await $api.put(`/proveedores/${payload.id}`, payload)
        await this.fetchProveedores()
        return data
      } else {
        const { data } = await $api.post('/proveedores', payload)
        await this.fetchProveedores()
        return data
      }
    },

    async registrarPagoProveedor(proveedorId, payload) {
      const { data } = await $api.post(`/proveedores/${proveedorId}/pago`, payload)
      await this.fetchProveedores()
      return data
    },

    // RECETAS
    async fetchReceta(productoId) {
      try {
        const { data } = await $api.get(`/recetas/producto/${productoId}`)
        this.recetaActual = data
        return data
      } catch (err) {
        console.error('Error al cargar receta:', err)
      }
    },

    async guardarReceta(productoId, payload) {
      const { data } = await $api.post(`/recetas/producto/${productoId}`, payload)
      return data
    },

    // KARDEX
    async fetchKardex(params = {}) {
      try {
        const { data } = await $api.get('/kardex', { params })
        this.kardex = data
        return data
      } catch (err) {
        console.error('Error al cargar kardex:', err)
      }
    },
  },
})