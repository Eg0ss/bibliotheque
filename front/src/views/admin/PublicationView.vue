<script setup>
/**
 * PublicationView.vue
 * Affiche toutes les références dont le statut est 'published'.
 * C'est une vue de consultation pour l'admin : il voit ce qui est visible dans le catalogue.
 *
 * Route : /admin/publication
 */
import { ref, onMounted } from 'vue'
import apiClient from '@/api/axios'

const references = ref([])   // Liste des références publiées
const loading    = ref(false)
const error      = ref(null)

/**
 * Charge les références publiées depuis l'API publique.
 * On utilise /api/references (route publique) car elle filtre déjà status = published.
 */
async function fetchPublished() {
  loading.value = true
  error.value   = null

  try {
    const res      = await apiClient.get('/references')
    // L'API retourne { data: [...] } grâce à ReferenceResource::collection()
    references.value = res.data.data ?? []
  } catch (err) {
    error.value = 'Impossible de charger les références publiées.'
    console.error(err)
  } finally {
    loading.value = false
  }
}

// Chargement automatique à l'ouverture de la page
onMounted(() => fetchPublished())
</script>

<template>
  <div>
    <!-- En-tête -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-[#042C53]">Publications</h1>
      <p class="text-sm text-gray-500 mt-1">
        Références actuellement visibles dans le catalogue public.
      </p>
    </div>

    <!-- Chargement -->
    <div v-if="loading" class="text-center py-16 text-gray-400">
      ⏳ Chargement...
    </div>

    <!-- Erreur -->
    <div v-else-if="error" class="text-center py-16 text-red-500">
      {{ error }}
    </div>

    <!-- Aucune référence publiée -->
    <div v-else-if="references.length === 0"
      class="bg-white rounded-xl border border-gray-100 p-12 text-center">
      <div class="text-5xl mb-3">📭</div>
      <p class="text-gray-400">Aucune référence publiée pour le moment.</p>
      <p class="text-sm text-gray-400 mt-1">
        Publiez des références depuis l'onglet "Demandes traitées".
      </p>
    </div>

    <!-- Grille des références publiées -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="ref in references"
        :key="ref.id"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex gap-4"
      >
        <!-- Couverture -->
        <img
          v-if="ref.cover"
          :src="ref.cover"
          :alt="ref.title"
          class="w-14 h-20 object-cover rounded-lg flex-shrink-0 bg-slate-100"
        />
        <div v-else class="w-14 h-20 rounded-lg bg-slate-100 flex-shrink-0 flex items-center justify-center text-2xl">
          📄
        </div>

        <!-- Infos -->
        <div class="flex-1 min-w-0">
          <h2 class="font-semibold text-[#042C53] text-sm line-clamp-2">
            {{ ref.title }}
          </h2>
          <p class="text-xs text-gray-500 mt-0.5">{{ ref.author }}</p>
          <div class="flex flex-wrap gap-1 mt-2">
            <span v-if="ref.category"
              class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">
              {{ ref.category }}
            </span>
            <span v-if="ref.type"
              class="text-xs bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full">
              {{ ref.type }}
            </span>
          </div>
          <!-- Badge publié -->
          <span class="inline-block mt-2 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
            ✅ Publié
          </span>
        </div>
      </div>
    </div>
  </div>
</template>