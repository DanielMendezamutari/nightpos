<script setup>

import NightPosFormActions from '@/components/nightpos/layout/NightPosFormActions.vue'

import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'

import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'

import { fetchRoom, markRoomMaintenance, updateRoom } from '@/api/rooms'

import { useFilteredRoomsTabs } from '@/composables/useRoomsSectionTabs'

import { ROOM_TYPE_OPTIONS, roomStatusColor } from '@/composables/useRoomStatus'

import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

import { useNightPosNotify } from '@/composables/useNightPosNotify'

import { getApiErrorMessage } from '@/services/http'



definePage({ meta: { permission: 'rooms.update' } })



const route = useRoute()

const roomTabs = useFilteredRoomsTabs()

const { can } = useNightPosPermissions()

const { notify } = useNightPosNotify()

const router = useRouter()

const roomId = computed(() => Number(route.params.id))

const loading = ref(true)

const saving = ref(false)

const refForm = ref()

const roomStatus = ref('')

const showOptionalDefaults = ref(false)



const form = ref({

  code: '',

  name: '',

  room_type: 'STANDARD',

  default_duration_minutes: null,

  suggested_price: null,

  notes: '',

})



const load = async () => {

  loading.value = true

  try {

    const data = await fetchRoom(roomId.value)

    const r = data.room

    roomStatus.value = r.status

    showOptionalDefaults.value = r.suggested_price != null || r.default_duration_minutes != null

    form.value = {

      code: r.code,

      name: r.name,

      room_type: r.room_type,

      default_duration_minutes: r.default_duration_minutes,

      suggested_price: r.suggested_price != null ? Number(r.suggested_price) : null,

      notes: r.notes || '',

    }

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

    await router.replace({ name: 'nightpos-rooms-list' })

  }

  finally {

    loading.value = false

  }

}



const save = async () => {

  const { valid } = await refForm.value?.validate() ?? { valid: false }

  if (!valid)

    return



  saving.value = true

  try {

    const payload = {

      code: form.value.code.trim(),

      name: form.value.name.trim(),

      room_type: form.value.room_type,

      notes: form.value.notes || null,

    }



    if (showOptionalDefaults.value && form.value.default_duration_minutes)

      payload.default_duration_minutes = Number(form.value.default_duration_minutes)



    if (showOptionalDefaults.value && form.value.suggested_price !== null && form.value.suggested_price !== '')

      payload.suggested_price = Number(form.value.suggested_price)



    await updateRoom(roomId.value, payload)

    notify('Habitación actualizada')

    await router.push({ name: 'nightpos-rooms-list' })

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    saving.value = false

  }

}



const toMaintenance = async () => {

  try {

    await markRoomMaintenance(roomId.value)

    notify('En mantenimiento')

    await load()

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

}



onMounted(async () => {

  if (!can('rooms.update')) {

    await router.replace({ name: 'nightpos-rooms-list' })



    return

  }



  await load()

})

</script>



<template>

  <div>

    <NightPosPageHeader

      title="Editar habitación"

      :breadcrumbs="[

        { title: 'Habitaciones', to: { name: 'nightpos-rooms-list' } },

        { title: 'Editar', disabled: true },

      ]"

    >

      <template #actions>

        <VChip

          v-if="roomStatus"

          :color="roomStatusColor(roomStatus)"

          label

        >

          {{ roomStatus }}

        </VChip>

        <VBtn

          v-if="can('rooms.maintenance') && roomStatus === 'AVAILABLE'"

          color="warning"

          variant="tonal"

          class="ms-2"

          @click="toMaintenance"

        >

          Mantenimiento

        </VBtn>

      </template>

    </NightPosPageHeader>

    <NightPosSectionTabs :tabs="roomTabs" />

    <VProgressLinear

      v-if="loading"

      indeterminate

      class="mb-4"

    />

    <VCard v-if="!loading">

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

                v-model="form.room_type"

                :items="ROOM_TYPE_OPTIONS"

                label="Tipo"

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



          <VSwitch

            v-model="showOptionalDefaults"

            label="Valores sugeridos opcionales"

            class="mt-2"

            hint="Solo referencia — no definen cobro ni liquidación"

            persistent-hint

          />

          <VRow v-if="showOptionalDefaults">

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

              />

            </VCol>

          </VRow>



          <NightPosFormActions

            :saving="saving"

            :cancel-to="{ name: 'nightpos-rooms-list' }"

            @save="save"

          />

        </VForm>

      </VCardText>

    </VCard>

  </div>

</template>


