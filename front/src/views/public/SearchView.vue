<script setup>
/**
 * SearchView.vue — Page de recherche avancée
 *
 * Ce que fait cette page :
 * 1. Au chargement : charge toutes les références publiées + les options de filtre
 * 2. L'utilisateur tape dans la barre ou choisit un filtre
 * 3. La recherche se déclenche automatiquement (watch) à chaque changement
 * 4. Les résultats s'affichent sous forme de grille de cartes
 */

import { ref, watch, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useReferenceStore } from '@/stores/referenceStore'
import ReferenceCard from '@/components/catalog/ReferenceCard.vue'

const route  = useRoute()
const router = useRouter()
const store  = useReferenceStore()

// ── Filtres réactifs ────────────────────────────────────────────────────
// Chaque ref correspond à un champ du formulaire
// Quand l'utilisateur modifie un champ, le watch() ci-dessous se déclenche
const searchText  = ref(route.query.q ?? '')   // pré-rempli si on vient de la page d'accueil
const categoryId  = ref('')
const typeId      = ref('')
const currentPage = ref(1)

// ── Au chargement de la page ────────────────────────────────────────────
onMounted(async () => {
  // Charger les options de filtres (catégories + types)
  await store.loadFiltersOptions()

  // Lancer la recherche initiale avec tous les paramètres
  // (si l'utilisateur arrive depuis la page d'accueil avec ?q=..., on l'utilise)
  doSearch()
})

// ── Fonction de recherche ───────────────────────────────────────────────
function doSearch(page = 1) {
  currentPage.value = page

  // On met à jour l'URL pour que la page soit partageabl (ex: ?q=droit&category_id=2)
  router.replace({
    query: {
      ...(searchText.value  ? { q: searchText.value }           : {}),
      ...(categoryId.value  ? { category_id: categoryId.value } : {}),
      ...(typeId.value      ? { type_id: typeId.value }         : {}),
    }
  })

  // Appel au store qui appelle l'API
  store.search({
    search     : searchText.value,
    category_id: categoryId.value,
    type_id    : typeId.value,
    page       : page,
    per_page   : 12,
  })
}

// ── Réinitialiser tous les filtres ──────────────────────────────────────
function resetFilters() {
  searchText.value = ''
  categoryId.value = ''
  typeId.value     = ''
  doSearch()
}

// ── watch : relancer la recherche à chaque changement de filtre ─────────
// { immediate: false } = on ne lance pas au premier rendu (onMounted s'en charge)
// Les filtres sont debouncés via un simple watch avec les modifications
watch([searchText, categoryId, typeId], () => {
  // Petit délai de 400ms pour éviter un appel API à chaque touche tapée
  // C'est ce qu'on appelle le "debounce" — on attend que l'utilisateur arrête de taper
  clearTimeout(searchDebounce)
  searchDebounce = setTimeout(() => doSearch(1), 400)
})

// Variable pour stocker le timer du debounce
let searchDebounce = null

// ── Indicateur de filtres actifs ────────────────────────────────────────
// Vrai si au moins un filtre est rempli (pour afficher le bouton "Réinitialiser")
const hasActiveFilters = computed(
  () => searchText.value || categoryId.value || typeId.value
)
</script>

