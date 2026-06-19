<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import {
  fetchPrintDevices,
  fetchPrintJobs,
  fetchPrintSettings,
  registerPrintDevice,
  rotatePrintDeviceKey,
  updatePrintDevice,
  updatePrintSettings,
} from '@/api/printDevices'
import { SETTINGS_SECTION_TABS } from '@/composables/useSettingsSectionTabs'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'settings.printers' } })

const { can } = useNightPosPermissions()
const { notify } = useNightPosNotify()

const loading = ref(false)
const saving = ref(false)
const devices = ref([])
const jobs = ref([])
const autoPrint = ref(true)
const newDeviceName = ref('PC Barra')
const revealedKey = ref('')
const revealedKeyDevice = ref('')

const deviceHeaders = [
  { title: 'Nombre', key: 'name' },
  { title: 'Estado', key: 'online' },
  { title: 'Impresora', key: 'printer_name' },
  { title: 'Última conexión', key: 'last_seen_at' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const jobHeaders = [
  { title: 'ID', key: 'id' },
  { title: 'Tipo', key: 'type' },
  { title: 'Origen', key: 'source_id' },
  { title: 'Estado', key: 'status' },
  { title: 'Error', key: 'last_error' },
  { title: 'Creado', key: 'created_at' },
]

const formatDateTime = value => {
  if (!value)
    return '—'
  try {
    return new Date(value).toLocaleString('es-BO', { dateStyle: 'short', timeStyle: 'short' })
  }
  catch {
    return value
  }
}

const load = async () => {
  loading.value = true
  try {
    const settings = await fetchPrintSettings()
    autoPrint.value = settings.auto_print_order_command ?? true
    devices.value = settings.devices ?? await fetchPrintDevices()
    jobs.value = await fetchPrintJobs({ limit: 30 })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const saveAutoPrint = async () => {
  saving.value = true
  try {
    await updatePrintSettings({ auto_print_order_command: autoPrint.value })
    notify('Configuración guardada')
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

const registerDevice = async () => {
  if (!newDeviceName.value.trim()) {
    notify('Indique el nombre del dispositivo', 'warning')
    return
  }
  saving.value = true
  try {
    const result = await registerPrintDevice({ name: newDeviceName.value.trim(), paper_width_mm: 80 })
    revealedKey.value = result.device_key
    revealedKeyDevice.value = result.device?.name ?? newDeviceName.value
    notify('Dispositivo registrado')
    newDeviceName.value = 'PC Barra'
    await load()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

const copyKey = async () => {
  if (!revealedKey.value)
    return
  try {
    await navigator.clipboard.writeText(revealedKey.value)
    notify('Clave copiada')
  }
  catch {
    notify('No se pudo copiar automáticamente', 'warning')
  }
}

const toggleDevice = async device => {
  saving.value = true
  try {
    await updatePrintDevice(device.id, { enabled: !device.enabled })
    notify('Dispositivo actualizado')
    await load()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    saving.value = false
  }
}

const rotateKey = async device => {
  saving.value = true
  try {
    const result = await rotatePrintDeviceKey(device.id)
    revealedKey.value = result.device_key
    revealedKeyDevice.value = device.name
    notify('Clave rotada')
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
      title="Impresoras"
      subtitle="Agente local de impresión por sucursal (USB vía PC barra)."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Configuración', disabled: true },
        { title: 'Impresoras', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="SETTINGS_SECTION_TABS" />

    <VAlert
      type="info"
      variant="tonal"
      class="mb-4"
    >
      Instale el agente Node.js en la PC del local y configure la <strong>device_key</strong>.
      El garzón comanda desde el celular; la impresión ocurre en la impresora USB de esta sucursal.
    </VAlert>

    <VRow>
      <VCol
        cols="12"
        md="4"
      >
        <VCard class="mb-4">
          <VCardTitle>Auto impresión comanda</VCardTitle>
          <VCardText>
            <VSwitch
              v-model="autoPrint"
              label="Imprimir al enviar a barra"
              :disabled="!can('settings.printers.manage') || saving"
              @update:model-value="saveAutoPrint"
            />
          </VCardText>
        </VCard>

        <VCard v-if="can('settings.printers.manage')">
          <VCardTitle>Registrar dispositivo</VCardTitle>
          <VCardText>
            <VTextField
              v-model="newDeviceName"
              label="Nombre (ej. PC Barra Centro)"
              class="mb-3"
            />
            <VBtn
              color="primary"
              :loading="saving"
              @click="registerDevice"
            >
              Generar device_key
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="8"
      >
        <VAlert
          v-if="revealedKey"
          type="warning"
          variant="tonal"
          class="mb-4"
          prominent
        >
          <div class="text-subtitle-2 mb-2">
            Clave para {{ revealedKeyDevice }} — copie ahora (solo se muestra una vez)
          </div>
          <code class="d-block mb-3 user-select-all">{{ revealedKey }}</code>
          <VBtn
            size="small"
            variant="tonal"
            @click="copyKey"
          >
            Copiar clave
          </VBtn>
        </VAlert>

        <VCard class="mb-4">
          <VCardTitle>Dispositivos de sucursal</VCardTitle>
          <VDataTable
            :headers="deviceHeaders"
            :items="devices"
            :loading="loading"
            density="compact"
            items-per-page="10"
          >
            <template #item.online="{ item }">
              <VChip
                :color="item.online ? 'success' : 'default'"
                size="small"
                variant="tonal"
              >
                {{ item.online ? 'Online' : 'Offline' }}
              </VChip>
            </template>
            <template #item.printer_name="{ item }">
              {{ item.printer_name || '—' }}
            </template>
            <template #item.last_seen_at="{ item }">
              {{ formatDateTime(item.last_seen_at) }}
            </template>
            <template #item.actions="{ item }">
              <template v-if="can('settings.printers.manage')">
                <VBtn
                  size="x-small"
                  variant="text"
                  @click="toggleDevice(item)"
                >
                  {{ item.enabled ? 'Desactivar' : 'Activar' }}
                </VBtn>
                <VBtn
                  size="x-small"
                  variant="text"
                  @click="rotateKey(item)"
                >
                  Rotar clave
                </VBtn>
              </template>
            </template>
          </VDataTable>
        </VCard>

        <VCard>
          <VCardTitle>Historial de impresión</VCardTitle>
          <VDataTable
            :headers="jobHeaders"
            :items="jobs"
            :loading="loading"
            density="compact"
            items-per-page="15"
          >
            <template #item.status="{ item }">
              <VChip
                :color="item.status === 'PRINTED' ? 'success' : item.status === 'FAILED' ? 'error' : 'warning'"
                size="small"
                variant="tonal"
              >
                {{ item.status }}
              </VChip>
            </template>
            <template #item.last_error="{ item }">
              {{ item.last_error || '—' }}
            </template>
            <template #item.created_at="{ item }">
              {{ formatDateTime(item.created_at) }}
            </template>
          </VDataTable>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
