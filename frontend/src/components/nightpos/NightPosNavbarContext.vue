<script setup>

import PlatformContextSelector from '@/components/nightpos/PlatformContextSelector.vue'

import { fetchCurrentCashSession } from '@/api/cash'

import { fetchCurrentShift } from '@/api/shifts'

import { useOnContextChange } from '@/composables/useOnContextChange'

import { usePlatformContext } from '@/composables/usePlatformContext'

import { useAuthStore } from '@/stores/auth'

import { useOperationalStore } from '@/stores/operational'



const auth = useAuthStore()

const operational = useOperationalStore()

const { isSuperAdmin, hasTenantContext, needsBranchSelection } = usePlatformContext()



const cashSession = ref(null)

const cashLoading = ref(false)

const currentShift = ref(null)

const shiftLoading = ref(false)



const roleLabel = computed(() => {

  const role = auth.user?.role

  const staff = auth.user?.staff_role



  if (role && staff)

    return `${role} · ${staff}`



  return role || staff || '—'

})



const loadCash = async () => {

  if (!auth.hasPermission('cash.access'))

    return



  cashLoading.value = true



  try {

    cashSession.value = await fetchCurrentCashSession()

  }

  catch {

    cashSession.value = null

  }

  finally {

    cashLoading.value = false

  }

}



const loadShift = async () => {

  if (!auth.hasPermission('shifts.access'))

    return



  shiftLoading.value = true



  try {

    currentShift.value = await fetchCurrentShift()

  }

  catch {

    currentShift.value = null

  }

  finally {

    shiftLoading.value = false

  }

}



const refreshNavbarData = async () => {

  if (!operational.tenant && (hasTenantContext.value || !isSuperAdmin.value))

    await operational.refreshContext().catch(() => {})



  await Promise.all([loadCash(), loadShift()])

}



onMounted(() => {

  refreshNavbarData()

})



useOnContextChange(() => {

  refreshNavbarData()

})



watch(() => auth.user?.id, () => {

  refreshNavbarData()

})

</script>



<template>

  <div class="nightpos-navbar-context d-flex align-center gap-1 gap-sm-2 flex-wrap overflow-x-auto py-1">

    <PlatformContextSelector v-if="isSuperAdmin" />



    <VChip

      v-if="needsBranchSelection"

      size="small"

      color="warning"

      variant="tonal"

      prepend-icon="ri-alert-line"

    >

      Seleccione sucursal

    </VChip>



    <VChip

      v-if="operational.tenant?.name"

      size="small"

      color="primary"

      variant="tonal"

      prepend-icon="ri-building-line"

    >

      {{ operational.tenant.name }}

    </VChip>



    <VChip

      v-if="operational.branch?.name || operational.branch?.code"

      size="small"

      color="secondary"

      variant="tonal"

      prepend-icon="ri-store-2-line"

    >

      {{ operational.branch?.name || operational.branch.code }}

    </VChip>



    <VChip

      size="small"

      :color="cashSession?.status === 'OPEN' ? 'success' : 'default'"

      variant="tonal"

      prepend-icon="ri-cash-line"

    >

      <template v-if="cashLoading">

        Caja…

      </template>

      <template v-else-if="cashSession?.status === 'OPEN'">

        Caja #{{ cashSession.id }} abierta

      </template>

      <template v-else-if="auth.hasPermission('cash.access')">

        Sin caja abierta

      </template>

      <template v-else>

        Caja N/A

      </template>

    </VChip>



    <VChip

      size="small"

      :color="currentShift?.status === 'OPEN' ? 'info' : 'default'"

      variant="tonal"

      prepend-icon="ri-time-line"

    >

      <template v-if="shiftLoading">

        Turno…

      </template>

      <template v-else-if="currentShift?.status === 'OPEN'">

        {{ currentShift.shift_type_label }} · {{ currentShift.business_date }}

        <span
          v-if="currentShift.auto_created"
          class="text-caption"
        > (auto)</span>

      </template>

      <template v-else-if="auth.hasPermission('shifts.access')">

        Sin turno abierto

      </template>

      <template v-else>

        Turno N/A

      </template>

    </VChip>



    <VChip

      size="small"

      variant="tonal"

      prepend-icon="ri-user-line"

    >

      {{ auth.user?.name || '—' }}

    </VChip>



    <VChip

      size="small"

      variant="outlined"

      prepend-icon="ri-shield-user-line"

    >

      {{ roleLabel }}

    </VChip>

  </div>

</template>



<style scoped>

.nightpos-navbar-context {

  max-inline-size: 55vw;

}

</style>

