<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { createServiceArea, fetchServiceAreas, updateServiceArea } from '@/api/serviceAreas'
import { SETTINGS_SECTION_TABS } from '@/composables/useSettingsSectionTabs'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'settings.service_areas' } })

const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const loading = ref(false)
const saving = ref(false)
const items = ref([])
const form = ref({ code: '', name: '', area_type: 'TABLE', status: 'active' })
const editId = ref(null)

const headers = [
  { title: 'Código', key: 'code' },
  { title: 'Nombre', key: 'name' },
  { title: 'Tipo', key: 'area_type' },
  { title: 'Estado', key: 'status' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const load = async () => {
  loading.value = true
  try {
    items.value = await fetchServiceAreas()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const resetForm = () => {
  editId.value = null
  form.value = { code: '', name: '', area_type: 'TABLE', status: 'active' }
}

const startEdit = row => {
  editId.value = row.id
  form.value = { code: row.code, name: row.name, area_type: row.area_type, status: row.status }
}

const save = async () => {
  saving.value = true
  try {
    if (editId.value)
      await updateServiceArea(editId.value, { name: form.value.name, area_type: form.value.area_type, status: form.value.status })
    else
      await createServiceArea(form.value)
    notify('Guardado')
    resetForm()
    await load()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Ambientes (salones)"
      subtitle="Salones y zonas del local. Las mesas numeradas se configuran en Mesas."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Configuración', disabled: true },
        { title: 'Ambientes', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="SETTINGS_SECTION_TABS" />
    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard v-if="can('settings.service_areas.manage')">
          <VCardTitle>{{ editId ? 'Editar ambiente' : 'Nuevo ambiente' }}</VCardTitle>
          <VCardText>
            <VTextField
              v-model="form.code"
              label="Código"
              :disabled="!!editId"
            />
            <VTextField
              v-model="form.name"
              label="Nombre"
              class="mt-3"
            />
            <VSelect
              v-model="form.area_type"
              label="Tipo"
              :items="['TABLE', 'VIP', 'BAR', 'ROOM', 'OTHER']"
              class="mt-3"
            />
            <VSelect
              v-model="form.status"
              label="Estado"
              :items="[
                { title: 'Activo', value: 'active' },
                { title: 'Inactivo', value: 'inactive' },
              ]"
              class="mt-3"
            />
            <VBtn
              color="primary"
              class="mt-4"
              :loading="saving"
              @click="save"
            >
              Guardar
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        cols="12"
        md="8"
      >
        <VCard>
          <VCardText>
            <VDataTable
              :headers="headers"
              :items="items"
              :loading="loading"
            >
              <template #item.actions="{ item }">
                <VBtn
                  v-if="can('settings.service_areas.manage')"
                  size="small"
                  variant="text"
                  @click="startEdit(item)"
                >
                  Editar
                </VBtn>
              </template>
            </VDataTable>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
</div>
</template>
