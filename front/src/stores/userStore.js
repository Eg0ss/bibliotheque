// src/stores/userStore.js
// Gère l'état de la liste des utilisateurs et les actions CRUD

import { defineStore } from 'pinia'
import { ref } from 'vue'
import userApi from '../api/userApi'
import router from '../router'

export const useUserStore = defineStore('user', () => {

  // ─── État ─────────────────────────────────────────────
  const users      = ref([])       // tableau des utilisateurs de la page courante
  const pagination = ref(null)     // infos de pagination retournées par Laravel
  const roles      = ref([])       // liste des rôles pour le formulaire
  const loading    = ref(false)
  const errors     = ref({})
  const success    = ref('')       // message de succès après une action

  // ─── Actions ──────────────────────────────────────────

  /**
   * Charger la liste des utilisateurs (page donnée)
   */
  async function fetchUsers(page = 1) {
    loading.value = true
    try {
      const response = await userApi.getAll(page)

      // Laravel paginate() retourne { data: [...], meta: { current_page, last_page... } }
      users.value      = response.data.data
      pagination.value = response.data.meta
    } catch (error) {
      console.error('Erreur chargement utilisateurs', error)
    } finally {
      loading.value = false
    }
  }

  /**
   * Charger la liste des rôles (pour le <select> du formulaire de création)
   */
  async function fetchRoles() {
    try {
      const response = await userApi.getRoles()
      roles.value = response.data.data
    } catch (error) {
      console.error('Erreur chargement rôles', error)
    }
  }

  /**
   * Créer un nouvel utilisateur
   * @param {Object} formData - données du formulaire
   */
  async function createUser(formData) {
    loading.value = true
    errors.value  = {}
    success.value = ''
    try {
      await userApi.create(formData)

      success.value = 'Compte créé avec succès.'

      // Rediriger vers la liste après création
      router.push('/admin/utilisateurs')

    } catch (error) {
      if (error.response?.status === 422) {
        // Erreurs de validation Laravel : on les affiche champ par champ
        errors.value = error.response.data.errors
      } else {
        errors.value = { general: ['Une erreur est survenue.'] }
      }
    } finally {
      loading.value = false
    }
  }

  return {
    users, pagination, roles, loading, errors, success,
    fetchUsers, fetchRoles, createUser,
  }
})