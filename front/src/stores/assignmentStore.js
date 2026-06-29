// src/stores/assignmentStore.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import assignmentApi from '../api/assignmentApi'
import { useToast } from 'primevue/usetoast'

export const useAssignmentStore = defineStore('assignment', () => {

  const pendingRequests = ref([])   // demandes en attente
  const assignments     = ref([])   // assignations existantes
  const gestionnaires   = ref([])   // liste pour le <select> du modal
  const pagination      = ref(null)
  const loading         = ref(false)
  const loadingAssign   = ref(false) // loading spécifique au bouton Assigner du modal

  const toast = useToast()

  // ── Charger les demandes en attente ─────────────────────────────────
  async function fetchPendingRequests(page = 1) {
    loading.value = true
    try {
      const res            = await assignmentApi.getPendingRequests(page)
      pendingRequests.value = res.data.data
      pagination.value     = {
        current_page: res.data.current_page,
        last_page   : res.data.last_page,
        total       : res.data.total,
      }
    } catch {
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les demandes.', life: 4000 })
    } finally {
      loading.value = false
    }
  }

  // ── Charger les assignations ─────────────────────────────────────────
  async function fetchAssignments(page = 1) {
    loading.value = true
    try {
      const res        = await assignmentApi.getAssignments(page)
      assignments.value = res.data.data
      pagination.value  = {
        current_page: res.data.current_page,
        last_page   : res.data.last_page,
        total       : res.data.total,
      }
    } catch {
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les assignations.', life: 4000 })
    } finally {
      loading.value = false
    }
  }

  // ── Charger les gestionnaires pour le modal ──────────────────────────
  async function fetchGestionnaires() {
    try {
      const res        = await assignmentApi.getGestionnaires()
      gestionnaires.value = res.data.data
    } catch {
      toast.add({ severity: 'warn', summary: 'Attention', detail: 'Impossible de charger les gestionnaires.', life: 3000 })
    }
  }

  // ── Assigner une demande ─────────────────────────────────────────────
  async function assignRequest(payload) {
    loadingAssign.value = true
    try {
      await assignmentApi.assign(payload)

      toast.add({
        severity: 'success',
        summary : 'Assignée',
        detail  : 'La demande a été assignée au gestionnaire avec succès.',
        life    : 4000,
      })

      // Rafraîchir les deux listes
      await fetchPendingRequests()
      await fetchAssignments()

      return true // signal de succès pour fermer le modal

    } catch (error) {
      const msg = error.response?.data?.message ?? 'Une erreur est survenue.'
      toast.add({ severity: 'error', summary: 'Erreur', detail: msg, life: 5000 })
      return false
    } finally {
      loadingAssign.value = false
    }
  }

  return {
    pendingRequests, assignments, gestionnaires,
    pagination, loading, loadingAssign,
    fetchPendingRequests, fetchAssignments,
    fetchGestionnaires, assignRequest,
  }
})