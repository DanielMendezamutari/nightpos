import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useNotifyStore } from '@/stores/notify'

/**
 * Snackbar global NightPOS — delega al store Pinia (un solo toast en App.vue).
 */
export function useNightPosNotify() {
  const store = useNotifyStore()
  const { show, text, color } = storeToRefs(store)

  const notify = (message, snackbarColor = 'success') => store.notify(message, snackbarColor)

  const snackbar = computed({
    get: () => ({
      show: show.value,
      text: text.value,
      color: color.value,
    }),
    set: (value) => {
      if (value && typeof value.show === 'boolean')
        show.value = value.show
    },
  })

  return { snackbar, notify }
}
