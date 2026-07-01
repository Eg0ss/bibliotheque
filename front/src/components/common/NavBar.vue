<script setup>
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore }          from '@/stores/authStore'
import { ref, onMounted, onUnmounted } from 'vue'

const authStore = useAuthStore()
const router    = useRouter()

// Contrôle l'ouverture/fermeture du menu déroulant
const isMenuOpen = ref(false)

// Référence DOM du menu, pour détecter les clics en dehors
const menuRef = ref(null)

function toggleMenu() {
  isMenuOpen.value = !isMenuOpen.value
}

function closeMenu() {
  isMenuOpen.value = false
}

// Ferme le menu si on clique n'importe où ailleurs sur la page
function handleClickOutside(event) {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    closeMenu()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

async function handleLogout() {
  closeMenu()
  await authStore.logout()
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
        <RouterLink to="/" class="rounded-md px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800" :class="{ 'bg-slate-100 text-[#1e3a5f]': $route.path === '/' }">
          Accueil
        </RouterLink>
        <RouterLink to="/catalogue" class="rounded-md px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800" :class="{ 'bg-slate-100 text-[#1e3a5f]': $route.path === '/catalogue' }">
          Catalogue
        </RouterLink>
        <RouterLink to="/recherche" class="rounded-md px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800" :class="{ 'bg-slate-100 text-[#1e3a5f]': $route.path === '/recherche' }">
          Recherche
        </RouterLink>
        <RouterLink to="/statistiques" class="rounded-md px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-800" :class="{ 'bg-slate-100 text-[#1e3a5f]': $route.path === '/statistiques' }">
          Statistiques
        </RouterLink>
      </nav>

      <div class="flex items-center gap-4">

        <!-- ── Utilisateur connecté : menu déroulant ───────────────────── -->
        <template v-if="authStore.isAuthenticated">
          <div ref="menuRef" class="relative">

            <!-- Bouton déclencheur : nom + flèche -->
            <button
              @click="toggleMenu"
              class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 transition"
            >
              <!-- Avatar initiale -->
              <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#1e3a5f] text-xs font-bold text-white uppercase">
                {{ authStore.user?.name?.charAt(0) ?? '?' }}
              </span>
              <span class="hidden sm:inline">{{ authStore.user?.name }}</span>
              <!-- Flèche qui s'inverse à l'ouverture -->
              <svg
                class="h-4 w-4 text-slate-400 transition-transform duration-150"
                :class="{ 'rotate-180': isMenuOpen }"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
              </svg>
            </button>

            <!-- Menu déroulant -->
            <Transition
              enter-active-class="transition ease-out duration-150"
              enter-from-class="opacity-0 -translate-y-1"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition ease-in duration-100"
              leave-from-class="opacity-100 translate-y-0"
              leave-to-class="opacity-0 -translate-y-1"
            >
              <div
                v-if="isMenuOpen"
                class="absolute right-0 mt-2 w-56 rounded-lg border border-slate-200 bg-white shadow-lg overflow-hidden"
              >
                <!-- En-tête : nom + email -->
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50">
                  <p class="text-sm font-semibold text-slate-800 truncate">{{ authStore.user?.name }}</p>
                  <p class="text-xs text-slate-500 truncate">{{ authStore.user?.email }}</p>
                </div>

                <!-- Liens -->
                <div class="py-1">
                  <RouterLink
                    to="/mon-espace/depots"
                    @click="closeMenu"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition"
                  >
                     Mon espace
                  </RouterLink>
                  <RouterLink
                    to="/mon-espace/profil"
                    @click="closeMenu"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition"
                  >
                     Mon profil
                  </RouterLink>
                </div>

                <!-- Déconnexion -->
                <div class="py-1 border-t border-slate-100">
                  <button
                    @click="handleLogout"
                    class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition"
                  >
                     Déconnexion
                  </button>
                </div>
              </div>
            </Transition>

          </div>
        </template>

        <!-- ── Visiteur non connecté ─────────────────────────────────────── -->
        <template v-else>
          <RouterLink to="/connexion" class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
            Connexion
          </RouterLink>
          <RouterLink to="/inscription" class="rounded-md bg-[#1e3a5f] px-3 py-2 text-sm font-medium text-white hover:bg-[#2d5a8e]">
            S'inscrire
          </RouterLink>
        </template>

      </div>
    </div>
  </header>
</template>