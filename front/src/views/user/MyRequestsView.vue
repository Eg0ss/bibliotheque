<!-- GET /api/user/depot-requests -->
<script setup>
import { onMounted } from 'vue'
import { useDepotRequestStore } from '@/stores/depotRequestStore'
import { RouterLink } from 'vue-router'
import DepotCard from '@/components/catalog/DepotCard.vue'

const store = useDepotRequestStore()

onMounted(() => store.fetchMyRequests())

function goToPage(page) {
  store.fetchMyRequests(page)
}
</script>

<template>
  <div>

    <!-- En-tête -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-[#1e3a5f]">Mes dépôts</h1>
        <p class="text-sm text-gray-500 mt-1">
          Suivez l'état de traitement de vos documents soumis.
        </p>
      </div>
      <RouterLink
        to="/mon-espace/depots/nouveau"
        class="bg-[#1e3a5f] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#0C447C] transition flex items-center gap-2"
      >
        <span>➕</span> Nouvelle demande
      </RouterLink>
    </div>

    <!-- Chargement -->
    <div v-if="store.loading" class="flex items-center justify-center py-20 text-gray-400">
      <span class="animate-pulse text-4xl">📚</span>
      <span class="ml-3">Chargement de vos dépôts…</span>
    </div>

    <!-- Liste vide -->
    <div
      v-else-if="store.myRequests.length === 0"
      class="bg-white rounded-xl border border-gray-100 shadow-sm p-14 text-center"
    >
      <div class="text-5xl mb-4">📂</div>
      <p class="text-gray-500 mb-4">Vous n'avez encore soumis aucune demande.</p>
      <RouterLink
        to="/mon-espace/depots/nouveau"
        class="inline-block bg-[#1e3a5f] text-white text-sm px-5 py-2 rounded-lg hover:bg-[#0C447C] transition"
      >
        Soumettre un premier document
      </RouterLink>
    </div>

    <!-- Grille des dépôts -->
    <div v-else>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
        <DepotCard
          v-for="req in store.myRequests"
          :key="req.id"
          :request="req"
        />
      </div>

      <!-- Pagination -->
      <div
        v-if="store.pagination && store.pagination.last_page > 1"
        class="flex items-center justify-between pt-6 text-sm text-gray-500"
      >
        <span>
          Page {{ store.pagination.current_page }} / {{ store.pagination.last_page }}
          <span class="text-gray-400">
            ({{ store.pagination.total }} dépôt{{ store.pagination.total > 1 ? 's' : '' }})
          </span>
        </span>
        <div class="flex gap-2">
          <button
            @click="goToPage(store.pagination.current_page - 1)"
            :disabled="store.pagination.current_page === 1"
            class="px-3 py-1.5 rounded-lg border text-sm hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
          >
            ← Précédent
          </button>
          <button
            @click="goToPage(store.pagination.current_page + 1)"
            :disabled="store.pagination.current_page === store.pagination.last_page"
            class="px-3 py-1.5 rounded-lg border text-sm hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
          >
            Suivant →
          </button>
        </div>
      </div>
    </div>

  </div>
</template>