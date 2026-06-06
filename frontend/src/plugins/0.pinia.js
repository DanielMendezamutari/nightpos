import { createPinia } from 'pinia'
import { useContextStore } from '@/stores/context'

export const store = createPinia()

export default function (app) {
  app.use(store)
  useContextStore().hydrateFromCookies()
}
