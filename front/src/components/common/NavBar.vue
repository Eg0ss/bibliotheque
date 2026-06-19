<script setup>
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { computed } from 'vue'

const authStore = useAuthStore()
const router = useRouter()

function handleLogout() {
  authStore.logout()
  router.push({ name: 'home' })
}
</script>

<template>
  <header class="sticky top-0 z-40 border-b border-slate-200 bg-white">
    <div class="container mx-auto flex h-16 items-center justify-between px-4">
      <RouterLink to="/" class="flex items-center gap-2">
        <div class="flex h-9 w-9 items-center justify-center rounded-md bg-[#1e3a5f] text-white">
          <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 0 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 1 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
          </svg>
        </div>
        <span class="text-base font-semibold text-slate-800">Bibliothèque Numérique</span>
      </RouterLink>
      <nav class="hidden items-center gap-1 md:flex">
        <RouterLink
          to="/"
          class="rounded-md px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800"
          :class="{ 'bg-slate-100 text-[#1e3a5f]': $route.path === '/' }"
        >
          Accueil
        </RouterLink>
        <RouterLink
          to="/catalogue"
          class="rounded-md px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800"
          :class="{ 'bg-slate-100 text-[#1e3a5f]': $route.path === '/catalogue' }"
        >
          Catalogue
        </RouterLink>
        <RouterLink
          to="/recherche"
          class="rounded-md px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800"
          :class="{ 'bg-slate-100 text-[#1e3a5f]': $route.path === '/recherche' }"
        >
          Recherche
        </RouterLink>
        <RouterLink
          to="/statistiques"
          class="rounded-md px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800"
          :class="{ 'bg-slate-100 text-[#1e3a5f]': $route.path === '/statistiques' }"
        >
          Statistiques
        </RouterLink>
      </nav>
      <div class="flex items-center gap-4">
        <template v-if="authStore.isAuthenticated">
          <div class="flex items-center gap-3">
            <span class="text-sm text-slate-600">{{ authStore.user?.name }}</span>
            <button
              @click="handleLogout"
              class="text-sm text-slate-600 hover:text-[#1e3a5f]"
            >
              Déconnexion
            </button>
          </div>
        </template>
        <template v-else>
          <RouterLink
            to="/connexion"
            class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
          >
            Connexion
          </RouterLink>
          <RouterLink
            to="/inscription"
            class="rounded-md bg-[#1e3a5f] px-3 py-2 text-sm font-medium text-white hover:bg-[#2d5a8e]"
          >
            S'inscrire
          </RouterLink>
        </template>
      </div>
    </div>
  </header>
</template>
