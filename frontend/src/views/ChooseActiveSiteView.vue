<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'
import { useNotificationStore } from '../stores/notificationStore'
import { useThemeStore } from '../stores/themeStore'

const router = useRouter()
const auth = useAuthStore()
const notify = useNotificationStore()
const themeStore = useThemeStore()
const loading = ref(false)

const sites = computed(() => auth.accessibleSites.value || [])

async function pick(siteId) {
  loading.value = true
  try {
    await auth.setActiveSite(siteId)
    notify.success('Sucursal seleccionada.')
    if (auth.user.value?.role === 'cashier' && auth.requiresOpenShift.value) {
      router.replace({ name: 'caja-workspace', query: { tab: 'apertura' } })
    } else if (auth.user.value?.role === 'waiter') {
      router.replace({ name: 'pos-mesero' })
    } else {
      router.replace({ name: 'dashboard' })
    }
  } catch (e) {
    notify.error(e instanceof Error ? e.message : 'No se pudo guardar la sucursal.')
  } finally {
    loading.value = false
  }
}

async function onLogout() {
  await auth.logout()
  router.replace({ name: 'login' })
}
</script>

<template>
  <section class="login-shell">
    <article class="login-card choose-site-card">
      <button class="theme-toggle-btn" type="button" @click="themeStore.toggleTheme()">
        {{ themeStore.theme.value === 'dark' ? 'Modo día' : 'Modo noche' }}
      </button>
      <div class="brand">
        <div class="brand-dot"></div>
        <div>
          <h1>NightPOS</h1>
          <p>Elige dónde trabajas hoy</p>
        </div>
      </div>
      <h2>Sucursal operativa</h2>
      <p class="login-subtitle">
        Selecciona la sucursal en la que atiendes en este momento. Podrás cambiarla después desde la barra superior.
      </p>
      <div class="choose-site-list">
        <button
          v-for="s in sites"
          :key="s.id"
          type="button"
          class="choose-site-btn"
          :disabled="loading"
          @click="pick(s.id)"
        >
          <strong>{{ s.code }}</strong>
          <span>{{ s.name }}</span>
        </button>
      </div>
      <button type="button" class="ghost-btn choose-site-logout" :disabled="loading" @click="onLogout">
        Cerrar sesión
      </button>
    </article>
  </section>
</template>

<style scoped>
.choose-site-card {
  width: min(480px, 100%);
}

.choose-site-list {
  display: grid;
  gap: 10px;
  margin: 16px 0 12px;
}

.choose-site-btn {
  display: grid;
  gap: 4px;
  text-align: left;
  padding: 14px 16px;
  border-radius: 12px;
  border: 1px solid rgba(145, 175, 255, 0.28);
  background: rgba(19, 33, 72, 0.55);
  color: #e8eeff;
  cursor: pointer;
  font: inherit;
  transition: background 0.15s ease, border-color 0.15s ease;
}

.choose-site-btn:hover:not(:disabled) {
  background: rgba(92, 129, 255, 0.28);
  border-color: rgba(113, 215, 255, 0.45);
}

.choose-site-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.choose-site-btn strong {
  font-size: 1rem;
  letter-spacing: 0.02em;
}

.choose-site-btn span {
  font-size: 0.88rem;
  color: #9eb3e9;
}

.choose-site-logout {
  width: 100%;
  margin-top: 8px;
}

:root[data-theme='light'] .choose-site-btn {
  background: rgba(230, 238, 255, 0.95);
  color: #1f2b4d;
  border-color: rgba(115, 138, 191, 0.35);
}

:root[data-theme='light'] .choose-site-btn span {
  color: #4b5f94;
}

:root[data-theme='light'] .choose-site-btn:hover:not(:disabled) {
  background: rgba(210, 224, 255, 0.95);
}
</style>
