<!-- Correspond à GET /api/admin/users -->
<script setup>
import { onMounted } from 'vue'
import { useUserStore } from '@/stores/userStore'
import { RouterLink } from 'vue-router'

const userStore = useUserStore()

// Charger la liste au montage du composant
onMounted(() => {
  userStore.fetchUsers()
})

// Changer de page dans la pagination
function goToPage(page) {
  userStore.fetchUsers(page)
}
</script>

<template>
  <div>
    <!-- En-tête de la page -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-[#042C53]">Gestion des utilisateurs</h1>
      <RouterLink
        to="/admin/utilisateurs/nouveau"
        class="bg-[#042C53] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#0C447C] transition"
      >
        + Créer un compte
      </RouterLink>
    </div>

    <!-- État de chargement -->
    <div v-if="userStore.loading" class="text-center py-12 text-gray-400">
      Chargement...
    </div>

    <!-- Tableau des utilisateurs -->
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
              <!-- Badge de rôle coloré -->
              <span class="px-2 py-1 rounded-full text-xs font-medium"
                :class="{
                  'bg-red-100 text-red-700'    : user.role?.slug === 'admin',
                  'bg-blue-100 text-blue-700'  : user.role?.slug === 'gestionnaire',
                  'bg-purple-100 text-purple-700': user.role?.slug === 'rh',
                  'bg-gray-100 text-gray-700'  : user.role?.slug === 'user',
                }"
              >
                {{ user.role?.name ?? '—' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <!-- Badge actif / inactif -->
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

          <!-- Si aucun résultat -->
          <tr v-if="userStore.users.length === 0">
            <td colspan="6" class="text-center py-8 text-gray-400">
              Aucun utilisateur trouvé.
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="userStore.pagination" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
        <span>
          Page {{ userStore.pagination.current_page }} / {{ userStore.pagination.last_page }}
        </span>
        <div class="flex gap-2">
          <button
            @click="goToPage(userStore.pagination.current_page - 1)"
            :disabled="userStore.pagination.current_page === 1"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-40"
          >
            ← Précédent
          </button>
          <button
            @click="goToPage(userStore.pagination.current_page + 1)"
            :disabled="userStore.pagination.current_page === userStore.pagination.last_page"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-40"
          >
            Suivant →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>