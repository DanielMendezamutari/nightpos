<script setup>
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import QuickGirlCreateDialog from '@/components/nightpos/staff/QuickGirlCreateDialog.vue'
import QuickWaiterCreateDialog from '@/components/nightpos/staff/QuickWaiterCreateDialog.vue'
import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'
import { createBracelet } from '@/api/bracelets'
import { useServiceCashSession } from '@/composables/useServiceCashSession'
import { useFilteredServiceTabs } from '@/composables/useServiceSectionTabs'
import { appendGirlToSelectList, loadOperationalGirlsForSelect } from '@/composables/useOperationalGirls'
import { appendWaiterToSelectList, loadOperationalWaitersForSelect } from '@/composables/useOperationalWaiters'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'bracelets.create' } })

const serviceTabs = useFilteredServiceTabs()
const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const router = useRouter()
const { cashSessionOpen, showOpenCash, loadingCash, onCashOpened } = useServiceCashSession()

const paymentMethods = [
  { title: 'Efectivo', value: 'CASH' },
  { title: 'QR', value: 'QR' },
  { title: 'Tarjeta', value: 'CARD' },
]

const girls = ref([])
const waiters = ref([])
const saving = ref(false)
const refForm = ref()
const showQuickGirl = ref(false)
const showQuickWaiter = ref(false)

const form = ref({
  girl_user_id: null,
  waiter_user_id: null,
  quantity: 1,
  unit_price: null,
  payment_method: 'CASH',
  notes: '',
})

const totalPreview = computed(() => {
  const q = Number(form.value.quantity) || 0
  const p = Number(form.value.unit_price) || 0

  return (q * p).toFixed(2)
})

const reloadGirls = async () => {
  girls.value = await loadOperationalGirlsForSelect()
}

const onGirlCreated = async girl => {
  girls.value = appendGirlToSelectList(girls.value, girl)
  if (girl?.id)
    form.value.girl_user_id = girl.id
}

const reloadWaiters = async () => {
  waiters.value = await loadOperationalWaitersForSelect()
}

const onWaiterCreated = async waiter => {
  waiters.value = appendWaiterToSelectList(waiters.value, waiter)
  if (waiter?.id)
    form.value.waiter_user_id = waiter.id
}

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid || !cashSessionOpen.value)
    return

  saving.value = true
  try {
    await createBracelet({
      girl_user_id: form.value.girl_user_id,
      waiter_user_id: form.value.waiter_user_id || null,
      quantity: Number(form.value.quantity),
      unit_price: Number(form.value.unit_price),
      payment_method: form.value.payment_method,
      notes: form.value.notes || null,
    })
    notify('Manillas registradas')
    await router.push({ name: 'nightpos-services-bracelets' })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

onMounted(async () => {
  if (!can('bracelets.create')) {
    await router.replace({ name: 'nightpos-services-bracelets' })

    return
  }

  await Promise.all([reloadGirls(), reloadWaiters()])
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Registrar manillas"
      subtitle="Servicio operativo independiente del catálogo de productos."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Servicios', to: { name: 'nightpos-services-bracelets' } },
        { title: 'Manillas', to: { name: 'nightpos-services-bracelets' } },
        { title: 'Registrar', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="serviceTabs" />

    <VAlert
      v-if="!loadingCash && !cashSessionOpen"
      type="warning"
      variant="tonal"
      class="mb-4"
      prominent
    >
      Debe abrir caja para registrar este servicio.
      <VBtn
        v-if="can('cash.access')"
        class="ms-2"
        size="small"
        color="primary"
        @click="showOpenCash = true"
      >
        Abrir caja ahora
      </VBtn>
    </VAlert>

    <VCard>
      <VCardText>
        <VForm
          ref="refForm"
          @submit.prevent="save"
        >
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VAutocomplete
                v-model="form.girl_user_id"
                :items="girls"
                label="Chica *"
                placeholder="Buscar chica..."
                :rules="[v => !!v || 'Requerido']"
                clearable
              >
                <template
                  v-if="can('staff.quick_create_girl')"
                  #append-item
                >
                  <VDivider class="my-2" />
                  <VListItem
                    prepend-icon="ri-user-add-line"
                    title="+ Registrar nueva chica"
                    class="text-primary"
                    @click="showQuickGirl = true"
                  />
                </template>
              </VAutocomplete>
              <VBtn
                v-if="can('staff.quick_create_girl')"
                variant="text"
                size="small"
                prepend-icon="ri-user-add-line"
                class="mt-1 px-0"
                @click="showQuickGirl = true"
              >
                + Nueva chica
              </VBtn>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VSelect
                v-model="form.waiter_user_id"
                :items="waiters"
                label="Garzón (opcional)"
                clearable
              >
                <template
                  v-if="can('staff.quick_create_waiter')"
                  #append-item
                >
                  <VDivider class="my-2" />
                  <VListItem
                    prepend-icon="ri-user-star-line"
                    title="+ Nuevo garzón"
                    class="text-primary"
                    @click="showQuickWaiter = true"
                  />
                </template>
              </VSelect>
              <VBtn
                v-if="can('staff.quick_create_waiter')"
                variant="text"
                size="small"
                prepend-icon="ri-user-star-line"
                class="mt-1 px-0"
                @click="showQuickWaiter = true"
              >
                + Nuevo garzón
              </VBtn>
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model.number="form.quantity"
                type="number"
                label="Cantidad *"
                min="1"
                :rules="[v => v >= 1 || 'Mínimo 1']"
              />
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model.number="form.unit_price"
                type="number"
                label="Precio unitario (BOB) *"
                min="0.01"
                step="0.01"
                :rules="[v => v > 0 || 'Mayor a 0']"
              />
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                :model-value="totalPreview"
                label="Total"
                readonly
              />
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <VSelect
                v-model="form.payment_method"
                :items="paymentMethods"
                label="Método de pago *"
                :rules="[v => !!v || 'Requerido']"
              />
            </VCol>
            <VCol cols="12">
              <VTextarea
                v-model="form.notes"
                label="Notas"
                rows="2"
              />
            </VCol>
          </VRow>
          <NightPosFormActions
            :saving="saving"
            :cancel-to="{ name: 'nightpos-services-bracelets' }"
            save-label="Registrar"
            @save="save"
          />
        </VForm>
      </VCardText>
    </VCard>

    <QuickGirlCreateDialog
      v-model="showQuickGirl"
      @created="onGirlCreated"
    />
    <QuickWaiterCreateDialog
      v-model="showQuickWaiter"
      @created="onWaiterCreated"
    />
    <QuickOpenCashDialog
      v-model="showOpenCash"
      @opened="onCashOpened"
    />
  </div>
</template>
