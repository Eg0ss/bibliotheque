<script setup>
import { onMounted, watch }          from 'vue'
import { useDepotRequestStore }      from '@/stores/depotRequestStore'
import { RouterLink }                from 'vue-router'
import DepotCard                     from '@/components/catalog/DepotCard.vue'

const store = useDepotRequestStore()

onMounted(() => store.fetchMyRequests())

// Relance automatiquement la recherche dès qu'un filtre change
watch(
  () => ({ ...store.filters }),
  () => store.fetchMyRequests(1),
  { deep: true }
)

function goToPage(page) {
  store.fetchMyRequests(page)
}

// Options du select — correspondent aux vrais statuts en base
const statusOptions = [
  { value: 'pending',          label: 'En attente d\'assignation'   },
  // { value: 'assigned',         label: 'Assignée au gestionnaire'    },
  // { value: 'manager_approved', label: 'Validée par le gestionnaire' },
  { value: 'published',        label: 'Publiée'                     },
  { value: 'rejected',         label: 'Rejetée'                     },
]
</script>

<template>
  <div>

    <!-- ── En-tête ─────────────────────────────────────────────────── -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-[#1e3a5f]">Mes dépôts</h1>
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

    <!-- ── Barre recherche + filtre ────────────────────────────────── -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-6">
      <div class="flex flex-wrap gap-3">

        <!-- Recherche par titre ou auteur -->
        <div class="flex-1 min-w-[200px]">
          <label class="block text-xs font-medium text-slate-500 mb-1">Rechercher</label>
          <input
            v-model="store.filters.search"
            type="text"
            placeholder="Titre ou auteur du document..."
            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
          />
        </div>

        <!-- Filtre par statut -->
        <div class="min-w-[210px]">
          <label class="block text-xs font-medium text-slate-500 mb-1">Statut</label>
          <select
            v-model="store.filters.status"
            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                   bg-white focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
          >
            <option value="">Tous les statuts</option>
            <option
              v-for="opt in statusOptions"
              :key="opt.value"
              :value="opt.value"
            >
              {{ opt.label }}
            </option>
          </select>
        </div>

        <!-- Bouton reset — visible seulement si un filtre est actif -->
        <div class="flex items-end">
          <button
            v-if="store.filters.search || store.filters.status"
            @click="store.resetFilters()"
            class="px-3 py-2 text-sm text-slate-500 border border-slate-300
                   rounded-lg hover:bg-slate-50 transition"
          >
            ✕ Réinitialiser
          </button>
        </div>

      </div>

      <!-- Compteur de résultats -->
      <p v-if="store.pagination" class="text-xs text-slate-400 mt-3">
        {{ store.pagination.total }}
        dépôt{{ store.pagination.total > 1 ? 's' : '' }}
        trouvé{{ store.pagination.total > 1 ? 's' : '' }}
      </p>
    </div>

    <!-- ── Chargement ───────────────────────────────────────────────── -->
    <div v-if="store.loading"
      class="flex items-center justify-center py-20 text-gray-400">
      <span class="animate-pulse text-4xl"></span>
      <span class="ml-3">Chargement de vos dépôts…</span>
    </div>

    <!-- ── Aucun résultat ─────────────────────────────────────────── -->
    <div
      v-else-if="store.myRequests.length === 0"
      class="bg-white rounded-xl border border-gray-100 shadow-sm p-14 text-center"
    >
      <div class="text-5xl mb-4">
        {{ store.filters.search || store.filters.status ? '' : '' }}
      </div>
      <p class="text-gray-500 mb-3">
        {{ store.filters.search || store.filters.status
          ? 'Aucun dépôt ne correspond à votre recherche.'
          : 'Vous n\'avez encore soumis aucune demande.' }}
      </p>
      <button
        v-if="store.filters.search || store.filters.status"
        @click="store.resetFilters()"
        class="text-sm text-[#1e3a5f] underline"
      >
        Effacer les filtres
      </button>
      <RouterLink
        v-else
        to="/mon-espace/depots/nouveau"
        class="inline-block bg-[#1e3a5f] text-white text-sm px-5 py-2
               rounded-lg hover:bg-[#0C447C] transition"
      >
        Soumettre un premier document
      </RouterLink>
    </div>

    <!-- ── Grille des dépôts (style inchangé) ─────────────────────── -->
    <div v-else>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
        <DepotCard
          v-for="req in store.myRequests"
          :key="req.id"
          :request="req"
        />
      </div>

      <!-- ── Pagination ──────────────────────────────────────────── -->
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