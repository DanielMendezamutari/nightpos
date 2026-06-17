<script setup>

import SettlementsCashBanner from '@/components/nightpos/settlements/SettlementsCashBanner.vue'

import SettlementPayDialog from '@/components/nightpos/settlements/SettlementPayDialog.vue'

import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'

import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'

import { fetchSettlement } from '@/api/settlements'

import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

import { useSettlementPayment } from '@/composables/useSettlementPayment'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'settlements.access' } })

const route = useRoute('nightpos-settlements-id')
const router = useRouter()
const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()



const {

  cashSessionOpen,

  loadingCash,

  showOpenCash,

  paySettlement,

  refreshCashSession,

} = useSettlementPayment()



const loading = ref(true)

const paying = ref(false)

const showPayDialog = ref(false)

const settlement = ref(null)

const items = ref([])



const isWaiter = computed(() => settlement.value?.settlement_type === 'WAITER')

const canPayPending = computed(() => settlement.value?.status === 'PENDING' && can('settlements.pay'))

const settlementTypeLabel = computed(() => ({
  WAITER: 'Garzón',
  GIRL: 'Chica',
  CLEANING: 'Limpieza',
}[settlement.value?.settlement_type] ?? 'Liquidación'))

const requiresCashToPay = computed(() => canPayPending.value && !loadingCash.value && !cashSessionOpen.value)



const itemHeaders = computed(() => {

  if (isWaiter.value) {

    return [

      { title: 'Venta', key: 'sale_number' },

      { title: 'Comanda', key: 'order_number' },

      { title: 'Descripción', key: 'description' },

      { title: 'Base', key: 'base_amount' },

      { title: '%', key: 'percent' },

      { title: 'Comisión', key: 'amount' },

    ]

  }



  return [

    { title: 'Fuente', key: 'source_type' },

    { title: 'Descripción', key: 'description' },

    { title: 'Monto', key: 'amount' },

    { title: 'Hora', key: 'registered_at' },

  ]

})



const sourceLabel = type => ({

  WAITER_COMMISSION: 'Comisión garzón',

  GIRL_CONSUMPTION: 'Consumo con acompañante',

  GIRL_BRACELET: 'Manilla',

  GIRL_ROOM: 'Pieza',

  GIRL_SHOW: 'Show',

  CLEANING_BASE: 'Base limpieza',

  CLEANING_ROOM: 'Pieza limpiada',

}[type] || type)



const load = async () => {

  loading.value = true



  try {

    const data = await fetchSettlement(route.params.id)



    settlement.value = data.settlement

    items.value = data.items ?? []

    await refreshCashSession()

  }

  catch (error) {

    if (import.meta.env.DEV) {

      console.error('[settlements/:id]', error?.response?.status, error?.response?.data?.message ?? error)

    }

    notify(getApiErrorMessage(error), 'error')

    router.replace({ name: 'nightpos-settlements' })

  }

  finally {

    loading.value = false

  }

}



const openPayDialog = async () => {

  await refreshCashSession()



  if (!cashSessionOpen.value) {

    showOpenCash.value = true

    return

  }



  showPayDialog.value = true

}



const confirmPay = async ({ payment_method, notes }) => {

  paying.value = true



  try {

    const result = await paySettlement(route.params.id, { payment_method, notes })



    if (result.ok) {

      settlement.value = result.data.settlement

      showPayDialog.value = false

    }

  }

  finally {

    paying.value = false

  }

}



const onCashOpened = async () => {

  await refreshCashSession()

  await load()

}



onMounted(load)

</script>



