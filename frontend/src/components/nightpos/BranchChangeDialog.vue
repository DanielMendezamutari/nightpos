<script setup>

import { usePlatformContext } from '@/composables/usePlatformContext'

import { useAuthStore } from '@/stores/auth'

import { useContextStore } from '@/stores/context'

import { useOperationalStore } from '@/stores/operational'

import { getApiErrorMessage } from '@/services/http'



const props = defineProps({

  modelValue: { type: Boolean, default: false },

})



const emit = defineEmits(['update:modelValue', 'changed'])



const auth = useAuthStore()

const operational = useOperationalStore()

const context = useContextStore()

const { applyContext } = usePlatformContext()



const selectedCode = ref('')

const loading = ref(false)

const errorMessage = ref('')



const branchItems = computed(() =>

  (operational.branches ?? []).map(b => ({

    title: `${b.name} (${b.code})`,

    value: b.code,

  })),

)



watch(() => props.modelValue, async open => {

  if (!open)

    return



  errorMessage.value = ''

  selectedCode.value = context.branchCode || operational.branch?.code || ''



  if (!operational.branches.length) {

    loading.value = true



    try {

      await operational.loadAvailableBranches()

    }

    catch (error) {

      errorMessage.value = getApiErrorMessage(error)

    }

    finally {

      loading.value = false

    }

  }

})



const close = () => emit('update:modelValue', false)



const apply = async () => {

  if (!selectedCode.value) {

    errorMessage.value = 'Seleccione una sucursal.'



    return

  }



  loading.value = true

  errorMessage.value = ''



  try {

    await applyContext({

      tenantSlug: context.tenantSlug,

      branchCode: selectedCode.value,

    })

    close()

    emit('changed')

  }

  catch (error) {

    errorMessage.value = getApiErrorMessage(error)

  }

  finally {

    loading.value = false

  }

}

</script>



<template>

  <VDialog

    :model-value="modelValue"

    max-width="420"

    @update:model-value="emit('update:modelValue', $event)"

  >

    <VCard title="Cambiar sucursal">

      <VCardText>

        <VAlert

          v-if="errorMessage"

          type="error"

          variant="tonal"

          class="mb-4"

        >

          {{ errorMessage }}

        </VAlert>



        <p

          v-if="auth.user?.tenant_id || context.tenantSlug"

          class="text-body-2 mb-4"

        >

          Empresa: {{ operational.tenant?.name || context.tenantSlug }}

        </p>



        <VSelect

          v-model="selectedCode"

          :items="branchItems"

          label="Sucursal"

          :loading="loading"

          :disabled="!branchItems.length"

        />

      </VCardText>

      <VCardActions>

        <VBtn

          variant="text"

          @click="close"

        >

          Cancelar

        </VBtn>

        <VBtn

          color="primary"

          :loading="loading"

          @click="apply"

        >

          Aplicar

        </VBtn>

      </VCardActions>

    </VCard>

  </VDialog>

</template>

