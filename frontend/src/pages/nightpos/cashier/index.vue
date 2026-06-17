<script setup>
definePage({
  meta: {
    permission: 'sales.charge',
    layout: 'blank',
  },
})

const router = useRouter()

onMounted(() => {
  router.replace({ name: 'nightpos-cashier-orders' })
})
</script>

<template>
  <div />
</template>
