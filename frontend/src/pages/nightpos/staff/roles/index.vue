<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { STAFF_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import {
  createAdminRole,
  deleteAdminRole,
  fetchAdminRoles,
  fetchManageablePermissions,
  updateAdminRole,
} from '@/api/roles'
import { useRouteDialogCleanup } from '@/composables/useRouteDialogCleanup'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'roles.access' } })

const { can, canCreateRole, canUpdateRole, canDeleteRole, canUpdateRolePermissions } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const router = useRouter()

const activeTab = ref('roles')
const roles = ref([])
const permissionGroups = ref([])
const loading = ref(false)
const loadingPermissions = ref(false)

const showCreateDialog = ref(false)
const showEditDialog = ref(false)
const showDeleteDialog = ref(false)
const saving = ref(false)
const selectedRole = ref(null)

useRouteDialogCleanup(showCreateDialog, showEditDialog, showDeleteDialog)

const form = ref({ name: '', slug: '' })

const headers = [
  { title: 'Rol', key: 'name' },
  { title: 'Slug', key: 'slug' },
  { title: 'Usuarios', key: 'users_count', width: 100 },
  { title: 'Permisos', key: 'permissions_count', width: 110 },
  { title: 'Acciones', key: 'actions', sortable: false, width: 220 },
]

const resetForm = () => {
  form.value = { name: '', slug: '' }
}

const loadRoles = async () => {
  loading.value = true
  try {
    roles.value = await fetchAdminRoles()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const loadPermissions = async () => {
  if (!can('permissions.access'))
    return

  loadingPermissions.value = true
  try {
    permissionGroups.value = await fetchManageablePermissions()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loadingPermissions.value = false
  }
}

const openCreate = () => {
  resetForm()
  showCreateDialog.value = true
}

const openEdit = role => {
  selectedRole.value = role
  form.value = { name: role.name, slug: role.slug }
  showEditDialog.value = true
}

const openDelete = role => {
  selectedRole.value = role
  showDeleteDialog.value = true
}

const saveCreate = async () => {
  saving.value = true
  try {
    await createAdminRole(form.value)
    notify('Rol creado')
    showCreateDialog.value = false
    await loadRoles()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

const saveEdit = async () => {
  if (!selectedRole.value)
    return

  saving.value = true
  try {
    await updateAdminRole(selectedRole.value.id, form.value)
    notify('Rol actualizado')
    showEditDialog.value = false
    await loadRoles()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

const confirmDelete = async () => {
  if (!selectedRole.value)
    return

  saving.value = true
  try {
    await deleteAdminRole(selectedRole.value.id)
    notify('Rol eliminado')
    showDeleteDialog.value = false
    await loadRoles()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

const goPermissions = role => {
  router.push({ name: 'nightpos-staff-roles-id-permissions', params: { id: role.id } })
}

watch(activeTab, tab => {
  if (tab === 'permissions' && permissionGroups.value.length === 0)
    loadPermissions()
})

onMounted(loadRoles)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Roles y permisos"
      subtitle="Gestión operativa de roles del negocio (sin permisos globales SaaS)."
      :breadcrumbs="[
        { title: 'Personal', disabled: true },
        { title: 'Roles y permisos', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          v-if="canCreateRole"
          color="primary"
          size="large"
          @click="openCreate"
        >
          <VIcon
            icon="ri-add-line"
            start
          />
          Nuevo rol
        </VBtn>
      </template>
    </NightPosPageHeader>

    <NightPosSectionTabs :tabs="STAFF_SECTION_TABS" />

    <VTabs
      v-model="activeTab"
      class="mb-4"
    >
      <VTab value="roles">
        Roles
      </VTab>
      <VTab
        value="permissions"
        :disabled="!can('permissions.access')"
      >
        Permisos
      </VTab>
    </VTabs>

    <VWindow v-model="activeTab">
      <VWindowItem value="roles">
        <VProgressLinear
          v-if="loading"
          indeterminate
          class="mb-4"
        />

        <VCard v-else>
          <VDataTable
            :headers="headers"
            :items="roles"
            item-value="id"
          >
            <template #item.slug="{ item }">
              <code>{{ item.slug }}</code>
              <VChip
                v-if="item.is_protected"
                size="x-small"
                color="warning"
                class="ms-2"
                label
              >
                Protegido
              </VChip>
            </template>
            <template #item.actions="{ item }">
              <VBtn
                v-if="canUpdateRolePermissions"
                size="small"
                variant="text"
                color="primary"
                @click="goPermissions(item)"
              >
                Permisos
              </VBtn>
              <VBtn
                v-if="canUpdateRole"
                size="small"
                variant="text"
                @click="openEdit(item)"
              >
                Editar
              </VBtn>
              <VBtn
                v-if="canDeleteRole"
                size="small"
                variant="text"
                color="error"
                :disabled="item.is_protected || item.users_count > 0"
                @click="openDelete(item)"
              >
                Eliminar
              </VBtn>
            </template>
          </VDataTable>
        </VCard>
      </VWindowItem>

      <VWindowItem value="permissions">
        <VProgressLinear
          v-if="loadingPermissions"
          indeterminate
          class="mb-4"
        />

        <VAlert
          v-else
          type="info"
          variant="tonal"
          class="mb-4"
        >
          Catálogo de permisos que puede asignar a roles operativos. Los permisos de plataforma SaaS no aparecen aquí.
        </VAlert>

        <VRow v-if="!loadingPermissions">
          <VCol
            v-for="group in permissionGroups"
            :key="group.key"
            cols="12"
            md="6"
          >
            <VCard>
              <VCardTitle class="text-subtitle-1">
                {{ group.label }}
              </VCardTitle>
              <VCardText>
                <VList density="compact">
                  <VListItem
                    v-for="perm in group.permissions"
                    :key="perm.slug"
                    :title="perm.label"
                    :subtitle="perm.slug"
                  />
                </VList>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VWindowItem>
    </VWindow>

    <VDialog
      v-if="showCreateDialog"
      v-model="showCreateDialog"
      max-width="480"
    >
      <VCard title="Nuevo rol local">
        <VCardText>
          <VTextField
            v-model="form.name"
            label="Nombre"
            class="mb-3"
          />
          <VTextField
            v-model="form.slug"
            label="Slug"
            hint="Solo minúsculas, números, guión y guión bajo"
            persistent-hint
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="showCreateDialog = false"
          >
            Cancelar
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            @click="saveCreate"
          >
            Crear
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-if="showEditDialog"
      v-model="showEditDialog"
      max-width="480"
    >
      <VCard title="Editar rol">
        <VCardText>
          <VTextField
            v-model="form.name"
            label="Nombre"
            class="mb-3"
          />
          <VTextField
            v-model="form.slug"
            label="Slug"
            :disabled="selectedRole?.is_protected"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="showEditDialog = false"
          >
            Cancelar
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            @click="saveEdit"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog
      v-if="showDeleteDialog"
      v-model="showDeleteDialog"
      max-width="420"
    >
      <VCard title="Eliminar rol">
        <VCardText>
          ¿Eliminar el rol <strong>{{ selectedRole?.name }}</strong>? Solo se permite si no tiene usuarios asignados.
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="showDeleteDialog = false"
          >
            Cancelar
          </VBtn>
          <VBtn
            color="error"
            :loading="saving"
            @click="confirmDelete"
          >
            Eliminar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
