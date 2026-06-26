<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import {
  fetchPlatformOperationsChecklist,
  fetchPlatformOperationsTechnicalProfile,
  fetchPlatformOperationsTenant,
  patchPlatformOperationsChecklistItem,
  updatePlatformOperationsTechnicalProfile,
} from '@/api/platformOperations'
import { PLATFORM_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { formatMoney } from '@/composables/useOrderHelpers'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    permissions: ['platform.operations.view', 'admin.tenants.list'],
  },
})

const route = useRoute()
const { notify } = useNightPosNotify()
const tenantId = computed(() => Number(route.params.id))

const tab = ref('summary')
const loading = ref(true)
const detail = ref(null)
const checklist = ref([])
const technicalProfile = ref(null)
const savingProfile = ref(false)

const STATUS_COLORS = {
  ONLINE: 'success',
  WARNING: 'warning',
  DEGRADED: 'orange',
  OFFLINE: 'secondary',
  CRITICAL: 'error',
}

const profileForm = ref({
  primary_pc_name: '',
  operating_system: '',
  ram: '',
  printer_model: '',
  printer_connection_type: '',
  remote_support_tool: '',
  remote_support_id: '',
  installer_name: '',
  installation_notes: '',
})

const load = async () => {
  loading.value = true
  try {
    detail.value = await fetchPlatformOperationsTenant(tenantId.value)
    const checklistData = await fetchPlatformOperationsChecklist(tenantId.value)
    checklist.value = checklistData.items ?? detail.value.installation_checklist ?? []

    const profileData = await fetchPlatformOperationsTechnicalProfile(tenantId.value)
    technicalProfile.value = profileData.profile
    if (profileData.profile) {
      profileForm.value = {
        primary_pc_name: profileData.profile.primary_pc_name ?? '',
        operating_system: profileData.profile.operating_system ?? '',
        ram: profileData.profile.ram ?? '',
        printer_model: profileData.profile.printer_model ?? '',
        printer_connection_type: profileData.profile.printer_connection_type ?? '',
        remote_support_tool: profileData.profile.remote_support_tool ?? '',
        remote_support_id: profileData.profile.remote_support_id ?? '',
        installer_name: profileData.profile.installer_name ?? '',
        installation_notes: profileData.profile.installation_notes ?? '',
      }
    }
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const toggleChecklistItem = async item => {
  try {
    const result = await patchPlatformOperationsChecklistItem(tenantId.value, item.key, {
      completed: !item.completed,
    })
    const updated = result.item
    const idx = checklist.value.findIndex(i => i.key === item.key)
    if (idx >= 0)
      checklist.value[idx] = updated
    notify('Checklist actualizado')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
}

const saveProfile = async () => {
  savingProfile.value = true
  try {
    const result = await updatePlatformOperationsTechnicalProfile(tenantId.value, profileForm.value)
    technicalProfile.value = result.profile
    notify('Perfil técnico guardado')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    savingProfile.value = false
  }
}

onMounted(load)
watch(tenantId, load)
</script>

<template>
  <div>
    <NightPosPageHeader
      :title="detail?.summary?.tenant_name ?? 'Cliente'"
      subtitle="Detalle operativo del tenant."
      :breadcrumbs="[
        { title: 'Plataforma', disabled: true },
        { title: 'Control Center', to: { name: 'nightpos-platform-control-center' } },
        { title: 'Clientes', to: { name: 'nightpos-platform-control-center-tenants' } },
        { title: detail?.summary?.tenant_name ?? 'Detalle', disabled: true },
      ]"
    />

    <NightPosSectionTabs :tabs="PLATFORM_SECTION_TABS" />

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <template v-else-if="detail">
      <VCard class="mb-4">
        <VCardText class="d-flex flex-wrap align-center gap-4">
          <VChip
            :color="STATUS_COLORS[detail.summary.operational_status] ?? 'default'"
          >
            {{ detail.summary.operational_status }}
          </VChip>
          <span>Health {{ detail.summary.health_score }}%</span>
          <span>Plan: {{ detail.summary.plan ?? '—' }}</span>
          <span>Ventas hoy: {{ formatMoney(detail.summary.sales_today ?? 0) }} BOB</span>
        </VCardText>
      </VCard>

      <VTabs
        v-model="tab"
        class="mb-4"
      >
        <VTab value="summary">
          Resumen
        </VTab>
        <VTab value="branches">
          Sucursales
        </VTab>
        <VTab value="agents">
          Agentes
        </VTab>
        <VTab value="checklist">
          Checklist
        </VTab>
        <VTab value="technical">
          Perfil técnico
        </VTab>
        <VTab value="issues">
          Incidencias
        </VTab>
      </VTabs>

      <VWindow v-model="tab">
        <VWindowItem value="summary">
          <VRow>
            <VCol
              cols="12"
              md="4"
            >
              <VCard>
                <VCardTitle>Operación</VCardTitle>
                <VCardText>
                  <div>Sucursales: {{ detail.summary.branches_count }}</div>
                  <div>Usuarios: {{ detail.summary.users_count }}</div>
                  <div>Comandas hoy: {{ detail.summary.orders_today }}</div>
                  <div>Print jobs hoy: {{ detail.summary.print_jobs_today }}</div>
                  <div>Ventas 7 días: {{ formatMoney(detail.summary.sales_last_7_days ?? 0) }} BOB</div>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>
        </VWindowItem>

        <VWindowItem value="branches">
          <VDataTable
            :headers="[
              { title: 'Sucursal', key: 'branch_name' },
              { title: 'Caja abierta', key: 'open_cash_session' },
              { title: 'Turno abierto', key: 'open_shift' },
              { title: 'Ventas hoy', key: 'sales_today' },
              { title: 'Comandas hoy', key: 'orders_today' },
              { title: 'Impresoras', key: 'print_devices' },
              { title: 'Agente', key: 'agent_version' },
            ]"
            :items="detail.branches ?? []"
            item-value="branch_id"
          >
            <template #item.open_cash_session="{ item }">
              <VIcon :icon="item.open_cash_session ? 'ri-checkbox-circle-fill' : 'ri-close-circle-line'" />
            </template>
            <template #item.open_shift="{ item }">
              <VIcon :icon="item.open_shift ? 'ri-checkbox-circle-fill' : 'ri-close-circle-line'" />
            </template>
            <template #item.sales_today="{ item }">
              {{ formatMoney(item.sales_today ?? 0) }} BOB
            </template>
            <template #item.print_devices="{ item }">
              <span v-if="!item.has_registered_agent">Sin agente</span>
              <span v-else>{{ item.print_devices_online }} online / {{ item.print_devices_offline }} offline</span>
            </template>
            <template #item.agent_version="{ item }">
              {{ item.agent_version ?? '—' }}
            </template>
          </VDataTable>
        </VWindowItem>

        <VWindowItem value="agents">
          <VDataTable
            :headers="[
              { title: 'Nombre', key: 'name' },
              { title: 'Sucursal', key: 'branch_name' },
              { title: 'Online', key: 'online' },
              { title: 'Versión', key: 'agent_version' },
              { title: 'Último seen', key: 'last_seen_at' },
              { title: 'Error', key: 'last_error' },
            ]"
            :items="detail.print_agents ?? []"
            item-value="id"
          >
            <template #item.online="{ item }">
              <VChip
                size="small"
                :color="item.online ? 'success' : 'error'"
              >
                {{ item.online ? 'Online' : 'Offline' }}
              </VChip>
            </template>
            <template #item.last_seen_at="{ item }">
              {{ item.last_seen_at ? new Date(item.last_seen_at).toLocaleString() : '—' }}
            </template>
          </VDataTable>
        </VWindowItem>

        <VWindowItem value="checklist">
          <VList>
            <VListItem
              v-for="item in checklist"
              :key="item.key"
              :title="item.label"
              :subtitle="item.notes"
            >
              <template #prepend>
                <VCheckbox
                  :model-value="item.completed"
                  hide-details
                  @update:model-value="() => toggleChecklistItem(item)"
                />
              </template>
            </VListItem>
          </VList>
        </VWindowItem>

        <VWindowItem value="technical">
          <VCard>
            <VCardText>
              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="profileForm.primary_pc_name"
                    label="PC principal"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="profileForm.operating_system"
                    label="Sistema operativo"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="4"
                >
                  <VTextField
                    v-model="profileForm.ram"
                    label="RAM"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="4"
                >
                  <VTextField
                    v-model="profileForm.printer_model"
                    label="Modelo impresora"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="4"
                >
                  <VTextField
                    v-model="profileForm.printer_connection_type"
                    label="Conexión impresora"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="4"
                >
                  <VTextField
                    v-model="profileForm.remote_support_tool"
                    label="Herramienta soporte remoto"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="4"
                >
                  <VTextField
                    v-model="profileForm.remote_support_id"
                    label="ID soporte remoto"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="4"
                >
                  <VTextField
                    v-model="profileForm.installer_name"
                    label="Instalador"
                  />
                </VCol>
                <VCol cols="12">
                  <VTextarea
                    v-model="profileForm.installation_notes"
                    label="Notas instalación"
                    rows="3"
                  />
                </VCol>
              </VRow>
              <VBtn
                color="primary"
                :loading="savingProfile"
                @click="saveProfile"
              >
                Guardar perfil técnico
              </VBtn>
            </VCardText>
          </VCard>
        </VWindowItem>

        <VWindowItem value="issues">
          <VList v-if="detail.issues?.length">
            <VListItem
              v-for="(issue, idx) in detail.issues"
              :key="idx"
              :title="issue.message"
              :subtitle="issue.type"
            >
              <template #prepend>
                <VIcon
                  icon="ri-alert-line"
                  :color="issue.severity === 'critical' ? 'error' : 'warning'"
                />
              </template>
            </VListItem>
          </VList>
          <VAlert
            v-else
            type="success"
            variant="tonal"
            text="Sin incidencias detectadas."
          />
        </VWindowItem>
      </VWindow>
    </template>
  </div>
</template>
