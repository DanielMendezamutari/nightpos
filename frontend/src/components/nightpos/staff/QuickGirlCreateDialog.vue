<script setup>
import { fetchAvailableBranches } from '@/api/branches'
import { quickCreateGirl } from '@/api/staff'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'
import { useOperationalStore } from '@/stores/operational'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'created'])

const { notify } = useNightPosNotify()
const operational = useOperationalStore()
const saving = ref(false)
const loadingBranches = ref(false)
const branchesLoadError = ref('')
const refForm = ref()
const branches = ref([])

const form = ref({
  name: '',
  pin: '',
  notes: '',
  branch_id: null,
  accessible_branch_ids: [],
})

const branchOptions = computed(() => branches.value.map(b => ({
  title: `${b.name} [${b.code}]`,
  value: b.id,
})))

const hasMultipleBranches = computed(() => branchOptions.value.length > 1)

const mainBranchOptions = computed(() => {
  const allowed = new Set(form.value.accessible_branch_ids)

  return branchOptions.value.filter(b => allowed.has(b.value))
})

const close = () => emit('update:modelValue', false)

const reset = () => {
  form.value = {
    name: '',
    pin: '',
    notes: '',
    branch_id: null,
    accessible_branch_ids: [],
  }
  branchesLoadError.value = ''
  refForm.value?.resetValidation?.()
}

const resolveDefaultBranchIds = (currentId) => {
  if (currentId)
    return [currentId]

  if (branches.value.length === 1)
    return [branches.value[0].id]

  return []
}

const initBranches = async () => {
  loadingBranches.value = true
  branchesLoadError.value = ''

  try {
    branches.value = await fetchAvailableBranches()

    if (!branches.value.length && operational.branches.length)
      branches.value = [...operational.branches]

    if (!operational.branch)
      await operational.loadBranchCurrent().catch(() => {})

    const currentId = operational.branch?.id ?? branches.value[0]?.id ?? null
    const defaultIds = resolveDefaultBranchIds(currentId)

    form.value.branch_id = currentId ?? defaultIds[0] ?? null
    form.value.accessible_branch_ids = [...defaultIds]

    if (!branches.value.length)
      branchesLoadError.value = 'No se pudieron cargar sucursales. Verifique su contexto de empresa.'
  }
  catch (error) {
    branches.value = []
    branchesLoadError.value = getApiErrorMessage(error)
  }
  finally {
    loadingBranches.value = false
  }
}

watch(() => form.value.accessible_branch_ids, ids => {
  if (!hasMultipleBranches.value) {
    form.value.branch_id = ids[0] ?? null

    return
  }

  if (form.value.branch_id != null && !ids.includes(form.value.branch_id))
    form.value.branch_id = ids[0] ?? null
}, { deep: true })

watch(() => props.modelValue, async open => {
  if (open) {
    reset()
    await initBranches()
  }
})

const save = async () => {
  const { valid } = await refForm.value?.validate() ?? { valid: false }
  if (!valid)
    return

  const accessibleIds = hasMultipleBranches.value
    ? form.value.accessible_branch_ids
    : (form.value.branch_id != null ? [form.value.branch_id] : [])

  const mainBranchId = form.value.branch_id ?? accessibleIds[0] ?? null

  if (!mainBranchId || !accessibleIds.length) {
    notify('Seleccione al menos una sucursal', 'error')

    return
  }

  saving.value = true
  try {
    const girl = await quickCreateGirl({
      name: form.value.name.trim(),
      pin: form.value.pin || null,
      notes: form.value.notes || null,
      branch_id: mainBranchId,
      accessible_branch_ids: accessibleIds,
    })
    notify('Chica registrada')
    emit('created', girl)
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
    max-width="520"
    persistent
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <VCardTitle class="d-flex align-center gap-2">
        <VIcon icon="ri-user-add-line" />
        Nueva chica
      </VCardTitle>
      <VCardText>
        <p class="text-body-2 mb-4">
          Indique en qué sucursal puede trabajar la chica.
        </p>

        <VAlert
          v-if="branchesLoadError"
          type="warning"
          variant="tonal"
          class="mb-4"
        >
          {{ branchesLoadError }}
        </VAlert>

        <VForm
          ref="refForm"
          @submit.prevent="save"
        >
          <VTextField
            v-model="form.name"
            label="Nombre *"
            autofocus
            :rules="[v => !!v?.trim() || 'Requerido']"
            class="mb-3"
          />

          <VSelect
            v-if="hasMultipleBranches"
            v-model="form.accessible_branch_ids"
            :items="branchOptions"
            :loading="loadingBranches"
            :disabled="!!branchesLoadError"
            label="Sucursales permitidas *"
            hint="Sucursales donde la chica puede operar"
            persistent-hint
            multiple
            chips
            class="mb-3"
            :rules="[v => Array.isArray(v) && v.length > 0 || 'Seleccione al menos una sucursal']"
          />

          <VSelect
            v-else
            v-model="form.branch_id"
            :items="branchOptions"
            :loading="loadingBranches"
            :disabled="!!branchesLoadError || branchOptions.length === 1"
            label="Sucursal *"
            hint="Sucursal donde trabajará la chica"
            persistent-hint
            class="mb-3"
            :rules="[v => v != null || 'Seleccione sucursal']"
          />

          <VSelect
            v-if="hasMultipleBranches && form.accessible_branch_ids.length > 1"
            v-model="form.branch_id"
            :items="mainBranchOptions"
            label="Sucursal principal *"
            hint="Sucursal base del perfil"
            persistent-hint
            class="mb-3"
            :rules="[v => v != null || 'Seleccione sucursal principal']"
          />

          <VTextField
            v-model="form.pin"
            label="PIN (opcional)"
            placeholder="4 a 6 dígitos"
            maxlength="6"
            hint="Para acceso operativo de la chica"
            persistent-hint
            class="mb-3"
          />
          <VTextarea
            v-model="form.notes"
            label="Observación (opcional)"
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
          :disabled="!!branchesLoadError"
          @click="save"
        >
          Guardar chica
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
