<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { createPaymentMethod, fetchPaymentMethods, updatePaymentMethod } from '@/api/paymentMethods'
import { SETTINGS_SECTION_TABS } from '@/composables/useSettingsSectionTabs'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'settings.payment_methods' } })

const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const loading = ref(false)
const saving = ref(false)
const items = ref([])

const form = ref({
  code: '',
  name: '',
  type: 'QR',
  enabled: true,
  requires_reference: false,
})
const editId = ref(null)

const headers = [
  { title: 'Código', key: 'code' },
  { title: 'Nombre', key: 'name' },
  { title: 'Tipo', key: 'type' },
  { title: 'Legacy', key: 'legacy_method' },
  { title: 'Activo', key: 'enabled' },
  { title: 'Ref.', key: 'requires_reference' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const load = async () => {
  loading.value = true
  try {
    items.value = await fetchPaymentMethods()
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
  form.value = { code: '', name: '', type: 'QR', enabled: true, requires_reference: false }
}

const startEdit = row => {
  editId.value = row.id
  form.value = {
    code: row.code,
    name: row.name,
    type: row.type,
    enabled: row.enabled,
    requires_reference: row.requires_reference,
  }
}

const save = async () => {
  saving.value = true
  try {
    if (editId.value) {
      await updatePaymentMethod(editId.value, {
        name: form.value.name,
        enabled: form.value.enabled,
        requires_reference: form.value.requires_reference,
      })
    }
    else {
      await createPaymentMethod(form.value)
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
      title="Métodos de pago"
      subtitle="Configuración por tenant — compatible con CASH / QR / CARD y cobro MIXED."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Configuración', disabled: true },
        { title: 'Métodos de pago', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="SETTINGS_SECTION_TABS" />
    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard v-if="can('settings.payment_methods.manage')">
          <VCardTitle>{{ editId ? 'Editar método' : 'Nuevo método' }}</VCardTitle>
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
              v-model="form.type"
              label="Tipo base"
              :items="['CASH', 'QR', 'CARD', 'OTHER']"
              :disabled="!!editId"
              class="mt-3"
            />
            <VSwitch
              v-model="form.enabled"
              label="Habilitado"
              class="mt-2"
            />
            <VSwitch
              v-model="form.requires_reference"
              label="Requiere referencia"
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
            <VDataTable
              :headers="headers"
              :items="items"
              :loading="loading"
            >
              <template #item.enabled="{ item }">
                <VChip
                  size="small"
                  :color="item.enabled ? 'success' : 'secondary'"
                >
                  {{ item.enabled ? 'Sí' : 'No' }}
                </VChip>
              </template>
              <template #item.requires_reference="{ item }">
                {{ item.requires_reference ? 'Sí' : 'No' }}
              </template>
              <template #item.actions="{ item }">
                <VBtn
                  v-if="can('settings.payment_methods.manage')"
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
