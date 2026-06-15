<script setup>

import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'

import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'

import { PLATFORM_SECTION_TABS } from '@/composables/useStaffSectionTabs'

import {

  createPlatformPlan,

  deletePlatformPlan,

  duplicatePlatformPlan,

  fetchPlatformPlanLimits,

  fetchPlatformPlans,

  updatePlatformPlan,

  updatePlatformPlanLimits,

} from '@/api/plans'

import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useRouteDialogCleanup } from '@/composables/useRouteDialogCleanup'
import { getApiErrorMessage } from '@/services/http'



definePage({ meta: { permission: 'admin.tenants.list' } })



const { notify } = useNightPosNotify()



const plans = ref([])

const loading = ref(false)

const saving = ref(false)



const showEditDialog = ref(false)

const showLimitsDialog = ref(false)

const selectedPlan = ref(null)

const limits = ref([])

useRouteDialogCleanup(showEditDialog, showLimitsDialog)



const form = ref({

  name: '',

  code: '',

  description: '',

  monthly_price: 0,

  yearly_price: 0,

  is_active: true,

  display_order: 0,

})



const LIMIT_KEYS = ['branches', 'users', 'cashiers', 'waiters', 'products', 'rooms']



const headers = [

  { title: 'Nombre', key: 'name' },

  { title: 'Código', key: 'code' },

  { title: 'Precio mensual', key: 'monthly_price' },

  { title: 'Precio anual', key: 'yearly_price' },

  { title: 'Estado', key: 'is_active' },

  { title: 'Tenants', key: 'tenants_count', width: 90 },

  { title: 'Acciones', key: 'actions', sortable: false, width: 280 },

]



const load = async () => {

  loading.value = true

  try {

    plans.value = await fetchPlatformPlans()

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    loading.value = false

  }

}



const resetForm = () => {

  form.value = {

    name: '',

    code: '',

    description: '',

    monthly_price: 0,

    yearly_price: 0,

    is_active: true,

    display_order: plans.value.length + 1,

  }

}



const openCreate = () => {

  selectedPlan.value = null

  resetForm()

  showEditDialog.value = true

}



const openEdit = plan => {

  selectedPlan.value = plan

  form.value = {

    name: plan.name,

    code: plan.code,

    description: plan.description || '',

    monthly_price: Number(plan.monthly_price),

    yearly_price: Number(plan.yearly_price),

    is_active: plan.is_active,

    display_order: plan.display_order,

  }

  showEditDialog.value = true

}



const savePlan = async () => {

  saving.value = true

  try {

    if (selectedPlan.value) {

      await updatePlatformPlan(selectedPlan.value.id, form.value)

      notify('Plan actualizado')

    }

    else {

      await createPlatformPlan(form.value)

      notify('Plan creado')

    }

    showEditDialog.value = false

    await load()

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    saving.value = false

  }

}



const openLimits = async plan => {

  selectedPlan.value = plan

  saving.value = true

  try {

    const data = await fetchPlatformPlanLimits(plan.id)

    const existing = Object.fromEntries((data.limits ?? []).map(l => [l.limit_key, l.limit_value]))

    limits.value = LIMIT_KEYS.map(key => ({

      limit_key: key,

      limit_value: existing[key] ?? 0,

    }))

    showLimitsDialog.value = true

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    saving.value = false

  }

}



const saveLimits = async () => {

  if (!selectedPlan.value)

    return



  saving.value = true

  try {

    await updatePlatformPlanLimits(selectedPlan.value.id, limits.value)

    notify('Límites actualizados')

    showLimitsDialog.value = false

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    saving.value = false

  }

}



