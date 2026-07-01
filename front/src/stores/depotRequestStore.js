// Store Pinia pour la gestion des demandes de dépôt

import { defineStore } from 'pinia'
import { ref } from 'vue'
import depotRequestApi from '../api/depotRequestApi'
import router from '../router'
import { useToast } from 'primevue/usetoast'

export const useDepotRequestStore = defineStore('depotRequest', () => {
  // ── État ────────────────────────────────────────────────────────────
  const myRequests = ref([]) // liste des demandes de l'utilisateur
  const pagination = ref(null) // pagination pour la liste des demandes
  const categories = ref([]) // pour le <select> catégorie
  const types = ref([]) // pour le <select> type
  const loading = ref(false)
  const errors = ref({})

  const stats = ref({
  total           : 0,
  pending         : 0,
  assigned        : 0,
  manager_approved: 0,
  published       : 0,
  rejected        : 0,
  in_progress     : 0,
})

const filters = ref({
  search: '',
  status: '',
})

  const toast = useToast()

  // ── Charger les catégories et types (pour les selects) ──────────────
  async function fetchFormOptions() {
    try {
      const [catRes, typeRes] = await Promise.all([
        depotRequestApi.getCategories(),
        depotRequestApi.getTypes(),
      ])
      categories.value = catRes.data.data
      types.value = typeRes.data.data
    } catch (error) {
      toast.add({
        severity: 'error',
        summary: 'Erreur',
        detail: 'Impossible de charger les options du formulaire.',
        life: 4000,
      })
    }
  }

  // ── Soumettre une nouvelle demande ────────────────────────────────────
  async function submitRequest(formData) {
    loading.value = true
    errors.value = {}
    try {
      await depotRequestApi.store(formData)

      toast.add({
        severity: 'success',
        summary: 'Demande soumise',
        detail: 'Votre demande a été soumise avec succès. Elle sera traitée prochainement.',
        life: 5000,
      })

      // Rediriger vers la liste des demandes
      router.push({ path: '/mon-espace/depots' })
    } catch (error) {
      if (error.response?.status === 422) {
        // Erreurs de validation Laravel → affichage champ par champ
        errors.value = error.response.data.errors
        toast.add({
          severity: 'warn',
          summary: 'Formulaire invalide',
          detail: 'Veuillez corriger les erreurs signalées.',
          life: 4000,
        })
      } else {
        toast.add({
          severity: 'error',
          summary: 'Erreur serveur',
          detail: 'Une erreur est survenue. Veuillez réessayer.',
          life: 5000,
        })
      }
    } finally {
      loading.value = false
    }
  }

  // ── Récupérer les demandes de l'utilisateur connecté ─────────────────
 async function fetchMyRequests(page = 1) {
  loading.value = true
  try {
    // On nettoie les filtres vides avant d'envoyer
    const activeFilters = Object.fromEntries(
      Object.entries(filters.value).filter(([, v]) => v !== '')
    )

    const response   = await depotRequestApi.getMyRequests(page, activeFilters)
    myRequests.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page   : response.data.last_page,
      total       : response.data.total,
    }
  } catch {
    toast.add({
      severity: 'error',
      summary : 'Erreur',
      detail  : 'Impossible de charger vos demandes.',
      life    : 4000,
    })
  } finally {
    loading.value = false
  }
}

// Réinitialiser les filtres
function resetFilters() {
  filters.value = { search: '', status: '' }
  fetchMyRequests(1)
}

  // ── Charger les statistiques personnelles ─────────────────────────────
async function fetchStats() {
  try {
    const res  = await depotRequestApi.getStats()
    stats.value = res.data.data
  } catch {
    // Silencieux : les stats ne doivent pas bloquer le chargement
  }
}

  return {
    myRequests,
    pagination,
    categories,
    types,
    loading,
    errors,
    stats,
    filters,
    fetchFormOptions,
    submitRequest,
    fetchMyRequests,
    fetchStats,
    resetFilters,
  }
})
