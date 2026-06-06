<script setup>
definePage({ meta: { permission: 'shifts.close' } })

const router = useRouter()

onMounted(() => {
  router.replace({ name: 'nightpos-shifts-close' })
})
</script>

<template>
  <VProgressLinear indeterminate />
</template>
