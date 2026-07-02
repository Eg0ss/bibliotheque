<!-- POST /api/admin/types -->
<script setup>
import { reactive } from 'vue'
import { useTypeStore } from '@/stores/typeStore'
import { RouterLink } from 'vue-router'

const typeStore = useTypeStore()

const form = reactive({
  name       : '',
  description: '',
})

function handleSubmit() {
  typeStore.createType(form)
}
</script>

<template>
  <div class="max-w-xl">

    <div class="flex items-center gap-4 mb-6">
      <RouterLink to="/admin/types" class="text-gray-400 hover:text-gray-600 text-sm">
        ← Retour à la liste
      </RouterLink>
      <h1 class="text-2xl font-bold text-[#042C53]">Nouveau type</h1>
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
            placeholder="Ex : Thèse, Mémoire, Article…"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
          />
          <p v-if="typeStore.errors.name" class="text-red-500 text-xs mt-1">
            {{ typeStore.errors.name[0] }}
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
            placeholder="Brève description du type de document..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C] resize-none"
          />
          <p v-if="typeStore.errors.description" class="text-red-500 text-xs mt-1">
            {{ typeStore.errors.description[0] }}
          </p>
        </div>

        <div class="pt-2">
          <button
            type="submit"
            :disabled="typeStore.loading"
            class="w-full bg-[#042C53] text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-[#0C447C] transition disabled:opacity-50"
          >
            {{ typeStore.loading ? 'Création en cours...' : 'Créer le type' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>