<template>

  <div>

    <NightPosPageHeader

      :title="settlement?.staff_name || 'Detalle liquidación'"

      :subtitle="settlement ? `${settlement.cut_label || ''}${settlement.cut_label ? ' · ' : ''}${settlement.settlement_type} · ${settlement.total_amount} BOB` : ''"

      :breadcrumbs="[

        { title: 'NightPOS', disabled: true },

        { title: 'Finanzas', disabled: true },

        { title: 'Liquidaciones', to: { name: 'nightpos-settlements' } },

        { title: 'Detalle', disabled: true },

      ]"

    >

      <template #actions>

        <VBtn

          v-if="canPayPending"

          color="success"

          prepend-icon="ri-check-line"

          @click="openPayDialog"

        >

          Marcar pagado

        </VBtn>

      </template>

    </NightPosPageHeader>



    <SettlementsCashBanner

      emphasize-pay-requirement

      @cash-opened="onCashOpened"

    />



    <VAlert

      v-if="requiresCashToPay"

      type="warning"

      variant="tonal"

      class="mb-4"

      prominent

    >

      Debe abrir caja para pagar esta liquidación.

    </VAlert>



    <VProgressLinear

      v-if="loading"

      indeterminate

      class="mb-4"

    />



    <template v-else-if="settlement">

      <VRow class="mb-4">

        <VCol

          cols="12"

          md="3"

        >

          <VCard variant="tonal">

            <VCardText>

              <p class="text-caption mb-1">

                Estado

              </p>

              <VChip

                :color="settlement.status === 'PAID' ? 'success' : 'warning'"

                variant="tonal"

              >

                {{ settlement.status }}

              </VChip>

            </VCardText>

          </VCard>

        </VCol>

        <VCol

          cols="12"

          md="3"

        >

          <VCard variant="tonal">

            <VCardText>

              <p class="text-caption mb-1">

                Total

              </p>

              <p class="text-h5 mb-0">

                {{ settlement.total_amount }} BOB

              </p>

            </VCardText>

          </VCard>

        </VCol>

        <VCol

          cols="12"

          md="3"

        >

          <VCard variant="tonal">

            <VCardText>

              <p class="text-caption mb-1">

                Rol

              </p>

              <p class="text-body-1 mb-0">

                {{ settlement.staff_role }}

              </p>

            </VCardText>

          </VCard>

        </VCol>

        <VCol

          v-if="settlement.paid_at"

          cols="12"

          md="3"

        >

          <VCard variant="tonal">

            <VCardText>

              <p class="text-caption mb-1">

                Pagado

              </p>

              <p class="text-body-2 mb-0">

                {{ settlement.paid_by_name || '—' }}

              </p>

              <p class="text-caption mb-0">

                {{ settlement.paid_at }}

              </p>

            </VCardText>

          </VCard>

        </VCol>

      </VRow>



      <VCard>

        <VCardTitle>Líneas de liquidación</VCardTitle>

        <VDataTable

          :headers="itemHeaders"

          :items="items"

          :items-per-page="20"

          class="text-no-wrap"

        >

          <template

            v-if="!isWaiter"

            #item.source_type="{ item }"

          >

            <VChip

              size="small"

              variant="tonal"

            >

              {{ sourceLabel(item.source_type) }}

            </VChip>

          </template>

          <template #item.description="{ item }">

            <div>

              <div>{{ item.display_description || item.description }}</div>

              <div
                v-if="item.units"
                class="text-caption text-medium-emphasis"
              >
                {{ item.units }} manilla(s) · {{ item.unit_amount }} c/u · {{ item.allocation_total_amount || item.amount }} BOB
              </div>

              <div

                v-if="item.product_name"

                class="text-caption text-medium-emphasis"

              >

                {{ item.product_name }}

                <span v-if="item.sale_number"> · {{ item.sale_number }}</span>

              </div>

            </div>

          </template>

          <template #item.registered_at="{ item }">

            {{ item.registered_at || item.created_at || '—' }}

          </template>

          <template #item.sale_number="{ item }">

            {{ item.sale_number || '—' }}

          </template>

          <template #item.order_number="{ item }">

            {{ item.order_number || '—' }}

          </template>

        </VDataTable>

      </VCard>

    </template>



    <SettlementPayDialog
      v-model="showPayDialog"
      :settlement="settlement"
      title="Confirmar pago"
      :type-label="settlementTypeLabel"
      :loading="paying"
      @confirm="confirmPay"
    />

    <QuickOpenCashDialog

      v-model="showOpenCash"

      @opened="onCashOpened"

    />

  </div>

</template>


