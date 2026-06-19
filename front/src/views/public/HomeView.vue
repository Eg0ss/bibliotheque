<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import ReferenceCard from '@/components/catalog/ReferenceCard.vue'

const router = useRouter()
const searchQuery = ref('')

// Mock data (for now)
const covers = [
  "https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=400&h=560&fit=crop",
  "https://images.unsplash.com/photo-1532012197267-da84d127e765?w=400&h=560&fit=crop",
  "https://images.unsplash.com/photo-1495446815901-a7297e633e8d?w=400&h=560&fit=crop",
  "https://images.unsplash.com/photo-1519682337058-a94d519337bc?w=400&h=560&fit=crop",
  "https://images.unsplash.com/photo-1457369804613-52c61a468e7d?w=400&h=560&fit=crop",
  "https://images.unsplash.com/photo-1512820790803-83ca734da794?w=400&h=560&fit=crop",
  "https://images.unsplash.com/photo-1589998059171-988d887df646?w=400&h=560&fit=crop",
  "https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=400&h=560&fit=crop",
]

const titles = [
  "Introduction à l'algorithmique avancée",
  "Histoire de la philosophie médiévale",
  "Économie politique contemporaine",
  "Manuel de droit administratif",
  "Statistiques appliquées aux sciences sociales",
  "Architecture des systèmes distribués",
  "Littérature francophone du XXe siècle",
  "Mécanique quantique fondamentale",
]

const authors = [
  "Dr. Amal Bennani",
  "Pr. Karim Tazi",
  "Dr. Leila Chraibi",
  "Pr. Mohammed Alami",
  "Dr. Salma El Fassi",
  "Pr. Youssef Naciri",
]

const cats = ["Sciences", "Lettres", "Droit", "Économie", "Médecine", "Ingénierie"]
const types = ["Thèse", "Mémoire", "Article", "Ouvrage", "Rapport"]

const references = Array.from({ length: 4 }, (_, i) => ({
  id: String(i + 1),
  title: titles[i],
  author: authors[i],
  year: 2018 + (i % 7),
  category: cats[i % cats.length],
  type: types[i % types.length],
  language: "Français",
  summary: "Cet ouvrage propose une analyse approfondie des concepts fondamentaux du domaine.",
  downloads: 50 + ((i * 37) % 950),
  cover: covers[i],
  status: "publie",
}))

const stats = [
  { label: "Références disponibles", value: "12 480" },
  { label: "Téléchargements", value: "284 901" },
  { label: "Utilisateurs inscrits", value: "4 320" },
]

function handleSearch() {
  if (searchQuery.value) {
    router.push({ name: 'recherche', query: { q: searchQuery.value } })
  }
}
</script>

<template>
  <div class="flex min-h-screen flex-col bg-[#f8f9fb]">
    <main class="flex-1">
      <section class="border-b border-slate-200 bg-white">
        <div class="container mx-auto px-4 py-16 text-center">
          <h1 class="text-4xl font-semibold tracking-tight text-slate-800 sm:text-5xl">
            Bibliothèque Numérique
          </h1>
          <p class="mx-auto mt-3 max-w-2xl text-base text-slate-500">
            Accédez à des milliers de références académiques, thèses, mémoires et articles validés par notre comité institutionnel.
          </p>
          <form @submit.prevent="handleSearch" class="mx-auto mt-8 flex max-w-xl items-center overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <svg class="ml-3 h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
              v-model="searchQuery"
              type="search"
              placeholder="Rechercher un titre, un auteur, un mot-clé..."
              class="flex-1 bg-transparent px-3 py-3 text-sm outline-none"
            />
            <button
              type="submit"
              class="bg-[#1e3a5f] px-6 py-3 text-sm font-medium text-white hover:bg-[#2d5a8e]"
            >
              Rechercher
            </button>
          </form>
        </div>
      </section>

      <section class="container mx-auto grid grid-cols-1 gap-4 px-4 py-10 sm:grid-cols-3">
        <div v-for="stat in stats" :key="stat.label" class="rounded-xl border border-slate-200 bg-white p-6 text-center">
          <div class="text-3xl font-semibold text-[#1e3a5f]">{{ stat.value }}</div>
          <div class="mt-1 text-sm text-slate-500">{{ stat.label }}</div>
        </div>
      </section>

      <section class="container mx-auto px-4 pb-10">
        <div class="mb-4 flex items-baseline justify-between">
          <h2 class="text-xl font-semibold text-slate-800">Dernières références ajoutées</h2>
        </div>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
          <ReferenceCard v-for="ref in references" :key="ref.id" :reference="ref" />
        </div>
      </section>

      <section class="container mx-auto px-4 pb-16">
        <h2 class="mb-4 text-xl font-semibold text-slate-800">Catégories</h2>
        <div class="flex flex-wrap gap-2">
          <span v-for="cat in cats" :key="cat" class="rounded-full border border-slate-200 bg-white px-4 py-1.5 text-sm text-slate-800 hover:border-[#1e3a5f] hover:text-[#1e3a5f]">
            {{ cat }}
          </span>
        </div>
      </section>
    </main>
  </div>
</template>
