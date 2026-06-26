<!-- GET /api/user/depot-requests -->
<script setup>
import { onMounted, computed } from 'vue'
import { useDepotRequestStore } from '@/stores/depotRequestStore'
import { RouterLink } from 'vue-router'

const store = useDepotRequestStore()

onMounted(() => store.fetchMyRequests())

function goToPage(page) {
  store.fetchMyRequests(page)
}

// ── Configuration des badges de statut ──────────────────────────────
// Chaque statut a un libellé FR, une couleur de fond et une couleur de texte
const statusConfig = {
  pending: {
    label : 'En attente',
    bg    : 'bg-gray-100',
    text  : 'text-gray-600',
    icon  : '🕐',
  },
  assigned: {
    label : 'Assignée',
    bg    : 'bg-blue-100',
    text  : 'text-blue-700',
    icon  : '👤',
  },
  en_verification: {
    label : 'En vérification',
    bg    : 'bg-yellow-100',
    text  : 'text-yellow-700',
    icon  : '🔍',
  },
  acceptee_partiel: {
    label : 'Acceptée partiellement',
    bg    : 'bg-teal-100',
    text  : 'text-teal-700',
    icon  : '✔️',
  },
  rejetee_partiel: {
    label : 'Rejetée partiellement',
    bg    : 'bg-orange-100',
    text  : 'text-orange-700',
    icon  : '↩️',
  },
  soumise_publication: {
    label : 'Soumise pour publication',
    bg    : 'bg-purple-100',
    text  : 'text-purple-700',
    icon  : '📤',
  },
  acceptee_definitif: {
    label : 'Acceptée définitivement',
    bg    : 'bg-green-100',
    text  : 'text-green-700',
    icon  : '✅',
  },
  rejetee_definitif: {
    label : 'Rejetée définitivement',
    bg    : 'bg-red-100',
    text  : 'text-red-700',
    icon  : '❌',
  },
}

// Retourne la config du badge selon le statut, avec un fallback
function getStatus(status) {
  return statusConfig[status] ?? {
    label: status,
    bg   : 'bg-gray-100',
    text : 'text-gray-500',
    icon : '❓',
  }
}
</script>

<template>
  <div>

    <!-- ── En-tête ── -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-[#1e3a5f]">Mes demandes de dépôt</h1>
        <p class="text-sm text-gray-500 mt-1">
          Suivez l'état de traitement de vos documents soumis.
        </p>
      </div>
      <RouterLink
        to="/mon-espace/depots/nouveau"
        class="bg-[#1e3a5f] text-white text-sm px-4 py-2 rounded-lg
               hover:bg-[#0C447C] transition flex items-center gap-2"
      >
        <span>➕</span> Nouvelle demande
      </RouterLink>
    </div>

    <!-- ── Chargement ── -->
    <div v-if="store.loading" class="flex items-center justify-center py-16 text-gray-400">
      <span class="animate-spin mr-2">⏳</span> Chargement de vos demandes...
    </div>

    <!-- ── Liste vide ── -->
    <div
      v-else-if="store.myRequests.length === 0"
      class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center"
    >
      <div class="text-5xl mb-4">📂</div>
      <p class="text-gray-500 mb-4">Vous n'avez encore soumis aucune demande.</p>
      <RouterLink
        to="/mon-espace/depots/nouveau"
        class="inline-block bg-[#1e3a5f] text-white text-sm px-5 py-2 rounded-lg
               hover:bg-[#0C447C] transition"
      >
        Soumettre un premier document
      </RouterLink>
    </div>

    <!-- ── Liste des demandes ── -->
    <div v-else class="space-y-4">
      <div
        v-for="req in store.myRequests"
        :key="req.id"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-5
               hover:shadow-md transition-shadow"
      >
        <div class="flex items-start justify-between gap-4">

          <!-- Infos principales -->
          <div class="flex-1 min-w-0">

            <!-- Titre du document -->
            <h2 class="text-base font-semibold text-[#1e3a5f] truncate">
              {{ req.reference?.title ?? '—' }}
            </h2>

            <!-- Auteur + catégorie + type -->
            <div class="flex flex-wrap items-center gap-2 mt-1">
              <span class="text-sm text-gray-500">
                {{ req.reference?.author ?? '—' }}
              </span>
              <span class="text-gray-300">·</span>
              <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                {{ req.reference?.category?.name ?? 'Sans catégorie' }}
              </span>
              <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                {{ req.reference?.type?.name ?? 'Sans type' }}
              </span>
            </div>

            <!-- Date de soumission -->
            <p class="text-xs text-gray-400 mt-2">
              Soumise le {{ new Date(req.created_at).toLocaleDateString('fr-FR', {
                day: '2-digit', month: 'long', year: 'numeric'
              }) }}
            </p>

            <!-- Justification / motif de décision (visible si présent) -->
            <div
              v-if="req.rejection_reason"
              class="mt-3 p-3 bg-red-50 border border-red-100 rounded-lg text-sm text-red-700"
            >
              <span class="font-medium">💬 Retour :</span> {{ req.rejection_reason }}
            </div>

          </div>

          <!-- Badge de statut -->
          <div class="flex-shrink-0">
            <span
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium"
              :class="[getStatus(req.status).bg, getStatus(req.status).text]"
            >
              {{ getStatus(req.status).icon }}
              {{ getStatus(req.status).label }}
            </span>
          </div>

        </div>
      </div>

      <!-- ── Pagination ── -->
      <div
        v-if="store.pagination && store.pagination.last_page > 1"
        class="flex items-center justify-between pt-2 text-sm text-gray-500"
      >
        <span>
          Page {{ store.pagination.current_page }} / {{ store.pagination.last_page }}
          <span class="text-gray-400">
            ({{ store.pagination.total }} demande{{ store.pagination.total > 1 ? 's' : '' }})
          </span>
        </span>
        <div class="flex gap-2">
          <button
            @click="goToPage(store.pagination.current_page - 1)"
            :disabled="store.pagination.current_page === 1"
            class="px-3 py-1.5 rounded-lg border text-sm hover:bg-gray-50
                   disabled:opacity-40 disabled:cursor-not-allowed transition"
          >
            ← Précédent
          </button>
          <button
            @click="goToPage(store.pagination.current_page + 1)"
            :disabled="store.pagination.current_page === store.pagination.last_page"
            class="px-3 py-1.5 rounded-lg border text-sm hover:bg-gray-50
                   disabled:opacity-40 disabled:cursor-not-allowed transition"
          >
            Suivant →
          </button>
        </div>
      </div>

    </div>
  </div>
</template>