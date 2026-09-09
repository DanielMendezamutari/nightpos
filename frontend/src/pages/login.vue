<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

definePage({ meta: { layout: 'blank' } })

const router = useRouter()
const authStore = useAuthStore()

// State
const loginMode = ref('pin') // 'pin' | 'password'
const pinValue = ref('')
const username = ref('admin.demo')
const password = ref('AdminDemo123!')
const isPasswordVisible = ref(false)
const tenantSlug = ref(authStore.tenantSlug || 'casa-demo')
const branchCode = ref(authStore.branchCode || 'CENTRO')
const errorMessage = ref('')
const successMessage = ref('')

// Demo shortcuts — 100% RestoTech Restaurante
const demoShortcuts = [
  { name: 'Cajero', pin: '1234', role: 'Caja y Facturación', color: 'warning', icon: 'ri-bank-card-line' },
  { name: 'Mesero / Garzón', pin: '5678', role: 'Toma de Pedidos y Salón', color: 'primary', icon: 'ri-user-smile-line' },
  { name: 'Administrador', pin: '2468', role: 'Gerencia y Control', color: 'error', icon: 'ri-shield-user-line' },
]

const pinDisplay = computed(() => {
  return pinValue.value ? '● '.repeat(pinValue.value.length).trim() : 'Ingrese PIN de 4 dígitos'
})

function appendPin(digit) {
  if (pinValue.value.length < 6) {
    pinValue.value += String(digit)
    errorMessage.value = ''
    if (pinValue.value.length === 4) {
      handlePinSubmit()
    }
  }
}

function clearPin() {
  pinValue.value = ''
  errorMessage.value = ''
}

function backspacePin() {
  pinValue.value = pinValue.value.slice(0, -1)
  errorMessage.value = ''
}

function selectDemo(demo) {
  pinValue.value = demo.pin
  handlePinSubmit()
}

async function handlePinSubmit() {
  if (!pinValue.value || pinValue.value.length < 4) {
    errorMessage.value = 'El PIN debe contener al menos 4 dígitos.'
    return
  }

  errorMessage.value = ''
  successMessage.value = ''

  const result = await authStore.loginWithPin(pinValue.value, tenantSlug.value, branchCode.value)
  if (result.success) {
    successMessage.value = `¡Bienvenido(a), ${result.user.name}!`
    const userRole = (result.user.role || '').toLowerCase()
    setTimeout(() => {
      if (userRole === 'cashier' || userRole === 'cajero') {
        window.location.href = '/cashier/orders'
      } else if (userRole === 'waiter' || userRole === 'mesero' || userRole === 'garzon') {
        window.location.href = '/waiter/tables'
      } else {
        window.location.href = '/'
      }
    }, 400)
  } else {
    errorMessage.value = result.message || 'PIN incorrecto o no asignado en esta sucursal.'
    pinValue.value = ''
  }
}

