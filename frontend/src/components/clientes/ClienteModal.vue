<script setup>
import { ref, watch } from 'vue'
import { useClientesStore } from '@/stores/clientes'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  cliente: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue', 'guardado'])

const clientesStore = useClientesStore()

const isEdit = ref(false)
const processing = ref(false)
const errorMessage = ref('')

const form = ref({
  nombre: '',
  apellidos: '',
  ci_nit: '0',
  tipo_documento: 'NIT',
  razon_social: '',
  celular: '',
  telefono: '',
  correo: '',
  direccion: '',
  cumpleanos: '',
  descuento_porcentaje: 0,
  limite_credito: 0,
  permite_credito: false,
  comentarios: '',
})

watch(() => props.modelValue, (val) => {
  if (val) {
    errorMessage.value = ''
    if (props.cliente) {
      isEdit.value = true
      form.value = {
        nombre: props.cliente.nombre || '',
        apellidos: props.cliente.apellidos || '',
        ci_nit: props.cliente.ci_nit || '0',
        tipo_documento: props.cliente.tipo_documento || 'NIT',
        razon_social: props.cliente.razon_social || props.cliente.nombre || '',
        celular: props.cliente.celular || '',
        telefono: props.cliente.telefono || '',
        correo: props.cliente.correo || '',
        direccion: props.cliente.direccion || '',
        cumpleanos: props.cliente.cumpleanos || '',
        descuento_porcentaje: parseFloat(props.cliente.descuento_porcentaje) || 0,
        limite_credito: parseFloat(props.cliente.limite_credito) || 0,
        permite_credito: !!props.cliente.permite_credito,
        comentarios: props.cliente.comentarios || '',
      }
    } else {
      isEdit.value = false
      form.value = {
        nombre: '',
        apellidos: '',
        ci_nit: '0',
        tipo_documento: 'NIT',
        razon_social: '',
        celular: '',
        telefono: '',
        correo: '',
        direccion: '',
        cumpleanos: '',
        descuento_porcentaje: 0,
        limite_credito: 0,
        permite_credito: false,
        comentarios: '',
      }
    }
  }
})

const close = () => {
  emit('update:modelValue', false)
}

