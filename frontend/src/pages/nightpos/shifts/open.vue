<script setup>
import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'
import NightPosFormPageLayout from '@/components/nightpos/layout/NightPosFormPageLayout.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { openShift } from '@/api/shifts'
import { useFilteredShiftTabs } from '@/composables/useShiftSectionTabs'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'shifts.open' } })

const shiftTabs = useFilteredShiftTabs()
const router = useRouter()
const { notify } = useNightPosNotify()

const form = ref({
  shift_type: 'DAY',
  business_date: new Date().toISOString().slice(0, 10),
  notes: '',
})
const saving = ref(false)
const refForm = ref()

const windowHint = computed(() => {
  if (form.value.shift_type === 'NIGHT') {
    return '21:00 del día operativo hasta 09:00 del día siguiente.'
  }

  return '09:00 a 21:00 del día operativo.'
})

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid)
    return

  saving.value = true
  try {
    await openShift({
      shift_type: form.value.shift_type,
      business_date: form.value.business_date,
      notes: form.value.notes?.trim() || null,
    })
    notify('Turno abierto')
    await router.push({ name: 'nightpos-shifts-current' })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Abrir turno"
      subtitle="Inicie el turno oficial Día o Noche para la sucursal."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Turnos', to: { name: 'nightpos-shifts' } },
        { title: 'Abrir', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="shiftTabs" />

    <VForm
      ref="refForm"
      @submit.prevent="save"
    >
      <NightPosFormPageLayout
        title="Datos del turno"
        :hint="windowHint"
      >
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <VSelect
              v-model="form.shift_type"
              label="Tipo de turno"
              :items="[
                { title: 'Día (09:00 – 21:00)', value: 'DAY' },
                { title: 'Noche (21:00 – 09:00)', value: 'NIGHT' },
              ]"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="form.business_date"
              label="Fecha operativa"
              type="date"
              :rules="[v => !!v || 'Requerido']"
            />
          </VCol>
          <VCol cols="12">
            <VTextarea
              v-model="form.notes"
              label="Notas"
              rows="2"
            />
          </VCol>
        </VRow>
        <template #actions>
          <NightPosFormActions
            :saving="saving"
            save-label="Abrir turno"
            :cancel-to="{ name: 'nightpos-shifts-current' }"
            @save="save"
          />
        </template>
      </NightPosFormPageLayout>
    </VForm>
</div>
</template>
