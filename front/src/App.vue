<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from './stores/authStore'
import { onMounted } from 'vue'

import GuestLayout from './layouts/GuestLayout.vue'
import UserLayout  from './layouts/UserLayout.vue'
import AdminLayout from './layouts/AdminLayout.vue'
import Toast from 'primevue/toast'

const route     = useRoute()
const authStore = useAuthStore()

// Au démarrage : vérifier si une session est encore active côté Laravel
onMounted(() => {
  authStore.fetchUser()
})

// Choisir le layout selon route.meta.layout
// 'admin'  → AdminLayout (sidebar pour Admin, Gestionnaire, RH)
// 'user'   → UserLayout (layout utilisateur connecté simple)
// 'guest'  → GuestLayout (navbar publique)
// 'none'   → pas de layout (ex: page connexion plein écran)
const currentLayout = computed(() => {
  const layout = route.meta.layout || 'guest'
  if (layout === 'none')  return null
  if (layout === 'admin') return AdminLayout
  if (layout === 'user')  return UserLayout
  return GuestLayout
})
</script>

<template>
  <!--
    Si layout = 'none' (ex: LoginView) → RouterView direct, pas de wrapper
    Sinon → le composant layout choisi enveloppe la page via son propre <RouterView>
  -->
      <Toast position="top-right" />
  <component v-if="currentLayout" :is="currentLayout" />
  <RouterView v-else />
</template>