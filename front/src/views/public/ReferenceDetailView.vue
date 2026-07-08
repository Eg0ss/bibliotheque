<script setup>
import { onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useReferenceStore } from '@/stores/referenceStore'
import StatusBadge from '@/components/common/StatusBadge.vue'
import DownloadButton from '@/components/document/DownloadButton.vue'
import LikeButton from '@/components/document/LikeButton.vue'

const route = useRoute()
const referenceStore = useReferenceStore()

// Le chargement (loadReference) appelle GET /api/references/{id}
// qui incrémente automatiquement le compteur de vues côté backend,
// que l'utilisateur soit connecté ou non.
onMounted(() => {
  referenceStore.loadReference(route.params.id)
})
</script>

<template>
  <div class="mx-auto max-w-4xl p-8">
    <!-- Chargement -->
    <div v-if="referenceStore.isLoading" class="py-16 text-center text-slate-500">
      Chargement de la référence...
    </div>

    <!-- Erreur -->
    <div v-else-if="referenceStore.error" class="py-16 text-center text-red-500">
      {{ referenceStore.error }}
    </div>

    <!-- Contenu -->
    <div v-else-if="referenceStore.currentReference" class="grid grid-cols-1 gap-8 sm:grid-cols-3">
      <!-- Couverture -->
      <div class="sm:col-span-1">
        <div class="aspect-[3/4] overflow-hidden rounded-xl bg-slate-100">
          <img v-if="referenceStore.currentReference.cover" :src="referenceStore.currentReference.cover"
            :alt="referenceStore.currentReference.title" class="h-full w-full object-cover" />
        </div>
      </div>

      <!-- Informations -->
      <div class="sm:col-span-2">
        <div class="flex flex-wrap gap-1.5">
          <StatusBadge tone="info">{{ referenceStore.currentReference.category }}</StatusBadge>
          <StatusBadge tone="gold">{{ referenceStore.currentReference.type }}</StatusBadge>
        </div>

        <h1 class="mt-3 text-2xl font-semibold text-slate-800">
          {{ referenceStore.currentReference.title }}
        </h1>

        <p class="mt-1 text-sm text-slate-500">
          {{ referenceStore.currentReference.author }}
          <span v-if="referenceStore.currentReference.publisher"> · {{ referenceStore.currentReference.publisher
            }}</span>
          <span v-if="referenceStore.currentReference.year"> · {{ referenceStore.currentReference.year }}</span>
          <span v-if="referenceStore.currentReference.language"> · {{ referenceStore.currentReference.language }}</span>
        </p>

        <p v-if="referenceStore.currentReference.abstract" class="mt-4 text-sm leading-relaxed text-slate-700">
          {{ referenceStore.currentReference.abstract }}
        </p>

        <!-- Compteurs -->
        <div class="mt-6 flex items-center gap-4 text-sm text-slate-500">
          <span>{{ referenceStore.currentReference.views ?? 0 }} vues</span>
          <span>{{ referenceStore.currentReference.downloads ?? 0 }} téléchargements</span>
        </div>

        <!-- Actions : télécharger / liker -->
        <div class="mt-6 flex flex-wrap items-center gap-3">
          <DownloadButton :reference-id="referenceStore.currentReference.id"
            :has-file="referenceStore.currentReference.has_file" />
          <LikeButton :reference-id="referenceStore.currentReference.id"
            :is-liked="referenceStore.currentReference.is_liked"
            :likes-count="referenceStore.currentReference.likes ?? 0" />
        </div>
      </div>
    </div>
  </div>
</template>