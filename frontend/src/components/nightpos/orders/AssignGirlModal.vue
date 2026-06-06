<script setup>
import QuickGirlCreateDialog from '@/components/nightpos/staff/QuickGirlCreateDialog.vue'
import { itemsNeedingGirl } from '@/composables/useOrderHelpers'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  order: { type: Object, default: null },
  staffUsers: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'confirm', 'staff-updated'])

const { can } = useNightPosPermissions()

const girlAssignments = ref({})
const showQuickGirl = ref(false)
const quickGirlTargetItemId = ref(null)

const pendingItems = computed(() => itemsNeedingGirl(props.order))

const girlSelectItems = computed(() =>
  props.staffUsers.map(u => ({
    title: u.name,
    value: u.id,
  })),
)

watch(() => props.modelValue, open => {
  if (open && props.order)
    girlAssignments.value = Object.fromEntries(pendingItems.value.map(item => [item.id, null]))
})

const close = () => emit('update:modelValue', false)

const openQuickGirl = itemId => {
  quickGirlTargetItemId.value = itemId
  showQuickGirl.value = true
}

const onGirlCreated = girl => {
  if (!girl?.id)
    return

  emit('staff-updated', { id: girl.id, name: girl.name })

  if (quickGirlTargetItemId.value != null)
    girlAssignments.value[quickGirlTargetItemId.value] = girl.id
}

const confirm = () => {
  emit('confirm', { ...girlAssignments.value })
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
      <VCardTitle>Asignar chicas</VCardTitle>
      <VCardText>
        <p class="text-body-2 mb-4">
          Hay productos CON_ACOMPANANTE sin chica asignada. Indique la chica antes de enviar a barra.
        </p>

        <div
          v-for="item in pendingItems"
          :key="item.id"
          class="mb-4"
        >
          <p class="font-weight-medium mb-2">
            {{ item.product_name }}
          </p>
          <VAutocomplete
            v-if="girlSelectItems.length || can('staff.quick_create_girl')"
            v-model="girlAssignments[item.id]"
            :items="girlSelectItems"
            label="Chica *"
            placeholder="Buscar chica..."
            clearable
          >
            <template
              v-if="can('staff.quick_create_girl')"
              #append-item
            >
              <VDivider class="my-2" />
              <VListItem
                prepend-icon="ri-user-add-line"
                title="+ Registrar nueva chica"
                class="text-primary"
                @click="openQuickGirl(item.id)"
              />
            </template>
          </VAutocomplete>
          <template v-else>
            <VTextField
              v-model.number="girlAssignments[item.id]"
              type="number"
              label="ID usuario chica"
              hint="Sin permiso para listar personal"
              persistent-hint
            />
            <VBtn
              v-if="can('staff.quick_create_girl')"
              variant="text"
              size="small"
              prepend-icon="ri-user-add-line"
              class="mt-1 px-0"
              @click="openQuickGirl(item.id)"
            >
              + Nueva chica
            </VBtn>
          </template>
        </div>
      </VCardText>
      <VCardActions>
        <VBtn
          variant="text"
          @click="close"
        >
          Cancelar
        </VBtn>
        <VBtn
          color="info"
          :loading="loading"
          @click="confirm"
        >
          Asignar y enviar
        </VBtn>
      </VCardActions>
    </VCard>

    <QuickGirlCreateDialog
      v-model="showQuickGirl"
      @created="onGirlCreated"
    />
  </VDialog>
</template>
