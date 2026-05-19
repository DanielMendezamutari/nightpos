<script setup>
import { computed, reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { API_BASE } from '../services/api'
import { useSidebar } from '../sidebar'
import { useAuthStore } from '../stores/authStore'
import ToastStack from './ToastStack.vue'
import { useNotificationStore } from '../stores/notificationStore'
import { useThemeStore } from '../stores/themeStore'

const router = useRouter()
const auth = useAuthStore()
const notify = useNotificationStore()
const themeStore = useThemeStore()
const {
  route,
  mobileMenuOpen,
  reportsMenuOpen,
  adminMenuOpen,
  adminDetallesOpen,
  maintenanceMenuOpen,
  posMenuOpen,
  branchContextLabel,
  isWaiterShell,
  menuItems,
  showReportsMenu,
  showAdminMenu,
  showMaintenanceMenu,
  showCajaMenu,
  showPosMenu,
  toggleAdminMenu,
  toggleDetallesMenu,
  toggleMaintenanceMenu,
  togglePosMenu,
  toggleReportsMenu,
  toggleMobileMenu,
  closeMobileMenu,
} = useSidebar(auth)

const cajaMenuLabel = computed(() => (auth.user.value?.role === 'cashier' ? 'Mi caja' : 'Caja'))

const switchingSite = ref(false)
const profileModalOpen = ref(false)
const savingProfile = ref(false)
const profileForm = reactive({
  name: '',
  email: '',
  pin: '',
  password: '',
  password_confirmation: '',
})

async function onLogout() {
  await auth.logout()
  notify.info('Sesion cerrada correctamente.')
  await router.replace({ name: 'login' })
}

async function onChangeActiveSite(event) {
  const siteId = Number(event.target.value || 0)
  if (!siteId) return
  switchingSite.value = true
  try {
    await auth.setActiveSite(siteId)
    notify.success('Sucursal activa actualizada.')
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudo cambiar la sucursal activa.')
  } finally {
    switchingSite.value = false
  }
}

function openProfileModal() {
  profileForm.name = auth.user.value?.name || ''
  profileForm.email = auth.user.value?.email || ''
  profileForm.pin = auth.user.value?.pin_code || ''
  profileForm.password = ''
  profileForm.password_confirmation = ''
  profileModalOpen.value = true
}

function closeProfileModal() {
  profileModalOpen.value = false
}

async function saveMyProfile() {
  const payload = {
    name: profileForm.name.trim(),
    email: profileForm.email.trim(),
  }

  if (profileForm.pin.trim()) {
    payload.pin = profileForm.pin.trim()
  } else {
    payload.pin = null
  }

  if (profileForm.password || profileForm.password_confirmation) {
    if (profileForm.password !== profileForm.password_confirmation) {
      notify.error('Las contrasenas no coinciden.')
      return
    }
    payload.password = profileForm.password
  }

  savingProfile.value = true
  try {
    await auth.updateMyProfile(payload)
    notify.success('Perfil actualizado correctamente.')
    closeProfileModal()
  } catch (error) {
    notify.error(error instanceof Error ? error.message : 'No se pudo guardar tu perfil.')
  } finally {
    savingProfile.value = false
  }
}
</script>

<template>
  <div :class="['app-shell', { 'app-shell--waiter': isWaiterShell }]">
    <template v-if="isWaiterShell">
      <header class="waiter-topbar">
        <div class="waiter-topbar-row">
          <span class="waiter-brand">NightPOS</span>
          <div class="waiter-topbar-actions">
            <button type="button" class="waiter-chip-btn" @click="themeStore.toggleTheme()">
              {{ themeStore.theme.value === 'dark' ? 'Claro' : 'Oscuro' }}
            </button>
            <button type="button" class="waiter-chip-btn" @click="openProfileModal">Perfil</button>
            <button type="button" class="waiter-chip-btn waiter-chip-btn--danger" @click="onLogout">Salir</button>
          </div>
        </div>
        <p v-if="branchContextLabel" class="waiter-branch-line">{{ branchContextLabel }}</p>
        <select
          v-if="auth.accessibleSites.value?.length > 1"
          class="waiter-site-select"
          :value="String(auth.resolvedActiveSiteId.value || '')"
          :disabled="switchingSite"
          @change="onChangeActiveSite"
        >
          <option v-for="s in auth.accessibleSites.value" :key="s.id" :value="s.id">
            {{ s.code }} — {{ s.name }}
          </option>
        </select>
      </header>
      <main class="main-content waiter-main">
        <ToastStack />
        <slot />
      </main>
      <nav class="waiter-tabbar" aria-label="Principal">
        <RouterLink
          to="/pos/mesero"
          class="waiter-tab"
          :class="{ 'waiter-tab--active': route.path === '/pos/mesero' }"
        >
          Mesas
        </RouterLink>
        <RouterLink
          to="/productos"
          class="waiter-tab"
          :class="{ 'waiter-tab--active': route.path === '/productos' }"
        >
          Carta
        </RouterLink>
      </nav>
    </template>

    <template v-else>
    <button class="mobile-menu-btn" @click="toggleMobileMenu">
      {{ mobileMenuOpen ? 'Cerrar menu' : 'Menu' }}
    </button>

    <div v-if="mobileMenuOpen" class="mobile-backdrop" @click="closeMobileMenu"></div>

    <aside :class="['sidebar', { 'sidebar-open': mobileMenuOpen }]">
      <div class="brand">
        <div class="brand-dot"></div>
        <div>
          <h1>NightPOS</h1>
          <p>SaaS Control Center</p>
        </div>
      </div>

      <nav class="menu">
        <div v-if="showReportsMenu" class="menu-group">
          <button
            type="button"
            class="menu-group-head"
            :class="{ 'menu-group-head-active': route.path.startsWith('/reportes') }"
            @click="toggleReportsMenu"
          >
            <span>Reportes</span>
            <span class="menu-chevron" :class="{ 'menu-chevron-open': reportsMenuOpen }" aria-hidden="true"></span>
          </button>
          <div v-show="reportsMenuOpen" class="menu-children">
            <RouterLink
              :to="{ name: 'reportes-productos-vendidos' }"
              :class="{ active: route.name === 'reportes-productos-vendidos' }"
              @click="closeMobileMenu"
            >
              Productos vendidos
            </RouterLink>
            <RouterLink
              :to="{ name: 'reportes-ventas' }"
              :class="{ active: route.name === 'reportes-ventas' }"
              @click="closeMobileMenu"
            >
              Ventas
            </RouterLink>
            <RouterLink
              :to="{ name: 'reportes-personal' }"
              :class="{ active: route.name === 'reportes-personal' }"
              @click="closeMobileMenu"
            >
              Por personal
            </RouterLink>
          </div>
        </div>

        <RouterLink
          v-for="item in menuItems"
          :key="item.to"
          :to="item.to"
          :class="{ active: route.path === item.to }"
          @click="closeMobileMenu"
        >
          {{ item.label }}
        </RouterLink>

        <div v-if="showPosMenu" class="menu-group">
          <button
            type="button"
            class="menu-group-head"
            :class="{ 'menu-group-head-active': route.path.startsWith('/pos') || route.path.startsWith('/piezas') }"
            @click="togglePosMenu"
          >
            <span>Punto de venta</span>
            <span class="menu-chevron" :class="{ 'menu-chevron-open': posMenuOpen }" aria-hidden="true"></span>
          </button>
          <div v-show="posMenuOpen" class="menu-children">
            <RouterLink
              v-if="auth.user.value?.role === 'waiter'"
              to="/pos/mesero"
              :class="{ active: route.path === '/pos/mesero' }"
              @click="closeMobileMenu"
            >
              Vista mesero
            </RouterLink>
            <RouterLink
              v-if="['cashier', 'admin', 'super_admin'].includes(auth.user.value?.role)"
              to="/pos/cajero"
              :class="{ active: route.path === '/pos/cajero' }"
              @click="closeMobileMenu"
            >
              Vista cajero
            </RouterLink>
            <RouterLink
              v-if="['cashier', 'admin', 'super_admin'].includes(auth.user.value?.role)"
              to="/piezas/control"
              :class="{ active: route.path === '/piezas/control' }"
              @click="closeMobileMenu"
            >
              Piezas por tiempo
            </RouterLink>
          </div>
        </div>

        <RouterLink
          v-if="showCajaMenu"
          to="/caja"
          class="menu-caja-link"
          :class="{ active: route.path === '/caja' }"
          @click="closeMobileMenu"
        >
          {{ cajaMenuLabel }}
        </RouterLink>

        <div v-if="showAdminMenu" class="menu-group">
          <button type="button" class="menu-group-head" :class="{ 'menu-group-head-active': route.path.startsWith('/administracion') }" @click="toggleAdminMenu">
            <span>Administración</span>
            <span class="menu-chevron" :class="{ 'menu-chevron-open': adminMenuOpen }" aria-hidden="true"></span>
          </button>
          <div v-show="adminMenuOpen" class="menu-children">
            <RouterLink
              to="/administracion/mi-sucursal"
              :class="{ active: route.path === '/administracion/mi-sucursal' }"
              @click="closeMobileMenu"
            >
              Mi sucursal
            </RouterLink>
            <RouterLink
              to="/administracion/personal"
              :class="{ active: route.path === '/administracion/personal' }"
              @click="closeMobileMenu"
            >
              Personal
            </RouterLink>
            <RouterLink
              v-if="auth.canManageUsers.value"
              to="/administracion/usuarios"
              :class="{ active: route.path === '/administracion/usuarios' }"
              @click="closeMobileMenu"
            >
              Usuarios
            </RouterLink>
            <div class="menu-nested">
              <button
                type="button"
                class="menu-nested-head"
                :class="{ 'menu-nested-head-active': route.path.startsWith('/administracion/detalles') }"
                @click="toggleDetallesMenu"
              >
                <span>Detalles</span>
                <span class="menu-chevron menu-chevron-sm" :class="{ 'menu-chevron-open': adminDetallesOpen }" aria-hidden="true"></span>
              </button>
              <div v-show="adminDetallesOpen" class="menu-children-nested">
                <RouterLink
                  to="/administracion/detalles/horarios"
                  :class="{ active: route.path === '/administracion/detalles/horarios' }"
                  @click="closeMobileMenu"
                >
                  Horarios
                </RouterLink>
                <RouterLink
                  to="/administracion/detalles/categorias"
                  :class="{ active: route.path === '/administracion/detalles/categorias' }"
                  @click="closeMobileMenu"
                >
                  Categorias
                </RouterLink>
                <RouterLink
                  to="/administracion/detalles/salas"
                  :class="{ active: route.path === '/administracion/detalles/salas' }"
                  @click="closeMobileMenu"
                >
                  Salas
                </RouterLink>
                <RouterLink
                  to="/administracion/detalles/mesas"
                  :class="{ active: route.path === '/administracion/detalles/mesas' }"
                  @click="closeMobileMenu"
                >
                  Mesas
                </RouterLink>
              </div>
            </div>
          </div>
        </div>

        <div v-if="showMaintenanceMenu" class="menu-group">
          <button
            type="button"
            class="menu-group-head"
            :class="{ 'menu-group-head-active': route.path.startsWith('/mantenimiento') }"
            @click="toggleMaintenanceMenu"
          >
            <span>Mantenimiento</span>
            <span class="menu-chevron" :class="{ 'menu-chevron-open': maintenanceMenuOpen }" aria-hidden="true"></span>
          </button>
          <div v-show="maintenanceMenuOpen" class="menu-children">
            <RouterLink
              to="/mantenimiento/productos"
              :class="{ active: route.path === '/mantenimiento/productos' }"
              @click="closeMobileMenu"
            >
              Productos
            </RouterLink>
            <RouterLink
              to="/mantenimiento/compras"
              :class="{ active: route.path === '/mantenimiento/compras' }"
              @click="closeMobileMenu"
            >
              Compras
            </RouterLink>
            <RouterLink
              to="/mantenimiento/traspasos"
              :class="{ active: route.path === '/mantenimiento/traspasos' }"
              @click="closeMobileMenu"
            >
              Traspasos
            </RouterLink>
          </div>
        </div>
      </nav>

      <div class="plan-card">
        <p class="plan-title">Estado SaaS</p>
        <p class="plan-state">Plan Empresarial Activo</p>
        <small>API: {{ API_BASE }}</small>
      </div>
    </aside>

    <main class="main-content">
      <ToastStack />
      <header class="topbar">
        <div class="topbar-lead">
          <h2>NightPOS Operativo</h2>
          <p>Control en tiempo real de caja, personal e inventario.</p>
          <p v-if="branchContextLabel" class="branch-pill" role="status">Sucursal: {{ branchContextLabel }}</p>
        </div>
        <div class="top-actions">
          <select
            v-if="['admin', 'manager', 'cashier', 'waiter', 'super_admin', 'owner'].includes(auth.user.value?.role) && auth.accessibleSites.value?.length > 1"
            class="site-switcher"
            :value="String(auth.resolvedActiveSiteId.value || '')"
            :disabled="switchingSite"
            @change="onChangeActiveSite"
          >
            <option v-for="s in auth.accessibleSites.value" :key="s.id" :value="s.id">
              {{ s.code }} - {{ s.name }}
            </option>
          </select>
          <button class="ghost-btn" @click="themeStore.toggleTheme()">
            {{ themeStore.theme.value === 'dark' ? 'Modo día' : 'Modo noche' }}
          </button>
          <button type="button" class="user-pill user-pill-btn" @click="openProfileModal">
            <strong>{{ auth.user.value?.name }}</strong>
            <small>{{ auth.user.value?.role }}</small>
          </button>
          <button class="ghost-btn" @click="openProfileModal">Mi perfil</button>
          <button class="ghost-btn" @click="onLogout">Salir</button>
        </div>
      </header>

      <slot />
    </main>
    </template>

    <div v-if="profileModalOpen" class="modal-overlay" @click.self="closeProfileModal">
      <div class="modal-card profile-modal-card" :class="{ 'profile-modal-card--waiter': isWaiterShell }">
        <div class="modal-head">
          <h3>Mi perfil</h3>
          <button type="button" class="ghost-btn" @click="closeProfileModal">Cerrar</button>
        </div>
        <form class="form-grid profile-form-grid" @submit.prevent="saveMyProfile">
          <label>
            Nombre
            <input v-model="profileForm.name" type="text" required />
          </label>
          <label>
            Correo
            <input v-model="profileForm.email" type="email" required />
          </label>
          <label>
            PIN (opcional)
            <input v-model="profileForm.pin" type="text" inputmode="numeric" maxlength="8" />
          </label>
          <label>
            Nueva contrasena (opcional)
            <input v-model="profileForm.password" type="password" minlength="8" />
          </label>
          <label>
            Confirmar contrasena
            <input v-model="profileForm.password_confirmation" type="password" minlength="8" />
          </label>
          <div class="profile-form-actions">
            <button type="button" class="ghost-btn" :disabled="savingProfile" @click="closeProfileModal">Cancelar</button>
            <button type="submit" class="primary-btn" :disabled="savingProfile">
              {{ savingProfile ? 'Guardando...' : 'Guardar cambios' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.topbar-lead {
  display: grid;
  gap: 6px;
  min-width: 0;
}

.branch-pill {
  margin: 0;
  display: inline-flex;
  align-items: center;
  width: fit-content;
  max-width: 100%;
  font-size: 0.82rem;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(92, 129, 255, 0.22);
  border: 1px solid rgba(113, 215, 255, 0.35);
  color: #e8f0ff;
}

:root[data-theme='light'] .branch-pill {
  color: #1a2744;
  background: rgba(92, 129, 255, 0.18);
  border-color: rgba(80, 110, 200, 0.35);
}

/* —— Garzón: barra superior + tabs inferiores —— */
.waiter-topbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 40;
  padding: calc(6px + env(safe-area-inset-top, 0px)) 10px 8px;
  background: rgba(11, 18, 42, 0.92);
  border-bottom: 1px solid rgba(142, 168, 245, 0.22);
  backdrop-filter: blur(10px);
}

:root[data-theme='light'] .waiter-topbar {
  background: rgba(246, 248, 255, 0.94);
  border-bottom-color: rgba(120, 140, 200, 0.25);
}

.waiter-topbar-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.waiter-brand {
  font-weight: 800;
  font-size: 0.92rem;
  letter-spacing: 0.02em;
}

.waiter-topbar-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  justify-content: flex-end;
}

.waiter-chip-btn {
  padding: 6px 8px;
  border-radius: 10px;
  border: 1px solid rgba(145, 175, 255, 0.35);
  background: rgba(25, 40, 85, 0.45);
  color: inherit;
  font: inherit;
  font-size: 0.72rem;
  font-weight: 700;
  cursor: pointer;
}

:root[data-theme='light'] .waiter-chip-btn {
  background: rgba(230, 238, 255, 0.95);
}

.waiter-chip-btn--danger {
  border-color: rgba(255, 150, 150, 0.45);
  color: #ffb4b4;
}

.waiter-branch-line {
  margin: 6px 0 0;
  font-size: 0.72rem;
  font-weight: 600;
  opacity: 0.85;
}

.waiter-site-select {
  margin-top: 6px;
  width: 100%;
  padding: 8px 10px;
  border-radius: 10px;
  font: inherit;
  font-size: 0.82rem;
}

.waiter-main {
  padding-top: calc(56px + env(safe-area-inset-top, 0px)) !important;
  padding-left: 12px !important;
  padding-right: 12px !important;
  padding-bottom: calc(72px + env(safe-area-inset-bottom, 0px)) !important;
  max-width: 100% !important;
}

.waiter-main :deep(.topbar) {
  display: none;
}

.waiter-tabbar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 40;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
  padding: 8px 10px calc(10px + env(safe-area-inset-bottom, 0px));
  background: rgba(11, 18, 42, 0.94);
  border-top: 1px solid rgba(142, 168, 245, 0.25);
  backdrop-filter: blur(10px);
}

