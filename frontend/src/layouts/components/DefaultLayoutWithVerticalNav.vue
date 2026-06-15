<script setup>
import { useNightPosNavItems } from '@/composables/useNightPosNavItems'
import { useConfigStore } from '@core/stores/config'
import { themeConfig } from '@themeConfig'

// Components
import NightPosNavbarContext from '@/components/nightpos/NightPosNavbarContext.vue'
import NightPosStabilityDebug from '@/components/nightpos/dev/NightPosStabilityDebug.vue'
import { useNightPosShell } from '@/composables/useNightPosShell'
import Footer from '@/layouts/components/Footer.vue'
import NavBarNotifications from '@/layouts/components/NavBarNotifications.vue'
import NavSearchBar from '@/layouts/components/NavSearchBar.vue'
import NavbarShortcuts from '@/layouts/components/NavbarShortcuts.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import NavBarI18n from '@core/components/I18n.vue'

// @layouts plugin
import { VerticalNavLayout } from '@layouts'

// SECTION: Loading Indicator
const isFallbackStateActive = ref(false)
const refLoadingIndicator = ref(null)

watch([
  isFallbackStateActive,
  refLoadingIndicator,
], () => {
  if (isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.fallbackHandle()
  if (!isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.resolveHandle()
}, { immediate: true })

// !SECTION
const configStore = useConfigStore()
const { showNightPosChrome } = useNightPosShell()
const { navItems } = useNightPosNavItems()

// ℹ️ Provide animation name for vertical nav collapse icon.
const verticalNavHeaderActionAnimationName = ref(null)

watch([
  () => configStore.isVerticalNavCollapsed,
  () => configStore.isAppRTL,
], val => {
  if (configStore.isAppRTL)
    verticalNavHeaderActionAnimationName.value = val[0] ? 'rotate-back-180' : 'rotate-180'
  else
    verticalNavHeaderActionAnimationName.value = val[0] ? 'rotate-180' : 'rotate-back-180'
}, { immediate: true })

</script>

<template>
  <VerticalNavLayout :nav-items="navItems">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <IconBtn
          id="vertical-nav-toggle-btn"
          class="ms-n2 d-lg-none"
          @click="toggleVerticalOverlayNavActive(true)"
        >
          <VIcon icon="ri-menu-line" />
        </IconBtn>

        <NavSearchBar
          v-show="!showNightPosChrome"
          class="ms-lg-n2"
        />

        <NightPosNavbarContext v-if="showNightPosChrome" />

        <VSpacer />

        <NavBarI18n
          v-if="!showNightPosChrome && themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
          :languages="themeConfig.app.i18n.langConfig"
        />
        <NavbarThemeSwitcher v-show="!showNightPosChrome" />
        <NavbarShortcuts v-show="!showNightPosChrome" />
        <NavBarNotifications
          v-show="!showNightPosChrome"
          class="me-2"
        />
        <UserProfile />
      </div>
    </template>

    <AppLoadingIndicator ref="refLoadingIndicator" />

    <!-- 👉 Pages -->
    <RouterView v-slot="{ Component }">
      <Suspense
        :timeout="20000"
        @fallback="isFallbackStateActive = true"
        @resolve="isFallbackStateActive = false"
      >
        <component :is="Component" />
      </Suspense>
    </RouterView>

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <NightPosStabilityDebug v-if="showNightPosChrome" />
  </VerticalNavLayout>
</template>

<style lang="scss">
@keyframes rotate-180 {
  from { transform: rotate(0deg); }
  to { transform: rotate(180deg); }
}

@keyframes rotate-back-180 {
  from { transform: rotate(180deg); }
  to { transform: rotate(0deg); }
}

.layout-vertical-nav {
  .nav-header {
    .header-action {
      animation-duration: 0.35s;
      animation-fill-mode: forwards;
      animation-name: v-bind(verticalNavHeaderActionAnimationName);
      transform: rotate(0deg);
    }
  }
}
</style>
