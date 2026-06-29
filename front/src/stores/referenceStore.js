import { defineStore } from 'pinia'
import { ref } from 'vue'
import { fetchPublishedReferences, fetchReferenceById } from '@/api/referenceApi'

// defineStore('nom', setup) — 'references' est l'identifiant unique du store
export const useReferenceStore = defineStore('references', () => {

  // ── État (données stockées) ──────────────────────────────────────────
  const references = ref([])       // La liste des références publiées
  const currentReference = ref(null) // La référence affichée en détail
  const isLoading = ref(false)     // true pendant le chargement (pour afficher un spinner)
  const error = ref(null)          // Stocke le message d'erreur si l'API échoue

  // ── Actions (fonctions qui modifient l'état) ─────────────────────────

  /**
   * Charge toutes les références publiées depuis l'API.
   * À appeler quand on arrive sur la page /catalogue.
   */
  async function loadReferences() {
    isLoading.value = true   // On active le loader
    error.value = null       // On efface les erreurs précédentes

    try {
      // On appelle l'API et on attend la réponse
      const response = await fetchPublishedReferences()

      // Axios place les données dans response.data
      // Laravel avec ReferenceResource::collection() les place dans response.data.data
      references.value = response.data.data

    } catch (err) {
      // En cas d'erreur réseau ou serveur (404, 500...)
      error.value = 'Impossible de charger le catalogue.'
      console.error('Erreur chargement références :', err)
    } finally {
      // finally s'exécute toujours, succès ou échec
      isLoading.value = false // On désactive le loader
    }
  }

  /**
   * Charge le détail d'une seule référence.
   * À appeler sur la page /catalogue/:id.
   *
   * @param {string|number} id
   */
  async function loadReference(id) {
    isLoading.value = true
    error.value = null

    try {
      const response = await fetchReferenceById(id)
      // Une seule ressource est dans response.data.data
      currentReference.value = response.data.data
    } catch (err) {
      error.value = 'Référence introuvable.'
      console.error('Erreur chargement référence :', err)
    } finally {
      isLoading.value = false
    }
  }

  // On retourne tout ce qu'on veut rendre accessible aux composants
  return {
    references,
    currentReference,
    isLoading,
    error,
    loadReferences,
    loadReference,
  }
})