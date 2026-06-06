<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchFirstNightChecklist } from '@/api/firstNightChecklist'
import { bootstrapOperationalData } from '@/api/settingsBootstrap'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { SETTINGS_SECTION_TABS } from '@/composables/useSettingsSectionTabs'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'settings.checklist' } })

const router = useRouter()
const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const loading = ref(false)
const bootstrapping = ref(false)
const checklist = ref({ complete: false, items: [] })

const load = async () => {
  loading.value = true
  try {
    checklist.value = await fetchFirstNightChecklist()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const goConfigure = routeName => {
  if (routeName)
    router.push({ name: routeName })
}

const runBootstrap = async () => {
  bootstrapping.value = true
  try {
    const result = await bootstrapOperationalData()
    notify(result.skipped ? 'Los datos ya existían' : 'Datos operativos cargados', 'success')
    await load()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    bootstrapping.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Checklist primera noche"
      subtitle="Verificación rápida antes de abrir operación."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Configuración', disabled: true },
        { title: 'Checklist', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="SETTINGS_SECTION_TABS" />
    <VAlert
      v-if="!checklist.complete && can('settings.bootstrap')"
      type="info"
      variant="tonal"
      class="mb-4"
    >
      ¿Sucursal nueva sin catálogo? Cargue productos, ambientes y maestros mínimos en un paso.
      <VBtn
        class="ms-2"
        size="small"
        color="primary"
        :loading="bootstrapping"
        @click="runBootstrap"
      >
        Cargar datos iniciales
      </VBtn>
    </VAlert>
    <VCard :loading="loading">
      <VCardText>
        <div class="d-flex align-center gap-3 mb-6">
          <VIcon
            :icon="checklist.complete ? 'ri-checkbox-circle-fill' : 'ri-error-warning-line'"
            :color="checklist.complete ? 'success' : 'warning'"
            size="32"
          />
          <div>
            <div class="text-h6">
              {{ checklist.complete ? 'Listo para operar' : 'Configuración incompleta' }}
            </div>
            <div class="text-body-2 text-medium-emphasis">
              Revise cada ítem y use «Ir a configurar» si falta algo.
            </div>
          </div>
          <VChip
            class="ms-auto"
            :color="checklist.complete ? 'success' : 'warning'"
          >
            {{ checklist.complete ? 'Completo' : 'Incompleto' }}
          </VChip>
        </div>
        <VList lines="two">
          <VListItem
            v-for="item in checklist.items"
            :key="item.key"
          >
            <template #prepend>
              <VIcon
                :icon="item.complete ? 'ri-check-line' : 'ri-close-circle-line'"
                :color="item.complete ? 'success' : 'error'"
              />
            </template>
            <VListItemTitle>{{ item.label }}</VListItemTitle>
            <VListItemSubtitle>
              <VChip
                size="x-small"
                :color="item.complete ? 'success' : 'warning'"
              >
                {{ item.complete ? 'OK' : 'Pendiente' }}
              </VChip>
            </VListItemSubtitle>
            <template #append>
              <VBtn
                v-if="!item.complete && item.configure_route"
                size="small"
                variant="tonal"
                @click="goConfigure(item.configure_route)"
              >
                Ir a configurar
              </VBtn>
            </template>
          </VListItem>
        </VList>
      </VCardText>
    </VCard>
</div>
</template>
