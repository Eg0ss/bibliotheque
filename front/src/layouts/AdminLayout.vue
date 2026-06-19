<script setup>
import { computed } from 'vue'
import NavBar from '@/components/common/NavBar.vue'
import { useAuthStore } from '@/stores/authStore'
import { RouterLink } from 'vue-router'

const authStore = useAuthStore()

const sidebarItems = computed(() => {
  const items = []
  if (authStore.role === 'admin') {
    items.push({ to: '/admin', label: 'Publication' })
    items.push({ to: '/admin/assignation', label: 'Assignation' })
    items.push({ to: '/admin/decision', label: 'Décision finale' })
  }
  if (authStore.role === 'gestionnaire') {
    items.push({ to: '/gestionnaire/documents', label: 'Documents en attente' })
    items.push({ to: '/gestionnaire/parametres', label: 'Paramètres de validation' })
  }
  if (authStore.role === 'rh') {
    items.push({ to: '/rh/utilisateurs', label: 'Utilisateurs' })
    items.push({ to: '/rh/historique', label: 'Historique des actions' })
  }
  return items
})
</script>

<template>
  <div class="min-h-screen flex bg-[#f8f9fb]">
    <!-- Sidebar -->
    <aside class="w-64 bg-[#1e3a5f] text-white flex flex-col">
      <div class="p-4 border-b border-white/20">
        <h2 class="text-lg font-semibold">Tableau de bord</h2>
        <p class="text-sm opacity-80">{{ authStore.user?.name }}</p>
        <p class="text-xs opacity-60 capitalize">{{ authStore.role }}</p>
      </div>
      <nav class="flex-1 p-4">
        <ul class="space-y-2">
          <li v-for="item in sidebarItems" :key="item.to">
            <RouterLink
              :to="item.to"
              class="block px-4 py-2 rounded-lg hover:bg-white/10 transition"
              :class="{ 'bg-white/20': $route.path === item.to }"
            >
              {{ item.label }}
            </RouterLink>
          </li>
        </ul>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
      <NavBar />
      <main class="flex-1 p-8">
        <RouterView />
      </main>
    </div>
  </div>
</template>
