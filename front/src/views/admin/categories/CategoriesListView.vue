<!-- GET /api/admin/categories -->
<script setup>
import { onMounted } from 'vue'
import { useCategoryStore } from '@/stores/categoryStore'
import { RouterLink } from 'vue-router'

const categoryStore = useCategoryStore()

onMounted(() => categoryStore.fetchCategories())

function goToPage(page) {
  categoryStore.fetchCategories(page)
}
</script>

<template>
  <div>
    <!-- En-tête -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-[#042C53]">Catégories</h1>
      <RouterLink
        to="/categories/nouvelle"
        class="bg-[#042C53] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#0C447C] transition"
      >
        + Nouvelle catégorie
      </RouterLink>
    </div>

    <!-- Chargement -->
    <div v-if="categoryStore.loading" class="text-center py-12 text-gray-400">
      Chargement...
    </div>

    <!-- Tableau -->
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-6 py-3 text-gray-600 font-medium">Nom</th>
            <th class="text-left px-6 py-3 text-gray-600 font-medium">Slug</th>
            <th class="text-left px-6 py-3 text-gray-600 font-medium">Catégorie parente</th>
            <th class="text-left px-6 py-3 text-gray-600 font-medium">Description</th>
            <th class="text-left px-6 py-3 text-gray-600 font-medium">Créée le</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="cat in categoryStore.categories"
            :key="cat.id"
            class="hover:bg-gray-50 transition"
          >
            <td class="px-6 py-4 font-medium text-gray-900">{{ cat.name }}</td>
            <td class="px-6 py-4 text-gray-400 font-mono text-xs">{{ cat.slug }}</td>
            <td class="px-6 py-4 text-gray-500">
              {{ cat.parent?.name ?? '—' }}
            </td>
            <td class="px-6 py-4 text-gray-500 max-w-xs truncate">
              {{ cat.description ?? '—' }}
            </td>
            <td class="px-6 py-4 text-gray-400">{{ cat.created_at }}</td>
          </tr>

          <tr v-if="categoryStore.categories.length === 0">
            <td colspan="5" class="text-center py-8 text-gray-400">
              Aucune catégorie trouvée.
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div
        v-if="categoryStore.pagination"
        class="px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500"
      >
        <span>
          Page {{ categoryStore.pagination.current_page }} / {{ categoryStore.pagination.last_page }}
        </span>
        <div class="flex gap-2">
          <button
            @click="goToPage(categoryStore.pagination.current_page - 1)"
            :disabled="categoryStore.pagination.current_page === 1"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-40"
          >
            ← Précédent
          </button>
          <button
            @click="goToPage(categoryStore.pagination.current_page + 1)"
            :disabled="categoryStore.pagination.current_page === categoryStore.pagination.last_page"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-40"
          >
            Suivant →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>