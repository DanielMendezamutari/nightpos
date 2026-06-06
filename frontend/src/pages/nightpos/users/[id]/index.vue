<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import { fetchAdminUser } from '@/api/users'
import { STAFF_CHIP_COLOR, STAFF_LABELS } from '@/composables/useUserAdminForm'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    permission: 'admin.users.list',
  },
})

const route = useRoute('nightpos-users-id')
const { canUpdateAdminUser, canCreateAdminUser } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const user = ref(null)
const loading = ref(true)

const userId = computed(() => Number(route.params.id))

const breadcrumbs = computed(() => [
  { title: 'NightPOS', disabled: true },
  { title: 'Usuarios', to: { name: 'nightpos-users' } },
  { title: user.value?.name || 'Detalle', disabled: true },
])

const load = async () => {
  loading.value = true

  try {
    user.value = await fetchAdminUser(userId.value)
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
    user.value = null
  }
  finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      :title="user?.name || 'Usuario'"
      subtitle="Ficha de personal y acceso operativo."
      :breadcrumbs="breadcrumbs"
    >
      <template #actions>
        <VBtn
          v-if="canUpdateAdminUser && user"
          variant="tonal"
          :to="{ name: 'nightpos-users-id-edit', params: { id: userId } }"
        >
          <VIcon
            icon="ri-edit-line"
            start
          />
          Editar
        </VBtn>
        <VBtn
          v-if="canCreateAdminUser && user"
          variant="outlined"
          :to="{ name: 'nightpos-users-id-edit', params: { id: userId }, query: { reset: 'pin' } }"
        >
          Reset PIN
        </VBtn>
      </template>
    </NightPosPageHeader>

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VAlert
      v-else-if="!user"
      type="error"
      variant="tonal"
    >
      Usuario no encontrado.
      <VBtn
        class="ms-2"
        variant="text"
        :to="{ name: 'nightpos-users' }"
      >
        Volver al listado
      </VBtn>
    </VAlert>

    <VRow v-else>
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText class="text-center pt-8">
            <VAvatar
              size="88"
              color="primary"
              variant="tonal"
              rounded="lg"
            >
              <VIcon
                icon="ri-user-line"
                size="44"
              />
            </VAvatar>
            <h5 class="text-h5 mt-4 mb-1">
              {{ user.name }}
            </h5>
            <p class="text-body-2 mb-3">
              @{{ user.username }}
            </p>
            <VChip
              :color="user.status === 'active' ? 'success' : 'secondary'"
              label
            >
              {{ user.status === 'active' ? 'Activo' : 'Inactivo' }}
            </VChip>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        cols="12"
        md="8"
      >
        <VCard title="Información">
          <VCardText>
            <VList lines="two">
              <VListItem title="Email">
                <template #subtitle>
                  {{ user.email || '—' }}
                </template>
              </VListItem>
              <VListItem title="Rol operativo">
                <template #subtitle>
                  <VChip
                    v-if="user.staff_role"
                    size="small"
                    :color="STAFF_CHIP_COLOR[user.staff_role] || 'secondary'"
                  >
                    {{ STAFF_LABELS[user.staff_role] || user.staff_role }}
                  </VChip>
                </template>
              </VListItem>
              <VListItem title="Sucursal principal">
                <template #subtitle>
                  {{ user.branch_name || '—' }}
                </template>
              </VListItem>
              <VListItem
                v-if="user.staff_role === 'WAITER'"
                title="Comisión garzón"
              >
                <template #subtitle>
                  {{ user.waiter_commission_percent }}%
                </template>
              </VListItem>
              <VListItem
                v-if="user.staff_role === 'GIRL'"
                title="Comisiones chica"
              >
                <template #subtitle>
                  {{ user.can_receive_girl_commissions ? 'Sí' : 'No' }}
                </template>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
</div>
</template>
