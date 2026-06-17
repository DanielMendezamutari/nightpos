<script setup>

import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'

import { fetchServiceTables, fetchWaiterTableAssignments, syncWaiterTableAssignments } from '@/api/serviceTables'

import { fetchServiceAreas } from '@/api/serviceAreas'

import { fetchStaffWaiters } from '@/api/staff'

import { STAFF_SECTION_TABS } from '@/composables/useStaffSectionTabs'

import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'

import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

import { useNightPosNotify } from '@/composables/useNightPosNotify'

import { getApiErrorMessage } from '@/services/http'

import { dismissStrayOverlays } from '@/utils/overlaySafety'



definePage({ meta: { permission: 'settings.waiter_assignments' } })



const { can } = useNightPosPermissions()

const { notify } = useNightPosNotify()



const catalogLoading = ref(false)

const assignmentsLoading = ref(false)

const saving = ref(false)

const waiters = ref([])

const areas = ref([])

const tables = ref([])

const selectedWaiterId = ref(null)

const filterAreaId = ref(null)

const selectedTableIds = ref([])



const waiterSelectRef = ref(null)

const areaSelectRef = ref(null)



const waiterItems = computed(() =>

  waiters.value.map(w => ({ title: w.name, value: w.id })),

)



const areaItems = computed(() =>

  areas.value.map(a => ({ title: a.name, value: a.id })),

)



const visibleTables = computed(() => {

  let list = tables.value.filter(t => t.status === 'active')

  if (filterAreaId.value)

    list = list.filter(t => t.service_area_id === filterAreaId.value)



  return list.sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0) || a.label.localeCompare(b.label, 'es'))

})



const groupedTables = computed(() => {

  const map = new Map()

  for (const table of visibleTables.value) {

    const area = table.service_area_name || 'General'

    if (!map.has(area))

      map.set(area, [])



    map.get(area).push(table)

  }



  return [...map.entries()].map(([area, items]) => ({ area, items }))

})



const closeOpenMenus = () => {

  waiterSelectRef.value?.blur?.()

  areaSelectRef.value?.blur?.()

  dismissStrayOverlays()

}



const toggleTable = id => {

  const set = new Set(selectedTableIds.value)

  if (set.has(id))

    set.delete(id)

  else

    set.add(id)



  selectedTableIds.value = [...set]

}



const isSelected = id => selectedTableIds.value.includes(id)



const loadCatalog = async () => {

  const [waiterList, areaList, tableList] = await Promise.all([

    fetchStaffWaiters(),

    fetchServiceAreas({ active_only: true }),

    fetchServiceTables({ active_only: true }),

  ])

  waiters.value = waiterList

  areas.value = areaList

  tables.value = tableList

}



const loadAssignments = async () => {

  if (!selectedWaiterId.value) {

    selectedTableIds.value = []



    return

  }



  assignmentsLoading.value = true

  try {

    const rows = await fetchWaiterTableAssignments({ waiter_user_id: selectedWaiterId.value })

    selectedTableIds.value = rows.map(r => r.service_table_id)

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    assignmentsLoading.value = false

  }

}



const save = async () => {

  if (!selectedWaiterId.value) {

    notify('Selecciona un garzón.', 'warning')



    return

  }



  closeOpenMenus()

  saving.value = true

  try {

    await syncWaiterTableAssignments({

      waiter_user_id: selectedWaiterId.value,

      service_table_ids: selectedTableIds.value,

    })

    notify('Asignación guardada')

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    saving.value = false

    closeOpenMenus()

  }

}



watch(selectedWaiterId, () => {

  nextTick(() => loadAssignments())

})



onBeforeRouteLeave(() => {

  closeOpenMenus()

})



onBeforeUnmount(() => {

  closeOpenMenus()

})



onMounted(async () => {

  catalogLoading.value = true

  try {

    await loadCatalog()

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    catalogLoading.value = false

  }

})

</script>



