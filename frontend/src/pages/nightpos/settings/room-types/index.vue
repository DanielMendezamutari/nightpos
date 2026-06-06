<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { createRoomType, fetchRoomTypes, updateRoomType } from '@/api/roomTypes'
import { SETTINGS_SECTION_TABS } from '@/composables/useSettingsSectionTabs'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { formatMoney } from '@/composables/useOrderHelpers'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'settings.room_types' } })

const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const loading = ref(false)
const saving = ref(false)
const items = ref([])
const form = ref({
  code: '',
  name: '',
  default_duration_minutes: 60,
  suggested_price: 0,
  status: 'active',
})
const editId = ref(null)

const headers = [
  { title: 'Código', key: 'code' },
  { title: 'Nombre', key: 'name' },
  { title: 'Duración (min)', key: 'default_duration_minutes' },
  { title: 'Precio sugerido', key: 'suggested_price' },
  { title: 'Estado', key: 'status' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const load = async () => {
  loading.value = true
  try {
    items.value = await fetchRoomTypes()
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
  form.value = { code: '', name: '', default_duration_minutes: 60, suggested_price: 0, status: 'active' }
}

const startEdit = row => {
  editId.value = row.id
  form.value = {
    code: row.code,
    name: row.name,
    default_duration_minutes: row.default_duration_minutes,
    suggested_price: Number(row.suggested_price),
    status: row.status,
  }
}

const save = async () => {
  saving.value = true
  try {
    if (editId.value) {
      await updateRoomType(editId.value, {
        name: form.value.name,
        default_duration_minutes: form.value.default_duration_minutes,
        suggested_price: form.value.suggested_price,
        status: form.value.status,
      })
    }
    else {
      await createRoomType(form.value)
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

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Tipos de habitación"
      subtitle="Compatible con STANDARD / VIP / SUITE y códigos personalizados."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Configuración', disabled: true },
        { title: 'Tipos habitación', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="SETTINGS_SECTION_TABS" />
    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard v-if="can('settings.room_types.manage')">
          <VCardTitle>{{ editId ? 'Editar tipo' : 'Nuevo tipo' }}</VCardTitle>
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
            <VTextField
              v-model.number="form.default_duration_minutes"
              type="number"
              label="Duración (min)"
              class="mt-3"
            />
            <VTextField
              v-model.number="form.suggested_price"
              type="number"
              label="Precio sugerido"
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
              <template #item.suggested_price="{ item }">
                {{ formatMoney(item.suggested_price) }}
              </template>
              <template #item.actions="{ item }">
                <VBtn
                  v-if="can('settings.room_types.manage')"
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
