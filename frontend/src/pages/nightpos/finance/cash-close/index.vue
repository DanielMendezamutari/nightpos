<script setup>
definePage({ meta: { permission: 'cash.access' } })

const router = useRouter()

onMounted(() => {
  router.replace({ name: 'nightpos-cash' })
})
</script>

<template>
  <VProgressLinear indeterminate />
</template>