<template>

  <div>

    <NightPosPageHeader

      title="Asignar mesas a garzones"

      subtitle="El garzón verá solo estas mesas en «Mis mesas». Toca para seleccionar o deseleccionar."

      :breadcrumbs="[

        { title: 'Personal', disabled: true },

        { title: 'Asignar mesas', disabled: true },

      ]"

    />

    <NightPosSectionTabs :tabs="STAFF_SECTION_TABS" />



    <VProgressLinear

      v-if="catalogLoading || assignmentsLoading"

      indeterminate

      color="primary"

      class="mb-4"

    />



    <VRow>

      <VCol

        cols="12"

        md="4"

      >

        <VCard>

          <VCardTitle>Garzón</VCardTitle>

          <VCardText>

            <VSelect

              ref="waiterSelectRef"

              v-model="selectedWaiterId"

              label="Seleccionar garzón"

              :items="waiterItems"

              :disabled="catalogLoading || assignmentsLoading"

              :menu-props="{ scrollStrategy: 'close' }"

              clearable

            />

            <VSelect

              ref="areaSelectRef"

              v-model="filterAreaId"

              label="Filtrar salón (opcional)"

              :items="[{ title: 'Todos', value: null }, ...areaItems]"

              class="mt-3"

              :disabled="catalogLoading"

              :menu-props="{ scrollStrategy: 'close' }"

              clearable

              hide-details

            />

            <VAlert

              v-if="selectedWaiterId"

              type="info"

              variant="tonal"

              density="compact"

              class="mt-4"

            >

              {{ selectedTableIds.length }} mesa{{ selectedTableIds.length === 1 ? '' : 's' }} seleccionada{{ selectedTableIds.length === 1 ? '' : 's' }}

            </VAlert>

            <VBtn

              v-if="can('settings.waiter_assignments.manage')"

              color="primary"

              block

              class="mt-4"

              size="large"

              :loading="saving"

              :disabled="!selectedWaiterId || catalogLoading || assignmentsLoading"

              @click="save"

            >

              Guardar asignación

            </VBtn>

          </VCardText>

        </VCard>

      </VCol>



      <VCol

        cols="12"

        md="8"

      >

        <VCard>

          <VCardTitle>Mesas disponibles</VCardTitle>

          <VCardText>

            <VAlert

              v-if="!selectedWaiterId"

              type="warning"

              variant="tonal"

            >

              Elige un garzón para ver y editar sus mesas.

            </VAlert>



            <template v-else-if="visibleTables.length">

              <section

                v-for="group in groupedTables"

                :key="group.area"

                class="mb-5"

              >

                <div class="text-subtitle-2 font-weight-bold mb-2 text-medium-emphasis">

                  {{ group.area }}

                </div>

                <div class="assignment-grid">

                  <VChip

                    v-for="table in group.items"

                    :key="table.id"

                    :color="isSelected(table.id) ? 'primary' : undefined"

                    :variant="isSelected(table.id) ? 'flat' : 'outlined'"

                    size="large"

                    class="assignment-grid__chip"

                    :disabled="!can('settings.waiter_assignments.manage') || assignmentsLoading"

                    @click="toggleTable(table.id)"

                  >

                    <VIcon

                      :icon="isSelected(table.id) ? 'ri-check-line' : 'ri-add-line'"

                      start

                    />

                    {{ table.label }}

                  </VChip>

                </div>

              </section>

            </template>



            <VAlert

              v-else

              type="info"

              variant="tonal"

            >

              No hay mesas activas. Créalas en Configuración → Mesas.

            </VAlert>

          </VCardText>

        </VCard>

      </VCol>

    </VRow>

  </div>

</template>



<style scoped>

.assignment-grid {

  display: flex;

  flex-wrap: wrap;

  gap: 10px;

}



.assignment-grid__chip {

  min-height: 44px;

  cursor: pointer;

  user-select: none;

}

</style>

