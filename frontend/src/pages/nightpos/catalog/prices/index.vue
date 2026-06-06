<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { CATALOG_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { fetchProducts } from '@/api/products'
import { activePriceByMode, productHasActivePricing } from '@/composables/useProductSaleModeLabels'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { formatMoney } from '@/composables/useOrderHelpers'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'products.list' } })

const { notify } = useNightPosNotify()
const products = ref([])
const loading = ref(true)

const headers = [
  { title: 'Producto', key: 'name' },
  { title: 'Precio cliente', key: 'solo' },
  { title: 'Con acompañante', key: 'companion' },
  { title: 'Estado', key: 'pricing' },
  { title: 'Acción', key: 'actions', sortable: false },
]

onMounted(async () => {
  try {
    products.value = await fetchProducts({ include: 'active_prices' })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Precios del catálogo"
      subtitle="Vista rápida de precios vigentes por producto."
      :breadcrumbs="[
        { title: 'Catálogo', disabled: true },
        { title: 'Precios', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="CATALOG_SECTION_TABS" />
    <VCard>
      <VDataTable
        :headers="headers"
        :items="products"
        :loading="loading"
        class="text-no-wrap"
      >
        <template #item.solo="{ item }">
          <template v-if="activePriceByMode(item.active_prices, 'SOLO_CLIENTE')">
            {{ formatMoney(activePriceByMode(item.active_prices, 'SOLO_CLIENTE').price) }}
          </template>
          <span
            v-else
            class="text-warning"
          >—</span>
        </template>
        <template #item.companion="{ item }">
          <template v-if="activePriceByMode(item.active_prices, 'CON_ACOMPANANTE')">
            {{ formatMoney(activePriceByMode(item.active_prices, 'CON_ACOMPANANTE').price) }}
          </template>
          <span v-else>—</span>
        </template>
        <template #item.pricing="{ item }">
          <VChip
            size="small"
            :color="productHasActivePricing(item) ? 'success' : 'warning'"
            label
          >
            {{ productHasActivePricing(item) ? 'Listo' : 'Sin precio' }}
          </VChip>
        </template>
        <template #item.actions="{ item }">
          <VBtn
            size="small"
            variant="tonal"
            :to="{ name: 'nightpos-products-id-prices', params: { id: item.id } }"
          >
            Editar precios
          </VBtn>
        </template>
      </VDataTable>
    </VCard>
  </div>
</template>
