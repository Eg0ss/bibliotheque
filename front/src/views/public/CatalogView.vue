<script setup>
import ReferenceCard from '@/components/catalog/ReferenceCard.vue'

// Mock data
const references = Array.from({ length: 8 }, (_, i) => ({
  id: String(i + 1),
  title: `Référence ${i + 1}`,
  author: `Auteur ${i + 1}`,
  year: 2020 + i,
  category: ['Sciences', 'Lettres', 'Droit', 'Économie'][i % 4],
  type: ['Thèse', 'Mémoire', 'Article', 'Ouvrage'][i % 4],
  language: 'Français',
  downloads: 100 + i * 50,
  cover: `https://picsum.photos/400/560?random=${i}`
}))
</script>

<template>
  <div class="p-8">
    <h1 class="text-3xl font-bold text-[#1e3a5f] mb-8">Catalogue</h1>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
      <ReferenceCard v-for="ref in references" :key="ref.id" :reference="ref" />
    </div>
  </div>
</template>
