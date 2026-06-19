<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import GuestLayout from '@/layouts/GuestLayout.vue'
import UserLayout from '@/layouts/UserLayout.vue'
import AdminLayout from '@/layouts/AdminLayout.vue'

const route = useRoute()

const currentLayout = computed(() => {
  const layoutName = route.meta.layout || 'guest'
  if (layoutName === 'none') return null
  if (layoutName === 'user') return UserLayout
  if (layoutName === 'admin') return AdminLayout
  return GuestLayout
})
</script>

<template>
  <component v-if="currentLayout" :is="currentLayout">
    <RouterView />
  </component>
  <RouterView v-else />
</template>
