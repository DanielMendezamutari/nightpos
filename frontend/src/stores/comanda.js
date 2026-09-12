import { defineStore } from 'pinia'
import { $api } from '@/utils/api'

export const useComandaStore = defineStore('comanda', {
  state: () => ({
    categorias: [],
    selectedCategoriaId: null,
    productos: [],
    observacionesCocina: [],
    carrito: [],
    busqueda: '',
    loading: false,
    error: null,
  }),

  getters: {
    totalItemsCarrito: state => state.carrito.reduce((acc, item) => acc + item.cantidad, 0),
    totalMontoCarrito: state => state.carrito.reduce((acc, item) => acc + item.subtotal, 0),

    filteredProductos: state => {
      if (!state.busqueda.trim()) return state.productos
      const q = state.busqueda.toLowerCase().trim()
      return state.productos.filter(p => 
        p.nombre.toLowerCase().includes(q) || 
        (p.codigo && p.codigo.toLowerCase().includes(q))
      )
    },
  },

  actions: {
    async fetchCategorias() {
      try {
        const res = await $api('/api/v1/menu/categorias')
        if (res.success && res.data) {
          this.categorias = res.data
          if (!this.selectedCategoriaId && this.categorias.length > 0) {
            this.selectedCategoriaId = this.categorias[0].id
            await this.fetchProductos(this.selectedCategoriaId)
          }
        }
      } catch (err) {
        console.error('Error fetching categorias:', err)
      }
    },

    async fetchProductos(categoriaId = null) {
      this.loading = true
      this.selectedCategoriaId = categoriaId
      try {
        const url = categoriaId 
          ? `/api/v1/menu/productos?categoria_id=${categoriaId}`
          : '/api/v1/menu/productos'
        const res = await $api(url)
        if (res.success && res.data) {
          this.productos = res.data
        }
      } catch (err) {
        console.error('Error fetching productos:', err)
      } finally {
        this.loading = false
      }
    },

    async fetchObservacionesCocina() {
      try {
        const res = await $api('/api/v1/menu/observaciones-cocina')
        if (res.success && res.data) {
          this.observacionesCocina = res.data
        }
      } catch (err) {
        console.error('Error fetching observaciones:', err)
      }
    },

    agregarItem(producto) {
      const existing = this.carrito.find(i => i.producto_id === producto.id)
      if (existing) {
        existing.cantidad += 1
        existing.subtotal = Math.round(existing.cantidad * existing.precio * 100) / 100
      } else {
        this.carrito.push({
          producto_id: producto.id,
          producto_nombre: producto.nombre,
          precio: parseFloat(producto.precio),
          cantidad: 1,
          subtotal: parseFloat(producto.precio),
          observaciones: '',
          destino_impresion: producto.destino_impresion,
        })
      }
    },

    incrementarCantidad(productoId) {
      const item = this.carrito.find(i => i.producto_id === productoId)
      if (item) {
        item.cantidad += 1
        item.subtotal = Math.round(item.cantidad * item.precio * 100) / 100
      }
    },

    decrementarCantidad(productoId) {
      const idx = this.carrito.findIndex(i => i.producto_id === productoId)
      if (idx !== -1) {
        if (this.carrito[idx].cantidad > 1) {
          this.carrito[idx].cantidad -= 1
          this.carrito[idx].subtotal = Math.round(this.carrito[idx].cantidad * this.carrito[idx].precio * 100) / 100
        } else {
          this.carrito.splice(idx, 1)
        }
      }
    },

    removerItem(productoId) {
      this.carrito = this.carrito.filter(i => i.producto_id !== productoId)
    },

    setObservacion(productoId, obs) {
      const item = this.carrito.find(i => i.producto_id === productoId)
      if (item) {
        item.observaciones = item.observaciones ? `${item.observaciones}, ${obs}` : obs
      }
    },

    limpiarCarrito() {
      this.carrito = []
    },

    async enviarComanda(mesaId, visitaId = null) {
      if (this.carrito.length === 0) return { success: false, message: 'El pedido está vacío' }

      this.loading = true
      try {
        const parsedVisitaId = visitaId || (typeof mesaId === 'string' && mesaId.startsWith('sin_mesa_') ? parseInt(mesaId.replace('sin_mesa_', '')) : null)

        const payload = {
          visita_id: parsedVisitaId,
          items: this.carrito.map(item => ({
            producto_id: item.producto_id,
            cantidad: item.cantidad,
            observaciones: item.observaciones || null,
          })),
        }

        const url = parsedVisitaId 
          ? `/api/v1/visitas/${parsedVisitaId}/comanda`
          : `/api/v1/mesas/${mesaId}/comanda`

        const res = await $api(url, {
          method: 'POST',
          body: payload,
        })

        if (res.success) {
          this.limpiarCarrito()
          return { success: true }
        }
        return { success: false, message: res.message }
      } catch (err) {
        return { success: false, message: err.data?.message || err.message }
      } finally {
        this.loading = false
      }
    },


    async fetchSubcuentas(mesaId) {
      try {
        const res = await $api(`/api/v1/mesas/${mesaId}/separar-cuentas`, { method: 'GET' })
        if (res.success && res.data) {
          this.subcuentasData = res.data
        }
        return res
      } catch (err) {
        console.error('Error cargando subcuentas:', err)
        return { success: false, message: err.data?.message || err.message }
      }
    },

    async crearSubcuenta(mesaId, nombreComensal = '') {
      try {
        const res = await $api(`/api/v1/mesas/${mesaId}/separar-cuentas/crear`, {
          method: 'POST',
          body: { nombre_comensal: nombreComensal },
        })
        if (res.success) {
          await this.fetchSubcuentas(mesaId)
        }
        return res
      } catch (err) {
        throw err
      }
    },

    async moverItemSubcuenta(mesaId, payload) {
      try {
        const res = await $api(`/api/v1/mesas/${mesaId}/separar-cuentas/mover-item`, {
          method: 'POST',
          body: payload,
        })
        if (res.success) {
          await this.fetchSubcuentas(mesaId)
        }
        return res
      } catch (err) {
        throw err
      }
    },

    async dividirEnPartesIguales(mesaId, personas) {
      try {
        const res = await $api(`/api/v1/mesas/${mesaId}/separar-cuentas/dividir-iguales`, {
          method: 'POST',
          body: { personas },
        })
        if (res.success) {
          await this.fetchSubcuentas(mesaId)
        }
        return res
      } catch (err) {
        throw err
      }
    },

    async juntarCuentas(mesaId) {
      try {
        const res = await $api(`/api/v1/mesas/${mesaId}/separar-cuentas/juntar`, {
          method: 'POST',
        })
        if (res.success) {
          await this.fetchSubcuentas(mesaId)
        }
        return res
      } catch (err) {
        throw err
      }
    },

    async registrarPagoParcial(mesaId, payload) {
      try {
        const res = await $api(`/api/v1/mesas/${mesaId}/separar-cuentas/pago-parcial`, {
          method: 'POST',
          body: payload,
        })
        if (res.success) {
          await this.fetchSubcuentas(mesaId)
        }
        return res
      } catch (err) {
        throw err
      }
    },

    async cobrarSubcuenta(subcuentaId, payload) {
      try {
        const res = await $api(`/api/v1/subcuentas/${subcuentaId}/cobrar`, {
          method: 'POST',
          body: payload,
        })
        return res
      } catch (err) {
        throw err
      }
    },

    async fetchReportePropinas(turnoId) {
      try {
        const res = await $api(`/api/v1/caja/reporte-propinas/${turnoId}`, { method: 'GET' })
        return res
      } catch (err) {
        console.error('Error cargando propinas:', err)
        return { success: false, data: [] }
      }
    },


    // BUCLE 9: OPERACIONES DE MESA Y SALON
    async cambiarMesa(origenId, destinoId, motivo = '') {
      try {
        const res = await $api(`/api/v1/mesas/${origenId}/cambiar-mesa`, {
          method: 'POST',
          body: { mesa_destino_id: destinoId, motivo },
        })
        return res
      } catch (err) {
        throw err
      }
    },

    async juntarMesa(origenId, destinoId, motivo = '') {
      try {
        const res = await $api(`/api/v1/mesas/${origenId}/juntar-mesa`, {
          method: 'POST',
          body: { mesa_destino_id: destinoId, motivo },
        })
        return res
      } catch (err) {
        throw err
      }
    },

    async fetchPedidosSinMesa() {
      try {
        const res = await $api('/api/v1/pedidos-sin-mesa', { method: 'GET' })
        return res
      } catch (err) {
        console.error('Error fetching pedidos sin mesa:', err)
        return []
      }
    },

    async crearPedidoSinMesa(payload) {
      try {
        const res = await $api('/api/v1/pedidos-sin-mesa', {
          method: 'POST',
          body: payload,
        })
        return res
      } catch (err) {
        throw err
      }
    },

    async asignarMesaAPedidoSinMesa(visitaId, mesaDestinoId) {
      try {
        const res = await $api(`/api/v1/pedidos-sin-mesa/${visitaId}/asignar-mesa`, {
          method: 'POST',
          body: { mesa_destino_id: mesaDestinoId },
        })
        return res
      } catch (err) {
        throw err
      }
    },

    async reasignarMesero(mesaId, meseroId, motivo = '') {
      try {
        const res = await $api(`/api/v1/mesas/${mesaId}/reasignar-mesero`, {
          method: 'POST',
          body: { mesero_id: meseroId, motivo },
        })
        return res
      } catch (err) {
        throw err
      }
    },

    async eliminarItemComandaExistente(detalleId) {
      try {
        const res = await $api(`/api/v1/visita-detalles/${detalleId}`, {
          method: 'DELETE',
        })
        return res.success
      } catch (err) {
        console.error('Error deleting detalle:', err)
        return false
      }
    },
  },
})