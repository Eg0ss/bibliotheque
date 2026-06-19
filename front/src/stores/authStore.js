import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

// Test users for development
const testUsers = [
  { id: '1', email: 'user@test.com', password: 'password', name: 'User Test', role: 'user' },
  { id: '2', email: 'gestionnaire@test.com', password: 'password', name: 'Gestionnaire Test', role: 'gestionnaire' },
  { id: '3', email: 'admin@test.com', password: 'password', name: 'Admin Test', role: 'admin' },
  { id: '4', email: 'rh@test.com', password: 'password', name: 'RH Test', role: 'rh' }
]

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)
  const role = computed(() => user.value?.role || 'guest')
  const isAuthenticated = computed(() => !!user.value)

  function setUser(userData) {
    user.value = userData
  }

  function setToken(t) {
    token.value = t
    localStorage.setItem('token', t)
  }

  function login(email, password) {
    const foundUser = testUsers.find(u => u.email === email && u.password === password)
    if (foundUser) {
      const { password: _, ...userData } = foundUser
      setUser(userData)
      setToken('test-token-' + userData.id)
      return true
    }
    return false
  }

  function logout() {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
  }

  return { user, token, role, isAuthenticated, setUser, setToken, login, logout }
})
