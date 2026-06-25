<script setup>
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import QuickGirlCreateDialog from '@/components/nightpos/staff/QuickGirlCreateDialog.vue'
import QuickRoomCreateDialog from '@/components/nightpos/rooms/QuickRoomCreateDialog.vue'
import { fetchAvailableRooms, fetchCleaningRooms } from '@/api/rooms'
import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'
import { createRoomService, printRoomService } from '@/api/roomServices'
import { useNightPosPrint } from '@/composables/useNightPosPrint'
import { useServiceCashSession } from '@/composables/useServiceCashSession'
import { useFilteredServiceTabs } from '@/composables/useServiceSectionTabs'
import { appendGirlToSelectList, loadOperationalGirlsForSelect } from '@/composables/useOperationalGirls'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { formatMoney } from '@/composables/useOrderHelpers'
import { preventNumberWheelScroll } from '@/composables/usePreventNumberWheel'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'room_services.create' } })

const DEFAULT_GIRL_PERCENT = 60

function localDatetimeString(date = new Date()) {
  const pad = n => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

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
const availableRooms = ref([])
const cleaningRooms = ref([])
const saving = ref(false)
const refForm = ref()
const showQuickGirl = ref(false)
const showQuickRoom = ref(false)
const girlSearch = ref('')
const lastRegistered = ref(null)
const reprintLoading = ref(false)

const form = ref({
  girl_user_id: null,
  room_id: null,
  total_amount: null,
  girl_percent: DEFAULT_GIRL_PERCENT,
  cleaning_amount: null,
  payment_method: 'CASH',
  duration_minutes: 60,
  started_at: localDatetimeString(),
  notes: '',
})

const estimatedEndTime = computed(() => {
  const raw = form.value.started_at
  const dur = Number(form.value.duration_minutes ?? 0)

  if (!raw || !dur)
    return null

  const start = new Date(raw)

  if (Number.isNaN(start.getTime()))
    return null

  const end = new Date(start.getTime() + dur * 60 * 1000)
  const pad = n => String(n).padStart(2, '0')

  return `${pad(end.getHours())}:${pad(end.getMinutes())}`
})

const grossGirlAmount = computed(() => {
  const total = Number(form.value.total_amount ?? 0)
  const percent = Number(form.value.girl_percent ?? 0)

  if (!total || percent < 0)
    return 0

  return Math.round(total * percent / 100 * 100) / 100
})

const cleaningAmountValue = computed(() => {
  const v = Number(form.value.cleaning_amount ?? 0)

  return v < 0 ? 0 : Math.min(v, grossGirlAmount.value)
})

const netGirlAmount = computed(() => {
  return Math.max(0, Math.round((grossGirlAmount.value - cleaningAmountValue.value) * 100) / 100)
})

const calculatedHouseAmount = computed(() => {
  const total = Number(form.value.total_amount ?? 0)

  if (!total)
    return 0

  return Math.round((total - grossGirlAmount.value) * 100) / 100
})

const cleaningExceedsGirl = computed(() => {
  const v = Number(form.value.cleaning_amount ?? 0)

  return v > grossGirlAmount.value
})

const roomItems = computed(() => availableRooms.value.map(r => ({
  title: `${r.code} — ${r.name}`,
  value: r.id,
  raw: r,
})))

const reloadGirls = async () => {
  girls.value = await loadOperationalGirlsForSelect()
}

const reloadAvailableRooms = async () => {
  const [available, cleaning] = await Promise.all([
    fetchAvailableRooms().catch(() => ({ items: [] })),
    fetchCleaningRooms().catch(() => ({ items: [] })),
  ])

  availableRooms.value = available.items ?? []
  cleaningRooms.value = cleaning.items ?? []
}

const noRoomsReady = computed(() => !availableRooms.value.length)

const onGirlCreated = async girl => {
  girls.value = appendGirlToSelectList(girls.value, girl)

  if (girl?.id)
    form.value.girl_user_id = girl.id
}

const onRoomCreated = async room => {
  await reloadAvailableRooms()

  if (room?.id)
    form.value.room_id = room.id
}

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }

  if (!valid || !cashSessionOpen.value)
    return

  saving.value = true

  try {
    const result = await createRoomService({
      girl_user_id: form.value.girl_user_id,
      room_id: form.value.room_id,
      total_amount: Number(form.value.total_amount),
      girl_percent: Number(form.value.girl_percent),
      cleaning_amount: form.value.cleaning_amount !== null && form.value.cleaning_amount !== ''
        ? Number(form.value.cleaning_amount)
        : 0,
      payment_method: form.value.payment_method,
      duration_minutes: Number(form.value.duration_minutes),
      started_at: form.value.started_at || null,
      notes: form.value.notes || null,
    })

    lastRegistered.value = {
      ...(result?.room_service ?? {}),
      print_job: result?.print_job ?? null,
      print_warning: result?.print_warning ?? null,
    }

    if (result?.print_warning) {
      notify(result.print_warning, 'warning')
    }
    else if (result?.print_job) {
      notify('Pieza registrada y ticket enviado a limpieza.')
    }
    else {
      notify('Pieza registrada.')
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

  openPrintRoute({ name: 'nightpos-print-room-service-id', params: { id: lastRegistered.value.id } })
}

const reprintTicket = async () => {
  if (!lastRegistered.value?.id)
    return

  reprintLoading.value = true
  try {
    const result = await printRoomService(lastRegistered.value.id, { reprint: true })
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
  await router.push({ name: 'nightpos-services-room-services' })
}

onMounted(async () => {
  if (!can('room_services.create')) {
    await router.replace({ name: 'nightpos-services-room-services' })

    return
  }

  await Promise.all([reloadGirls(), reloadAvailableRooms()])
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Registrar pieza"
      subtitle="Defina total cobrado y porcentaje para la chica; el sistema calcula el reparto."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Servicios', to: { name: 'nightpos-services-room-services' } },
        { title: 'Piezas', to: { name: 'nightpos-services-room-services' } },
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
      v-if="noRoomsReady"
      type="warning"
      variant="tonal"
      class="mb-4"
    >
      <div class="mb-2">
        No hay habitaciones <strong>disponibles</strong> para asignar pieza.
      </div>
      <div
        v-if="availableRooms.length === 0 && cleaningRooms.length"
        class="text-body-2 mb-2"
      >
        Habitaciones en limpieza:
        <strong>{{ cleaningRooms.map(r => r.code).join(', ') }}</strong>
      </div>
      <div class="d-flex flex-wrap gap-2 mt-2">
        <VBtn
          v-if="can('room_services.cleaning_view')"
          size="small"
          variant="tonal"
          color="info"
          prepend-icon="ri-brush-line"
          :to="{ name: 'nightpos-rooms-cleaning' }"
        >
          Ir a limpieza
        </VBtn>
        <VBtn
          v-if="can('rooms.create')"
          size="small"
          variant="text"
          @click="showQuickRoom = true"
        >
          + Nueva habitación
        </VBtn>
      </div>
    </VAlert>

    <VAlert
      v-else-if="cleaningRooms.length"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-4"
    >
      Disponibles: <strong>{{ availableRooms.length }}</strong> · En limpieza:
      <strong>{{ cleaningRooms.map(r => r.code).join(', ') }}</strong>
      <VBtn
        v-if="can('room_services.cleaning_view')"
        class="ms-2"
        size="x-small"
        variant="text"
        :to="{ name: 'nightpos-rooms-cleaning' }"
      >
        Limpieza
      </VBtn>
    </VAlert>

    <VAlert
      v-if="lastRegistered"
      type="success"
      variant="tonal"
      class="mb-4"
      prominent
    >
      Pieza registrada{{ lastRegistered.print_job ? ' y ticket enviado a limpieza' : '' }}.
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
                v-model:search="girlSearch"
                :items="girls"
                label="Chica *"
                placeholder="Buscar chica..."
                :rules="[v => !!v || 'Requerido']"
                clearable
              >
                <template #append-item>
                  <VDivider class="my-2" />
                  <VListItem
                    v-if="can('staff.quick_create_girl')"
                    prepend-icon="ri-user-add-line"
                    title="+ Registrar nueva chica"
                    class="text-primary"
                    @click="showQuickGirl = true"
                  />
                </template>
              </VAutocomplete>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VAutocomplete
                v-model="form.room_id"
                :items="roomItems"
                label="Habitación *"
                placeholder="Buscar habitación disponible..."
                :rules="[v => !!v || 'Seleccione habitación disponible']"
                clearable
              >
                <template
                  v-if="can('rooms.create')"
                  #append-item
                >
                  <VDivider class="my-2" />
                  <VListItem
                    prepend-icon="ri-hotel-bed-line"
                    title="+ Nueva habitación"
                    class="text-primary"
                    @click="showQuickRoom = true"
                  />
                </template>
              </VAutocomplete>
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model.number="form.duration_minutes"
                type="number"
                label="Duración acordada (min) *"
                min="1"
                :rules="[v => v >= 1 || 'Mínimo 1 minuto']"
                @wheel="preventNumberWheelScroll"
              />
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model="form.started_at"
                type="datetime-local"
                label="Hora de inicio *"
                :hint="estimatedEndTime ? `Termina aprox: ${estimatedEndTime}` : ''"
                persistent-hint
                :rules="[v => !!v || 'Requerido']"
              />
            </VCol>
            <VCol cols="12">
              <p class="text-subtitle-2 mb-2">
                Montos del servicio (BOB)
              </p>
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model.number="form.total_amount"
                type="number"
                label="Monto total cobrado *"
                min="0.01"
                step="0.01"
                :rules="[v => v > 0 || 'Mayor a 0']"
                @wheel="preventNumberWheelScroll"
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
            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model.number="form.girl_percent"
                type="number"
                label="Porcentaje chica (%) *"
                min="0"
                max="100"
                step="0.01"
                :rules="[
                  v => v >= 0 || 'Mínimo 0',
                  v => v <= 100 || 'Máximo 100',
                ]"
                @wheel="preventNumberWheelScroll"
              />
            </VCol>
            <VCol
              cols="12"
              md="4"
            >
              <VTextField
                v-model.number="form.cleaning_amount"
                type="number"
                label="Monto limpieza (BOB)"
                hint="Se descuenta del bruto de la chica"
                persistent-hint
                min="0"
                step="0.01"
                :error="cleaningExceedsGirl"
                :error-messages="cleaningExceedsGirl ? 'No puede superar el bruto de la chica' : ''"
                @wheel="preventNumberWheelScroll"
              />
            </VCol>
            <VCol cols="12">
              <VCard
                variant="tonal"
                color="secondary"
              >
                <VCardText>
                  <div class="text-caption text-medium-emphasis mb-2">
                    Distribución automática
                  </div>
                  <VRow dense>
                    <VCol cols="6" sm="3">
                      <div class="text-caption text-medium-emphasis">
                        Total cobrado
                      </div>
                      <div class="font-weight-bold">
                        {{ formatMoney(Number(form.total_amount ?? 0)) }} BOB
                      </div>
                    </VCol>
                    <VCol cols="6" sm="3">
                      <div class="text-caption text-medium-emphasis">
                        Chica bruta
                      </div>
                      <div class="font-weight-bold">
                        {{ formatMoney(grossGirlAmount) }} BOB
                      </div>
                    </VCol>
                    <VCol cols="6" sm="3">
                      <div class="text-caption text-medium-emphasis">
                        Limpieza
                      </div>
                      <div class="font-weight-bold text-warning">
                        -{{ formatMoney(cleaningAmountValue) }} BOB
                      </div>
                    </VCol>
                    <VCol cols="6" sm="3">
                      <div class="text-caption text-medium-emphasis">
                        Chica neta
                      </div>
                      <div class="font-weight-bold text-success">
                        {{ formatMoney(netGirlAmount) }} BOB
                      </div>
                    </VCol>
                    <VCol cols="6" sm="3">
                      <div class="text-caption text-medium-emphasis">
                        Casa
                      </div>
                      <div class="font-weight-bold">
                        {{ formatMoney(calculatedHouseAmount) }} BOB
                      </div>
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>
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
            :cancel-to="{ name: 'nightpos-services-room-services' }"
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
    <QuickRoomCreateDialog
      v-model="showQuickRoom"
      @created="onRoomCreated"
    />
    <QuickOpenCashDialog
      v-model="showOpenCash"
      @opened="onCashOpened"
    />
  </div>
</template>