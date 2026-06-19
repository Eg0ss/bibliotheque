// crée une instance Axios unique pour tout le projet
// Elle sera importée dans tous les fichiers *Api.js

import axios from 'axios'

const apiClient = axios.create({
  // L'URL de base de ton API Laravel
  baseURL: 'http://localhost:8000',

  // CRUCIAL avec Sanctum : permet d'envoyer et recevoir les cookies de session
  // Sans ça, Laravel ne reconnaîtra jamais l'utilisateur connecté
  withCredentials: true,
  withXSRFToken: true,

  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

export default apiClient