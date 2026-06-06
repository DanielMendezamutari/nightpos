import { createApp } from 'vue'
import App from '@/App.vue'
import { registerPlugins } from '@core/utils/plugins'

// Styles
import '@core/scss/template/index.scss'
import '@styles/styles.scss'

// Create vue app
const app = createApp(App)


// Register plugins
registerPlugins(app)

// Mount vue app
app.mount('#app')

// Quitar splash HTML inicial (evita pantalla cargando infinita si Vue ya montó)
const loaderEl = document.getElementById('loading-bg')
if (loaderEl)
  loaderEl.remove()
