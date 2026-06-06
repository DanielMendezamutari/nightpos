<script setup>
import WaiterBottomNav from '@/components/nightpos/waiter/WaiterBottomNav.vue'
import WaiterMobileHeader from '@/components/nightpos/waiter/WaiterMobileHeader.vue'
import { createOrder } from '@/api/orders'
import { fetchWaiterServiceAreas } from '@/api/waiter'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'
import { buildWaiterCreateOrderPayload } from '@/utils/waiterOrderPayload'

definePage({
  meta: {
    layout: 'blank',
    permission: 'orders.create',
  },
})

const router = useRouter()
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
}

const submit = async () => {
  const payload = buildWaiterCreateOrderPayload(form.value)

  if (!payload) {
    showHint.value = true
    notify('Escribe la mesa o ambiente para abrir la comanda.', 'warning')

    return
  }

  showHint.value = false
  saving.value = true
  try {
    const order = await createOrder(payload)
    notify('Comanda abierta')
    await router.replace({ name: 'nightpos-waiter-orders-id', params: { id: order.id }, query: { add: 1 } })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

onMounted(loadAreas)
</script>

<template>
  <div class="waiter-shell">
    <WaiterMobileHeader
      title="Nueva comanda"
      show-back
    />

    <VContainer class="py-4 px-4">
      <VAlert
        v-if="showHint"
        type="warning"
        variant="tonal"
        class="mb-4"
      >
        Escribe la mesa o ambiente para abrir la comanda.
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
          Toca un ambiente
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
        color="primary"
        class="waiter-primary-btn"
        :loading="saving"
        @click="submit"
      >
        Abrir comanda
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
