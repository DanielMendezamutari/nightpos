<script setup>
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import QuickGirlCreateDialog from '@/components/nightpos/staff/QuickGirlCreateDialog.vue'
import QuickShowTypeCreateDialog from '@/components/nightpos/shows/QuickShowTypeCreateDialog.vue'
import { fetchShowTypes } from '@/api/showTypes'
import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'
import { createShow, printShow } from '@/api/shows'
import { useNightPosPrint } from '@/composables/useNightPosPrint'
import { useServiceCashSession } from '@/composables/useServiceCashSession'
import { useFilteredServiceTabs } from '@/composables/useServiceSectionTabs'
import { appendGirlToSelectList, loadOperationalGirlsForSelect } from '@/composables/useOperationalGirls'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'shows.create' } })

const serviceTabs = useFilteredServiceTabs()
const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const { openPrintRoute } = useNightPosPrint()
const router = useRouter()
const { cashSessionOpen, showOpenCash, loadingCash, onCashOpened } = useServiceCashSession()

const paymentMethods = [
  { title: 'Efectivo', value: 'CASH' },
  { title: 'QR', value: 'QR' },
  { title: 'Tarjeta', value: 'CARD' },
]

const girls = ref([])
const showTypes = ref([])
const saving = ref(false)
const refForm = ref()
const showQuickGirl = ref(false)
const showQuickShowType = ref(false)
const lastRegistered = ref(null)
const reprintLoading = ref(false)

const form = ref({
  girl_user_id: null,
  show_type: null,
  unit_price: null,
  payment_method: 'CASH',
  registered_at: '',
  notes: '',
})

const reloadGirls = async () => {
  girls.value = await loadOperationalGirlsForSelect()
}

const reloadShowTypes = async () => {
  const items = await fetchShowTypes().catch(() => [])

  showTypes.value = items.map(t => ({
    title: t.suggested_price ? `${t.name} (sug. ${t.suggested_price})` : t.name,
    value: t.name,
    suggested_price: t.suggested_price,
  }))
}

const onShowTypeChange = typeName => {
  const match = showTypes.value.find(t => t.value === typeName)
  if (match?.suggested_price != null)
    form.value.unit_price = Number(match.suggested_price)
}

const onShowTypeCreated = async showType => {
  await reloadShowTypes()
  if (showType?.name) {
    form.value.show_type = showType.name
    onShowTypeChange(showType.name)
  }
}

const onGirlCreated = async girl => {
  girls.value = appendGirlToSelectList(girls.value, girl)
  if (girl?.id)
    form.value.girl_user_id = girl.id
}

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid || !cashSessionOpen.value)
    return

  saving.value = true
  try {
    const result = await createShow({
      girl_user_id: form.value.girl_user_id,
      show_type: form.value.show_type,
      unit_price: Number(form.value.unit_price),
      payment_method: form.value.payment_method,
      registered_at: form.value.registered_at || null,
      notes: form.value.notes || null,
    })

    lastRegistered.value = {
      ...(result?.show ?? {}),
      print_job: result?.print_job ?? null,
      print_warning: result?.print_warning ?? null,
    }

    if (result?.print_warning) {
      notify(result.print_warning, 'warning')
    }
    else if (result?.print_job) {
      notify('Show registrado y ticket enviado a impresora.')
    }
    else {
      notify('Show registrado.')
    }
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

const openTicket = () => {
  if (!lastRegistered.value?.id)
    return

  openPrintRoute({ name: 'nightpos-print-show-id', params: { id: lastRegistered.value.id } })
}

const reprintTicket = async () => {
  if (!lastRegistered.value?.id)
    return

  reprintLoading.value = true
  try {
    const result = await printShow(lastRegistered.value.id, { reprint: true })
    if (result?.print_warning)
      notify(result.print_warning, 'warning')
    else
      notify('Ticket reenviado a impresora.')
  }
  catch (error) {
    notify(getApiErrorMessage(error) || 'No se pudo reimprimir. Puede abrir la vista imprimible.', 'error')
    openTicket()
  }
  finally {
    reprintLoading.value = false
  }
}

const goToList = async () => {
  await router.push({ name: 'nightpos-services-shows' })
}

onMounted(async () => {
  if (!can('shows.create')) {
    await router.replace({ name: 'nightpos-services-shows' })

    return
  }

  await Promise.all([reloadGirls(), reloadShowTypes()])
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Registrar show"
      subtitle="Show directo — sin garzón. Liquidación solo a la chica."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Servicios', to: { name: 'nightpos-services-shows' } },
        { title: 'Shows', to: { name: 'nightpos-services-shows' } },
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

    <VAlert
      v-if="lastRegistered"
      type="success"
      variant="tonal"
      class="mb-4"
      prominent
    >
      Show registrado{{ lastRegistered.print_job ? ' y ticket enviado a impresora' : '' }}.
      <div class="d-flex flex-wrap gap-2 mt-3">
        <VBtn
          size="small"
          variant="tonal"
          prepend-icon="ri-eye-line"
          @click="openTicket"
        >
          Ver ticket
        </VBtn>
        <VBtn
          size="small"
          variant="outlined"
          prepend-icon="ri-printer-line"
          :loading="reprintLoading"
          @click="reprintTicket"
        >
          Reimprimir ticket
        </VBtn>
        <VBtn
          size="small"
          variant="text"
          @click="goToList"
        >
          Ir al listado
        </VBtn>
      </div>
    </VAlert>

    <VCard v-if="!lastRegistered">
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
                v-model="form.show_type"
                :items="showTypes"
                label="Tipo de show *"
                :rules="[v => !!v || 'Requerido']"
                @update:model-value="onShowTypeChange"
              >
                <template
                  v-if="can('show_types.create')"
                  #append-item
                >
                  <VDivider class="my-2" />
                  <VListItem
                    prepend-icon="ri-mic-line"
                    title="+ Nuevo tipo de show"
                    class="text-primary"
                    @click="showQuickShowType = true"
                  />
                </template>
              </VSelect>
              <VBtn
                v-if="can('show_types.create')"
                variant="text"
                size="small"
                prepend-icon="ri-mic-line"
                class="mt-1 px-0"
                @click="showQuickShowType = true"
              >
                + Nuevo tipo de show
              </VBtn>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model.number="form.unit_price"
                type="number"
                label="Precio (BOB) *"
                min="0.01"
                step="0.01"
                :rules="[v => v > 0 || 'Mayor a 0']"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="form.registered_at"
                type="datetime-local"
                label="Hora (opcional)"
                hint="Vacío = ahora"
                persistent-hint
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
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
            :cancel-to="{ name: 'nightpos-services-shows' }"
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
    <QuickShowTypeCreateDialog
      v-model="showQuickShowType"
      @created="onShowTypeCreated"
    />
    <QuickOpenCashDialog
      v-model="showOpenCash"
      @opened="onCashOpened"
    />
  </div>
</template>
