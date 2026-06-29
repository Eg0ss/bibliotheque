<!-- GET /api/admin/depot-requests -->
<script setup>
import { onMounted } from 'vue'
import { useAssignmentStore } from '@/stores/assignmentStore'
import { useRouter } from 'vue-router'

const store  = useAssignmentStore()
const router = useRouter()

onMounted(() => store.fetchPendingRequests())

function goToPage(page) {
  store.fetchPendingRequests(page)
}

// Redirige vers Assignation avec l'id de la demande pré-sélectionné
function goToAssign(requestId) {
  router.push({ name: 'admin.assignation', query: { depot_request_id: requestId } })
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' })
}
</script>

<template>
  <div>
    <!-- En-tête -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-[#042C53]">Demandes en attente</h1>
      <p class="text-sm text-gray-500 mt-1">
        Liste de toutes les demandes soumises par les utilisateurs, en attente d'assignation.
      </p>
    </div>

    <!-- Chargement -->
    <div v-if="store.loading" class="text-center py-16 text-gray-400">
      ⏳ Chargement...
    </div>

    <!-- Liste vide -->
    <div v-else-if="store.pendingRequests.length === 0"
      class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
      <div class="text-5xl mb-3">📭</div>
      <p class="text-gray-400">Aucune demande en attente pour le moment.</p>
    </div>

    <!-- Tableau -->
    <div v-else class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Document</th>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Demandeur</th>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Type</th>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Catégorie</th>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Soumise le</th>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Statut</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="req in store.pendingRequests" :key="req.id"
            class="hover:bg-gray-50 transition">
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
            <td class="px-5 py-4 text-gray-500">
              {{ req.reference?.type?.name ?? '—' }}
            </td>
            <td class="px-5 py-4 text-gray-500">
              {{ req.reference?.category?.name ?? '—' }}
            </td>
            <td class="px-5 py-4 text-gray-400 text-xs">
              {{ formatDate(req.created_at) }}
            </td>
            <td class="px-5 py-4">
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium"
                :class="req.status === 'pending'
                  ? 'bg-gray-100 text-gray-600'
                  : 'bg-blue-100 text-blue-700'">
                {{ req.status === 'pending' ? '🕐 En attente' : '👤 Assignée' }}
              </span>
            </td>
            <td class="px-5 py-4 text-right">
              <!-- Bouton Assigner uniquement si la demande est encore pending -->
              <button
                v-if="req.status === 'pending'"
                @click="goToAssign(req.id)"
                class="bg-[#042C53] text-white text-xs px-3 py-1.5 rounded-lg
                       hover:bg-[#0C447C] transition"
              >
                Assigner →
              </button>
              <span v-else class="text-xs text-gray-400 italic">Déjà assignée</span>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="store.pagination && store.pagination.last_page > 1"
        class="px-5 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
        <span>Page {{ store.pagination.current_page }} / {{ store.pagination.last_page }}
          ({{ store.pagination.total }} demande{{ store.pagination.total > 1 ? 's' : '' }})
        </span>
        <div class="flex gap-2">
          <button @click="goToPage(store.pagination.current_page - 1)"
            :disabled="store.pagination.current_page === 1"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-40">
            ← Précédent
          </button>
          <button @click="goToPage(store.pagination.current_page + 1)"
            :disabled="store.pagination.current_page === store.pagination.last_page"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-40">
            Suivant →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>