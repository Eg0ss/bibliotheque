<script setup>
/**
 * UsersListView.vue
 * Liste des utilisateurs avec recherche + filtres par statut et rôle
 * Route : /admin/utilisateurs
 */

import { onMounted, watch } from 'vue'
import { useUserStore }     from '@/stores/userStore'
import { RouterLink }       from 'vue-router'

const userStore = useUserStore()

// ── Au chargement de la page ──────────────────────────────────────────────
onMounted(async () => {
  // On charge simultanément les users ET les rôles (pour le select de filtre)
  await Promise.all([
    userStore.fetchUsers(),
    userStore.fetchRoles(),
  ])
})

// ── Surveiller les filtres pour relancer la recherche automatiquement ─────
// watch surveille les 3 filtres à la fois (deep: true = surveille les sous-propriétés)
// Dès qu'un filtre change → on revient à la page 1 et on relance fetchUsers
watch(
  () => ({ ...userStore.filters }),
  () => {
    userStore.fetchUsers(1)
  },
  { deep: true }
)

// ── Changer de page ───────────────────────────────────────────────────────
function goToPage(page) {
  userStore.fetchUsers(page)
}
</script>

<template>
  <div>

    <!-- ── En-tête ─────────────────────────────────────────────────────── -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-[#042C53]">Gestion des utilisateurs</h1>
      <RouterLink
        to="/admin/utilisateurs/nouveau"
        class="bg-[#042C53] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#0C447C] transition"
      >
        + Créer un compte
      </RouterLink>
    </div>

    <!-- ── Barre de recherche + filtres ───────────────────────────────── -->
    <!--
      On bind directement sur userStore.filters avec v-model
      Le watch ci-dessus détecte chaque changement et relance fetchUsers()
    -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
      <div class="flex flex-wrap gap-3">

        <!-- Recherche par nom / email -->
        <div class="flex-1 min-w-[200px]">
          <label class="block text-xs font-medium text-gray-500 mb-1">Rechercher</label>
          <input
            v-model="userStore.filters.search"
            type="text"
            placeholder="Nom ou adresse e-mail..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
          />
        </div>

        <!-- Filtre par statut -->
        <div class="min-w-[150px]">
          <label class="block text-xs font-medium text-gray-500 mb-1">Statut</label>
          <select
            v-model="userStore.filters.status"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
          >
            <option value="">Tous les statuts</option>
            <option value="1">Actifs</option>
            <option value="0">Inactifs</option>
          </select>
        </div>

        <!-- Filtre par rôle (alimenté par userStore.roles) -->
        <div class="min-w-[160px]">
          <label class="block text-xs font-medium text-gray-500 mb-1">Rôle</label>
          <select
            v-model="userStore.filters.role_id"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
          >
            <option value="">Tous les rôles</option>
            <!-- Les rôles sont chargés depuis fetchRoles() dans onMounted -->
            <option v-for="role in userStore.roles" :key="role.id" :value="role.id">
              {{ role.name }}
            </option>
          </select>
        </div>

        <!-- Bouton réinitialiser (affiché seulement si un filtre est actif) -->
        <div class="flex items-end">
          <button
            v-if="userStore.filters.search || userStore.filters.status !== '' || userStore.filters.role_id !== ''"
            @click="userStore.resetFilters()"
            class="px-3 py-2 text-sm text-gray-500 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
          >
            ✕ Réinitialiser
          </button>
        </div>

      </div>

      <!-- Compteur de résultats -->
      <p v-if="userStore.pagination" class="text-xs text-gray-400 mt-3">
        {{ userStore.pagination.total }} utilisateur{{ userStore.pagination.total > 1 ? 's' : '' }} trouvé{{ userStore.pagination.total > 1 ? 's' : '' }}
      </p>
    </div>

    <!-- ── Chargement ──────────────────────────────────────────────────── -->
    <div v-if="userStore.loading" class="text-center py-12 text-gray-400">
      Chargement...
    </div>

    <!-- ── Tableau ─────────────────────────────────────────────────────── -->
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-6 py-3 text-gray-600 font-medium">Nom</th>
            <th class="text-left px-6 py-3 text-gray-600 font-medium">Email</th>
            <th class="text-left px-6 py-3 text-gray-600 font-medium">Rôle</th>
            <th class="text-left px-6 py-3 text-gray-600 font-medium">Statut</th>
            <th class="text-left px-6 py-3 text-gray-600 font-medium">Créé le</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="user in userStore.users"
            :key="user.id"
            class="hover:bg-gray-50 transition"
          >
            <td class="px-6 py-4 font-medium text-gray-900">{{ user.name }}</td>
            <td class="px-6 py-4 text-gray-600">{{ user.email }}</td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 rounded-full text-xs font-medium"
                :class="{
                  'bg-red-100 text-red-700'      : user.role?.slug === 'admin',
                  'bg-blue-100 text-blue-700'    : user.role?.slug === 'gestionnaire',
                  'bg-purple-100 text-purple-700': user.role?.slug === 'rh',
                  'bg-gray-100 text-gray-700'    : user.role?.slug === 'user',
                }"
              >
                {{ user.role?.name ?? '—' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 rounded-full text-xs font-medium"
                :class="user.is_active
                  ? 'bg-green-100 text-green-700'
                  : 'bg-red-100 text-red-700'"
              >
                {{ user.is_active ? 'Actif' : 'Inactif' }}
              </span>
            </td>
            <td class="px-6 py-4 text-gray-500">{{ user.created_at }}</td>
            <td class="px-6 py-4 text-right">
              <RouterLink
                :to="`/admin/utilisateurs/${user.id}`"
                class="text-[#0C447C] hover:underline text-xs"
              >
                Voir
              </RouterLink>
            </td>
          </tr>

          <!-- Aucun résultat -->
          <tr v-if="userStore.users.length === 0">
            <td colspan="6" class="text-center py-10 text-gray-400">
              <p class="text-base mb-1">Aucun utilisateur trouvé</p>
              <p class="text-xs">Essayez de modifier vos critères de recherche</p>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ── Pagination ────────────────────────────────────────────────── -->
      <div
        v-if="userStore.pagination && userStore.pagination.last_page > 1"
        class="px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500"
      >
        <span>
          Page {{ userStore.pagination.current_page }} / {{ userStore.pagination.last_page }}
        </span>
        <div class="flex gap-2">
          <button
            @click="goToPage(userStore.pagination.current_page - 1)"
            :disabled="userStore.pagination.current_page === 1"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-40 transition"
          >
            ← Précédent
          </button>
          <button
            @click="goToPage(userStore.pagination.current_page + 1)"
            :disabled="userStore.pagination.current_page === userStore.pagination.last_page"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-40 transition"
          >
            Suivant →
          </button>
        </div>
      </div>
    </div>

  </div>
</template>