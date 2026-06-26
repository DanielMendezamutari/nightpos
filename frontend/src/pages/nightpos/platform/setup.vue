<script setup>

import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'

import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'

import { platformSetup } from '@/api/platform'

import { fetchPlatformPlans } from '@/api/plans'

import { PLATFORM_SECTION_TABS } from '@/composables/useStaffSectionTabs'

import { useNightPosNotify } from '@/composables/useNightPosNotify'

import { usePlatformContext } from '@/composables/usePlatformContext'

import { getApiErrorMessage } from '@/services/http'

import { useContextStore } from '@/stores/context'



definePage({ meta: { permission: 'platform.setup' } })



const router = useRouter()

const contextStore = useContextStore()

const { isSuperAdmin } = usePlatformContext()

const { notify } = useNightPosNotify()



const step = ref(1)

const saving = ref(false)

const refForm = ref()

const plans = ref([])

const loadingPlans = ref(false)



const tenant = ref({

  name: '',

  slug: '',

  status: 'active',

  plan_id: null,

})

const branch = ref({

  name: '',

  code: '',

  address: '',

  status: 'active',

})

const admin = ref({

  name: '',

  username: '',

  email: '',

  password: '',

  pin: '',

})



const result = ref(null)



const activePlanOptions = computed(() => plans.value

  .filter(p => p.is_active)

  .map(p => ({ title: `${p.name} (${p.code})`, value: p.id })))



const selectedPlanLabel = computed(() => {

  const plan = plans.value.find(p => p.id === tenant.value.plan_id)

  return plan ? `${plan.name} (${plan.code})` : '—'

})



const setupSummary = computed(() => {

  const data = result.value

  if (!data)

    return null



  return {

    tenantName: data.tenant?.name ?? '—',

    branchName: data.branch?.name ?? '—',

    branchCode: data.branch?.code ?? '—',

    adminUsername: data.admin?.username ?? '—',

    planLabel: data.tenant?.plan?.name

      ? `${data.tenant.plan.name} (${data.tenant.plan.code})`

      : (data.tenant?.plan_name ?? selectedPlanLabel.value),

    tenantSlug: data.tenant?.slug ?? null,

    branchCodeValue: data.branch?.code ?? null,

    roles: data.roles ?? [],

    bootstrap: data.bootstrap ?? [],

  }

})



const postWizardChecklist = computed(() => {

  const summary = setupSummary.value

  if (!summary)

    return []



  const bootstrap = summary.bootstrap ?? []



  return [

    { label: 'Empresa creada', done: true },

    { label: 'Sucursal creada', done: true },

    { label: 'Admin creado', done: true },

    { label: 'Permisos iniciales listos', done: summary.roles.includes('tenant_owner') },

    { label: 'Métodos de pago creados', done: bootstrap.includes('payment_methods') },

    { label: 'Configuración de caja creada', done: bootstrap.includes('cash_reasons') || bootstrap.includes('cash_register') },

    { label: 'Impresoras disponibles para configurar', done: summary.roles.includes('tenant_owner') },

  ]

})



watch(() => tenant.value.name, name => {

  if (!tenant.value.slug && name) {

    tenant.value.slug = name.trim().toLowerCase()

      .replace(/\s+/g, '-')

      .replace(/[^a-z0-9-]/g, '')

  }

})



const resetWizard = () => {

  step.value = 1

  result.value = null

  saving.value = false

}



const nextStep = async () => {

  const { valid } = await refForm.value?.validate() ?? { valid: false }

  if (valid)

    step.value += 1

}



const finish = async () => {

  const { valid } = await refForm.value?.validate() ?? { valid: false }

  if (!valid)

    return



  saving.value = true

  try {

    const data = await platformSetup({

      tenant: {

        name: tenant.value.name.trim(),

        slug: tenant.value.slug.trim(),

        status: tenant.value.status,

        plan_id: tenant.value.plan_id || null,

      },

      branch: {

        name: branch.value.name.trim(),

        code: branch.value.code.trim().toUpperCase(),

        address: branch.value.address?.trim() || null,

        status: branch.value.status,

      },

      admin: {

        name: admin.value.name.trim(),

        username: admin.value.username.trim(),

        email: admin.value.email?.trim() || null,

        password: admin.value.password,

        pin: admin.value.pin || null,

      },

    })

    result.value = data

    step.value = 4

    notify('Empresa operativa creada')

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    saving.value = false

  }

}



const operate = async () => {

  if (!setupSummary.value?.tenantSlug)

    return



  await contextStore.applyContext({

    tenantSlug: setupSummary.value.tenantSlug,

    branchCode: setupSummary.value.branchCodeValue,

  })

  await router.push({ name: 'nightpos-dashboard' })

}



onBeforeRouteLeave(() => {

  resetWizard()

})



onMounted(async () => {

  if (!isSuperAdmin.value) {

    router.replace({ name: 'nightpos-platform-dashboard' })

    return

  }



  loadingPlans.value = true

  try {

    plans.value = await fetchPlatformPlans()

    const freePlan = plans.value.find(p => p.is_active && p.code === 'FREE')

    if (freePlan)

      tenant.value.plan_id = freePlan.id

  }

  catch {

    plans.value = []

  }

  finally {

    loadingPlans.value = false

  }

})

</script>



