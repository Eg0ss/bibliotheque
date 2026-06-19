<script setup>
import { ref } from 'vue'
import ReferenceCard from '@/components/catalog/ReferenceCard.vue'

const searchQuery = ref('')
const results = Array.from({ length: 4 }, (_, i) => ({
  id: String(i + 1),
  title: `Résultat ${i + 1}`,
  author: `Auteur ${i + 1}`,
  year: 2020 + i,
  category: ['Sciences', 'Lettres', 'Droit', 'Économie'][i % 4],
  type: ['Thèse', 'Mémoire', 'Article', 'Ouvrage'][i % 4],
  language: 'Français',
  downloads: 100 + i * 50,
  cover: `https://picsum.photos/400/560?random=${i + 10}`
}))
</script>

<template>
  <div class="p-8">
    <h1 class="text-3xl font-bold text-[#1e3a5f] mb-4">Recherche</h1>
    <div class="mb-8">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Rechercher une référence..."
        class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
      />
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
      <ReferenceCard v-for="ref in results" :key="ref.id" :reference="ref" />
    </div>
  </div>
</template>
