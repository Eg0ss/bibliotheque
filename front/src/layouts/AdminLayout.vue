<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { RouterLink, useRouter } from 'vue-router'

const authStore = useAuthStore()
const router    = useRouter()

// On calcule les items de menu selon le rôle de l'utilisateur connecté
const sidebarItems = computed(() => {
  const role = authStore.userRole // ← corrigé (était authStore.role)

  if (role === 'admin') {
    return [
      // Icône + libellé + chemin pour chaque entrée de menu
      { to: '/admin',                    label: 'Tableau de bord',    icon: '🏠' },
      { to: '/admin/utilisateurs',       label: 'Utilisateurs',       icon: '👥' },
      { to: '/admin/publication',        label: 'Publications',        icon: '📚' },
      { to: '/admin/assignation',        label: 'Assignations',        icon: '📋' },
      { to: '/admin/decision',           label: 'Décisions finales',   icon: '✅' },
    ]
  }

  if (role === 'gestionnaire') {
    return [
      { to: '/gestionnaire',             label: 'Tableau de bord',    icon: '🏠' },
      { to: '/gestionnaire/documents',   label: 'Documents à vérifier', icon: '📄' },
      { to: '/gestionnaire/parametres',  label: 'Paramètres de validation', icon: '⚙️' },
    ]
  }

  if (role === 'rh') {
    return [
      { to: '/rh',                       label: 'Tableau de bord',    icon: '🏠' },
      { to: '/rh/utilisateurs',          label: 'Utilisateurs',       icon: '👥' },
      { to: '/rh/historique',            label: 'Historique',          icon: '📜' },
    ]
  }

  return []
})

// Déconnexion depuis le layout
async function handleLogout() {
  await authStore.logout()
}
</script>

<template>
  <div class="min-h-screen flex bg-[#f8f9fb]">

    <!-- ── Sidebar ── -->
    <aside class="w-64 bg-[#042C53] text-white flex flex-col min-h-screen">

      <!-- Logo / Titre -->
      <div class="p-5 border-b border-white/20">
        <h1 class="text-base font-bold tracking-wide uppercase text-white/90">
          📖 Bibliothèque
        </h1>
        <p class="text-xs text-white/50 mt-1">Espace administration</p>
      </div>

      <!-- Infos utilisateur connecté -->
      <div class="px-5 py-4 border-b border-white/10 bg-white/5">
        <p class="text-sm font-semibold text-white truncate">
          {{ authStore.user?.name }}
        </p>
        <span class="inline-block mt-1 text-xs bg-white/20 text-white/80 rounded-full px-2 py-0.5 capitalize">
          {{ authStore.userRole }}
        </span>
      </div>

      <!-- Menu de navigation -->
      <nav class="flex-1 p-4 space-y-1">
        <RouterLink
          v-for="item in sidebarItems"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-white/80 hover:bg-white/10 hover:text-white transition-all"
          :class="{ 'bg-white/20 text-white font-semibold': $route.path === item.to }"
        >
          <span class="text-base">{{ item.icon }}</span>
          {{ item.label }}
        </RouterLink>
      </nav>

      <!-- Bouton déconnexion en bas -->
      <div class="p-4 border-t border-white/10">
        <button
          @click="handleLogout"
          class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-white/70 hover:bg-red-600/30 hover:text-white transition-all"
        >
          <span>🚪</span> Déconnexion
        </button>
      </div>
    </aside>

    <!-- ── Contenu principal ── -->
    <div class="flex-1 flex flex-col overflow-hidden">

      <!-- Barre du haut -->
      <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
        <h2 class="text-sm font-medium text-gray-500">
          <!-- Titre dynamique selon la route active -->
          {{ $route.name?.replace('admin.', '').replace('.', ' › ') }}
        </h2>
        <span class="text-sm text-gray-400">{{ authStore.user?.email }}</span>
      </header>

      <!-- Zone de contenu : RouterView affiche la page active -->
      <main class="flex-1 overflow-auto p-6">
        <RouterView />
      </main>
    </div>

  </div>
</template>