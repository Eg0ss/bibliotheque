<script setup>
import { ref, watch } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { RouterLink, useRoute } from 'vue-router'

const authStore = useAuthStore()
const route = useRoute()

// Gestion du menu déroulant (même logique que AdminLayout)
const openGroup = ref(null)

function toggleGroup(name) {
  openGroup.value = openGroup.value === name ? null : name
}

// Menus de l'espace utilisateur connecté
const sidebarItems = [
  {
    type: 'link',
    to: '/mon-espace/depots',
    label: 'Mes dépôts',
  },
  // {
  //   type: 'group',
  //   name: 'catalogue',
  //   label: 'Catalogue',
  //   children: [
  //     { to: '/catalogue', label: 'Parcourir' },
  //     { to: '/recherche', label: 'Recherche avancée' },
  //     { to: '/statistiques', label: 'Statistiques' },
  //   ],
  // },
  {
    type: 'link',
    to: '/mon-espace/depots/nouveau',
    label: 'Soumettre une demande',

  },
  {
    type: 'link',
    to: '/mon-espace/profil',
    label: 'Mon profil'
  },
]

// Ouvre automatiquement le groupe si on est sur une de ses routes enfants
watch(
  () => route.path,
  (path) => {
    for (const item of sidebarItems) {
      if (item.type === 'group') {
        const match = item.children.some(child => path.startsWith(child.to))
        if (match) {
          openGroup.value = item.name
          return
        }
      }
    }
  },
  { immediate: true }
)

async function handleLogout() {
  await authStore.logout()
}
</script>

<template>
  <div class="min-h-screen flex bg-[#f8f9fb]">

    <!-- ── Sidebar utilisateur ── -->
    <aside class="w-64 bg-[#1e3a5f] text-white flex flex-col min-h-screen">

      <!-- Logo -->
      <div class="p-5 border-b border-white/20">
        <RouterLink to="/" class="flex items-center gap-2">
          <div class="flex h-8 w-8 items-center justify-center rounded-md bg-white/20">
            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 0 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 1 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
            </svg>
          </div>
          <span class="text-sm font-bold tracking-wide text-white/90 uppercase">
            Bibliothèque
          </span>
        </RouterLink>
        <p class="text-xs text-white/50 mt-1 ml-10">Espace personnel</p>
      </div>

      
      <!-- Infos utilisateur connecté -->
      <div class="px-5 py-4 border-b border-white/10 bg-white/5">
        <!-- Avatar initiales -->
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-full bg-white/20 flex items-center justify-center
                      text-sm font-bold text-white uppercase flex-shrink-0">
            {{ authStore.user?.name?.charAt(0) ?? '?' }}
          </div>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-white truncate">
              {{ authStore.user?.name }}
            </p>
            <span class="inline-block mt-0.5 text-xs bg-white/20 text-white/80
                         rounded-full px-2 py-0.5">
              Utilisateur
            </span>
          </div>
        </div>
      </div>

        <!-- ── Bouton retour à l'accueil publique ─────────────────────────────
       Bien visible, séparé du reste, pour basculer facilement
       vers le catalogue public sans se déconnecter -->
        <div class="px-4 pt-4">
          <RouterLink to="/" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-lg
             bg-white/10 hover:bg-white/20 text-white text-sm font-medium
             border border-white/20 transition-all">
            <!-- <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg> -->
            Retour à l'accueil
          </RouterLink>
        </div>
        
      <!-- Navigation -->
      <nav class="flex-1 p-4 space-y-1">
        <template v-for="item in sidebarItems" :key="item.to ?? item.name">

          <!-- Lien simple -->
          <RouterLink v-if="item.type === 'link'" :to="item.to" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm
                   text-white/80 hover:bg-white/10 hover:text-white transition-all"
            :class="{ 'bg-white/20 text-white font-semibold': route.path === item.to }">
            <span class="text-base">{{ item.icon }}</span>
            {{ item.label }}
          </RouterLink>

          <!-- Groupe déroulant -->
          <div v-else-if="item.type === 'group'">
            <button @click="toggleGroup(item.name)" class="w-full flex items-center justify-between px-4 py-2.5
                     rounded-lg text-sm transition-all" :class="openGroup === item.name
                      ? 'bg-white/20 text-white font-semibold'
                      : 'text-white/80 hover:bg-white/10 hover:text-white'">
              <span class="flex items-center gap-3">
                <span class="text-base">{{ item.icon }}</span>
                {{ item.label }}
              </span>
              <span class="text-xs transition-transform duration-200 inline-block"
                :class="openGroup === item.name ? 'rotate-180' : 'rotate-0'">
                ▼
              </span>
            </button>

            <!-- Sous-menus -->
            <div v-show="openGroup === item.name" class="mt-1 ml-4 pl-3 border-l border-white/20 space-y-1">
              <RouterLink v-for="child in item.children" :key="child.to" :to="child.to" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm
                       text-white/70 hover:bg-white/10 hover:text-white transition-all"
                :class="{ 'bg-white/15 text-white font-medium': route.path.startsWith(child.to) }">
                <span class="text-sm">{{ child.icon }}</span>
                {{ child.label }}
              </RouterLink>
            </div>
          </div>

        </template>
      </nav>

      <!-- Déconnexion -->
      <div class="p-4 border-t border-white/10">
        <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm
                 text-white/70 hover:bg-red-600/30 hover:text-white transition-all">
          <span></span> Déconnexion
        </button>
      </div>
    </aside>

    <!-- ── Contenu principal ── -->
    <div class="flex-1 flex flex-col overflow-hidden">

      <!-- Barre du haut -->
      <header class="bg-white border-b border-gray-200 px-6 py-3
                     flex items-center justify-between">
        <h2 class="text-sm font-medium text-gray-500 capitalize">
          <!-- {{ route.name?.toString().replace(/\./g, ' › ') }} -->
        </h2>
        <span class="text-sm text-gray-400">{{ authStore.user?.email }}</span>
      </header>

      <!-- Page active -->
      <main class="flex-1 overflow-auto p-6">
        <RouterView />
      </main>
    </div>

  </div>
</template>