<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { RouterLink, useRoute } from 'vue-router'
import { useNotificationStore } from '@/stores/notificationStore'

const notifStore = useNotificationStore()
const showNotifPanel = ref(false)
const authStore = useAuthStore()
const route = useRoute()
const openGroup = ref(null)


// Charger le count au montage de la sidebar
onMounted(() => {
  if (authStore.userRole === 'gestionnaire') {
    notifStore.fetchUnreadCount()
  }
})

function toggleNotifPanel() {
  showNotifPanel.value = !showNotifPanel.value
  if (showNotifPanel.value) {
    notifStore.fetchNotifications()
  }
}


function toggleGroup(name) {
  openGroup.value = openGroup.value === name ? null : name
}

// ── sidebarItems déclaré EN PREMIER ──────────────────────────
const sidebarItems = computed(() => {
  const role = authStore.userRole

  if (role === 'admin') {
    return [
      { type: 'link', to: '/admin', label: 'Tableau de bord' },
      { type: 'link', to: '/admin/demandes-attente', label: 'Demandes en attente' },
      { type: 'link', to: '/admin/demandes-traitees', label: 'Demandes traitées' },
      {
        type: 'group',
        name: 'initialisation',
        label: 'Initialisation',
        children: [
          { to: '/admin/utilisateurs', label: 'Utilisateurs' },
          { to: '/admin/categories', label: 'Catégories' },
          { to: '/admin/types', label: 'Types' },
        ],
      },
      { type: 'link', to: '/admin/publication', label: 'Publications' },
      { type: 'link', to: '/admin/assignation', label: 'Assignations' },
      { type: 'link', to: '/admin/decision', label: 'Décisions finales' },
    ]
  }

  if (role === 'gestionnaire') {
    return [
      { type: 'link', to: '/gestionnaire', label: 'Tableau de bord' },
      { type: 'link', to: '/gestionnaire/documents', label: 'Documents à vérifier' },
      { type: 'link', to: '/gestionnaire/validations', label: 'Mes validations' },
    ]
  }

  if (role === 'rh') {
    return [
      { type: 'link', to: '/rh', label: 'Tableau de bord' },
      { type: 'link', to: '/rh/utilisateurs', label: 'Utilisateurs' },
      { type: 'link', to: '/rh/historique', label: 'Historique' },
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
    <aside class="fixed left-0 top-0 h-screen w-64 bg-[#042C53] text-white flex flex-col overflow-y-auto z-40">

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

        <div class="flex items-center gap-4">

          <!-- 🔔 Cloche notifications (visible uniquement pour le gestionnaire) -->
          <div v-if="authStore.userRole === 'gestionnaire'" class="relative">
            <button @click="toggleNotifPanel"
              class="relative p-2 rounded-lg hover:bg-gray-100 transition text-gray-500">
              🔔
              <!-- Badge rouge avec le nombre de non lues -->
              <span v-if="notifStore.unreadCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs
                 rounded-full h-5 w-5 flex items-center justify-center font-bold">
                {{ notifStore.unreadCount > 9 ? '9+' : notifStore.unreadCount }}
              </span>
            </button>

            <!-- Panel de notifications -->
            <div v-if="showNotifPanel" class="absolute right-0 top-10 w-80 bg-white rounded-xl shadow-xl border
               border-gray-200 z-50 overflow-hidden">

              <!-- Header panel -->
              <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-800">Notifications</span>
                <button v-if="notifStore.unreadCount > 0" @click="notifStore.markAllRead()"
                  class="text-xs text-[#042C53] hover:underline">
                  Tout marquer comme lu
                </button>
              </div>

              <!-- Liste -->
              <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                <div v-if="notifStore.loading" class="py-8 text-center text-gray-400 text-sm">
                  Chargement...
                </div>
                <div v-else-if="notifStore.notifications.length === 0" class="py-8 text-center text-gray-400 text-sm">
                  Aucune notification
                </div>
                <div v-else v-for="notif in notifStore.notifications" :key="notif.id"
                  @click="notifStore.markRead(notif.id)" class="px-4 py-3 cursor-pointer hover:bg-gray-50 transition"
                  :class="{ 'bg-blue-50': !notif.read_at }">
                  <!-- Point bleu si non lue -->
                  <div class="flex items-start gap-2">
                    <span v-if="!notif.read_at" class="mt-1.5 h-2 w-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                    <span v-else class="mt-1.5 h-2 w-2 flex-shrink-0"></span>
                    <div>
                      <p class="text-sm text-gray-800 font-medium">
                        {{ notif.data.reference_title }}
                      </p>
                      <p class="text-xs text-gray-500 mt-0.5">
                        {{ notif.data.message }}
                      </p>
                      <p class="text-xs text-gray-400 mt-1">
                        Par {{ notif.data.assigned_by_name }} · {{ notif.data.assigned_at }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <span class="text-sm text-gray-400">{{ authStore.user?.email }}</span>
        </div>
      </header>

      <main class="ml-69 flex-1 min-h-screen overflow-y-auto">
        <RouterView />
      </main>
    </div>

  </div>
</template>