<template>
  <div class="min-h-screen bg-[#f8f9fb]">
    <div class="container mx-auto px-4 py-10">

      <!-- ── Titre de la page ── -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#1e3a5f]">Recherche avancée</h1>
        <p class="text-gray-500 mt-1 text-sm">
          Recherchez parmi toutes les références publiées de la bibliothèque.
        </p>
      </div>

      <!-- ── Zone de recherche + filtres ── -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-8">

        <!-- Barre de recherche principale -->
        <div class="relative mb-4">
          <!-- Icône loupe à gauche -->
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>

          <!--
            v-model="searchText" : lie l'input à la variable réactive
            @keydown.enter : si l'utilisateur appuie Entrée, lancer immédiatement
          -->
          <input
            v-model="searchText"
            @keydown.enter="doSearch()"
            type="search"
            placeholder="Rechercher par titre ou nom d'auteur..."
            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] bg-white"
          />
        </div>

        <!-- Ligne de filtres (catégorie + type) -->
        <div class="flex flex-wrap gap-3 items-center">

          <!-- Filtre catégorie -->
          <select
            v-model="categoryId"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white
                   focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] min-w-[180px]"
          >
            <option value="">Toutes les catégories</option>
            <!--
              store.categories est chargé par loadFiltersOptions()
              On boucle dessus pour créer les options dynamiquement
            -->
            <option v-for="cat in store.categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>

          <!-- Filtre type (Thèse, Mémoire...) -->
          <select
            v-model="typeId"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white
                   focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] min-w-[160px]"
          >
            <option value="">Tous les types</option>
            <option v-for="type in store.types" :key="type.id" :value="type.id">
              {{ type.name }}
            </option>
          </select>

          <!-- Bouton Réinitialiser (visible uniquement si un filtre est actif) -->
          <button
            v-if="hasActiveFilters"
            @click="resetFilters"
            class="px-4 py-2 text-sm text-gray-500 border border-gray-300 rounded-lg
                   hover:bg-gray-50 transition flex items-center gap-1"
          >
            ✕ Réinitialiser
          </button>
        </div>
      </div>

      <!-- ── Résultats ── -->

      <!-- Compteur de résultats -->
      <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">
          <template v-if="store.isSearching">
            Recherche en cours...
          </template>
          <template v-else>
            <!-- Affiche "X résultat(s)" avec gestion du singulier/pluriel -->
            <span class="font-semibold text-[#1e3a5f]">{{ store.searchTotal }}</span>
            référence{{ store.searchTotal > 1 ? 's' : '' }} trouvée{{ store.searchTotal > 1 ? 's' : '' }}
            <template v-if="searchText">
              pour <span class="italic">"{{ searchText }}"</span>
            </template>
          </template>
        </p>
      </div>

      <!-- Chargement -->
      <div v-if="store.isSearching" class="flex justify-center py-16">
        <div class="flex items-center gap-2 text-gray-400">
          <span class="animate-spin text-xl">⏳</span>
          <span class="text-sm">Recherche en cours...</span>
        </div>
      </div>

      <!-- Aucun résultat -->
      <div
        v-else-if="store.searchResults.length === 0"
        class="bg-white rounded-xl border border-gray-100 p-16 text-center"
      >
        <div class="text-5xl mb-4">🔍</div>
        <p class="text-gray-500 font-medium">Aucune référence trouvée.</p>
        <p class="text-gray-400 text-sm mt-1">
          Essayez avec d'autres mots-clés ou supprimez certains filtres.
        </p>
        <button
          v-if="hasActiveFilters"
          @click="resetFilters"
          class="mt-4 px-4 py-2 text-sm bg-[#1e3a5f] text-white rounded-lg hover:bg-[#0C447C] transition"
        >
          Voir toutes les références
        </button>
      </div>

      <!-- Grille de résultats -->
      <div
        v-else
        class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4"
      >
        <!--
          ReferenceCard reçoit chaque référence via :reference="ref"
          Le composant s'occupe de l'affichage de chaque carte
        -->
        <ReferenceCard
          v-for="ref in store.searchResults"
          :key="ref.id"
          :reference="ref"
        />
      </div>

      <!-- ── Pagination ── -->
      <div
        v-if="store.searchMeta && store.searchMeta.last_page > 1"
        class="flex items-center justify-center gap-3 mt-10"
      >
        <button
          @click="doSearch(store.searchMeta.current_page - 1)"
          :disabled="store.searchMeta.current_page === 1"
          class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50
                 disabled:opacity-40 disabled:cursor-not-allowed transition"
        >
          ← Précédent
        </button>

        <span class="text-sm text-gray-500">
          Page {{ store.searchMeta.current_page }} / {{ store.searchMeta.last_page }}
        </span>

        <button
          @click="doSearch(store.searchMeta.current_page + 1)"
          :disabled="store.searchMeta.current_page === store.searchMeta.last_page"
          class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50
                 disabled:opacity-40 disabled:cursor-not-allowed transition"
        >
          Suivant →
        </button>
      </div>

    </div>
  </div>
</template>