const duplicate = async plan => {

  try {

    await duplicatePlatformPlan(plan.id)

    notify('Plan duplicado')

    await load()

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

}



const deactivate = async plan => {

  try {

    await deletePlatformPlan(plan.id)

    notify(plan.tenants_count > 0 ? 'Plan desactivado' : 'Plan eliminado')

    await load()

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

}



onMounted(load)

</script>



<template>

  <div>

    <NightPosPageHeader

      title="Planes SaaS"

      subtitle="Catálogo de planes, precios y límites operativos."

      :breadcrumbs="[

        { title: 'Plataforma', disabled: true },

        { title: 'Planes', disabled: true },

      ]"

    >

      <template #actions>

        <VBtn

          color="primary"

          @click="openCreate"

        >

          Nuevo plan

        </VBtn>

      </template>

    </NightPosPageHeader>

    <NightPosSectionTabs :tabs="PLATFORM_SECTION_TABS" />



    <VDataTable

      :headers="headers"

      :items="plans"

      :loading="loading"

      class="mt-4"

    >

      <template #item.monthly_price="{ item }">

        ${{ item.monthly_price }}

      </template>

      <template #item.yearly_price="{ item }">

        ${{ item.yearly_price }}

      </template>

      <template #item.is_active="{ item }">

        <VChip

          size="small"

          :color="item.is_active ? 'success' : 'secondary'"

        >

          {{ item.is_active ? 'Activo' : 'Inactivo' }}

        </VChip>

      </template>

      <template #item.actions="{ item }">

        <VBtn

          size="small"

          variant="text"

          @click="openEdit(item)"

        >

          Editar

        </VBtn>

        <VBtn

          size="small"

          variant="text"

          @click="openLimits(item)"

        >

          Límites

        </VBtn>

        <VBtn

          size="small"

          variant="text"

          @click="duplicate(item)"

        >

          Duplicar

        </VBtn>

        <VBtn

          size="small"

          variant="text"

          color="warning"

          @click="deactivate(item)"

        >

          {{ item.tenants_count > 0 ? 'Desactivar' : 'Eliminar' }}

        </VBtn>

      </template>

    </VDataTable>



    <VDialog
      v-if="showEditDialog"
      v-model="showEditDialog"
      max-width="560"
    >

      <VCard title="Plan">

        <VCardText>

          <VTextField

            v-model="form.name"

            label="Nombre"

            class="mb-2"

          />

          <VTextField

            v-model="form.code"

            label="Código"

            class="mb-2"

          />

          <VTextarea

            v-model="form.description"

            label="Descripción"

            rows="2"

            class="mb-2"

          />

          <VRow>

            <VCol cols="6">

              <VTextField

                v-model.number="form.monthly_price"

                label="Precio mensual"

                type="number"

                min="0"

              />

            </VCol>

            <VCol cols="6">

              <VTextField

                v-model.number="form.yearly_price"

                label="Precio anual"

                type="number"

                min="0"

              />

            </VCol>

          </VRow>

          <VSwitch

            v-if="selectedPlan"

            v-model="form.is_active"

            label="Activo"

            class="mt-2"

          />

          <VTextField

            v-model.number="form.display_order"

            label="Orden"

            type="number"

            class="mt-2"

          />

        </VCardText>

        <VCardActions>

          <VSpacer />

          <VBtn

            variant="text"

            @click="showEditDialog = false"

          >

            Cancelar

          </VBtn>

          <VBtn

            color="primary"

            :loading="saving"

            @click="savePlan"

          >

            Guardar

          </VBtn>

        </VCardActions>

      </VCard>

    </VDialog>



    <VDialog
      v-if="showLimitsDialog"
      v-model="showLimitsDialog"
      max-width="480"
    >

      <VCard :title="`Límites — ${selectedPlan?.name || ''}`">

        <VCardSubtitle class="px-4 pb-2">

          -1 = ilimitado

        </VCardSubtitle>

        <VCardText>

          <VTextField

            v-for="(row, idx) in limits"

            :key="row.limit_key"

            v-model.number="limits[idx].limit_value"

            :label="row.limit_key"

            type="number"

            class="mb-2"

          />

        </VCardText>

        <VCardActions>

          <VSpacer />

          <VBtn

            variant="text"

            @click="showLimitsDialog = false"

          >

            Cancelar

          </VBtn>

          <VBtn

            color="primary"

            :loading="saving"

            @click="saveLimits"

          >

            Guardar límites

          </VBtn>

        </VCardActions>

      </VCard>

    </VDialog>

  </div>

</template>

