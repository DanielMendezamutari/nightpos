<script setup>
import { useAuthStore } from '@/stores/auth'
import { useContextStore } from '@/stores/context'
import { resolveHomeRoute } from '@/utils/resolveHomeRoute'
import { useGenerateImageVariant } from '@/@core/composable/useGenerateImageVariant'
import miscMaskLight from '@images/misc/misc-mask-light.png'
import pages401 from '@images/pages/401.png'
import miscMaskDark from '@images/misc/misc-mask-dark.png'
import miscObj from '@images/pages/misc-401-object.png'

const miscThemeMask = useGenerateImageVariant(miscMaskLight, miscMaskDark)

const auth = useAuthStore()
const contextStore = useContextStore()

const homeTarget = computed(() => {
  if (!auth.isAuthenticated)
    return { name: 'login' }

  return resolveHomeRoute(auth.user, {
    tenantSlug: contextStore.tenantSlug,
    branchCode: contextStore.branchCode,
  })
})

definePage({
  alias: '/pages/misc/not-authorized',
  meta: {
    layout: 'blank',
    public: true,
  },
})
</script>

<template>
  <div class="misc-wrapper">
    <ErrorHeader
      status-code="401"
      title="You are not authorized! 🔐"
      description="You don't have permission to access this page. Go Home!"
      class="mb-10"
    />

    <!-- 👉 Image -->
    <div class="misc-avatar w-100 text-center">
      <VImg
        :src="pages401"
        alt="Coming Soon"
        :height="$vuetify.display.xs ? 400 : 500"
        class="my-sm-5"
      />

      <VBtn
        :to="homeTarget"
        class="mt-10"
      >
        Back to Home
      </VBtn>

      <VImg
        :src="miscThemeMask"
        class="d-none d-md-block footer-coming-soon flip-in-rtl"
        cover
      />

      <VImg
        :src="miscObj"
        class="d-none d-md-block footer-coming-soon-obj"
        :max-width="212"
        height="165"
      />
    </div>
  </div>
</template>

<style lang="scss">
@use "@core/scss/template/pages/misc.scss";
</style>
