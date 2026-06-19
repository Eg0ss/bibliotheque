// Toutes les fonctions qui appellent les routes d'authentification Laravel

import apiClient from './axios'

const authApi = {

  /**
   * récupérer le cookie CSRF avant tout POST (register, login)
   * Laravel refuse les POST sans ce cookie (protection CSRF)
   */
  getCsrfCookie() {
    return apiClient.get('/sanctum/csrf-cookie')
  },

  /**
   * Inscription : envoie name, email, password, password_confirmation
   */
  register(data) {
    return apiClient.post('/api/register', data)
  },

  /**
   * Connexion : envoie email, password
   */
  login(credentials) {
    return apiClient.post('/api/login', credentials)
  },

  /**
   * Déconnexion : invalide la session côté serveur
   */
  logout() {
    return apiClient.post('/api/logout')
  },

  /**
   * Récupérer l'utilisateur connecté (appelé au rechargement de page)
   */
  me() {
    return apiClient.get('/api/me')
  },
}

export default authApi