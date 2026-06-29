<!-- GET /api/gestionnaire/documents -->
<script setup>
import { onMounted } from 'vue'
import { useGestionnaireStore } from '@/stores/gestionnaireStore'

const store = useGestionnaireStore()

onMounted(() => store.fetchDocuments())

function goToPage(page) {
  store.fetchDocuments(page)
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'long', year: 'numeric',
  })
}

// Badge de statut de la demande
const statusConfig = {
  pending  : { label: 'En attente',   bg: 'bg-gray-100',   text: 'text-gray-600'  },
  assigned : { label: 'Assignée',     bg: 'bg-blue-100',   text: 'text-blue-700'  },
  rejected : { label: 'Rejetée',      bg: 'bg-red-100',    text: 'text-red-700'   },
  published: { label: 'Publiée',      bg: 'bg-green-100',  text: 'text-green-700' },
}

function getStatus(status) {
  return statusConfig[status] ?? { label: status, bg: 'bg-gray-100', text: 'text-gray-500' }
}
</script>

<template>
  <div>

    <!-- En-tête -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-[#042C53]">Documents à vérifier</h1>
      <p class="text-sm text-gray-500 mt-1">
        Références documentaires qui vous ont été assignées pour vérification.
      </p>
    </div>

    <!-- Chargement -->
    <div v-if="store.loading" class="text-center py-16 text-gray-400">
      ⏳ Chargement...
    </div>

    <!-- Liste vide -->
    <div v-else-if="store.documents.length === 0"
      class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
      <div class="text-5xl mb-3">📭</div>
      <p class="text-gray-400">Aucun document ne vous a été assigné pour le moment.</p>
    </div>

    <!-- Liste des assignations -->
    <div v-else class="space-y-4">
      <div
        v-for="assignment in store.documents"
        :key="assignment.id"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow"
      >
        <div class="flex items-start justify-between gap-4">

          <!-- Infos du document -->
          <div class="flex-1 min-w-0">

            <!-- Titre -->
            <h2 class="text-base font-semibold text-[#042C53] truncate">
              {{ assignment.depot_request?.reference?.title ?? '—' }}
            </h2>

            <!-- Méta : auteur · type · catégorie -->
            <div class="flex flex-wrap items-center gap-2 mt-1">
              <span class="text-sm text-gray-500">
                ✍️ {{ assignment.depot_request?.reference?.author ?? '—' }}
              </span>
              <span class="text-gray-300">·</span>
              <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">
                {{ assignment.depot_request?.reference?.type?.name ?? 'Sans type' }}
              </span>
              <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                {{ assignment.depot_request?.reference?.category?.name ?? 'Sans catégorie' }}
              </span>
            </div>

            <!-- Demandeur -->
            <div class="mt-2 text-xs text-gray-400 flex items-center gap-4">
              <span>
                👤 Soumis par
                <span class="font-medium text-gray-600">
                  {{ assignment.depot_request?.user?.name ?? '—' }}
                </span>
              </span>
              <span>
                🗓️ {{ formatDate(assignment.created_at) }}
              </span>
            </div>

            <!-- Instructions de l'admin -->
            <div
              v-if="assignment.instructions"
              class="mt-3 p-3 bg-amber-50 border border-amber-100 rounded-lg text-sm text-amber-800"
            >
              <span class="font-medium">📝 Instructions de l'admin :</span>
              {{ assignment.instructions }}
            </div>

            <!-- Fichiers joints -->
            <div
              v-if="assignment.depot_request?.reference?.documents?.length"
              class="mt-3 flex flex-wrap gap-2"
            >
              <span
                v-for="doc in assignment.depot_request.reference.documents"
                :key="doc.id"
                class="inline-flex items-center gap-1 text-xs bg-gray-100 text-gray-600
                       px-2 py-1 rounded-full"
              >
                📄 {{ doc.original_name ?? 'Fichier joint' }}
              </span>
            </div>
            <p v-else class="mt-2 text-xs text-gray-400 italic">
              Aucun fichier joint pour le moment.
            </p>

          </div>

          <!-- Colonne droite : statut + assigné par -->
          <div class="flex flex-col items-end gap-2 flex-shrink-0">

            <!-- Badge statut de la demande -->
            <span
              class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
              :class="[
                getStatus(assignment.depot_request?.status).bg,
                getStatus(assignment.depot_request?.status).text
              ]"
            >
              {{ getStatus(assignment.depot_request?.status).label }}
            </span>

            <!-- Assigné par -->
            <span class="text-xs text-gray-400">
              Assigné par
              <span class="font-medium text-gray-500">
                {{ assignment.assigned_by?.name ?? '—' }}
              </span>
            </span>

          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div
        v-if="store.pagination && store.pagination.last_page > 1"
        class="flex items-center justify-between pt-2 text-sm text-gray-500"
      >
        <span>
          Page {{ store.pagination.current_page }} / {{ store.pagination.last_page }}
          ({{ store.pagination.total }} document{{ store.pagination.total > 1 ? 's' : '' }})
        </span>
        <div class="flex gap-2">
          <button
            @click="goToPage(store.pagination.current_page - 1)"
            :disabled="store.pagination.current_page === 1"
            class="px-3 py-1.5 rounded-lg border hover:bg-gray-50 disabled:opacity-40 transition"
          >
            ← Précédent
          </button>
          <button
            @click="goToPage(store.pagination.current_page + 1)"
            :disabled="store.pagination.current_page === store.pagination.last_page"
            class="px-3 py-1.5 rounded-lg border hover:bg-gray-50 disabled:opacity-40 transition"
          >
            Suivant →
          </button>
        </div>
      </div>
    </div>

  </div>
</template>