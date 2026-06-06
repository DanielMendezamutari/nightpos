<script setup>

import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'

import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'

import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'

import { createRoom } from '@/api/rooms'

import { fetchRoomTypes } from '@/api/roomTypes'

import { useFilteredRoomsTabs } from '@/composables/useRoomsSectionTabs'

import { ROOM_TYPE_OPTIONS } from '@/composables/useRoomStatus'

import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

import { useNightPosNotify } from '@/composables/useNightPosNotify'

import { getApiErrorMessage } from '@/services/http'



definePage({ meta: { permission: 'rooms.create' } })



const roomTabs = useFilteredRoomsTabs()

const { can } = useNightPosPermissions()

const { notify } = useNightPosNotify()

const router = useRouter()

const saving = ref(false)

const refForm = ref()

const showOptionalDefaults = ref(false)



const roomTypeCatalog = ref([])



const form = ref({

  code: '',

  name: '',

  room_type_id: null,

  room_type: 'STANDARD',

  default_duration_minutes: null,

  suggested_price: null,

  notes: '',

})



const save = async () => {

  const { valid } = await refForm.value?.validate() ?? { valid: false }

  if (!valid)

    return



  saving.value = true

  try {

    const payload = {

      code: form.value.code.trim(),

      name: form.value.name.trim(),

      notes: form.value.notes || null,

    }



    if (form.value.room_type_id)

      payload.room_type_id = form.value.room_type_id

    else

      payload.room_type = form.value.room_type



    if (showOptionalDefaults.value && form.value.default_duration_minutes)

      payload.default_duration_minutes = Number(form.value.default_duration_minutes)



    if (showOptionalDefaults.value && form.value.suggested_price !== null && form.value.suggested_price !== '')

      payload.suggested_price = Number(form.value.suggested_price)



    await createRoom(payload)

    notify('Habitación creada')

    await router.push({ name: 'nightpos-rooms-list' })

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    saving.value = false

  }

}



onMounted(async () => {

  if (!can('rooms.create'))

    await router.replace({ name: 'nightpos-rooms-list' })



  try {

    roomTypeCatalog.value = await fetchRoomTypes({ active_only: true })

  }

  catch {

    roomTypeCatalog.value = []

  }

})

</script>



<template>

  <div>

    <NightPosPageHeader

      title="Nueva habitación"

      subtitle="Recurso físico del local — el precio real se define al registrar la pieza."

      :breadcrumbs="[

        { title: 'NightPOS', disabled: true },

        { title: 'Habitaciones', to: { name: 'nightpos-rooms-dashboard' } },

        { title: 'Crear', disabled: true },

      ]"

    />

    <NightPosSectionTabs :tabs="roomTabs" />

    <VCard>

      <VCardText>

        <VForm

          ref="refForm"

          @submit.prevent="save"

        >

          <VRow>

            <VCol

              cols="12"

              md="4"

            >

              <VTextField

                v-model="form.code"

                label="Código *"

                :rules="[v => !!v?.trim() || 'Requerido']"

              />

            </VCol>

            <VCol

              cols="12"

              md="8"

            >

              <VTextField

                v-model="form.name"

                label="Nombre *"

                :rules="[v => !!v?.trim() || 'Requerido']"

              />

            </VCol>

            <VCol

              cols="12"

              md="6"

            >

              <VSelect

                v-if="roomTypeCatalog.length"

                v-model="form.room_type_id"

                :items="roomTypeCatalog.map(t => ({ title: `${t.name} (${t.code})`, value: t.id }))"

                label="Tipo catalogado"

                clearable

              />

              <VSelect

                v-else

                v-model="form.room_type"

                :items="ROOM_TYPE_OPTIONS"

                label="Tipo *"

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



          <VExpansionPanels

            v-model="showOptionalDefaults"

            class="mt-2"

          >

            <VExpansionPanel value="defaults">

              <VExpansionPanelTitle>

                Valores sugeridos opcionales

              </VExpansionPanelTitle>

              <VExpansionPanelText>

                <p class="text-body-2 text-medium-emphasis mb-4">

                  Solo referencia interna. No definen el cobro ni la liquidación de la pieza.

                </p>

                <VRow>

                  <VCol

                    cols="12"

                    md="6"

                  >

                    <VTextField

                      v-model.number="form.suggested_price"

                      type="number"

                      label="Precio sugerido (BOB)"

                      min="0"

                      step="0.01"

                      hint="Opcional — no es el precio del servicio"

                      persistent-hint

                    />

                  </VCol>

                  <VCol

                    cols="12"

                    md="6"

                  >

                    <VTextField

                      v-model.number="form.default_duration_minutes"

                      type="number"

                      label="Duración sugerida (min)"

                      min="1"

                      hint="Opcional — la duración real se define al registrar la pieza"

                      persistent-hint

                    />

                  </VCol>

                </VRow>

              </VExpansionPanelText>

            </VExpansionPanel>

          </VExpansionPanels>



          <NightPosFormActions

            :saving="saving"

            :cancel-to="{ name: 'nightpos-rooms-list' }"

            save-label="Guardar"

            @save="save"

          />

        </VForm>

      </VCardText>

    </VCard>

  </div>

</template>


