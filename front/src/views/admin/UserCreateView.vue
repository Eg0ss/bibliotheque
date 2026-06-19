<!-- Correspond à POST /api/admin/users -->
<script setup>
import { reactive, onMounted } from 'vue'
import { useUserStore } from '@/stores/userStore'
import { RouterLink } from 'vue-router'

const userStore = useUserStore()

// Données du formulaire (réactives : liées aux champs v-model)
const form = reactive({
  name                 : '',
  email                : '',
  password             : '',
  password_confirmation: '',
  role_id              : '',
})

// Charger les rôles au montage (pour remplir le <select>)
onMounted(() => {
  userStore.fetchRoles()
})

// Soumettre le formulaire
function handleSubmit() {
  userStore.createUser(form)
}
</script>

<template>
  <div class="max-w-xl">

    <!-- En-tête -->
    <div class="flex items-center gap-4 mb-6">
      <RouterLink to="/admin/utilisateurs" class="text-gray-400 hover:text-gray-600 text-sm">
        ← Retour à la liste
      </RouterLink>
      <h1 class="text-2xl font-bold text-[#042C53]">Créer un compte utilisateur</h1>
    </div>

    <!-- Message de succès -->
    <div v-if="userStore.success" class="bg-green-50 text-green-700 p-4 rounded-lg mb-4">
      {{ userStore.success }}
    </div>

    <!-- Erreur générale -->
    <div v-if="userStore.errors.general" class="bg-red-50 text-red-700 p-4 rounded-lg mb-4">
      {{ userStore.errors.general[0] }}
    </div>

    <!-- Formulaire -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <form @submit.prevent="handleSubmit" class="space-y-5">

        <!-- Nom -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
          <input
            v-model="form.name"
            type="text"
            required
            placeholder="Ex : Jean Dupont"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
          />
          <p v-if="userStore.errors.name" class="text-red-500 text-xs mt-1">
            {{ userStore.errors.name[0] }}
          </p>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Adresse e-mail</label>
          <input
            v-model="form.email"
            type="email"
            required
            placeholder="jean.dupont@exemple.bj"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
          />
          <p v-if="userStore.errors.email" class="text-red-500 text-xs mt-1">
            {{ userStore.errors.email[0] }}
          </p>
        </div>

        <!-- Rôle -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
          <select
            v-model="form.role_id"
            required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C] bg-white"
          >
            <option value="" disabled>— Sélectionner un rôle —</option>
            <!-- Les rôles sont chargés depuis l'API via userStore.fetchRoles() -->
            <option v-for="role in userStore.roles" :key="role.id" :value="role.id">
              {{ role.name }}
            </option>
          </select>
          <p v-if="userStore.errors.role_id" class="text-red-500 text-xs mt-1">
            {{ userStore.errors.role_id[0] }}
          </p>
        </div>

        <!-- Mot de passe -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
          <input
            v-model="form.password"
            type="password"
            required
            placeholder="Minimum 8 caractères"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
          />
          <p v-if="userStore.errors.password" class="text-red-500 text-xs mt-1">
            {{ userStore.errors.password[0] }}
          </p>
        </div>

        <!-- Confirmation mot de passe -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            required
            placeholder="Répéter le mot de passe"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
          />
        </div>

        <!-- Bouton soumettre -->
        <div class="pt-2">
          <button
            type="submit"
            :disabled="userStore.loading"
            class="w-full bg-[#042C53] text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-[#0C447C] transition disabled:opacity-50"
          >
            {{ userStore.loading ? 'Création en cours...' : 'Créer le compte' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>