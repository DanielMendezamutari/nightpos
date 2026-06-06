<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchProduct } from '@/api/products'
import { fetchCategories } from '@/api/categories'
import { activePriceByMode, productHasActivePricing, saleModeLabel } from '@/composables/useProductSaleModeLabels'
import { formatMoney } from '@/composables/useOrderHelpers'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'products.list' } })

const route = useRoute('nightpos-products-id')
const { canUpdateProduct } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const product = ref(null)
const categoryName = ref('—')
const loading = ref(true)

const PRODUCT_TYPE_LABELS = {
  beverage: 'Bebida',
  service: 'Servicio',
  food: 'Comida',
}

const activePrices = computed(() => product.value?.active_prices ?? [])
const solo = computed(() => activePriceByMode(activePrices.value, 'SOLO_CLIENTE'))
const companion = computed(() => activePriceByMode(activePrices.value, 'CON_ACOMPANANTE'))

const load = async () => {
  loading.value = true
  try {
    const p = await fetchProduct(route.params.id)
    const cats = await fetchCategories().catch(() => [])
    product.value = p
    categoryName.value = cats.find(c => c.id === p.category_id)?.name || '—'
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      :title="product?.name || 'Producto'"
      subtitle="Ficha de catálogo y precios vigentes."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Productos', to: { name: 'nightpos-products' } },
        { title: product?.name || 'Detalle', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          v-if="canUpdateProduct && product"
          variant="tonal"
          :to="{ name: 'nightpos-products-id-edit', params: { id: route.params.id } }"
        >
          Editar datos
        </VBtn>
        <VBtn
          v-if="product"
          color="primary"
          :to="{ name: 'nightpos-products-id-prices', params: { id: route.params.id } }"
        >
          Configurar precios
        </VBtn>
      </template>
    </NightPosPageHeader>

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VRow v-else-if="product">
      <VCol
        cols="12"
        md="5"
      >
        <VCard title="Datos generales">
          <VCardText>
            <VList lines="two">
              <VListItem title="Tipo">
                <template #subtitle>
                  {{ PRODUCT_TYPE_LABELS[product.product_type] || product.product_type }}
                </template>
              </VListItem>
              <VListItem title="Categoría">
                <template #subtitle>
                  {{ categoryName }}
                </template>
              </VListItem>
              <VListItem title="Estado">
                <template #subtitle>
                  <VChip
                    size="small"
                    :color="product.status === 'active' ? 'success' : 'secondary'"
                    label
                  >
                    {{ product.status === 'active' ? 'Activo' : 'Inactivo' }}
                  </VChip>
                </template>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        cols="12"
        md="7"
      >
        <VCard title="Precios vigentes">
          <VCardText>
            <VAlert
              v-if="!productHasActivePricing(product)"
              type="warning"
              variant="tonal"
              class="mb-4"
            >
              Este producto no tiene precios configurados. No se puede vender en comandas hasta agregarlos.
            </VAlert>

            <VRow v-if="solo || companion">
              <VCol
                v-if="solo"
                cols="12"
                sm="6"
              >
                <VCard
                  variant="tonal"
                  color="primary"
                >
                  <VCardText>
                    <div class="text-caption mb-1">
                      {{ saleModeLabel('SOLO_CLIENTE') }}
                    </div>
                    <div class="text-h5 font-weight-bold">
                      {{ formatMoney(solo.price) }}
                    </div>
                  </VCardText>
                </VCard>
              </VCol>
              <VCol
                v-if="companion"
                cols="12"
                sm="6"
              >
                <VCard
                  variant="tonal"
                  color="secondary"
                >
                  <VCardText>
                    <div class="text-caption mb-1">
                      {{ saleModeLabel('CON_ACOMPANANTE') }}
                    </div>
                    <div class="text-h5 font-weight-bold">
                      {{ formatMoney(companion.price) }}
                    </div>
                    <div class="text-body-2 mt-2">
                      Chica {{ formatMoney(companion.girl_amount) }} · Casa {{ formatMoney(companion.house_amount) }}
                    </div>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
</div>
</template>
