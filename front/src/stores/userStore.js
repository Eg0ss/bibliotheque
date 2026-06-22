// src/stores/userStore.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import userApi from '../api/userApi'
import router from '../router'

// Import du composable PrimeVue Toast
import { useToast } from 'primevue/usetoast'

export const useUserStore = defineStore('user', () => {

  // ─── État ─────────────────────────────────────────────
  const users      = ref([])
  const pagination = ref(null)
  const roles      = ref([])
  const loading    = ref(false)
  const errors     = ref({})

  // useToast() donne accès au service Toast initialisé dans main.js
  const toast = useToast()

  // ─── Actions ──────────────────────────────────────────

  async function fetchUsers(page = 1) {
    loading.value = true
    try {
      const response   = await userApi.getAll(page)
      users.value      = response.data.data
      pagination.value = response.data.meta
    } catch (error) {
      // Toast d'erreur si le chargement échoue
      toast.add({
        severity : 'error',
        summary  : 'Erreur',
        detail   : 'Impossible de charger la liste des utilisateurs.',
        life     : 4000, // disparaît après 4 secondes
      })
    } finally {
      loading.value = false
    }
  }

  async function fetchRoles() {
    try {
      const response = await userApi.getRoles()
      roles.value    = response.data.data
    } catch (error) {
      toast.add({
        severity : 'warn',
        summary  : 'Attention',
        detail   : 'Impossible de charger les rôles.',
        life     : 3000,
      })
    }
  }

  async function createUser(formData) {
    loading.value = true
    errors.value  = {}
    try {
      await userApi.create(formData)

      // Toast de succès
      toast.add({
        severity : 'success',
        summary  : 'Compte créé',
        detail   : 'Le compte utilisateur a été créé avec succès.',
        life     : 4000,
      })

      // Redirection vers la liste
      router.push('/admin/utilisateurs')

    } catch (error) {
      if (error.response?.status === 422) {
        // Erreurs de validation → affichées champ par champ dans le formulaire
        errors.value = error.response.data.errors

        // Toast d'avertissement général
        toast.add({
          severity : 'warn',
          summary  : 'Formulaire invalide',
          detail   : 'Veuillez corriger les erreurs dans le formulaire.',
          life     : 4000,
        })
      } else {
        // Toast d'erreur serveur
        toast.add({
          severity : 'error',
          summary  : 'Erreur serveur',
          detail   : 'Une erreur est survenue. Veuillez réessayer.',
          life     : 5000,
        })
        errors.value = { general: ['Une erreur est survenue.'] }
      }
    } finally {
      loading.value = false
    }
  }

  return {
    users, pagination, roles, loading, errors,
    fetchUsers, fetchRoles, createUser,
  }
})