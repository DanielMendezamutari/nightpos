<script setup>

import { createOrder } from '@/api/orders'
import { fetchServiceAreas } from '@/api/serviceAreas'

import QuickWaiterCreateDialog from '@/components/nightpos/staff/QuickWaiterCreateDialog.vue'

import { appendWaiterToSelectList, loadOperationalWaitersForSelect } from '@/composables/useOperationalWaiters'

import { useNightPosPermissions } from '@/composables/useNightPosPermissions'

import { useAuthStore } from '@/stores/auth'

import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'



definePage({

  meta: {

    permission: 'orders.access',

  },

})



const auth = useAuthStore()

const router = useRouter()

const { can } = useNightPosPermissions()



const isWaiter = computed(() => auth.staffRole === 'WAITER')



const saving = ref(false)

const { notify } = useNightPosNotify()

const waiters = ref([])

const showQuickWaiter = ref(false)

const refForm = ref()



const serviceAreas = ref([])

const form = ref({

  table_label: '',

  service_area_id: null,

  notes: '',

  waiter_user_id: null,

})



const reloadWaiters = async () => {

  waiters.value = await loadOperationalWaitersForSelect()

}



const onWaiterCreated = async waiter => {

  waiters.value = appendWaiterToSelectList(waiters.value, waiter)

  if (waiter?.id)

    form.value.waiter_user_id = waiter.id

}



onMounted(async () => {

  if (isWaiter.value) {

    form.value.waiter_user_id = auth.user?.id ?? null



    return

  }



  await reloadWaiters()

  if (can('settings.service_areas')) {
    try {
      serviceAreas.value = (await fetchServiceAreas({ active_only: true })).map(a => ({
        title: `${a.name} (${a.code})`,
        value: a.id,
      }))
    }
    catch {
      serviceAreas.value = []
    }
  }

})



const submit = async () => {

  if (!form.value.service_area_id && !form.value.table_label?.trim()) {

    notify('Seleccione un ambiente o indique mesa / cliente.', 'warning')

    return

  }



  if (!isWaiter.value && !form.value.waiter_user_id) {

    notify('Seleccione el garzón de la comanda.', 'warning')



    return

  }



  saving.value = true



  try {

    const payload = {

      table_label: form.value.table_label?.trim() || null,

      service_area_id: form.value.service_area_id || null,

      notes: form.value.notes?.trim() || null,

    }



    if (!isWaiter.value)

      payload.waiter_user_id = form.value.waiter_user_id



    const order = await createOrder(payload)



    notify('Comanda creada')

    await router.replace({ name: 'nightpos-orders-id', params: { id: order.id } })

  }

  catch (error) {

    notify(getApiErrorMessage(error), 'error')

  }

  finally {

    saving.value = false

  }

}

</script>



<template>

  <div class="orders-new">

    <VBtn

      variant="text"

      class="mb-2"

      :to="{ name: 'nightpos-orders' }"

    >

      <VIcon

        icon="ri-arrow-left-line"

        start

      />

      Volver

    </VBtn>



    <h4 class="text-h4 mb-4">

      Nueva comanda

    </h4>



    <VCard max-width="560">

      <VCardText>

        <VForm

          ref="refForm"

          @submit.prevent="submit"

        >

          <VSelect

            v-if="serviceAreas.length"

            v-model="form.service_area_id"

            :items="serviceAreas"

            label="Ambiente catalogado (opcional)"

            clearable

            class="mb-4"

          />

          <VTextField

            v-model="form.table_label"

            label="Mesa / etiqueta libre"

            placeholder="Ej. Mesa 5, walk-in"

            :hint="serviceAreas.length ? 'Opcional si eligió ambiente' : 'Requerido si no hay ambiente'"

            persistent-hint

            autofocus

            class="mb-4"

          />



          <VTextField

            v-model="form.notes"

            label="Notas (opcional)"

            rows="2"

            class="mb-4"

          />



          <VTextField

            v-if="isWaiter"

            :model-value="auth.user?.name"

            label="Garzón"

            readonly

            hint="Usuario actual de la sesión"

            persistent-hint

            class="mb-6"

          />



          <template v-else>

            <VAutocomplete

              v-model="form.waiter_user_id"

              :items="waiters"

              label="Garzón *"

              placeholder="Seleccione garzón..."

              :rules="[v => !!v || 'Requerido']"

              class="mb-2"

            >

              <template

                v-if="can('staff.quick_create_waiter')"

                #append-item

              >

                <VDivider class="my-2" />

                <VListItem

                  prepend-icon="ri-user-add-line"

                  title="+ Registrar nuevo garzón"

                  class="text-primary"

                  @click="showQuickWaiter = true"

                />

              </template>

            </VAutocomplete>

            <VBtn

              v-if="can('staff.quick_create_waiter')"

              variant="text"

              size="small"

              prepend-icon="ri-user-add-line"

              class="mb-6 px-0"

              @click="showQuickWaiter = true"

            >

              + Nuevo garzón

            </VBtn>

          </template>



          <VBtn

            color="primary"

            size="x-large"

            block

            :loading="saving"

            @click="submit"

          >

            Abrir comanda

          </VBtn>

        </VForm>

      </VCardText>

    </VCard>



    <QuickWaiterCreateDialog

      v-model="showQuickWaiter"

      @created="onWaiterCreated"

    />
</div>

</template>

