<!-- POST /api/admin/categories -->
<script setup>
import { reactive, onMounted, ref } from 'vue'
import { useCategoryStore } from '@/stores/categoryStore'
import categoryApi from '@/api/categoryApi'
import { RouterLink } from 'vue-router'


const categoryStore = useCategoryStore()

const form = reactive({
  name       : '',
  description: '',
  parent_id  : '',   // vide = catégorie racine
})

// Liste des catégories existantes pour le select "catégorie parente"
const parentOptions = ref([])

onMounted(async () => {
  try {
    const res      = await categoryApi.getAllFlat()
    parentOptions.value = res.data.data
  } catch {
    // Si l'appel échoue, le select reste vide → pas bloquant
  }
})

function handleSubmit() {
  // On envoie null si aucune catégorie parente sélectionnée
  const payload = {
    ...form,
    parent_id: form.parent_id || null,
  }
  categoryStore.createCategory(payload)
}
</script>

<template>
  <div class="max-w-xl">

    <div class="flex items-center gap-4 mb-6">
      <RouterLink to="/admin/categories" class="text-gray-400 hover:text-gray-600 text-sm">
        ← Retour à la liste
      </RouterLink>
      <h1 class="text-2xl font-bold text-[#042C53]">Nouvelle catégorie</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <form @submit.prevent="handleSubmit" class="space-y-5">

        <!-- Nom -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Nom <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            placeholder="Ex : Sciences humaines"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
          />
          <p v-if="categoryStore.errors.name" class="text-red-500 text-xs mt-1">
            {{ categoryStore.errors.name[0] }}
          </p>
        </div>

        <!-- Catégorie parente (optionnelle) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Catégorie parente
            <span class="text-gray-400 font-normal">(optionnelle)</span>
          </label>
          <select
            v-model="form.parent_id"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C] bg-white"
          >
            <option value="">— Aucune (catégorie racine) —</option>
            <option
              v-for="cat in parentOptions"
              :key="cat.id"
              :value="cat.id"
            >
              {{ cat.name }}
            </option>
          </select>
          <p v-if="categoryStore.errors.parent_id" class="text-red-500 text-xs mt-1">
            {{ categoryStore.errors.parent_id[0] }}
          </p>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Description
            <span class="text-gray-400 font-normal">(optionnelle)</span>
          </label>
          <textarea
            v-model="form.description"
            rows="3"
            placeholder="Brève description de la catégorie..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C] resize-none"
          />
          <p v-if="categoryStore.errors.description" class="text-red-500 text-xs mt-1">
            {{ categoryStore.errors.description[0] }}
          </p>
        </div>

        <div class="pt-2">
          <button
            type="submit"
            :disabled="categoryStore.loading"
            class="w-full bg-[#042C53] text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-[#0C447C] transition disabled:opacity-50"
          >
            {{ categoryStore.loading ? 'Création en cours...' : 'Créer la catégorie' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>