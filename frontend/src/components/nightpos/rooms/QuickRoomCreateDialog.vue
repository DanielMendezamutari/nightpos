<script setup>

import { createRoom } from '@/api/rooms'

import { ROOM_TYPE_OPTIONS } from '@/composables/useRoomStatus'

import { useNightPosNotify } from '@/composables/useNightPosNotify'

import { getApiErrorMessage } from '@/services/http'



const props = defineProps({

  modelValue: { type: Boolean, default: false },

})



const emit = defineEmits(['update:modelValue', 'created'])



const { notify } = useNightPosNotify()

const saving = ref(false)

const refForm = ref()



const form = ref({

  code: '',

  name: '',

  room_type: 'STANDARD',

  notes: '',

})



const close = () => emit('update:modelValue', false)



const reset = () => {

  form.value = {

    code: '',

    name: '',

    room_type: 'STANDARD',

    notes: '',

  }

  refForm.value?.resetValidation?.()

}



watch(() => props.modelValue, open => {

  if (open)

    reset()

})



const save = async () => {

  const { valid } = await refForm.value?.validate() ?? { valid: false }

  if (!valid)

    return



  saving.value = true

  try {

    const data = await createRoom({

      code: form.value.code.trim(),

      name: form.value.name.trim(),

      room_type: form.value.room_type,

      notes: form.value.notes || null,

    })

    notify('Habitación creada')

    emit('created', data?.room ?? data)

    close()

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

  <VDialog

    :model-value="modelValue"

    max-width="480"

    persistent

    @update:model-value="emit('update:modelValue', $event)"

  >

    <VCard>

      <VCardTitle class="d-flex align-center gap-2">

        <VIcon icon="ri-hotel-bed-line" />

        Nueva habitación

      </VCardTitle>

      <VCardText>

        <p class="text-body-2 mb-4">

          Recurso físico — se creará como <strong>disponible</strong>. El precio se define al registrar la pieza.

        </p>

        <VForm

          ref="refForm"

          @submit.prevent="save"

        >

          <VTextField

            v-model="form.code"

            label="Código *"

            class="mb-3"

            :rules="[v => !!v?.trim() || 'Requerido']"

          />

          <VTextField

            v-model="form.name"

            label="Nombre *"

            class="mb-3"

            :rules="[v => !!v?.trim() || 'Requerido']"

          />

          <VSelect

            v-model="form.room_type"

            :items="ROOM_TYPE_OPTIONS"

            label="Tipo *"

            class="mb-3"

          />

          <VTextarea

            v-model="form.notes"

            label="Notas"

            rows="2"

          />

        </VForm>

      </VCardText>

      <VCardActions>

        <VBtn

          variant="text"

          @click="close"

        >

          Cancelar

        </VBtn>

        <VSpacer />

        <VBtn

          color="primary"

          :loading="saving"

          @click="save"

        >

          Guardar habitación

        </VBtn>

      </VCardActions>

    </VCard>

  </VDialog>

</template>


