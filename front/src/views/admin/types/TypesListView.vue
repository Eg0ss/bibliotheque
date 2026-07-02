<!-- GET /api/admin/types -->
<script setup>
import { onMounted } from 'vue'
import { useTypeStore } from '@/stores/typeStore'
import { RouterLink } from 'vue-router'

const typeStore = useTypeStore()

onMounted(() => typeStore.fetchTypes())

function goToPage(page) {
  typeStore.fetchTypes(page)
}
</script>

<template>
  <div>
    <!-- En-tête -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-[#042C53]">Types</h1>
      <RouterLink
        to="/admin/types/nouveau"
        class="bg-[#042C53] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#0C447C] transition"
      >
        + Nouveau type
      </RouterLink>
    </div>

    <!-- Chargement -->
    <div v-if="typeStore.loading" class="text-center py-12 text-gray-400">
      Chargement...
    </div>

    <!-- Tableau -->
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-6 py-3 text-gray-600 font-medium">Nom</th>
            <th class="text-left px-6 py-3 text-gray-600 font-medium">Description</th>
            <th class="text-left px-6 py-3 text-gray-600 font-medium">Créé le</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="type in typeStore.types"
            :key="type.id"
            class="hover:bg-gray-50 transition"
          >
            <td class="px-6 py-4 font-medium text-gray-900">{{ type.name }}</td>
            <td class="px-6 py-4 text-gray-500 max-w-xs truncate">{{ type.description ?? '—' }}</td>
            <td class="px-6 py-4 text-gray-400">{{ type.created_at }}</td>
          </tr>

          <tr v-if="typeStore.types.length === 0">
            <td colspan="3" class="text-center py-8 text-gray-400">
              Aucun type trouvé.
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div
        v-if="typeStore.pagination && typeStore.pagination.last_page > 1"
        class="px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500"
      >
        <span>Page {{ typeStore.pagination.current_page }} / {{ typeStore.pagination.last_page }}</span>
        <div class="flex gap-2">
          <button
            @click="goToPage(typeStore.pagination.current_page - 1)"
            :disabled="typeStore.pagination.current_page === 1"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-40"
          >← Précédent</button>
          <button
            @click="goToPage(typeStore.pagination.current_page + 1)"
            :disabled="typeStore.pagination.current_page === typeStore.pagination.last_page"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-40"
          >Suivant →</button>
        </div>
      </div>
    </div>
  </div>
</template>