async function handlePasswordSubmit() {
  if (!username.value || !password.value) {
    errorMessage.value = 'Ingrese usuario y contraseña.'
    return
  }

  errorMessage.value = ''
  successMessage.value = ''

  const result = await authStore.loginWithPassword(username.value, password.value, tenantSlug.value)
  if (result.success) {
    successMessage.value = `¡Bienvenido(a), ${result.user.name}!`
    setTimeout(() => {
      window.location.href = '/'
    }, 400)
  } else {
    errorMessage.value = result.message || 'Usuario o contraseña incorrectos.'
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

function handleKeydown(e) {
  if (loginMode.value === 'pin') {
    if (e.key >= '0' && e.key <= '9') {
      appendPin(e.key)
    } else if (e.key === 'Backspace') {
      backspacePin()
    } else if (e.key === 'Enter') {
      handlePinSubmit()
    } else if (e.key === 'Escape') {
      clearPin()
    }
  }
}
</script>

<template>
  <div class="pos-auth-container min-vh-100 d-flex align-center justify-center pa-4">
    <VCard
      class="pos-auth-card w-100 elevation-12"
      max-width="540"
      rounded="xl"
    >
      <!-- Header -->
      <VCardItem class="text-center pt-8 pb-4">
        <div class="d-flex align-center justify-center gap-2 mb-2">
          <VAvatar
            color="primary"
            variant="flat"
            size="48"
            rounded="lg"
          >
            <VIcon
              icon="ri-restaurant-2-fill"
              size="30"
              color="white"
            />
          </VAvatar>
          <div class="text-left">
            <h2 class="text-h4 font-weight-bold text-primary mb-0">
              RestoTech POS
            </h2>
            <span class="text-caption text-medium-emphasis">Punto de Venta para Restaurantes</span>
          </div>
        </div>

        <div class="d-flex align-center justify-center gap-2 mt-2">
          <VChip
            size="small"
            color="success"
            variant="tonal"
            prepend-icon="ri-shield-check-line"
          >
            Local-First POS
          </VChip>
          <VChip
            size="small"
            color="info"
            variant="tonal"
            prepend-icon="ri-store-2-line"
          >
            Sucursal: {{ branchCode }}
          </VChip>
        </div>
      </VCardItem>

      <VCardText class="px-6 pb-6">
        <!-- Selector de Modo -->
        <VBtnToggle
          v-model="loginMode"
          mandatory
          color="primary"
          variant="tonal"
          density="comfortable"
          class="w-100 mb-6 justify-center"
        >
          <VBtn
            value="pin"
            prepend-icon="ri-keypad-line"
            class="flex-grow-1"
          >
            PIN Táctil (Rápido)
          </VBtn>
          <VBtn
            value="password"
            prepend-icon="ri-lock-password-line"
            class="flex-grow-1"
          >
            Contraseña (Admin)
          </VBtn>
        </VBtnToggle>

        <!-- Alerts -->
        <VAlert
          v-if="errorMessage"
          type="error"
          variant="tonal"
          density="compact"
          class="mb-4"
          closable
          @click:close="errorMessage = ''"
        >
          {{ errorMessage }}
        </VAlert>

        <VAlert
          v-if="successMessage"
          type="success"
          variant="tonal"
          density="compact"
          class="mb-4"
        >
          {{ successMessage }}
        </VAlert>

        <!-- MODO 1: PIN TÁCTIL -->
        <div v-if="loginMode === 'pin'">
          <!-- Display PIN -->
          <div class="pin-display-box rounded-lg pa-4 mb-4 text-center">
            <div
              class="text-h5 font-weight-bold letter-spacing-2"
              :class="pinValue ? 'text-primary' : 'text-medium-emphasis'"
            >
              {{ pinDisplay }}
            </div>
            <div class="text-caption text-medium-emphasis mt-1">
              Ingrese su código personal de 4 dígitos
            </div>
          </div>

          <!-- Teclado Numérico -->
          <div class="pin-keypad mb-4">
            <div
              v-for="row in [[1,2,3],[4,5,6],[7,8,9]]"
              :key="row[0]"
              class="d-flex gap-3 mb-3"
            >
              <VBtn
                v-for="num in row"
                :key="num"
                size="x-large"
                variant="outlined"
                color="secondary"
                class="flex-grow-1 keypad-btn text-h5 font-weight-bold"
                :disabled="authStore.loading"
                @click="appendPin(num)"
              >
                {{ num }}
              </VBtn>
            </div>
            <div class="d-flex gap-3">
              <VBtn
                size="x-large"
                variant="text"
                color="error"
                class="flex-grow-1 keypad-btn"
                :disabled="!pinValue || authStore.loading"
                @click="clearPin"
              >
                <VIcon
                  icon="ri-close-circle-line"
                  size="24"
                />
              </VBtn>
              <VBtn
                size="x-large"
                variant="outlined"
                color="secondary"
                class="flex-grow-1 keypad-btn text-h5 font-weight-bold"
                :disabled="authStore.loading"
                @click="appendPin(0)"
              >
                0
              </VBtn>
              <VBtn
                size="x-large"
                variant="text"
                color="warning"
                class="flex-grow-1 keypad-btn"
                :disabled="!pinValue || authStore.loading"
                @click="backspacePin"
              >
                <VIcon
                  icon="ri-delete-back-2-line"
                  size="24"
                />
              </VBtn>
            </div>
          </div>

          <!-- Botón de Envío -->
          <VBtn
            block
            size="large"
            color="primary"
            class="mb-4"
            :loading="authStore.loading"
            :disabled="pinValue.length < 4"
            prepend-icon="ri-login-box-line"
            @click="handlePinSubmit"
          >
            Ingresar al POS
          </VBtn>

          <!-- Acceso Rápido Demo RestoTech -->
          <VDivider class="my-4" />
          <div class="text-caption text-medium-emphasis mb-2 text-center">
            Perfiles de Restaurante:
          </div>
          <div class="d-flex gap-2 flex-wrap justify-center">
            <VBtn
              v-for="demo in demoShortcuts"
              :key="demo.pin"
              size="small"
              :color="demo.color"
              variant="tonal"
              :prepend-icon="demo.icon"
              @click="selectDemo(demo)"
            >
              {{ demo.name }} ({{ demo.pin }})
            </VBtn>
          </div>
        </div>

        <!-- MODO 2: USUARIO Y CONTRASEÑA -->
        <div v-else>
          <VForm @submit.prevent="handlePasswordSubmit">
            <VTextField
              v-model="username"
              label="Usuario / Correo"
              prepend-inner-icon="ri-user-line"
              placeholder="admin.demo"
              class="mb-4"
              :disabled="authStore.loading"
            />

            <VTextField
              v-model="password"
              label="Contraseña"
              placeholder="••••••••••••"
              prepend-inner-icon="ri-lock-line"
              :type="isPasswordVisible ? 'text' : 'password'"
              :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
              class="mb-6"
              :disabled="authStore.loading"
              @click:append-inner="isPasswordVisible = !isPasswordVisible"
            />

            <VBtn
              block
              size="large"
              color="primary"
              type="submit"
              :loading="authStore.loading"
              prepend-icon="ri-login-box-line"
            >
              Iniciar Sesión
            </VBtn>
          </VForm>
        </div>
      </VCardText>
    </VCard>
  </div>
</template>

<style scoped>
.pos-auth-container {
  background: radial-gradient(circle at 50% 30%, rgba(var(--v-theme-primary), 0.08) 0%, rgba(0, 0, 0, 0.02) 100%);
}

.pin-display-box {
  background: rgba(var(--v-theme-surface), 0.6);
  border: 2px dashed rgba(var(--v-theme-primary), 0.3);
  min-height: 80px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.keypad-btn {
  height: 62px !important;
  border-radius: 12px;
  transition: all 0.15s ease-in-out;
}

.keypad-btn:active {
  transform: scale(0.95);
}

.letter-spacing-2 {
  letter-spacing: 0.35rem;
}
</style>
