<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { STAFF_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import {
  fetchAdminRole,
  fetchManageablePermissions,
  updateAdminRolePermissions,
} from '@/api/roles'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'roles.permissions.update' } })

const route = useRoute('nightpos-staff-roles-id-permissions')
const router = useRouter()
const { notify } = useNightPosNotify()

const roleId = computed(() => Number(route.params.id))
const role = ref(null)
const groups = ref([])
const selected = ref(new Set())
const loading = ref(true)
const saving = ref(false)

const breadcrumbs = computed(() => [
  { title: 'Personal', disabled: true },
  { title: 'Roles', to: { name: 'nightpos-staff-roles' } },
  { title: role.value?.name || 'Permisos', disabled: true },
])

const load = async () => {
  loading.value = true
  try {
    const [roleData, permissionGroups] = await Promise.all([
      fetchAdminRole(roleId.value),
      fetchManageablePermissions(),
    ])

    role.value = roleData
    groups.value = permissionGroups

    const manageable = new Set(
      permissionGroups.flatMap(g => g.permissions.map(p => p.slug)),
    )

    selected.value = new Set(
      (roleData.permissions ?? []).filter(slug => manageable.has(slug)),
    )
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
    await router.push({ name: 'nightpos-staff-roles' })
  }
  finally {
    loading.value = false
  }
}

const isChecked = slug => selected.value.has(slug)

const toggle = (slug, value) => {
  const next = new Set(selected.value)
  if (value)
    next.add(slug)
  else
    next.delete(slug)
  selected.value = next
}

const save = async () => {
  saving.value = true
  try {
    await updateAdminRolePermissions(roleId.value, [...selected.value])
    notify('Permisos guardados')
    await load()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      :title="role ? `Permisos — ${role.name}` : 'Permisos del rol'"
      :subtitle="role ? `Slug: ${role.slug}` : ''"
      :breadcrumbs="breadcrumbs"
    >
      <template #actions>
        <VBtn
          variant="tonal"
          :to="{ name: 'nightpos-staff-roles' }"
        >
          Volver
        </VBtn>
        <VBtn
          color="primary"
          size="large"
          :loading="saving"
          @click="save"
        >
          Guardar
        </VBtn>
      </template>
    </NightPosPageHeader>

    <NightPosSectionTabs :tabs="STAFF_SECTION_TABS" />

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <template v-else>
      <VAlert
        type="info"
        variant="tonal"
        class="mb-4"
      >
        Active solo los permisos operativos necesarios para este rol. Los permisos de plataforma no se muestran ni se pueden asignar desde aquí.
      </VAlert>

      <VRow>
        <VCol
          v-for="group in groups"
          :key="group.key"
          cols="12"
          md="6"
          lg="4"
        >
          <VCard>
            <VCardTitle class="text-subtitle-1">
              {{ group.label }}
            </VCardTitle>
            <VCardText>
              <div
                v-for="perm in group.permissions"
                :key="perm.slug"
                class="d-flex align-center justify-space-between py-2"
              >
                <div>
                  <div class="text-body-2 font-weight-medium">
                    {{ perm.label }}
                  </div>
                  <div class="text-caption text-medium-emphasis">
                    {{ perm.slug }}
                  </div>
                </div>
                <VSwitch
                  :model-value="isChecked(perm.slug)"
                  color="primary"
                  hide-details
                  density="compact"
                  @update:model-value="toggle(perm.slug, $event)"
                />
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </template>
  </div>
</template>
