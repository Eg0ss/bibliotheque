// src/stores/userStore.js
// Ce store est le "cerveau" de la gestion des utilisateurs côté Vue
// Il centralise toutes les actions (fetch, create, update, delete)
// et les états (loading, errors, liste des users)

import { defineStore } from 'pinia'
import { ref } from 'vue'
import userApi from '../api/userApi'
import router from '../router'
import { useToast } from 'primevue/usetoast'

export const useUserStore = defineStore('user', () => {

  // ─── État ─────────────────────────────────────────────────────────────────
  const users         = ref([])        // liste des utilisateurs affichée dans le tableau
  const currentUser   = ref(null)      // utilisateur en cours de visualisation/modification
  const pagination    = ref(null)      // infos de pagination (page courante, total, etc.)
  const roles         = ref([])        // liste des rôles pour les <select>
  const loading       = ref(false)     // true pendant un appel API → désactive les boutons
  const errors        = ref({})        // erreurs de validation renvoyées par Laravel
  const toast = useToast()             //service notification
  const filters = ref({
  search  : '',   // texte tapé dans la barre de recherche
  status  : '',   // '' = tous | '1' = actifs | '0' = inactifs
  role_id : '',   // '' = tous les rôles | sinon l'id du rôle
})

  // ─── RÉCUPÉRER LA LISTE DES UTILISATEURS ─────────────────────────────────
  /**
 * Charger les utilisateurs en passant les filtres actifs à l'API
 */
async function fetchUsers(page = 1) {
  loading.value = true
  try {
    // On nettoie les filtres : on retire les clés dont la valeur est vide ''
    const activeFilters = Object.fromEntries(
      Object.entries(filters.value).filter(([, v]) => v !== '')
    )

    const response   = await userApi.getAll(page, activeFilters)
    users.value      = response.data.data
    pagination.value = response.data.meta
  } catch (error) {
    toast.add({
      severity : 'error',
      summary  : 'Erreur',
      detail   : 'Impossible de charger la liste des utilisateurs.',
      life     : 4000,
    })
  } finally {
    loading.value = false
  }
}

/**
 * Réinitialiser tous les filtres et recharger la liste
 */
function resetFilters() {
  filters.value = { search: '', status: '', role_id: '' }
  fetchUsers(1)
}

  // ─── RÉCUPÉRER UN SEUL UTILISATEUR ───────────────────────────────────────
  // Appelé quand on arrive sur la page de détail /admin/utilisateurs/:id
  async function fetchUser(id) {
    loading.value   = true
    currentUser.value = null // on vide d'abord pour éviter d'afficher un ancien user
    try {
      const response    = await userApi.getOne(id)
      // Laravel via UserResource renvoie { data: { id, name, email, role, ... } }
      currentUser.value = response.data.data
    } catch (error) {
      toast.add({
        severity : 'error',
        summary  : 'Introuvable',
        detail   : 'Cet utilisateur n\'existe pas ou a été supprimé.',
        life     : 4000,
      })
      // On redirige vers la liste si le user n'existe pas
      router.push('/admin/utilisateurs')
    } finally {
      loading.value = false
    }
  }

  // ─── RÉCUPÉRER LES RÔLES (pour les <select> dans les formulaires) ─────────
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

  // ─── CRÉER UN UTILISATEUR ────────────────────────────────────────────────
  async function createUser(formData) {
    loading.value = true
    errors.value  = {}
    try {
      await userApi.create(formData)

      toast.add({
        severity : 'success',
        summary  : 'Compte créé',
        detail   : 'Le compte utilisateur a été créé avec succès.',
        life     : 4000,
      })

      router.push('/admin/utilisateurs')

    } catch (error) {
      if (error.response?.status === 422) {
        // 422 = erreur de validation Laravel → on affiche les erreurs champ par champ
        errors.value = error.response.data.errors
        toast.add({
          severity : 'warn',
          summary  : 'Formulaire invalide',
          detail   : 'Veuillez corriger les erreurs dans le formulaire.',
          life     : 4000,
        })
      } else {
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

  // ─── MODIFIER UN UTILISATEUR ─────────────────────────────────────────────
  async function updateUser(id, formData) {
    loading.value = true
    errors.value  = {}
    try {
      const response = await userApi.update(id, formData)

      // On met à jour currentUser avec les nouvelles données reçues du serveur
      // (plus fiable que de mettre à jour manuellement le state local)
      currentUser.value = response.data.user

      toast.add({
        severity : 'success',
        summary  : 'Modifications enregistrées',
        detail   : 'Le compte a été mis à jour avec succès.',
        life     : 4000,
      })

      router.push('/admin/utilisateurs')

    } catch (error) {
      if (error.response?.status === 422) {
        errors.value = error.response.data.errors
        toast.add({
          severity : 'warn',
          summary  : 'Formulaire invalide',
          detail   : 'Veuillez corriger les erreurs dans le formulaire.',
          life     : 4000,
        })
      } else {
        toast.add({
          severity : 'error',
          summary  : 'Erreur serveur',
          detail   : 'Une erreur est survenue. Veuillez réessayer.',
          life     : 5000,
        })
      }
    } finally {
      loading.value = false
    }
  }

  // ─── ACTIVER / DÉSACTIVER UN UTILISATEUR ─────────────────────────────────
  async function toggleUserStatus(id) {
    try {
      const response = await userApi.toggleStatus(id)

      // On met à jour is_active dans currentUser sans recharger toute la page
      if (currentUser.value) {
        currentUser.value.is_active = response.data.is_active
      }

      // On met aussi à jour dans la liste si elle est chargée
      const userInList = users.value.find(u => u.id === id)
      if (userInList) {
        userInList.is_active = response.data.is_active
      }

      toast.add({
        severity : 'success',
        summary  : 'Statut mis à jour',
        detail   : response.data.message,
        life     : 3000,
      })

    } catch (error) {
      toast.add({
        severity : 'error',
        summary  : 'Erreur',
        detail   : 'Impossible de modifier le statut.',
        life     : 4000,
      })
    }
  }

 // Suspendre / réactiver un compte
async function suspendUser(id) {
  loading.value = true
  try {
    const response = await userApi.suspend(id)

    // Mettre à jour is_suspended dans currentUser sans recharger
    if (currentUser.value) {
      currentUser.value.is_suspended = response.data.is_suspended
    }

    toast.add({
      severity: response.data.is_suspended ? 'warn' : 'success',
      summary : response.data.is_suspended ? 'Compte suspendu' : 'Compte réactivé',
      detail  : response.data.message,
      life    : 4000,
    })

  } catch (error) {
    const msg = error.response?.data?.message ?? 'Une erreur est survenue.'
    toast.add({ severity: 'error', summary: 'Erreur', detail: msg, life: 5000 })
  } finally {
    loading.value = false
  }
}

  // ─── On expose tout ce dont les composants Vue ont besoin ─────────────────
  return {
    users, currentUser, pagination, roles,filters, loading, errors, 
    fetchUsers, fetchUser, fetchRoles, resetFilters,
    createUser, updateUser, toggleUserStatus, suspendUser,
  }
})