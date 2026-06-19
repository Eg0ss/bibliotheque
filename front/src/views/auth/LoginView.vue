<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref('')

function handleLogin(e) {
  e.preventDefault()
  error.value = ''
  
  const success = authStore.login(email.value, password.value)
  if (success) {
    const role = authStore.role
    if (role === 'admin' || role === 'gestionnaire' || role === 'rh') {
      router.push({ path: role === 'admin' ? '/admin' : role === 'gestionnaire' ? '/gestionnaire/documents' : '/rh/utilisateurs' })
    } else {
      router.push({ name: 'home' })
    }
  } else {
    error.value = 'Email ou mot de passe incorrect'
  }
}

// Test users quick login
function quickLogin(testUser) {
  email.value = testUser.email
  password.value = testUser.password
  handleLogin({ preventDefault: () => {} })
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-[#f8f9fb] p-4">
    <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-8 max-w-md w-full">
      <h1 class="text-2xl font-bold text-center mb-6 text-[#1e3a5f]">Connexion</h1>
      
      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
          <input
            v-model="email"
            type="email"
            required
            class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
            placeholder="email@test.com"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Mot de passe</label>
          <input
            v-model="password"
            type="password"
            required
            class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
            placeholder="password"
          />
        </div>

        <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

        <button
          type="submit"
          class="w-full bg-[#1e3a5f] text-white py-2 rounded-lg hover:bg-[#2d5a8e] transition"
        >
          Se connecter
        </button>
      </form>

      <div class="mt-8 pt-6 border-t border-slate-200">
        <p class="text-sm text-slate-500 mb-3">Utilisateurs de test :</p>
        <div class="space-y-2">
          <button
            @click="quickLogin({ email: 'user@test.com', password: 'password' })"
            class="w-full text-left px-3 py-2 rounded-lg bg-slate-50 hover:bg-slate-100 transition"
          >
            <span class="font-medium">Utilisateur</span>
            <span class="text-sm text-slate-500 block">user@test.com / password</span>
          </button>
          <button
            @click="quickLogin({ email: 'gestionnaire@test.com', password: 'password' })"
            class="w-full text-left px-3 py-2 rounded-lg bg-slate-50 hover:bg-slate-100 transition"
          >
            <span class="font-medium">Gestionnaire</span>
            <span class="text-sm text-slate-500 block">gestionnaire@test.com / password</span>
          </button>
          <button
            @click="quickLogin({ email: 'admin@test.com', password: 'password' })"
            class="w-full text-left px-3 py-2 rounded-lg bg-slate-50 hover:bg-slate-100 transition"
          >
            <span class="font-medium">Admin</span>
            <span class="text-sm text-slate-500 block">admin@test.com / password</span>
          </button>
          <button
            @click="quickLogin({ email: 'rh@test.com', password: 'password' })"
            class="w-full text-left px-3 py-2 rounded-lg bg-slate-50 hover:bg-slate-100 transition"
          >
            <span class="font-medium">RH</span>
            <span class="text-sm text-slate-500 block">rh@test.com / password</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
