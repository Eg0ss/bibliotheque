import { defineStore } from 'pinia'
import { ref }         from 'vue'
import gestionnaireApi from '../api/gestionnaireApi'
import router          from '../router'
import { useToast }    from 'primevue/usetoast'

export const useGestionnaireStore = defineStore('gestionnaire', () => {

  const documents        = ref([])   // documents assignés en attente
  const myValidations    = ref([])   // décisions déjà prises
  const currentDocument  = ref(null) // assignation affichée dans la page détail
  const pagination       = ref(null)
  const loading          = ref(false)

  const toast = useToast()

  // ── Charger les documents assignés en attente ─────────────────────────
  async function fetchDocuments(page = 1) {
    loading.value = true
    try {
      const res        = await gestionnaireApi.getDocuments(page)
      documents.value  = res.data.data
      pagination.value = {
        current_page: res.data.current_page,
        last_page   : res.data.last_page,
        total       : res.data.total,
      }
    } catch {
      toast.add({ severity: 'error', summary: 'Erreur',
        detail: 'Impossible de charger vos documents.', life: 4000 })
    } finally {
      loading.value = false
    }
  }

  // ── Charger une assignation précise (page détail) ─────────────────────
  async function fetchDocument(id) {
    loading.value        = true
    currentDocument.value = null
    try {
      const res             = await gestionnaireApi.getDocument(id)
      currentDocument.value = res.data.data
    } catch {
      toast.add({ severity: 'error', summary: 'Introuvable',
        detail: 'Ce document n\'existe pas.', life: 4000 })
      router.push('/gestionnaire/documents')
    } finally {
      loading.value = false
    }
  }

  // ── Prendre une décision ──────────────────────────────────────────────
  async function decide(id, decision, comment = '') {
    loading.value = true
    try {
      const res = await gestionnaireApi.decide(id, { decision, comment })
      toast.add({ severity: 'success', summary: 'Décision enregistrée',
        detail: res.data.message, life: 4000 })
      // Retourner à la liste après décision
      router.push('/gestionnaire/documents')
    } catch (error) {
      const detail = error.response?.data?.message ?? 'Une erreur est survenue.'
      toast.add({ severity: 'error', summary: 'Erreur', detail, life: 5000 })
    } finally {
      loading.value = false
    }
  }

  // ── Charger mes validations déjà effectuées ───────────────────────────
  async function fetchMyValidations(page = 1) {
    loading.value = true
    try {
      const res           = await gestionnaireApi.getMyValidations(page)
      myValidations.value = res.data.data
      pagination.value    = {
        current_page: res.data.current_page,
        last_page   : res.data.last_page,
        total       : res.data.total,
      }
    } catch {
      toast.add({ severity: 'error', summary: 'Erreur',
        detail: 'Impossible de charger vos validations.', life: 4000 })
    } finally {
      loading.value = false
    }
  }

  return {
    documents, myValidations, currentDocument, pagination, loading,
    fetchDocuments, fetchDocument, decide, fetchMyValidations,
  }
})