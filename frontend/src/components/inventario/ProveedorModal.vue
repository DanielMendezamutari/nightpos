<script setup>
import { ref, watch } from 'vue'
import { useInventarioStore } from '@/stores/inventario'

const props = defineProps({
  modelValue: Boolean,
  proveedor: Object,
})

const emit = defineEmits(['update:modelValue', 'guardado'])
const store = useInventarioStore()

const form = ref({
  id: null,
  nombre: '',
  razon_social: '',
  nit: '',
  telefono: '',
  celular: '',
  contacto: '',
  direccion: '',
  correo: '',
  banco: '',
  nro_cuenta: '',
  titular_cuenta: '',
})

watch(() => props.modelValue, (val) => {
  if (val) {
    if (props.proveedor && props.proveedor.id) {
      form.value = { ...props.proveedor }
    } else {
      form.value = {
        id: null,
        nombre: '',
        razon_social: '',
        nit: '',
        telefono: '',
        celular: '',
        contacto: '',
        direccion: '',
        correo: '',
        banco: '',
        nro_cuenta: '',
        titular_cuenta: '',
      }
    }
  }
})

const guardar = async () => {
  if (!form.value.nombre) return
  await store.guardarProveedor(form.value)
  emit('update:modelValue', false)
  emit('guardado')
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="650px"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <VCardTitle class="d-flex align-center justify-space-between bg-primary text-white pa-4">
        <span class="text-h6 font-weight-bold">
          <VIcon icon="ri-truck-line" class="me-2" />
          {{ form.id ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
        </span>
        <VBtn icon="ri-close-line" variant="text" color="white" density="comfortable" @click="emit('update:modelValue', false)" />
      </VCardTitle>

      <VCardText class="pa-4">
        <VRow>
          <VCol cols="12" md="6">
            <VTextField v-model="form.nombre" label="Nombre Comercial *" placeholder="Distribuidora..." density="compact" variant="outlined" />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField v-model="form.razon_social" label="RazÃ³n Social Fiscal" density="compact" variant="outlined" />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField v-model="form.nit" label="NIT / CI" density="compact" variant="outlined" />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField v-model="form.telefono" label="TelÃ©fono / Celular" density="compact" variant="outlined" />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField v-model="form.contacto" label="Persona de Contacto" density="compact" variant="outlined" />
          </VCol>
          <VCol cols="12">
            <VTextField v-model="form.direccion" label="DirecciÃ³n / DepÃ³sito" density="compact" variant="outlined" />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField v-model="form.banco" label="Banco" placeholder="BNB, Mercantil..." density="compact" variant="outlined" />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField v-model="form.nro_cuenta" label="Nro. de Cuenta" density="compact" variant="outlined" />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField v-model="form.titular_cuenta" label="Titular de Cuenta" density="compact" variant="outlined" />
          </VCol>
        </VRow>
      </VCardText>

      <VCardActions class="pa-4 pt-0">
        <VSpacer />
        <VBtn variant="outlined" color="secondary" @click="emit('update:modelValue', false)">Cancelar</VBtn>
        <VBtn color="primary" variant="elevated" @click="guardar">
          <VIcon icon="ri-save-line" class="me-1" />
          Guardar Proveedor
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>