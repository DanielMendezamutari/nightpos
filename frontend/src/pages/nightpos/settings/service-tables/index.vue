<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { createServiceTable, fetchServiceTables, updateServiceTable } from '@/api/serviceTables'
import { fetchServiceAreas } from '@/api/serviceAreas'
import { SETTINGS_SECTION_TABS } from '@/composables/useSettingsSectionTabs'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'settings.service_tables' } })

const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const loading = ref(false)
const saving = ref(false)
const areas = ref([])
const items = ref([])
const filterAreaId = ref(null)
const editId = ref(null)

const form = ref({
  service_area_id: null,
  code: '',
  label: '',
  sort_order: 0,
  status: 'active',
})

const headers = [
  { title: 'Salón', key: 'service_area_name' },
  { title: 'Código', key: 'code' },
  { title: 'Etiqueta', key: 'label' },
  { title: 'Orden', key: 'sort_order', width: '90px' },
  { title: 'Estado', key: 'status' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const areaItems = computed(() =>
  areas.value.map(a => ({ title: a.name, value: a.id })),
)

const filteredItems = computed(() => {
  if (!filterAreaId.value)
    return items.value

  return items.value.filter(i => i.service_area_id === filterAreaId.value)
})

const load = async () => {
  loading.value = true
  try {
    const [areaList, tableList] = await Promise.all([
      fetchServiceAreas({ active_only: true }),
      fetchServiceTables(filterAreaId.value ? { service_area_id: filterAreaId.value } : {}),
    ])
    areas.value = areaList
    items.value = tableList
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
  form.value = {
    service_area_id: filterAreaId.value ?? areas.value[0]?.id ?? null,
    code: '',
    label: '',
    sort_order: 0,
    status: 'active',
  }
}

const startEdit = row => {
  editId.value = row.id
  form.value = {
    service_area_id: row.service_area_id,
    code: row.code,
    label: row.label,
    sort_order: row.sort_order ?? 0,
    status: row.status,
  }
}

const save = async () => {
  if (!form.value.service_area_id || !form.value.label?.trim()) {
    notify('Salón y etiqueta son obligatorios.', 'warning')

    return
  }

  saving.value = true
  try {
    if (editId.value) {
      await updateServiceTable(editId.value, {
        label: form.value.label,
        sort_order: Number(form.value.sort_order) || 0,
        status: form.value.status,
      })
    }
    else {
      if (!form.value.code?.trim()) {
        notify('El código es obligatorio.', 'warning')

        return
      }
      await createServiceTable({
        service_area_id: form.value.service_area_id,
        code: form.value.code,
        label: form.value.label,
        sort_order: Number(form.value.sort_order) || 0,
        status: form.value.status,
      })
    }
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

watch(filterAreaId, load)

onMounted(async () => {
  await load()
  resetForm()
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Mesas por salón"
      subtitle="Mesas numeradas para el garzón. Primero crea el salón en Ambientes, luego las mesas aquí."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Configuración', disabled: true },
        { title: 'Mesas', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="SETTINGS_SECTION_TABS" />

    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard v-if="can('settings.service_tables.manage')">
          <VCardTitle>{{ editId ? 'Editar mesa' : 'Nueva mesa' }}</VCardTitle>
          <VCardText>
            <VSelect
              v-model="form.service_area_id"
              label="Salón"
              :items="areaItems"
              :disabled="!!editId"
            />
            <VTextField
              v-model="form.code"
              label="Código"
              placeholder="VIP-01"
              class="mt-3"
              :disabled="!!editId"
            />
            <VTextField
              v-model="form.label"
              label="Etiqueta visible"
              placeholder="Mesa 1"
              class="mt-3"
            />
            <VTextField
              v-model.number="form.sort_order"
              label="Orden en grid"
              type="number"
              min="0"
              class="mt-3"
            />
            <VSelect
              v-model="form.status"
              label="Estado"
              :items="[
                { title: 'Activa', value: 'active' },
                { title: 'Inactiva', value: 'inactive' },
              ]"
              class="mt-3"
            />
            <div class="d-flex gap-2 mt-4">
              <VBtn
                color="primary"
                :loading="saving"
                @click="save"
              >
                Guardar
              </VBtn>
              <VBtn
                v-if="editId"
                variant="text"
                @click="resetForm"
              >
                Cancelar
              </VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="8"
      >
        <VCard>
          <VCardText>
            <VSelect
              v-model="filterAreaId"
              label="Filtrar por salón"
              :items="[{ title: 'Todos', value: null }, ...areaItems]"
              clearable
              class="mb-4"
              hide-details
            />
            <VDataTable
              :headers="headers"
              :items="filteredItems"
              :loading="loading"
            >
              <template #item.status="{ item }">
                <VChip
                  size="small"
                  :color="item.status === 'active' ? 'success' : 'default'"
                  variant="tonal"
                >
                  {{ item.status === 'active' ? 'Activa' : 'Inactiva' }}
                </VChip>
              </template>
              <template #item.actions="{ item }">
                <VBtn
                  v-if="can('settings.service_tables.manage')"
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
