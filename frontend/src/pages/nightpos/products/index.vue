<script setup>
import QuickProductCreateDialog from '@/components/nightpos/catalog/QuickProductCreateDialog.vue'
import NightPosContextCards from '@/components/nightpos/layout/NightPosContextCards.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { CATALOG_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { fetchCategories } from '@/api/categories'
import { fetchProducts } from '@/api/products'
import { activePriceByMode, productHasActivePricing } from '@/composables/useProductSaleModeLabels'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { formatMoney } from '@/composables/useOrderHelpers'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'products.list' } })

const { canCreateProduct, canUpdateProduct, can } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const products = ref([])
const categories = ref([])
const loading = ref(false)
const showQuickProduct = ref(false)
const filterStatus = ref(null)
const filterCategory = ref(null)

const PRODUCT_TYPE_LABELS = {
  beverage: 'Bebida',
  service: 'Servicio',
  food: 'Comida',
}

const categoryMap = computed(() => {
  const map = {}
  categories.value.forEach(c => { map[c.id] = c.name })
  return map
})

const filteredProducts = computed(() => {
  let list = products.value
  if (filterStatus.value)
    list = list.filter(p => p.status === filterStatus.value)
  if (filterCategory.value)
    list = list.filter(p => p.category_id === filterCategory.value)
  return list
})

const widgetData = computed(() => {
  const list = products.value
  const active = list.filter(p => p.status === 'active').length
  const withPrices = list.filter(p => productHasActivePricing(p)).length
  const missing = list.filter(p => p.status === 'active' && !productHasActivePricing(p)).length

  return [
    { title: 'Productos', value: list.length, desc: 'En catálogo', icon: 'ri-goblet-line' },
    { title: 'Activos', value: active, desc: 'Estado activo', icon: 'ri-checkbox-circle-line' },
    { title: 'Sin precio', value: missing, desc: 'Activos sin precio', icon: 'ri-error-warning-line', color: missing ? 'warning' : 'success' },
    { title: 'Con precios', value: withPrices, desc: 'Listos para vender', icon: 'ri-price-tag-3-line' },
  ]
})

const headers = [
  { title: 'Producto', key: 'name' },
  { title: 'Categoría', key: 'category_id' },
  { title: 'Precio cliente', key: 'price_solo' },
  { title: 'Con acompañante', key: 'price_acomp' },
  { title: 'Chica / Casa', key: 'split' },
  { title: 'Estado', key: 'status' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const soloPrice = item => activePriceByMode(item.active_prices, 'SOLO_CLIENTE')
const companionPrice = item => activePriceByMode(item.active_prices, 'CON_ACOMPANANTE')

const loadProducts = async () => {
  loading.value = true
  try {
    const [prods, cats] = await Promise.all([
      fetchProducts({ include: 'active_prices' }),
      fetchCategories().catch(() => []),
    ])
    products.value = prods
    categories.value = cats
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const onQuickProductCreated = async () => {
  await loadProducts()
  notify('Producto agregado al catálogo')
}

onMounted(loadProducts)
</script>

<template>
  <div class="products-page">
    <NightPosPageHeader
      title="Productos"
      subtitle="Catálogo boliche — precios desde la API, sin cálculo en pantalla."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Productos', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          v-if="can('products.quick_create')"
          color="primary"
          size="large"
          class="me-2"
          @click="showQuickProduct = true"
        >
          <VIcon
            icon="ri-flashlight-line"
            start
          />
          Producto rápido
        </VBtn>
        <VBtn
          v-if="canCreateProduct"
          variant="tonal"
          size="large"
          :to="{ name: 'nightpos-products-create' }"
        >
          <VIcon
            icon="ri-add-line"
            start
          />
          Nuevo producto
        </VBtn>
      </template>
    </NightPosPageHeader>

    <NightPosSectionTabs :tabs="CATALOG_SECTION_TABS" />
    <NightPosContextCards />

    <VCard class="mb-6">
      <VCardText class="px-2">
        <VRow>
          <VCol
            v-for="data in widgetData"
            :key="data.title"
            cols="12"
            sm="6"
            md="3"
          >
            <div class="d-flex justify-space-between px-4 py-2">
              <div>
                <p class="text-body-2 mb-0">
                  {{ data.title }}
                </p>
                <h4 class="text-h4">
                  {{ data.value }}
                </h4>
                <p class="text-caption text-medium-emphasis mb-0">
                  {{ data.desc }}
                </p>
              </div>
              <VAvatar
                :color="data.color || 'primary'"
                variant="tonal"
                rounded="lg"
                size="44"
              >
                <VIcon :icon="data.icon" />
              </VAvatar>
            </div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VCard>
      <VCardText>
        <VRow>
          <VCol
            cols="12"
            sm="6"
          >
            <VSelect
              v-model="filterStatus"
              label="Estado"
              :items="[
                { title: 'Todos', value: null },
                { title: 'Activo', value: 'active' },
                { title: 'Inactivo', value: 'inactive' },
              ]"
              clearable
            />
          </VCol>
          <VCol
            cols="12"
            sm="6"
          >
            <VSelect
              v-model="filterCategory"
              label="Categoría"
              :items="[
                { title: 'Todas', value: null },
                ...categories.map(c => ({ title: c.name, value: c.id })),
              ]"
              clearable
            />
          </VCol>
        </VRow>
      </VCardText>
      <VDivider />
      <VDataTable
        :headers="headers"
        :items="filteredProducts"
        :loading="loading"
        item-value="id"
        class="text-no-wrap"
        :items-per-page="15"
      >
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-2">
            <RouterLink
              :to="{ name: 'nightpos-products-id', params: { id: item.id } }"
              class="font-weight-medium text-primary"
            >
              {{ item.name }}
            </RouterLink>
            <VChip
              v-if="item.status === 'active' && !productHasActivePricing(item)"
              color="warning"
              size="x-small"
              label
            >
              Sin precio
            </VChip>
          </div>
        </template>
        <template #item.category_id="{ item }">
          {{ categoryMap[item.category_id] || '—' }}
        </template>
        <template #item.price_solo="{ item }">
          <template v-if="soloPrice(item)">
            {{ formatMoney(soloPrice(item).price) }}
          </template>
          <span
            v-else
            class="text-warning"
          >—</span>
        </template>
        <template #item.price_acomp="{ item }">
          <template v-if="companionPrice(item)">
            {{ formatMoney(companionPrice(item).price) }}
          </template>
          <span v-else>—</span>
        </template>
        <template #item.split="{ item }">
          <template v-if="companionPrice(item)">
            <span class="text-caption">
              {{ formatMoney(companionPrice(item).girl_amount) }}
              /
              {{ formatMoney(companionPrice(item).house_amount) }}
            </span>
          </template>
          <span v-else>—</span>
        </template>
        <template #item.status="{ item }">
          <VChip
            :color="item.status === 'active' ? 'success' : 'secondary'"
            size="small"
            label
          >
            {{ item.status === 'active' ? 'Activo' : 'Inactivo' }}
          </VChip>
        </template>
        <template #item.actions="{ item }">
          <VBtn
            size="small"
            variant="text"
            :to="{ name: 'nightpos-products-id-prices', params: { id: item.id } }"
          >
            Precios
          </VBtn>
          <VBtn
            v-if="canUpdateProduct"
            size="small"
            variant="tonal"
            :to="{ name: 'nightpos-products-id-edit', params: { id: item.id } }"
          >
            Editar
          </VBtn>
        </template>
      </VDataTable>
    </VCard>

    <QuickProductCreateDialog
      v-model="showQuickProduct"
      @created="onQuickProductCreated"
    />
</div>
</template>