<template>

  <div>

    <NightPosPageHeader

      title="Alta rápida SaaS"

      subtitle="Empresa + primera sucursal + administrador en un solo flujo."

      :breadcrumbs="[

        { title: 'Plataforma', disabled: true },

        { title: 'Setup', disabled: true },

      ]"

    />

    <NightPosSectionTabs :tabs="PLATFORM_SECTION_TABS" />



    <VCard>

      <VCardText>

        <VStepper

          v-model="step"

          alt-labels

          class="mb-6"

        >

          <VStepperHeader>

            <VStepperItem

              :value="1"

              title="Empresa"

            />

            <VStepperItem

              :value="2"

              title="Sucursal"

            />

            <VStepperItem

              :value="3"

              title="Administrador"

            />

            <VStepperItem

              :value="4"

              title="Confirmación"

            />

          </VStepperHeader>

        </VStepper>



        <div v-if="step === 4 && setupSummary">

          <VAlert

            type="success"

            variant="tonal"

            class="mb-4"

          >

            <strong>{{ setupSummary.tenantName }}</strong> lista para operar.

            Sucursal <strong>{{ setupSummary.branchName }}</strong> ({{ setupSummary.branchCode }}).

            Admin: <strong>{{ setupSummary.adminUsername }}</strong>.

            Plan: <strong>{{ setupSummary.planLabel }}</strong>.

          </VAlert>

          <VCard
            variant="outlined"
            class="mb-4"
          >
            <VCardTitle>Checklist post-alta</VCardTitle>
            <VCardText>
              <VList density="compact">
                <VListItem
                  v-for="item in postWizardChecklist"
                  :key="item.label"
                  :prepend-icon="item.done ? 'ri-checkbox-circle-fill' : 'ri-checkbox-blank-circle-line'"
                  :class="item.done ? 'text-success' : ''"
                >
                  <VListItemTitle>{{ item.label }}</VListItemTitle>
                </VListItem>
              </VList>

              <div class="d-flex flex-wrap gap-2 mt-4">
                <VBtn
                  variant="tonal"
                  prepend-icon="ri-printer-line"
                  :to="{ name: 'nightpos-settings-printers' }"
                >
                  Configurar impresoras
                </VBtn>
                <VBtn
                  variant="tonal"
                  prepend-icon="ri-user-add-line"
                  :to="{ name: 'nightpos-users-create' }"
                >
                  Crear usuarios
                </VBtn>
                <VBtn
                  variant="tonal"
                  prepend-icon="ri-shopping-bag-line"
                  :to="{ name: 'nightpos-products' }"
                >
                  Cargar productos
                </VBtn>
              </div>
            </VCardText>
          </VCard>

          <VBtn

            color="primary"

            size="large"

            prepend-icon="ri-dashboard-line"

            @click="operate"

          >

            Operar en esta empresa

          </VBtn>

        </div>



        <VForm

          v-else

          ref="refForm"

          @submit.prevent="step === 3 ? finish() : null"

        >

          <div v-if="step === 1">

            <VTextField

              v-model="tenant.name"

              label="Nombre empresa *"

              class="mb-3"

              :rules="[v => !!v?.trim() || 'Requerido']"

            />

            <VTextField

              v-model="tenant.slug"

              label="Slug *"

              hint="Único — login y contexto"

              persistent-hint

              class="mb-3"

              :rules="[v => !!v?.trim() || 'Requerido']"

            />

            <VSelect

              v-model="tenant.status"

              :items="[

                { title: 'Activa', value: 'active' },

                { title: 'Inactiva', value: 'inactive' },

              ]"

              label="Estado"

              class="mb-3"

            />

            <VSelect

              v-model="tenant.plan_id"

              :items="activePlanOptions"

              :loading="loadingPlans"

              label="Plan *"

              hint="Catálogo de planes activos"

              persistent-hint

              class="mb-3"

              :rules="[v => v != null || 'Selecciona un plan']"

            />

          </div>



          <div v-else-if="step === 2">

            <VTextField

              v-model="branch.name"

              label="Nombre sucursal *"

              class="mb-3"

              :rules="[v => !!v?.trim() || 'Requerido']"

            />

            <VTextField

              v-model="branch.code"

              label="Código sucursal *"

              class="mb-3"

              :rules="[v => !!v?.trim() || 'Requerido']"

            />

            <VTextField

              v-model="branch.address"

              label="Dirección (opcional)"

            />

          </div>



          <div v-else-if="step === 3">

            <VTextField

              v-model="admin.name"

              label="Nombre admin *"

              class="mb-3"

              :rules="[v => !!v?.trim() || 'Requerido']"

            />

            <VTextField

              v-model="admin.username"

              label="Usuario *"

              class="mb-3"

              :rules="[v => !!v?.trim() || 'Requerido']"

            />

            <VTextField

              v-model="admin.email"

              label="Email (opcional)"

              class="mb-3"

            />

            <VTextField

              v-model="admin.password"

              type="password"

              label="Contraseña *"

              class="mb-3"

              :rules="[v => !!v && v.length >= 6 || 'Mínimo 6 caracteres']"

            />

            <VTextField

              v-model="admin.pin"

              label="PIN (opcional)"

              maxlength="6"

            />

          </div>



          <div class="d-flex gap-2 mt-6">

            <VBtn

              v-if="step > 1"

              variant="tonal"

              @click="step -= 1"

            >

              Atrás

            </VBtn>

            <VSpacer />

            <VBtn

              v-if="step < 3"

              color="primary"

              @click="nextStep"

            >

              Siguiente

            </VBtn>

            <VBtn

              v-else

              color="primary"

              :loading="saving"

              @click="finish"

            >

              Crear empresa operativa

            </VBtn>

          </div>

        </VForm>

      </VCardText>

    </VCard>

  </div>

</template>

