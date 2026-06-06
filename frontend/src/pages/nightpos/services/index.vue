<script setup>
definePage({ meta: { permission: 'bracelets.access' } })

const router = useRouter()

onMounted(() => {
  router.replace({ name: 'nightpos-services-bracelets' })
})
</script>

<template>
  <VProgressLinear indeterminate />
</template>
