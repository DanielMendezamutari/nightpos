<script setup>

import { reactive, ref } from 'vue'

import { useRouter } from 'vue-router'

import { useAuthStore } from '../stores/authStore'

import { useNotificationStore } from '../stores/notificationStore'

import { useThemeStore } from '../stores/themeStore'



const router = useRouter()

const auth = useAuthStore()

const notify = useNotificationStore()

const themeStore = useThemeStore()

const loading = ref(false)

const errorMsg = ref('')

const showPassword = ref(false)

const loginMode = ref('pin') // pin|password — PIN por defecto



const form = reactive({

  email: '',

  password: '',

  pin: '',

})



/** Quita readonly en el primer foco para evitar autofill del navegador al cargar. */

function unlockAutofillGuard(event) {

  event.target.removeAttribute('readonly')

}



async function submit() {

  loading.value = true

  errorMsg.value = ''

  try {

    if (loginMode.value === 'pin') {

      await auth.loginWithPin(form.pin)

    } else {

      await auth.login(form.email, form.password)

    }

    notify.success('Sesion iniciada correctamente.')

    if (auth.user.value?.role === 'cashier' && auth.requiresOpenShift.value) {

      router.push({ name: 'caja-workspace', query: { tab: 'apertura' } })

    } else if (auth.user.value?.role === 'waiter') {

      router.push('/pos/mesero')

    } else if (['cashier', 'admin', 'super_admin'].includes(auth.user.value?.role)) {

      router.push('/pos/cajero')

    } else {

      router.push('/dashboard')

    }

  } catch (error) {

    errorMsg.value = error instanceof Error ? error.message : 'No se pudo iniciar sesion.'

    notify.error(errorMsg.value)

  } finally {

    loading.value = false

  }

}

</script>



<template>

  <section class="login-shell">

    <article class="login-card">

      <button class="theme-toggle-btn" type="button" @click="themeStore.toggleTheme()">

        {{ themeStore.theme.value === 'dark' ? 'Modo día' : 'Modo noche' }}

      </button>

      <div class="brand">

        <div class="brand-dot"></div>

        <div>

          <h1>NightPOS</h1>

          <p>Acceso seguro al sistema</p>

        </div>

      </div>

      <h2>Iniciar sesion</h2>

      <p class="login-subtitle">Usa tu usuario segun tu rol del sistema.</p>

      <div class="login-mode-tabs">

        <button type="button" :class="{ active: loginMode === 'pin' }" @click="loginMode = 'pin'">PIN</button>

        <button type="button" :class="{ active: loginMode === 'password' }" @click="loginMode = 'password'">Correo</button>

      </div>

      <form class="login-form" autocomplete="off" @submit.prevent="submit">

        <label v-if="loginMode === 'pin'">

          PIN numerico

          <input

            v-model="form.pin"

            type="password"

            inputmode="numeric"

            maxlength="8"

            placeholder="Ingresa tu PIN"

            autocomplete="off"

            readonly

            @focus="unlockAutofillGuard"

            required

          />

        </label>

        <template v-else>

          <label>

            Email

            <input

              v-model="form.email"

              type="email"

              name="nightpos-login-email"

              placeholder="tu@email.com"

              autocomplete="off"

              autocapitalize="off"

              spellcheck="false"

              readonly

              @focus="unlockAutofillGuard"

              required

            />

          </label>

          <label>

            Contraseña

            <div class="password-wrap">

              <input

                v-model="form.password"

                :type="showPassword ? 'text' : 'password'"

                name="nightpos-login-password"

                placeholder="Ingresa tu contraseña"

                autocomplete="off"

                readonly

                @focus="unlockAutofillGuard"

                required

              />

              <button class="password-eye" type="button" @click="showPassword = !showPassword">

                {{ showPassword ? 'Ocultar' : 'Ver' }}

              </button>

            </div>

          </label>

        </template>

        <button class="primary-btn" :disabled="loading">{{ loading ? 'Ingresando...' : 'Entrar al sistema' }}</button>

      </form>

      <p v-if="errorMsg" class="error-text">{{ errorMsg }}</p>

    </article>

  </section>

</template>

