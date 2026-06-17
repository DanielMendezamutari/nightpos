<script setup>
import { useCashierAccount } from '@/composables/useCashierAccount'

defineProps({
  variant: {
    type: String,
    default: 'panel',
    validator: value => ['panel', 'compact'].includes(value),
  },
})

const {
  displayName,
  roleLabel,
  branchLabel,
  logout,
  switchAccount,
} = useCashierAccount()
</script>

<template>
  <div class="cashier-account">
    <template v-if="variant === 'panel'">
      <p class="text-overline text-medium-emphasis mb-2">
        Cuenta
      </p>

      <VCard
        variant="tonal"
        class="mb-4"
      >
        <VCardText>
          <div class="text-h6 font-weight-medium mb-1">
            {{ displayName }}
          </div>
          <div class="text-body-2 text-medium-emphasis mb-1">
            {{ roleLabel }}
          </div>
          <div class="text-body-2">
            Sucursal: <strong>{{ branchLabel }}</strong>
          </div>
        </VCardText>
      </VCard>

      <VBtn
        block
        variant="tonal"
        color="primary"
        class="mb-2"
        prepend-icon="ri-user-shared-line"
        @click="switchAccount"
      >
        Cambiar cuenta
      </VBtn>

      <VBtn
        block
        variant="outlined"
        color="error"
        prepend-icon="ri-logout-box-r-line"
        @click="logout"
      >
        Cerrar sesión
      </VBtn>
    </template>

    <template v-else>
      <VList density="compact">
        <VListItem
          prepend-icon="ri-user-line"
          :title="displayName"
          :subtitle="`${roleLabel} · ${branchLabel}`"
        />
        <VDivider class="my-1" />
        <VListItem
          prepend-icon="ri-user-shared-line"
          title="Cambiar cuenta"
          @click="switchAccount"
        />
        <VListItem
          prepend-icon="ri-logout-box-r-line"
          title="Cerrar sesión"
          base-color="error"
          @click="logout"
        />
      </VList>
    </template>
  </div>
</template>
