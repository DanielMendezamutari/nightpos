<script setup>
/**
 * Fallback admin/dev — comanda manual con table_label.
 * Garzones (staff_role WAITER) son redirigidos a Mis mesas vía router guard.
 */
import WaiterBottomNav from '@/components/nightpos/waiter/WaiterBottomNav.vue'
import WaiterMobileHeader from '@/components/nightpos/waiter/WaiterMobileHeader.vue'
import { createOrder } from '@/api/orders'
import { fetchWaiterServiceAreas } from '@/api/waiter'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'
import { buildWaiterCreateOrderPayload } from '@/utils/waiterOrderPayload'
import { isWaiterStaff } from '@/utils/resolveHomeRoute'
import { useAuthStore } from '@/stores/auth'

definePage({
  meta: {
    layout: 'blank',
    permission: 'orders.create',
  },
})

const router = useRouter()
const auth = useAuthStore()
const { notify } = useNightPosNotify()
const saving = ref(false)
const loadingAreas = ref(true)
const showHint = ref(false)
const serviceAreas = ref([])

const form = ref({
  table_label: '',
  service_area_id: null,
  notes: '',
})

const loadAreas = async () => {
  loadingAreas.value = true
  try {
    serviceAreas.value = await fetchWaiterServiceAreas({ active_only: true })
  }
  catch {
    serviceAreas.value = []
  }
  finally {
    loadingAreas.value = false
  }
}

const pickArea = area => {
  if (form.value.service_area_id === area.id) {
    form.value.service_area_id = null

    return
  }

  form.value.service_area_id = area.id
  form.value.table_label = area.name
}

const submit = async () => {
  const payload = buildWaiterCreateOrderPayload(form.value)

  if (!payload) {
    showHint.value = true
    notify('Indica la mesa o ambiente.', 'warning')

    return
  }

  showHint.value = false
  saving.value = true
  try {
    const order = await createOrder(payload)
    notify('Comanda abierta')
    await router.replace({ name: 'nightpos-waiter-orders-id', params: { id: order.id }, query: { add: '1' } })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

onMounted(async () => {
  if (isWaiterStaff(auth.user)) {
    await router.replace({ name: 'nightpos-waiter' })

    return
  }

  await loadAreas()
})
</script>

<template>
  <div class="waiter-shell">
    <WaiterMobileHeader
      title="Otra mesa"
      show-back
    />

    <VContainer class="py-4 px-4">
      <VAlert
        type="info"
        variant="tonal"
        class="mb-4"
        prominent
      >
        <strong>Uso excepcional.</strong>
        Lo normal es tocar tu mesa en «Mis mesas». Usa esto solo si la mesa no está en tu lista.
      </VAlert>

      <VAlert
        v-if="showHint"
        type="warning"
        variant="tonal"
        class="mb-4"
      >
        Escribe la mesa o elige un ambiente.
      </VAlert>

      <VProgressLinear
        v-if="loadingAreas"
        indeterminate
        class="mb-4"
      />

      <section
        v-if="serviceAreas.length"
        class="mb-5"
      >
        <div class="text-body-2 text-medium-emphasis mb-3">
          Atajo por ambiente
        </div>
        <div class="d-flex flex-wrap gap-2">
          <VBtn
            v-for="area in serviceAreas"
            :key="area.id"
            size="x-large"
            class="waiter-area-btn"
            :color="form.service_area_id === area.id ? 'primary' : 'tonal'"
            @click="pickArea(area)"
          >
            {{ area.name }}
          </VBtn>
        </div>
      </section>

      <VTextField
        v-model="form.table_label"
        label="Mesa o ambiente"
        placeholder="Ej: Mesa 5, VIP, Barra"
        variant="solo-filled"
        density="comfortable"
        class="waiter-field mb-5"
        hide-details
        @update:model-value="form.service_area_id = null; showHint = false"
      />

      <VBtn
        block
        size="x-large"
        color="secondary"
        variant="tonal"
        class="waiter-primary-btn"
        :loading="saving"
        @click="submit"
      >
        Abrir comanda excepcional
      </VBtn>
    </VContainer>

    <WaiterBottomNav />
  </div>
</template>

<style scoped lang="scss">
@use '@styles/waiter-mobile';

.waiter-area-btn {
  flex: 1 1 calc(50% - 8px);
  min-height: 52px;
}

.waiter-field :deep(.v-field) {
  font-size: 1.1rem;
  min-height: 56px;
}

.waiter-primary-btn {
  min-height: 56px;
  font-size: 1.05rem;
}
</style>
