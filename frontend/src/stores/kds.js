import { defineStore } from 'pinia'
import { $api } from '@/utils/api'

// Motor de audio nativo Web Audio API para alertas de cocina
let audioCtx = null
function getAudioContext() {
  if (typeof window === 'undefined') return null
  if (!audioCtx) {
    const AudioContext = window.AudioContext || window.webkitAudioContext
    if (AudioContext) {
      audioCtx = new AudioContext()
    }
  }
  if (audioCtx && audioCtx.state === 'suspended') {
    audioCtx.resume()
  }
  return audioCtx
}

function playBeep(freq = 880, duration = 0.15, type = 'sine') {
  try {
    const ctx = getAudioContext()
    if (!ctx) return
    const osc = ctx.createOscillator()
    const gain = ctx.createGain()
    osc.type = type
    osc.frequency.setValueAtTime(freq, ctx.currentTime)
    gain.gain.setValueAtTime(0.3, ctx.currentTime)
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration)
    osc.connect(gain)
    gain.connect(ctx.destination)
    osc.start()
    osc.stop(ctx.currentTime + duration)
  } catch (e) {
    console.warn('Audio Web API notice:', e)
  }
}

export const useKdsStore = defineStore('kds', {
  state: () => ({
    tickets: [],
    historial: [],
    metrics: {
      total_activos: 0,
      demorados: 0,
      tiempo_promedio_min: 0,
      estacion: 'TODAS',
    },
    estacionActiva: 'TODAS',
    loading: false,
    error: null,
    audioEnabled: true,
    autoRefresh: true,
    lastTicketCount: 0,
    pollingInterval: null,
  }),

  getters: {
    estacionesDisponibles: () => [
      { id: 'TODAS', label: 'Todas las Estaciones', icon: 'ri-apps-line' },
      { id: 'COCINA', label: 'Cocina Caliente', icon: 'ri-fire-line' },
      { id: 'BARRA', label: 'Barra & Bebidas', icon: 'ri-goblet-line' },
      { id: 'PARRILLA', label: 'Parrilla', icon: 'ri-restaurant-line' },
    ],
  },

  actions: {
    playNewOrderSound() {
      if (!this.audioEnabled) return
      playBeep(880, 0.12, 'triangle')
      setTimeout(() => playBeep(1320, 0.2, 'sine'), 130)
    },

    playFoodReadySound() {
      if (!this.audioEnabled) return
      playBeep(523.25, 0.1, 'sine') // Do
      setTimeout(() => playBeep(659.25, 0.1, 'sine'), 100) // Mi
      setTimeout(() => playBeep(783.99, 0.25, 'triangle'), 200) // Sol
    },

    async fetchTickets(silencioso = false) {
      if (!silencioso) this.loading = true
      try {
        const res = await $api(`/api/v1/kds/tickets?estacion=${this.estacionActiva}`)
        if (res.success && res.data) {
          // Si aumentó la cantidad de tickets y no es carga inicial, sonar alerta de comanda
          if (this.lastTicketCount > 0 && res.data.length > this.lastTicketCount) {
            this.playNewOrderSound()
          }
          this.tickets = res.data
          this.lastTicketCount = res.data.length
          if (res.metrics) {
            this.metrics = res.metrics
          }
        }
      } catch (err) {
        console.error('Error cargando tickets KDS:', err)
        this.error = err.message || 'Error de conexión con monitor de cocina'
      } finally {
        if (!silencioso) this.loading = false
      }
    },

    async setEstacion(estacion) {
      this.estacionActiva = estacion
      await this.fetchTickets()
    },

    async cambiarEstadoItem(itemId, nuevoEstado) {
      try {
        const res = await $api(`/api/v1/kds/items/${itemId}/estado`, {
          method: 'PATCH',
          body: { estado: nuevoEstado },
        })

        if (res.success) {
          // Actualizar en memoria el ítem
          for (const ticket of this.tickets) {
            const item = ticket.items.find(i => i.id === itemId)
            if (item) {
              item.estado = res.data.estado
              item.es_terminado = res.data.es_terminado
              item.terminado_at = res.data.terminado_at
              break
            }
          }
        }
        return res
      } catch (err) {
        console.error('Error al actualizar ítem:', err)
        throw err
      }
    },

    async despacharTicket(visitaId) {
      try {
        const res = await $api(`/api/v1/kds/tickets/${visitaId}/despachar`, {
          method: 'POST',
          body: { estacion: this.estacionActiva },
        })

        if (res.success) {
          this.playFoodReadySound()
          // Eliminar de tickets activos en vista inmediatamente
          this.tickets = this.tickets.filter(t => t.visita_id !== visitaId)
          this.metrics.total_activos = Math.max(0, this.metrics.total_activos - 1)
          this.lastTicketCount = this.tickets.length
          // Actualizar historial en segundo plano
          this.fetchHistorial()
        }
        return res
      } catch (err) {
        console.error('Error despachando comanda:', err)
        throw err
      }
    },

    async fetchHistorial() {
      try {
        const res = await $api('/api/v1/kds/historial')
        if (res.success && res.data) {
          this.historial = res.data
        }
      } catch (err) {
        console.error('Error cargando historial KDS:', err)
      }
    },

    async revertirDespacho(visitaId) {
      try {
        const res = await $api(`/api/v1/kds/tickets/${visitaId}/revertir`, {
          method: 'POST',
        })

        if (res.success) {
          await this.fetchTickets(true)
          await this.fetchHistorial()
        }
        return res
      } catch (err) {
        console.error('Error revirtiendo despacho:', err)
        throw err
      }
    },

    startPolling(seconds = 8) {
      this.stopPolling()
      this.pollingInterval = setInterval(() => {
        if (this.autoRefresh) {
          this.fetchTickets(true)
        }
      }, seconds * 1000)
    },

    stopPolling() {
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval)
        this.pollingInterval = null
      }
    },
  },
})
