// src/stores/gestionnaireStore.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import gestionnaireApi from '../api/gestionnaireApi'
import { useToast } from 'primevue/usetoast'

export const useGestionnaireStore = defineStore('gestionnaire', () => {

  const documents  = ref([])   // assignations du gestionnaire connecté
  const pagination = ref(null)
  const loading    = ref(false)

  const toast = useToast()

  // Charger les documents assignés au gestionnaire connecté
  async function fetchDocuments(page = 1) {
    loading.value = true
    try {
      const res      = await gestionnaireApi.getDocuments(page)
      documents.value = res.data.data
      pagination.value = {
        current_page: res.data.current_page,
        last_page   : res.data.last_page,
        total       : res.data.total,
      }
    } catch {
      toast.add({
        severity: 'error',
        summary : 'Erreur',
        detail  : 'Impossible de charger vos documents assignés.',
        life    : 4000,
      })
    } finally {
      loading.value = false
    }
  }

  return { documents, pagination, loading, fetchDocuments }
})