<!-- <script setup>
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
</template> -->
<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/authStore'

const authStore = useAuthStore()


// Au démarrage de l'application, vérifier si l'utilisateur a encore une session active
// Cela permet de rester connecté après un rechargement de page
onMounted(() => {
  authStore.fetchUser()
})
</script>

<template>
  <router-view />
</template>