const submit = async () => {
  if (!form.value.nombre.trim()) {
    errorMessage.value = 'El nombre del cliente es obligatorio'
    return
  }

  processing.value = true
  errorMessage.value = ''

  const payload = {
    ...form.value,
    razon_social: form.value.razon_social || form.value.nombre,
  }

  let res
  if (isEdit.value && props.cliente?.id) {
    res = await clientesStore.actualizarCliente(props.cliente.id, payload)
  } else {
    res = await clientesStore.crearCliente(payload)
  }

  processing.value = false

  if (res.success) {
    emit('update:modelValue', false)
    emit('guardado', res.data)
  } else {
    errorMessage.value = res.message || 'Error al guardar cliente'
  }
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="800"
    persistent
    scrollable
  >
    <VCard class="cliente-modal-card">
      <VCardItem class="bg-primary text-white py-3 px-4">
        <div class="d-flex align-center justify-space-between w-100">
          <div class="d-flex align-center gap-2">
            <VIcon icon="ri-user-add-line" size="26" />
            <span class="text-h6 font-weight-black text-uppercase">
              {{ isEdit ? 'Editar Ficha de Cliente' : 'Registrar Nuevo Cliente' }}
            </span>
          </div>
          <VBtn
            icon="ri-close-line"
            variant="text"
            color="white"
            density="comfortable"
            @click="close"
          />
        </div>
      </VCardItem>

      <VCardText class="pa-4 bg-surface">
        <VAlert
          v-if="errorMessage"
          type="error"
          variant="tonal"
          class="mb-3"
          closable
          @click:close="errorMessage = ''"
        >
          {{ errorMessage }}
        </VAlert>

        <VRow>
          <!-- Datos Personales -->
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.nombre"
              label="Nombre *"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="ri-user-line"
              placeholder="Ej: Carlos"
              autofocus
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.apellidos"
              label="Apellidos"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="ri-user-follow-line"
              placeholder="Ej: Mendoza Roca"
            />
          </VCol>

          <!-- Datos Fiscales SIAT -->
          <VCol cols="12" md="4">
            <VSelect
              v-model="form.tipo_documento"
              label="Tipo Documento"
              :items="['NIT', 'CI', 'CEX', 'PASAPORTE']"
              variant="outlined"
              density="comfortable"
            />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField
              v-model="form.ci_nit"
              label="NÂ° NIT / CI"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="ri-hashtag"
              placeholder="0 o NIT"
            />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField
              v-model="form.razon_social"
              label="RazÃ³n Social (Factura)"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="ri-building-line"
              placeholder="Nombre fiscal"
            />
          </VCol>

          <!-- Contacto -->
          <VCol cols="12" md="4">
            <VTextField
              v-model="form.celular"
              label="Celular / WhatsApp"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="ri-phone-line"
              placeholder="Ej: 77348912"
            />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField
              v-model="form.correo"
              label="Correo ElectrÃ³nico"
              type="email"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="ri-mail-line"
              placeholder="cliente@ejemplo.com"
            />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField
              v-model="form.cumpleanos"
              label="Fecha de CumpleaÃ±os"
              type="date"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="ri-cake-2-line"
            />
          </VCol>

          <!-- DirecciÃ³n -->
          <VCol cols="12">
            <VTextField
              v-model="form.direccion"
              label="DirecciÃ³n de Domicilio / Oficina"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="ri-map-pin-line"
              placeholder="Av. Principal #123"
            />
          </VCol>

          <!-- PolÃ­ticas Comerciales y CrÃ©dito (RestoTech _MaxDeuda, _Descuento) -->
          <VCol cols="12">
            <VCard variant="outlined" class="pa-3 bg-surface-variant">
              <div class="text-subtitle-2 font-weight-black text-primary mb-2">
                POLÃTICAS DE CRÃ‰DITO Y DESCUENTO (RESTOTECH PARITY)
              </div>
              <VRow>
                <VCol cols="12" md="4">
                  <VCheckbox
                    v-model="form.permite_credito"
                    label="Permitir Cuenta Corriente / CrÃ©dito"
                    color="primary"
                    density="compact"
                    hide-details
                    class="font-weight-bold"
                  />
                </VCol>
                <VCol cols="12" md="4">
                  <VTextField
                    v-model.number="form.limite_credito"
                    label="LÃ­mite MÃ¡ximo de CrÃ©dito (Bs.)"
                    type="number"
                    variant="outlined"
                    density="compact"
                    prefix="Bs."
                    :disabled="!form.permite_credito"
                  />
                </VCol>
                <VCol cols="12" md="4">
                  <VTextField
                    v-model.number="form.descuento_porcentaje"
                    label="Descuento Fijo Preferencial (%)"
                    type="number"
                    variant="outlined"
                    density="compact"
                    suffix="%"
                    min="0"
                    max="100"
                  />
                </VCol>
              </VRow>
            </VCard>
          </VCol>

          <!-- Comentarios -->
          <VCol cols="12">
            <VTextarea
              v-model="form.comentarios"
              label="Observaciones y Preferencias GastronÃ³micas"
              rows="2"
              variant="outlined"
              density="comfortable"
              placeholder="Alergias, mesa favorita, vino de preferencia..."
            />
          </VCol>
        </VRow>
      </VCardText>

      <VCardActions class="pa-4 bg-surface-variant d-flex justify-space-between align-center">
        <VBtn
          variant="outlined"
          color="secondary"
          @click="close"
        >
          Cancelar
        </VBtn>
        <VBtn
          color="primary"
          variant="flat"
          class="px-6 font-weight-bold"
          :loading="processing"
          @click="submit"
        >
          {{ isEdit ? 'Guardar Cambios' : 'Registrar Cliente' }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>