<script setup>
definePage({ meta: { permission: 'shifts.access' } })

const router = useRouter()

onMounted(() => {
  router.replace({ name: 'nightpos-shifts-current' })
})
</script>

<template>
  <VProgressLinear indeterminate />
</template>
