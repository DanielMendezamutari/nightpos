<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosContextCards from '@/components/nightpos/layout/NightPosContextCards.vue'
import { useOperationalStore } from '@/stores/operational'

definePage({ meta: { permission: null } })

const operational = useOperationalStore()

onMounted(() => {
  operational.refreshContext().catch(() => {})
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Sucursal actual"
      subtitle="Contexto operativo de la sesión."
    />
    <NightPosContextCards />
    <VCard>
      <VCardText>
        <VList lines="two">
          <VListItem title="Empresa">
            <template #subtitle>
              {{ operational.tenant?.name || '—' }} ({{ operational.tenant?.slug }})
            </template>
          </VListItem>
          <VListItem title="Sucursal">
            <template #subtitle>
              {{ operational.branch?.name }} [{{ operational.branch?.code }}]
            </template>
          </VListItem>
        </VList>
      </VCardText>
    </VCard>
  </div>
</template>
