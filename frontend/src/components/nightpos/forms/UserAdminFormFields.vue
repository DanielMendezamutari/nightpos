<script setup>
import { STAFF_ROLES } from '@/composables/useUserAdminForm'

const form = defineModel({ type: Object, required: true })

const props = defineProps({
  branches: { type: Array, default: () => [] },
  isCreate: { type: Boolean, default: false },
  showCommissionField: { type: Boolean, default: false },
  showGirlCommissionField: { type: Boolean, default: false },
  showCleaningPayField: { type: Boolean, default: false },
  /** personal | access | commission | security | all */
  section: { type: String, default: 'all' },
})

const show = section => props.section === 'all' || props.section === section
</script>

<template>
  <VRow>
    <VCol
      v-if="show('personal')"
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.name"
        label="Nombre"
        :rules="[v => !!v?.trim() || 'Requerido']"
      />
    </VCol>
    <VCol
      v-if="show('personal')"
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.username"
        label="Usuario (login)"
        :rules="[v => !!v?.trim() || 'Requerido']"
      />
    </VCol>
    <VCol
      v-if="show('personal')"
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.email"
        label="Email (opcional)"
      />
    </VCol>
    <VCol
      v-if="show('access')"
      cols="12"
      md="6"
    >
      <VSelect
        v-model="form.staff_role"
        label="Rol operativo"
        :items="STAFF_ROLES"
      />
    </VCol>
    <VCol
      v-if="show('commission') && showCommissionField"
      cols="12"
      md="6"
    >
      <VTextField
        v-model.number="form.waiter_commission_percent"
        type="number"
        label="Comisión garzón (%)"
        min="0"
        max="100"
        hint="Ej: 5 o 6"
        persistent-hint
      />
    </VCol>
    <VCol
      v-if="show('commission') && showGirlCommissionField"
      cols="12"
      md="6"
    >
      <VSwitch
        v-model="form.can_receive_girl_commissions"
        label="Recibe comisiones de chica"
        color="warning"
      />
    </VCol>
    <template v-if="show('commission') && showCleaningPayField">
      <VCol cols="12">
        <div class="text-subtitle-2 mb-1">
          Pago limpieza
        </div>
      </VCol>
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model.number="form.cleaning_base_amount"
          type="number"
          label="Base por turno (Bs)"
          min="0"
          hint="Ej: 30"
          persistent-hint
        />
      </VCol>
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model.number="form.cleaning_room_amount"
          type="number"
          label="Pago por pieza limpiada (Bs)"
          min="0"
          hint="Ej: 10"
          persistent-hint
        />
      </VCol>
    </template>
    <VCol
      v-if="show('access')"
      cols="12"
      md="6"
    >
      <VSelect
        v-model="form.branch_id"
        label="Sucursal principal"
        :items="branches.map(b => ({ title: `${b.name} [${b.code}]`, value: b.id }))"
        clearable
      />
    </VCol>
    <VCol
      v-if="show('access')"
      cols="12"
      md="6"
    >
      <VSelect
        v-model="form.accessible_branch_ids"
        label="Sucursales permitidas"
        :items="branches.map(b => ({ title: b.name, value: b.id }))"
        multiple
        chips
      />
    </VCol>
    <VCol
      v-if="show('access')"
      cols="12"
      md="6"
    >
      <VSelect
        v-model="form.status"
        label="Estado"
        :items="[
          { title: 'Activo', value: 'active' },
          { title: 'Inactivo', value: 'inactive' },
        ]"
      />
    </VCol>
    <template v-if="isCreate && (show('security') || show('all'))">
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model="form.pin"
          label="PIN (4-6 dígitos)"
          type="password"
          autocomplete="new-password"
        />
      </VCol>
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model="form.password"
          label="Contraseña (opcional)"
          type="password"
          autocomplete="new-password"
        />
      </VCol>
    </template>
  </VRow>
</template>
