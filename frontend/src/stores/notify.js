import { defineStore } from 'pinia'

/**
 * Snackbar global NightPOS — una sola instancia para toda la app.
 */
export const useNotifyStore = defineStore('notify', {
  state: () => ({
    show: false,
    text: '',
    color: 'success',
    timeout: 3500,
  }),

  actions: {
    notify(text, color = 'success', timeout = 3500) {
      this.text = text
      this.color = color
      this.timeout = timeout
      this.show = true
    },

    hide() {
      this.show = false
    },
  },
})
