import { fileURLToPath } from 'node:url'
import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'
import { VueRouterAutoImports, getPascalCaseRouteName } from 'unplugin-vue-router'
import VueRouter from 'unplugin-vue-router/vite'
import { defineConfig } from 'vite'
import Layouts from 'vite-plugin-vue-layouts'
import { VitePWA } from 'vite-plugin-pwa'
import vuetify from 'vite-plugin-vuetify'
import svgLoader from 'vite-svg-loader'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    // Docs: https://github.com/posva/unplugin-vue-router
    // ℹ️ This plugin should be placed before vue plugin
    VueRouter({
      getRouteName: routeNode => {
        // Convert pascal case to kebab case
        return getPascalCaseRouteName(routeNode)
          .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
          .toLowerCase()
      },
      beforeWriteFiles: root => {
        root.insert('/apps/email/:filter', '/src/pages/apps/email/index.vue')
        root.insert('/apps/email/:label', '/src/pages/apps/email/index.vue')
      },
    }),
    vue({
      template: {
        compilerOptions: {
          isCustomElement: tag => tag === 'swiper-container' || tag === 'swiper-slide',
        },
      },
    }),
    vueJsx(),

    // Docs: https://github.com/vuetifyjs/vuetify-loader/tree/master/packages/vite-plugin
    vuetify({
      styles: {
        configFile: 'src/assets/styles/variables/_vuetify.scss',
      },
    }),

    // Docs: https://github.com/johncampionjr/vite-plugin-vue-layouts#vite-plugin-vue-layouts
    Layouts({
      layoutsDirs: './src/layouts/',
    }),

    // Docs: https://github.com/antfu/unplugin-vue-components#unplugin-vue-components
    Components({
      dirs: ['src/@core/components', 'src/views/demos', 'src/components'],
      dts: true,
      resolvers: [
        componentName => {
          // Auto import `VueApexCharts`
          if (componentName === 'VueApexCharts')
            return { name: 'default', from: 'vue3-apexcharts', as: 'VueApexCharts' }
        },
      ],
    }),

    // Docs: https://github.com/antfu/unplugin-auto-import#unplugin-auto-import
    AutoImport({
      imports: ['vue', VueRouterAutoImports, '@vueuse/core', '@vueuse/math', 'vue-i18n', 'pinia'],
      dirs: [
        './src/@core/utils',
        './src/@core/composable/',
        './src/composables/',
        './src/utils/',
        './src/stores/',
        './src/plugins/*/composables/*',
      ],
      vueTemplate: true,

      // ℹ️ Disabled to avoid confusion & accidental usage
      ignore: ['useCookies', 'useStorage'],
      eslintrc: {
        enabled: true,
        filepath: './.eslintrc-auto-import.json',
      },
    }),

    // Docs: https://github.com/intlify/bundle-tools/tree/main/packages/unplugin-vue-i18n#intlifyunplugin-vue-i18n
    VueI18nPlugin({
      runtimeOnly: true,
      compositionOnly: true,
      include: [
        fileURLToPath(new URL('./src/plugins/i18n/locales/**', import.meta.url)),
      ],
    }),
    svgLoader(),

    VitePWA({
      registerType: 'prompt',
      injectRegister: 'script',
      // Manifests are managed in public/ and injected dynamically per context.
      manifest: false,
      devOptions: {
        enabled: false,
      },
      workbox: {
        // Precache all build artifacts.
        globPatterns: ['**/*.{js,css,html,woff2,svg,png,ico,webmanifest}'],
        // SPA: serve cached shell for any navigation when offline.
        navigateFallback: 'index.html',
        navigateFallbackDenylist: [
          /^\/api\//,
          /^\/backend\/public\/api\//,
          /\/manifest.*\.webmanifest$/,
          /^\/events\//,
          /\/events\//,
          /\/sanctum\//,
          /\/broadcasting\//,
          /\/storage\//,
        ],
        runtimeCaching: [
          {
            // API: always network — never cache responses.
            urlPattern: ({ url }) => {
              const path = url.pathname

              return path.includes('/api/')
                || path.includes('/sanctum/')
                || path.includes('/broadcasting/')
                || path.includes('/events/')
            },
            handler: 'NetworkOnly',
          },
          {
            // Versioned assets already in precache; this catches unversioned ones.
            urlPattern: /\.(png|jpg|jpeg|webp|ico|gif)(\?.*)?$/,
            handler: 'CacheFirst',
            options: {
              cacheName: 'nightpos-images',
              expiration: { maxEntries: 80, maxAgeSeconds: 60 * 60 * 24 * 30 },
            },
          },
          {
            // Fonts from CDN if any.
            urlPattern: /\.(woff|woff2|ttf|eot)(\?.*)?$/,
            handler: 'CacheFirst',
            options: {
              cacheName: 'nightpos-fonts',
              expiration: { maxEntries: 20, maxAgeSeconds: 60 * 60 * 24 * 365 },
            },
          },
        ],
        skipWaiting: false,
        clientsClaim: false,
      },
    }),
  ],
  define: { 'process.env': {} },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      '@themeConfig': fileURLToPath(new URL('./themeConfig.js', import.meta.url)),
      '@core': fileURLToPath(new URL('./src/@core', import.meta.url)),
      '@layouts': fileURLToPath(new URL('./src/@layouts', import.meta.url)),
      '@images': fileURLToPath(new URL('./src/assets/images/', import.meta.url)),
      '@styles': fileURLToPath(new URL('./src/assets/styles/', import.meta.url)),
      '@configured-variables': fileURLToPath(new URL('./src/assets/styles/variables/_template.scss', import.meta.url)),
      '@db': fileURLToPath(new URL('./src/plugins/fake-api/handlers/', import.meta.url)),
      '@api-utils': fileURLToPath(new URL('./src/plugins/fake-api/utils/', import.meta.url)),
    },
  },
  server: {
    proxy: {
      '/api': {
        target: process.env.VITE_BACKEND_URL || 'http://nightpos.test',
        changeOrigin: true,
        secure: false,
      },
    },
  },
  build: {
    chunkSizeWarningLimit: 5000,
  },
  optimizeDeps: {
    exclude: ['vuetify'],
    entries: [
      './src/**/*.vue',
    ],
  },
})
