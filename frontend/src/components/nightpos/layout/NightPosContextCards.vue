<script setup>
import { useOperationalStore } from '@/stores/operational'
import { usePlatformContext } from '@/composables/usePlatformContext'
import { useAuthStore } from '@/stores/auth'

const operational = useOperationalStore()
const { contextLabel, hasFullContext, isSuperAdmin, needsBranchSelection } = usePlatformContext()
const auth = useAuthStore()
</script>

<template>
  <VAlert
    v-if="needsBranchSelection"
    type="warning"
    variant="tonal"
    class="mb-4"
  >
    Seleccione sucursal en la barra superior para operar en este local.
  </VAlert>

  <VRow class="mb-4">
    <VCol
      cols="12"
      sm="6"
      md="4"
    >
      <VCard variant="tonal">
        <VCardText class="d-flex align-center gap-3">
          <VAvatar
            color="primary"
            variant="tonal"
            rounded="lg"
          >
            <VIcon icon="ri-building-line" />
          </VAvatar>
          <div>
            <p class="text-caption mb-0">
              Empresa
            </p>
            <p class="text-body-1 font-weight-medium mb-0">
              {{ operational.tenant?.name || (isSuperAdmin ? 'Modo global' : '—') }}
            </p>
          </div>
        </VCardText>
      </VCard>
    </VCol>
    <VCol
      cols="12"
      sm="6"
      md="4"
    >
      <VCard variant="tonal">
        <VCardText class="d-flex align-center gap-3">
          <VAvatar
            color="secondary"
            variant="tonal"
            rounded="lg"
          >
            <VIcon icon="ri-store-2-line" />
          </VAvatar>
          <div>
            <p class="text-caption mb-0">
              Sucursal
            </p>
            <p class="text-body-1 font-weight-medium mb-0">
              {{ operational.branch?.name || operational.branch?.code || (hasFullContext ? '—' : 'Sin sucursal') }}
            </p>
          </div>
        </VCardText>
      </VCard>
    </VCol>
    <VCol
      cols="12"
      sm="6"
      md="4"
    >
      <VCard variant="tonal">
        <VCardText class="d-flex align-center gap-3">
          <VAvatar
            color="info"
            variant="tonal"
            rounded="lg"
          >
            <VIcon icon="ri-user-line" />
          </VAvatar>
          <div>
            <p class="text-caption mb-0">
              Sesión
            </p>
            <p class="text-body-1 font-weight-medium mb-0">
              {{ auth.user?.name || '—' }}
            </p>
            <p class="text-caption mb-0">
              {{ contextLabel }}
            </p>
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
