import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useToast } from 'primevue/usetoast'
import {
  fetchPublishedReferences,
  fetchReferenceById,
  downloadReferenceFile,
  toggleLikeReference,
  searchReferences,
} from '@/api/referenceApi'
import apiClient from '@/api/axios'

export const useReferenceStore = defineStore('references', () => {
  // ── État ──────────────────────────────────────────────────────────────
  const references = ref([])
  const currentReference = ref(null)
  const isLoading = ref(false)
  const error = ref(null)
  
  const searchResults  = ref([])    // résultats de la recherche
  const searchTotal    = ref(0)     // total de résultats trouvés
  const searchMeta     = ref(null)  // infos pagination (current_page, last_page...)
  const isSearching    = ref(false) // true pendant l'appel API recherche
  const categories     = ref([])    // liste des catégories pour le filtre
  const types          = ref([])    // liste des types pour le filtre

  const toast = useToast()

  // ── Chargement du catalogue ──────────────────────────────────────────
  async function loadReferences() {
    isLoading.value = true
    error.value = null

    try {
      const response = await fetchPublishedReferences()
      references.value = response.data.data
    } catch (err) {
      error.value = 'Impossible de charger le catalogue.'
      console.error('Erreur chargement références :', err)
    } finally {
      isLoading.value = false
    }
  }

  // ── Chargement du détail (incrémente automatiquement les vues côté backend) ──
  async function loadReference(id) {
    isLoading.value = true
    error.value = null

    try {
      const response = await fetchReferenceById(id)
      currentReference.value = response.data.data
    } catch (err) {
      error.value = 'Référence introuvable.'
      console.error('Erreur chargement référence :', err)
    } finally {
      isLoading.value = false
    }
  }

  // ── NOUVELLE FONCTION : charger catégories et types pour les filtres ───
  async function loadFiltersOptions() {
    try {
      // On fait les deux appels en parallèle (Promise.all = plus rapide)
      const [catsRes, typesRes] = await Promise.all([
        apiClient.get('/api/categories'),
        apiClient.get('/api/types'),
      ])
      categories.value = catsRes.data.data
      types.value      = typesRes.data.data
    } catch (err) {
      console.error('Erreur chargement filtres :', err)
    }
  }

  // ── NOUVELLE FONCTION : recherche dynamique ────────────────────────────
  /**
   * Effectue la recherche en envoyant les filtres à Laravel.
   * @param {Object} filters - { search, category_id, type_id, page }
   */
  async function search(filters = {}) {
    isSearching.value = true
    error.value = null
    try {
      // On nettoie les filtres vides pour ne pas envoyer category_id='' à Laravel
      const cleanFilters = Object.fromEntries(
        Object.entries(filters).filter(([, v]) => v !== '' && v !== null && v !== undefined)
      )

      const response = await searchReferences(cleanFilters)

      // Laravel paginate() retourne data + meta avec les infos de pagination
      searchResults.value = response.data.data
      searchMeta.value    = response.data.meta
      searchTotal.value   = response.data.meta?.total ?? response.data.data.length

    } catch (err) {
      error.value = 'Erreur lors de la recherche.'
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Recherche impossible.', life: 3000 })
    } finally {
      isSearching.value = false
    }
  }


  /**
   * Traite les erreurs communes au like et au téléchargement :
   * - 401 : personne n'est connecté
   * - 403 : compte suspendu ou désactivé
   * - autre : erreur générique
   * Affiche une notification PrimeVue (Toast), jamais une alert().
   */
  function notifyAccessError(err) {
    const status = err.response?.status
    const code = err.response?.data?.code

    if (status === 401) {
      toast.add({
        severity: 'warn',
        summary: 'Connexion requise',
        detail: 'Vous devez créer un compte ou vous connecter pour effectuer cette action.',
        life: 5000,
      })
      return
    }

    if (status === 403 && (code === 'account_suspended' || code === 'account_disabled')) {
      toast.add({
        severity: 'error',
        summary: 'Compte bloqué',
        detail: err.response.data.message,
        life: 6000,
      })
      return
    }

    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Une erreur est survenue. Veuillez réessayer.',
      life: 5000,
    })
  }

  /**
   * Met à jour localement le compteur d'une référence (dans la liste
   * ET dans currentReference) sans devoir recharger toute la page.
   */
  function patchReferenceCounts(id, patch) {
    const inList = references.value.find((r) => String(r.id) === String(id))
    if (inList) Object.assign(inList, patch)

    if (currentReference.value && String(currentReference.value.id) === String(id)) {
      Object.assign(currentReference.value, patch)
    }
  }

  /**
   * LIKE / UNLIKE — bascule automatique.
   */
  async function likeReference(id) {
    try {
      const response = await toggleLikeReference(id)
      const { liked, likes_count } = response.data

      patchReferenceCounts(id, { is_liked: liked, likes: likes_count })
    } catch (err) {
      notifyAccessError(err)
    }
  }

  /**
   * TÉLÉCHARGEMENT — récupère le fichier en blob et déclenche
   * le téléchargement dans le navigateur, sans quitter la page.
   */
  async function downloadReference(id, filename = 'document.pdf') {
    try {
      const response = await downloadReferenceFile(id)

      // On essaie de récupérer le vrai nom du fichier depuis les headers
      const disposition = response.headers['content-disposition']
      let finalName = filename
      if (disposition) {
        const match = disposition.match(/filename="?([^"]+)"?/)
        if (match) finalName = match[1]
      }

      // Création d'un lien temporaire pour déclencher le téléchargement
      const blobUrl = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = blobUrl
      link.setAttribute('download', finalName)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(blobUrl)

      // Le compteur a été incrémenté côté backend : on met à jour l'affichage
      const current = references.value.find((r) => String(r.id) === String(id))
        || currentReference.value

      patchReferenceCounts(id, {
        downloads: (current?.downloads ?? 0) + 1,
      })

      toast.add({
        severity: 'success',
        summary: 'Téléchargement',
        detail: 'Le fichier a été téléchargé avec succès.',
        life: 3000,
      })
    } catch (err) {
      notifyAccessError(err)
    }
  }

  return {
    references,
    currentReference,
    isLoading,
    error,
     searchResults, searchTotal, searchMeta, isSearching, categories, types,
    loadReferences,
    loadReference,
    likeReference,
    downloadReference,
    search, loadFiltersOptions,
  }
})