<script setup>
import { onMounted } from 'vue'
import ReferenceCard from '@/components/catalog/ReferenceCard.vue'
import { useReferenceStore } from '@/stores/referenceStore'

// On récupère le store — c'est lui qui contient les données et les fonctions
const referenceStore = useReferenceStore()

// onMounted() s'exécute une fois que le composant est affiché dans le navigateur.
// C'est ici qu'on déclenche le chargement des données.
onMounted(() => {
  referenceStore.loadReferences()
})
</script>

<template>
  <div class="p-8">
    <h1 class="text-3xl font-bold text-[#1e3a5f] mb-8">Catalogue</h1>

    <!-- Affichage pendant le chargement -->
    <div v-if="referenceStore.isLoading" class="text-center text-slate-500 py-16">
      Chargement du catalogue...
    </div>

    <!-- Affichage en cas d'erreur -->
    <div v-else-if="referenceStore.error" class="text-center text-red-500 py-16">
      {{ referenceStore.error }}
    </div>

    <!-- Aucune référence publiée -->
    <div v-else-if="referenceStore.references.length === 0" class="text-center text-slate-400 py-16">
      Aucune référence publiée pour l'instant.
    </div>

    <!-- Liste des références — v-for boucle sur les vraies données du store -->
    <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-6">
      <ReferenceCard
        v-for="ref in referenceStore.references"
        :key="ref.id"
        :reference="ref"
      />
    </div>
  </div>
</template>