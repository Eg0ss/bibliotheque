<script setup>
import { computed, ref, watch } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { RouterLink, useRoute } from 'vue-router'

const authStore = useAuthStore()
const route     = useRoute()
const openGroup = ref(null)

function toggleGroup(name) {
  openGroup.value = openGroup.value === name ? null : name
}

// ── sidebarItems déclaré EN PREMIER ──────────────────────────
const sidebarItems = computed(() => {
  const role = authStore.userRole

  if (role === 'admin') {
    return [
      { type: 'link',  to: '/admin',            label: 'Tableau de bord'  },
      { type: 'link', to: '/admin/demandes-attente',  label: 'Demandes en attente' },
      {
        type    : 'group',
        name    : 'initialisation',
        label   : 'Initialisation',
        children: [
          { to: '/admin/utilisateurs', label: 'Utilisateurs'},
          { to: '/admin/categories',   label: 'Catégories' },
          { to: '/admin/types',        label: 'Types'},
        ],
      },
      { type: 'link',  to: '/admin/publication', label: 'Publications'     },
      { type: 'link',  to: '/admin/assignation', label: 'Assignations'},
      { type: 'link',  to: '/admin/decision',    label: 'Décisions finales' },
    ]
  }

  if (role === 'gestionnaire') {
    return [
      { type: 'link', to: '/gestionnaire',             label: 'Tableau de bord' },
      { type: 'link', to: '/gestionnaire/documents',   label: 'Documents à vérifier' },
      { type: 'link', to: '/gestionnaire/parametres',  label: 'Mes validation' },
    ]
  }

  if (role === 'rh') {
    return [
      { type: 'link', to: '/rh',               label: 'Tableau de bord' },
      { type: 'link', to: '/rh/utilisateurs',  label: 'Utilisateurs' },
      { type: 'link', to: '/rh/historique',    label: 'Historique' },
    ]
  }

  return []
})

// ── watch déclaré APRÈS sidebarItems ─────────────────────────
// Maintenant sidebarItems.value est accessible correctement
watch(
  () => route.path,
  (path) => {
    for (const item of sidebarItems.value) {
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

    <!-- ── Sidebar ── -->
    <aside class="w-64 bg-[#042C53] text-white flex flex-col min-h-screen">

      <!-- Logo -->
      <div class="p-5 border-b border-white/20">
        <h1 class="text-base font-bold tracking-wide uppercase text-white/90">
          Bibliothèque
        </h1>
        <p class="text-xs text-white/50 mt-1">Espace administration</p>
      </div>

      <!-- Infos utilisateur -->
      <div class="px-5 py-4 border-b border-white/10 bg-white/5">
        <p class="text-sm font-semibold text-white truncate">
          {{ authStore.user?.name }}
        </p>
        <span class="inline-block mt-1 text-xs bg-white/20 text-white/80 rounded-full px-2 py-0.5 capitalize">
          {{ authStore.userRole }}
        </span>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 p-4 space-y-1">
        <template v-for="item in sidebarItems" :key="item.to ?? item.name">

          <!-- Lien simple -->
          <RouterLink v-if="item.type === 'link'" :to="item.to"
            class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-white/80 hover:bg-white/10 hover:text-white transition-all"
            :class="{ 'bg-white/20 text-white font-semibold': route.path === item.to }">
            <span class="text-base">{{ item.icon }}</span>
            {{ item.label }}
          </RouterLink>

          <!-- Groupe déroulant -->
          <!-- Groupe déroulant -->
          <div v-else-if="item.type === 'group'">

            <!-- Bouton du groupe -->
            <button @click="toggleGroup(item.name)"
              class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm transition-all" :class="openGroup === item.name
                ? 'bg-white/20 text-white font-semibold'
                : 'text-white/80 hover:bg-white/10 hover:text-white'">
              <span class="flex items-center gap-3">
                <span class="text-base">{{ item.icon }}</span>
                {{ item.label }}
              </span>

              <!-- Flèche : tourne uniquement selon openGroup (plus de isGroupActive) -->
              <span class="text-xs transition-transform duration-200 inline-block"
                :class="openGroup === item.name ? 'rotate-180' : 'rotate-0'">
                ▼
              </span>
            </button>

            <!-- Sous-menus : visibles uniquement si openGroup === item.name -->
            <div v-show="openGroup === item.name" class="mt-1 ml-4 pl-3 border-l border-white/20 space-y-1">
              <RouterLink v-for="child in item.children" :key="child.to" :to="child.to"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-white/70 hover:bg-white/10 hover:text-white transition-all"
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
        <button @click="handleLogout"
          class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-white/70 hover:bg-red-600/30 hover:text-white transition-all">
          <span></span> Déconnexion
        </button>
      </div>
    </aside>

    <!-- ── Contenu principal ── -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
        <h2 class="text-sm font-medium text-gray-500 capitalize">
          {{ route.name?.toString().replace(/\./g, ' › ') }}
        </h2>
        <span class="text-sm text-gray-400">{{ authStore.user?.email }}</span>
      </header>

      <main class="flex-1 overflow-auto p-6">
        <RouterView />
      </main>
    </div>

  </div>
</template>