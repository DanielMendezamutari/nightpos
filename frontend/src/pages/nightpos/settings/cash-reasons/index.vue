<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { createCashMovementReason, fetchCashMovementReasons, updateCashMovementReason } from '@/api/cashMovementReasons'
import { SETTINGS_SECTION_TABS } from '@/composables/useSettingsSectionTabs'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'settings.cash_reasons' } })

const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const loading = ref(false)
const saving = ref(false)
const items = ref([])
const filterType = ref(null)

const form = ref({ type: 'EXPENSE', name: '', status: 'active' })
const editId = ref(null)

const headers = [
  { title: 'Tipo', key: 'type' },
  { title: 'Nombre', key: 'name' },
  { title: 'Estado', key: 'status' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const filtered = computed(() => {
  if (!filterType.value)
    return items.value
  return items.value.filter(i => i.type === filterType.value)
})

const load = async () => {
  loading.value = true
  try {
    items.value = await fetchCashMovementReasons()
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
  form.value = { type: 'EXPENSE', name: '', status: 'active' }
}

const startEdit = row => {
  editId.value = row.id
  form.value = { type: row.type, name: row.name, status: row.status }
}

const save = async () => {
  if (!form.value.name?.trim()) {
    notify('Indique el nombre', 'warning')
    return
  }
  saving.value = true
  try {
    if (editId.value)
      await updateCashMovementReason(editId.value, { name: form.value.name, status: form.value.status })
    else
      await createCashMovementReason(form.value)
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
      title="Motivos de caja"
      subtitle="Catálogo para ingresos y egresos manuales."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Configuración', disabled: true },
        { title: 'Motivos de caja', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="SETTINGS_SECTION_TABS" />
    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard v-if="can('settings.cash_reasons.manage')">
          <VCardTitle>{{ editId ? 'Editar motivo' : 'Nuevo motivo' }}</VCardTitle>
          <VCardText>
            <VSelect
              v-model="form.type"
              label="Tipo"
              :items="[
                { title: 'Ingreso', value: 'INCOME' },
                { title: 'Egreso', value: 'EXPENSE' },
              ]"
              :disabled="!!editId"
            />
            <VTextField
              v-model="form.name"
              label="Nombre"
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
                variant="tonal"
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
              v-model="filterType"
              label="Filtrar tipo"
              clearable
              :items="[
                { title: 'Ingresos', value: 'INCOME' },
                { title: 'Egresos', value: 'EXPENSE' },
              ]"
              class="mb-4"
              style="max-width: 240px"
            />
            <VDataTable
              :headers="headers"
              :items="filtered"
              :loading="loading"
              density="comfortable"
            >
              <template #item.type="{ item }">
                <VChip
                  size="small"
                  :color="item.type === 'INCOME' ? 'success' : 'error'"
                >
                  {{ item.type === 'INCOME' ? 'Ingreso' : 'Egreso' }}
                </VChip>
              </template>
              <template #item.status="{ item }">
                <VChip
                  size="small"
                  :color="item.status === 'active' ? 'success' : 'secondary'"
                >
                  {{ item.status }}
                </VChip>
              </template>
              <template #item.actions="{ item }">
                <VBtn
                  v-if="can('settings.cash_reasons.manage')"
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
