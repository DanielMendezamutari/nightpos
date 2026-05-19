import './assets/main.css'
import './stores/themeStore'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { setUnauthorizedNavigator } from './services/api'

setUnauthorizedNavigator(() => {
  if (router.currentRoute.value.name !== 'login') {
    router.replace({ name: 'login' })
  }
})

createApp(App).use(router).mount('#app')
