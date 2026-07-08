<script setup>
import { ref } from 'vue'
import { useReferenceStore } from '@/stores/referenceStore'

const props = defineProps({
  referenceId: { type: [String, Number], required: true },
  hasFile: { type: Boolean, default: true },
})

const referenceStore = useReferenceStore()
const isDownloading = ref(false)

async function handleDownload() {
  if (isDownloading.value) return
  isDownloading.value = true
  try {
    await referenceStore.downloadReference(props.referenceId)
  } finally {
    isDownloading.value = false
  }
}
</script>

<template>
  <!--
    Si aucun fichier n'est associé à la référence, le bouton est désactivé.
    Sinon, un clic déclenche le store : c'est le backend qui décide si
    l'utilisateur a le droit de télécharger (connecté, actif, non suspendu).
  -->
  <button
    type="button"
    :disabled="!hasFile || isDownloading"
    @click="handleDownload"
    class="inline-flex items-center gap-2 rounded-lg bg-[#1e3a5f] px-5 py-2.5 text-sm font-medium text-white transition hover:bg-[#2d5a8e] disabled:cursor-not-allowed disabled:opacity-50"
  >
    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
    </svg>
    <span v-if="!hasFile">Fichier indisponible</span>
    <span v-else-if="isDownloading">Téléchargement...</span>
    <span v-else>Télécharger le PDF</span>
  </button>
</template>