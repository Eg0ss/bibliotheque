<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from './stores/authStore'
import { onMounted } from 'vue'

import GuestLayout from './layouts/GuestLayout.vue'
import UserLayout  from './layouts/UserLayout.vue'
import AdminLayout from './layouts/AdminLayout.vue'
import Toast       from 'primevue/toast'

const route     = useRoute()
const authStore = useAuthStore()

onMounted(() => {
  authStore.fetchUser()
})

const currentLayout = computed(() => {
  // route.matched contient toutes les routes de la hiérarchie active
  // ex: pour /mon-espace/profil → [route parent /mon-espace, route enfant profil]
  // On parcourt de l'enfant vers le parent (.reverse()) pour prendre
  // le meta le plus spécifique en premier
  const layout = [...route.matched]
    .reverse()
    .find(r => r.meta?.layout)
    ?.meta?.layout ?? 'guest'

  if (layout === 'none')  return null        // LoginView, RegisterView → pas de layout
  if (layout === 'admin') return AdminLayout  // Admin, Gestionnaire, RH → sidebar admin
  if (layout === 'user')  return UserLayout   // Espace utilisateur connecté → sidebar user
  return GuestLayout                          // Pages publiques → navbar + footer
})
</script>

<template>
  <!--
    Toast PrimeVue : affiché globalement, accessible depuis n'importe quel store
    position="top-right" = coin supérieur droit
  -->
  <Toast position="top-right" />

  <!--
    Si layout = 'none' (connexion/inscription) : RouterView direct, plein écran
    Sinon : le layout enveloppe la page et contient son propre <RouterView>
  -->
  <component v-if="currentLayout" :is="currentLayout" />
  <RouterView v-else />
</template>