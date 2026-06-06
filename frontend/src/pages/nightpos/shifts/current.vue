<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchCurrentShift } from '@/api/shifts'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { useFilteredShiftTabs } from '@/composables/useShiftSectionTabs'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useOperationalStore } from '@/stores/operational'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'shifts.access' } })

const shiftTabs = useFilteredShiftTabs()
const operational = useOperationalStore()
const { notify } = useNightPosNotify()

const shift = ref(null)
const loading = ref(true)

const load = async () => {
  loading.value = true
  try {
    shift.value = await fetchCurrentShift()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

onMounted(load)
useOnContextChange(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Turno actual"
      subtitle="Clasificación de ventas, caja y comandas para reportes y fiscalización."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Turnos', to: { name: 'nightpos-shifts' } },
        { title: 'Actual', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="shiftTabs" />

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VAlert
      v-else-if="!shift"
      type="info"
      variant="tonal"
      class="mb-4"
    >
      Aún no hay turno clasificado en este periodo. Al abrir caja, crear comandas o cobrar, el sistema asigna el turno automáticamente para reportes.
    </VAlert>

    <VRow v-else>
      <VCol
        cols="12"
        md="8"
      >
        <VCard>
          <VCardItem>
            <VCardTitle>{{ shift.name }}</VCardTitle>
            <VCardSubtitle>
              {{ shift.shift_type_label }} · {{ shift.business_date }}
              <VChip
                v-if="shift.auto_created"
                size="x-small"
                color="info"
                variant="tonal"
                class="ms-2"
              >
                Auto
              </VChip>
            </VCardSubtitle>
          </VCardItem>
          <VCardText>
            <VList density="compact">
              <VListItem
                prepend-icon="ri-flag-line"
                :title="`Estado: ${shift.status}`"
              />
              <VListItem
                prepend-icon="ri-time-line"
                :title="`${shift.starts_at} → ${shift.ends_at}`"
                subtitle="Ventana oficial"
              />
              <VListItem
                prepend-icon="ri-user-line"
                :title="shift.opened_by_name || `Usuario #${shift.opened_by_user_id}`"
                subtitle="Apertura"
              />
              <VListItem
                prepend-icon="ri-store-2-line"
                :title="shift.branch_name || operational.branch?.name || 'Sucursal'"
              />
              <VListItem
                v-if="shift.notes"
                prepend-icon="ri-sticky-note-line"
                :title="shift.notes"
              />
            </VList>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText>
            <p class="text-body-2 text-medium-emphasis mb-4">
              El turno agrupa comandas, caja y ventas para reportes. No es obligatorio abrirlo manualmente antes de vender.
            </p>
            <VBtn
              v-if="$can('access', 'shifts.close')"
              block
              color="primary"
              variant="outlined"
              :to="{ name: 'nightpos-shifts-close' }"
            >
              Ir a cierre de turno
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
