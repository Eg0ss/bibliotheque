<script setup>
import { onMounted, watch } from 'vue'
import { useAssignmentStore } from '@/stores/assignmentStore'
import { useRouter }          from 'vue-router'

const store  = useAssignmentStore()
const router = useRouter()

onMounted(async () => {
  // On charge les options de filtre en parallèle des demandes
  await Promise.all([
    store.fetchPendingRequests(),
    store.fetchFilterOptions(),
  ])
})

// Relance automatiquement quand un filtre change
watch(
  () => ({ ...store.filters }),
  () => store.fetchPendingRequests(1),
  { deep: true }
)

function goToPage(page) {
  store.fetchPendingRequests(page)
}

function goToAssign(requestId) {
  router.push({ name: 'admin.assignation', query: { depot_request_id: requestId } })
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'long', year: 'numeric',
  })
}
</script>

<template>
  <div>

    <!-- ── En-tête ─────────────────────────────────────────────────── -->
    <div class="mb-5">
      <h1 class="text-2xl font-bold text-[#042C53]">Demandes en attente</h1>
      <p class="text-sm text-gray-500 mt-1">
        Références soumises par les utilisateurs, non encore assignées à un gestionnaire.
      </p>
    </div>

    <!-- ── Barre de recherche + filtres ───────────────────────────── -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-4">
      <div class="flex flex-wrap gap-3">

        <!-- Recherche texte -->
        <div class="flex-1 min-w-[220px]">
          <label class="block text-xs font-medium text-gray-500 mb-1">
            Rechercher
          </label>
          <input
            v-model="store.filters.search"
            type="text"
            placeholder="Titre, auteur ou nom du demandeur..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#042C53]"
          />
        </div>

        <!-- Filtre par type -->
        <div class="min-w-[170px]">
          <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
          <select
            v-model="store.filters.type_id"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                   bg-white focus:outline-none focus:ring-2 focus:ring-[#042C53]"
          >
            <option value="">Tous les types</option>
            <option v-for="t in store.types" :key="t.id" :value="t.id">
              {{ t.name }}
            </option>
          </select>
        </div>

        <!-- Filtre par catégorie -->
        <div class="min-w-[170px]">
          <label class="block text-xs font-medium text-gray-500 mb-1">Catégorie</label>
          <select
            v-model="store.filters.category_id"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                   bg-white focus:outline-none focus:ring-2 focus:ring-[#042C53]"
          >
            <option value="">Toutes les catégories</option>
            <option v-for="c in store.categories" :key="c.id" :value="c.id">
              {{ c.name }}
            </option>
          </select>
        </div>

        <!-- Bouton reset -->
        <div class="flex items-end">
          <button
            v-if="store.filters.search || store.filters.type_id || store.filters.category_id"
            @click="store.resetFilters()"
            class="px-3 py-2 text-sm text-gray-500 border border-gray-300
                   rounded-lg hover:bg-gray-50 transition"
          >
            ✕ Réinitialiser
          </button>
        </div>

      </div>

      <!-- Compteur -->
      <p v-if="store.pagination" class="text-xs text-gray-400 mt-3">
        {{ store.pagination.total }}
        demande{{ store.pagination.total > 1 ? 's' : '' }}
        en attente{{ store.pagination.total > 1 ? '' : '' }}
      </p>
    </div>

    <!-- ── Chargement ───────────────────────────────────────────────── -->
    <div v-if="store.loading" class="text-center py-16 text-gray-400">
       Chargement...
    </div>

    <!-- ── Aucun résultat ─────────────────────────────────────────── -->
    <div
      v-else-if="store.pendingRequests.length === 0"
      class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center"
    >
      <div class="text-5xl mb-3">
        {{ store.filters.search || store.filters.type_id || store.filters.category_id
          ? '' : '' }}
      </div>
      <p class="text-gray-400 mb-2">
        {{ store.filters.search || store.filters.type_id || store.filters.category_id
          ? 'Aucune demande ne correspond à votre recherche.'
          : 'Aucune demande en attente pour le moment.' }}
      </p>
      <button
        v-if="store.filters.search || store.filters.type_id || store.filters.category_id"
        @click="store.resetFilters()"
        class="text-sm text-[#042C53] underline"
      >
        Effacer les filtres
      </button>
    </div>

    <!-- ── Tableau ─────────────────────────────────────────────────── -->
    <div v-else class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Document</th>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Demandeur</th>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Type</th>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Catégorie</th>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Soumise le</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="req in store.pendingRequests"
            :key="req.id"
            class="hover:bg-gray-50 transition"
          >
            <td class="px-5 py-4">
              <p class="font-medium text-gray-900 truncate max-w-[180px]">
                {{ req.reference?.title ?? '—' }}
              </p>
              <p class="text-xs text-gray-400 mt-0.5">
                {{ req.reference?.author ?? '' }}
              </p>
            </td>
            <td class="px-5 py-4">
              <p class="text-gray-700">{{ req.user?.name ?? '—' }}</p>
              <p class="text-xs text-gray-400">{{ req.user?.email }}</p>
            </td>
            <td class="px-5 py-4">
              <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded-full">
                {{ req.reference?.type?.name ?? '—' }}
              </span>
            </td>
            <td class="px-5 py-4">
              <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                {{ req.reference?.category?.name ?? '—' }}
              </span>
            </td>
            <td class="px-5 py-4 text-gray-400 text-xs">
              {{ formatDate(req.created_at) }}
            </td>
            <td class="px-5 py-4 text-right">
              <button
                @click="goToAssign(req.id)"
                class="bg-[#042C53] text-white text-xs px-3 py-1.5 rounded-lg
                       hover:bg-[#0C447C] transition"
              >
                Assigner →
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div
        v-if="store.pagination && store.pagination.last_page > 1"
        class="px-5 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500"
      >
        <span>
          Page {{ store.pagination.current_page }} / {{ store.pagination.last_page }}
          ({{ store.pagination.total }} demande{{ store.pagination.total > 1 ? 's' : '' }})
        </span>
        <div class="flex gap-2">
          <button
            @click="goToPage(store.pagination.current_page - 1)"
            :disabled="store.pagination.current_page === 1"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-40"
          >
            ← Précédent
          </button>
          <button
            @click="goToPage(store.pagination.current_page + 1)"
            :disabled="store.pagination.current_page === store.pagination.last_page"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-40"
          >
            Suivant →
          </button>
        </div>
      </div>
    </div>

  </div>
</template>