:root[data-theme='light'] .waiter-tabbar {
  background: rgba(246, 248, 255, 0.96);
  border-top-color: rgba(120, 140, 200, 0.28);
}

.waiter-tab {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 14px 12px;
  border-radius: 14px;
  font-weight: 800;
  font-size: 0.95rem;
  text-decoration: none;
  color: var(--color-muted, #97ace4);
  border: 2px solid transparent;
}

.waiter-tab--active {
  color: #fff;
  background: rgba(92, 129, 255, 0.45);
  border-color: rgba(113, 215, 255, 0.4);
}

:root[data-theme='light'] .waiter-tab--active {
  color: #1a2744;
  background: rgba(92, 129, 255, 0.22);
}
</style>

<style>
/* Sin scope: paneles hijos del POS mesero / carta */
.app-shell--waiter .main-content .panel {
  padding: 14px 14px !important;
  border-radius: 16px !important;
}

.app-shell--waiter .main-content .panel-head h3,
.app-shell--waiter .main-content .panel-head h4 {
  font-size: 1.05rem !important;
}

.app-shell--waiter .main-content .primary-btn {
  min-height: 48px;
  font-size: 1rem;
  border-radius: 14px;
}

.app-shell--waiter .main-content .ghost-btn {
  min-height: 44px;
  padding: 10px 14px;
  border-radius: 12px;
}

.app-shell--waiter .main-content table.data-table {
  font-size: 0.88rem;
}

.app-shell--waiter .main-content .maint-tab-intro {
  font-size: 0.86rem;
}

.app-shell--waiter .profile-modal-card--waiter {
  width: min(100%, 26rem) !important;
  max-height: 85vh;
  margin: 12px;
}

.app-shell--waiter .profile-form-grid {
  grid-template-columns: 1fr !important;
}
</style>
