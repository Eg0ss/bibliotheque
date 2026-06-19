// src/store/authStore.js
// Le "cerveau" de l'authentification côté Vue
// Stocke l'utilisateur connecté et expose les actions login/logout/register

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authApi from '../api/authApi'
import router from '../router'

export const useAuthStore = defineStore('auth', () => {

  // ─── État ───────────────────────────────────────────────
  // L'utilisateur connecté (null si personne n'est connecté)
  const user = ref(null)

  // true pendant qu'on attend une réponse de l'API
  const loading = ref(false)

  // Message d'erreur à afficher dans les formulaires
  const errors = ref({})

  // ─── Getters (computed) ──────────────────────────────────
  // true si un utilisateur est connecté
  const isAuthenticated = computed(() => !!user.value)

  // Le nom du rôle de l'utilisateur connecté (ex: 'admin', 'user', 'gestionnaire')
  const userRole = computed(() => user.value?.role?.name ?? null)

  // ─── Actions ─────────────────────────────────────────────

  /**
   * Inscription
   * @param {Object} formData - { name, email, password, password_confirmation }
   */
  async function register(formData) {
    loading.value = true
    errors.value  = {}
    try {
      // 1. Obtenir le cookie CSRF (obligatoire avant tout POST avec Sanctum)
      await authApi.getCsrfCookie()

      // 2. Envoyer les données d'inscription
      const response = await authApi.register(formData)

      // 3. Stocker l'utilisateur retourné par Laravel
      user.value = response.data.user

      // 4. Rediriger vers la page d'accueil de l'espace utilisateur
      router.push('/mon-espace/depots')

    } catch (error) {
      // Laravel renvoie les erreurs de validation dans error.response.data.errors
      if (error.response?.status === 422) {
        errors.value = error.response.data.errors
      } else {
        errors.value = { general: ['Une erreur est survenue. Réessayez.'] }
      }
    } finally {
      loading.value = false
    }
  }

  /**
   * Connexion
   * @param {Object} credentials - { email, password }
   */
  async function login(credentials) {
    loading.value = true
    errors.value  = {}
    try {
      // 1. Cookie CSRF obligatoire avant le POST /login
      await authApi.getCsrfCookie()

      // 2. Tenter la connexion
      const response = await authApi.login(credentials)

      // 3. Stocker l'utilisateur
      user.value = response.data.user

      // 4. Rediriger selon le rôle
      redirectAfterLogin()

    } catch (error) {
      if (error.response?.status === 422) {
        errors.value = error.response.data.errors
      } else {
        errors.value = { general: ['Une erreur est survenue. Réessayez.'] }
      }
    } finally {
      loading.value = false
    }
  }

  /**
   * Déconnexion
   */
  async function logout() {
    try {
      await authApi.logout()
    } finally {
      // Même si l'API échoue, on vide l'état local et on redirige
      user.value = null
      router.push('/connexion')
    }
  }

  /**
   * Vérifier si l'utilisateur est encore connecté (appelé au rechargement de page)
   * Si la session est encore valide côté Laravel, on récupère l'utilisateur
   */
  async function fetchUser() {
    try {
      const response = await authApi.me()
      user.value = response.data.user
    } catch {
      // 401 = session expirée ou inexistante : l'utilisateur n'est pas connecté
      user.value = null
    }
  }

  /**
   * Redirection intelligente après connexion selon le rôle
   */
  function redirectAfterLogin() {
    const role = userRole.value
    if (role === 'admin')        router.push('/admin')
    else if (role === 'gestionnaire') router.push('/gestionnaire')
    else if (role === 'rh')      router.push('/rh')
    else                         router.push('/mon-espace/depots')
  }

  return {
    user, loading, errors,
    isAuthenticated, userRole,
    register, login, logout, fetchUser,
  }
})