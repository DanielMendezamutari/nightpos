<script setup>
import { ref, watch } from 'vue'
import { useInventarioStore } from '@/stores/inventario'

const props = defineProps({
  modelValue: Boolean,
  almacen: Object,
})

const emit = defineEmits(['update:modelValue', 'guardado'])
const store = useInventarioStore()

const loading = ref(false)
const form = ref({
  id: null,
  nombre: '',
  codigo: '',
  descripcion: '',
  responsable: '',
  es_interno: true,
})

watch(() => props.modelValue, (val) => {
  if (val) {
    if (props.almacen && props.almacen.id) {
      form.value = { ...props.almacen }
    } else {
      form.value = {
        id: null,
        nombre: '',
        codigo: '',
        descripcion: '',
        responsable: '',
        es_interno: true,
      }
    }
  }
})

const cerrar = () => {
  emit('update:modelValue', false)
}

const guardar = async () => {
  if (!form.value.nombre.trim()) {
    alert('El nombre del almacén es obligatorio')
    return
  }

  loading.value = true
  try {
    const payload = {
      nombre: form.value.nombre.trim(),
      codigo: form.value.codigo ? form.value.codigo.trim() : null,
      descripcion: form.value.descripcion ? form.value.descripcion.trim() : null,
      responsable: form.value.responsable ? form.value.responsable.trim() : null,
      es_interno: !!form.value.es_interno,
    }

    if (form.value.id) {
      payload.id = form.value.id
    }

    const res = await store.guardarAlmacen(payload)
    emit('guardado', res)
    cerrar()
  } catch (err) {
    alert('Error al guardar almacén: ' + (err.data?.message || err.message))
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <VDialog :model-value="modelValue" max-width="550" persistent @update:model-value="emit('update:modelValue', $event)">
    <VCard>
      <VCardItem class="bg-primary text-white">
        <template #prepend>
          <VIcon icon="ri-building-line" size="24" class="me-2" />
        </template>
        <VCardTitle class="text-white font-weight-bold">
          {{ form.id ? 'Editar Almacén' : 'Nuevo Almacén / Depósito' }}
        </VCardTitle>
        <VCardSubtitle class="text-white opacity-80">
          Control de existencias y centros de producción RestoTech
        </VCardSubtitle>
      </VCardItem>

      <VCardText class="pa-4">
        <VRow>
          <VCol cols="12" md="8">
            <VTextField
              v-model="form.nombre"
              label="Nombre del Almacén *"
              placeholder="Ej. Almacén Central, Barra 1, Cocina Caliente"
              variant="outlined"
              density="comfortable"
              required
            />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField
              v-model="form.codigo"
              label="Código (Opcional)"
              placeholder="Ej. ALM-01"
              variant="outlined"
              density="comfortable"
            />
          </VCol>
          <VCol cols="12">
            <VTextField
              v-model="form.responsable"
              label="Responsable / Encargado"
              placeholder="Ej. Juan Pérez (Jefe de Cocina)"
              variant="outlined"
              density="comfortable"
            />
          </VCol>
          <VCol cols="12">
            <VTextarea
              v-model="form.descripcion"
              label="Descripción o Ubicación"
              placeholder="Notas sobre el almacén o tipo de insumos que resguarda..."
              variant="outlined"
              rows="2"
              density="comfortable"
            />
          </VCol>
          <VCol cols="12">
            <VSwitch
              v-model="form.es_interno"
              color="primary"
              label="Almacén Interno (Producción / Bodega no expuesta a venta directa)"
              hide-details
            />
          </VCol>
        </VRow>
      </VCardText>

      <VCardActions class="pa-4 bg-var-theme-background d-flex justify-end gap-2">
        <VBtn variant="outlined" color="secondary" :disabled="loading" @click="cerrar">
          Cancelar
        </VBtn>
        <VBtn variant="elevated" color="primary" :loading="loading" prepend-icon="ri-save-line" @click="guardar">
          Guardar Almacén
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
