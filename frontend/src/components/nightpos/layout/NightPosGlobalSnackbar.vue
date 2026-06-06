<script setup>
import { storeToRefs } from 'pinia'
import { useNotifyStore } from '@/stores/notify'

const store = useNotifyStore()
const { show, text, color, timeout } = storeToRefs(store)
</script>

<template>
  <VSnackbar
    :model-value="show"
    :color="color"
    :timeout="timeout"
    location="top"
    @update:model-value="value => { if (!value) store.hide() }"
  >
    {{ text }}
    <template #actions>
      <VBtn
        icon="ri-close-line"
        size="small"
        variant="text"
        @click="store.hide()"
      />
    </template>
  </VSnackbar>
</template>
