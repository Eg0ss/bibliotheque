<script setup>
// On importe le store d'authentification
import { useAuthStore } from '../../stores/authStore'
import { reactive } from 'vue'

const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: '',
})

async function handleSubmit() {
   authStore.login(form)
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="bg-white p-8 rounded-lg shadow w-full max-w-md">
      <h1 class="text-2xl font-bold text-[#0C447C] mb-6">Connexion</h1>

      <div v-if="authStore.errors.general" class="bg-red-50 text-red-700 p-3 rounded mb-4">
        {{ authStore.errors.general[0] }}
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">

        <div>
          <label class="block text-sm font-medium text-gray-700">Adresse e-mail</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="mt-1 w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
          />
          <p v-if="authStore.errors.email" class="text-red-500 text-sm mt-1">
            {{ authStore.errors.email[0] }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
          <input
            v-model="form.password"
            type="password"
            required
            class="mt-1 w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
          />
          <p v-if="authStore.errors.password" class="text-red-500 text-sm mt-1">
            {{ authStore.errors.password[0] }}
          </p>
        </div>

        <button
          type="submit"
          :disabled="authStore.loading"
          class="w-full bg-[#0C447C] text-white py-2 rounded font-semibold hover:bg-[#042C53] transition disabled:opacity-50"
        >
          {{ authStore.loading ? 'Connexion en cours...' : 'Se connecter' }}
        </button>

      </form>

      <p class="text-sm text-center mt-4 text-gray-600">
        Pas encore de compte ?
        <router-link to="/inscription" class="text-[#0C447C] font-medium hover:underline">
          S'inscrire
        </router-link>
      </p>
    </div>
  </div>
</template>