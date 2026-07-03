<script setup>
/**
 * UserShowView.vue
 * Page de détail + modification d'un utilisateur
 * Route : /admin/utilisateurs/:id
 *
 * Ce composant fait 3 choses :
 *  1. Afficher les infos du user (nom, email, rôle, statut)
 *  2. Permettre de modifier ces infos via un formulaire
 *  3. Proposer les actions : activer/désactiver, supprimer
 */

import { reactive, ref, watch, onMounted, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useUserStore } from '@/stores/userStore'
import { useAuthStore } from '@/stores/authStore'

// useRoute() donne accès aux paramètres de l'URL (/admin/utilisateurs/:id)
const route = useRoute()
const userStore = useUserStore()

// Contrôle l'affichage du formulaire de modification
const showEditForm = ref(false)

// Le formulaire de modification (reactive = chaque propriété est réactive)
const form = reactive({
  name: '',
  email: '',
  role_id: '',
  password: '',      // optionnel : vide = pas de changement
  password_confirmation: '',
})

// Contrôle la confirmation de suspension
const showSuspendConfirm = ref(false)

// Vérifie si l'admin connecté regarde sa propre fiche
const isSelf = computed(
  () => userStore.currentUser?.id === useAuthStore().user?.id
)

// ─── Au chargement de la page ─────────────────────────────────────────────
onMounted(async () => {
  // On charge simultanément le user ET la liste des rôles (pour le <select>)
  await Promise.all([
    userStore.fetchUser(route.params.id),
    userStore.fetchRoles(),
  ])
})

// ─── Pré-remplir le formulaire quand les données du user arrivent ──────────
// watch surveille currentUser : dès qu'il change, on remplit le formulaire
watch(() => userStore.currentUser, (user) => {
  if (user) {
    form.name = user.name
    form.email = user.email
    form.role_id = user.role?.id ?? ''
  }
})

// ─── Soumettre la modification ────────────────────────────────────────────
function handleUpdate() {
  userStore.updateUser(route.params.id, form)
}

// ─── Activer / désactiver ─────────────────────────────────────────────────
function handleToggleStatus() {
  userStore.toggleUserStatus(route.params.id)
}

function handleSuspend() {
  userStore.suspendUser(route.params.id)
  showSuspendConfirm.value = false
}
</script>

<template>
  <div class="max-w-2xl">

    <!-- Lien retour + titre -->
    <div class="flex items-center gap-4 mb-6">
      <RouterLink to="/admin/utilisateurs" class="text-gray-400 hover:text-gray-600 text-sm">
        ← Retour à la liste
      </RouterLink>
      <h1 class="text-2xl font-bold text-[#042C53]">Détail du compte</h1>
    </div>

    <!-- Chargement -->
    <div v-if="userStore.loading && !userStore.currentUser" class="text-center py-12 text-gray-400">
      Chargement...
    </div>

    <!-- Contenu principal -->
    <div v-else-if="userStore.currentUser">

      <!-- ── Carte d'informations ───────────────────────────────────────── -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">

        <!-- En-tête : avatar initiale + nom + statut -->
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-4">
            <!-- Avatar : cercle avec la première lettre du nom -->
            <div
              class="w-12 h-12 rounded-full bg-[#042C53] text-white flex items-center justify-center text-lg font-bold">
              {{ userStore.currentUser.name?.charAt(0).toUpperCase() }}
            </div>
            <div>
              <p class="font-semibold text-gray-900 text-lg">{{ userStore.currentUser.name }}</p>
              <p class="text-sm text-gray-500">{{ userStore.currentUser.email }}</p>
            </div>
          </div>

          <!-- Badge statut actif/inactif -->
          <span class="px-3 py-1 rounded-full text-xs font-medium" :class="userStore.currentUser.is_active
            ? 'bg-green-100 text-green-700'
            : 'bg-red-100 text-red-700'">
            {{ userStore.currentUser.is_active ? 'Actif' : 'Inactif' }}
          </span>
        </div>

        <!-- Grille d'informations -->
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-gray-500 mb-1">Rôle</p>
            <span class="px-2 py-1 rounded-full text-xs font-medium" :class="{
              'bg-red-100 text-red-700': userStore.currentUser.role?.slug === 'admin',
              'bg-blue-100 text-blue-700': userStore.currentUser.role?.slug === 'gestionnaire',
              'bg-purple-100 text-purple-700': userStore.currentUser.role?.slug === 'rh',
              'bg-gray-100 text-gray-700': userStore.currentUser.role?.slug === 'user',
            }">
              {{ userStore.currentUser.role?.name ?? '—' }}
            </span>
          </div>
          <div>
            <p class="text-gray-500 mb-1">Créé le</p>
            <p class="font-medium text-gray-800">{{ userStore.currentUser.created_at }}</p>
          </div>
        </div>

        <!-- ── Boutons d'actions ── -->
        <div class="flex flex-wrap gap-3 mt-6 pt-6 border-t border-gray-100">

          <!-- Modifier -->
          <button @click="showEditForm = !showEditForm" class="px-4 py-2 text-sm font-medium rounded-lg border border-[#042C53]
           text-[#042C53] hover:bg-[#042C53] hover:text-white transition">
            {{ showEditForm ? 'Annuler la modification' : 'Modifier' }}
          </button>

          <!-- Activer / Désactiver (is_active) — pas sur son propre compte -->
          <button v-if="!isSelf" @click="handleToggleStatus" :disabled="userStore.loading"
            class="px-4 py-2 text-sm font-medium rounded-lg transition disabled:opacity-50" :class="userStore.currentUser.is_active
              ? 'bg-orange-100 text-orange-700 hover:bg-orange-200'
              : 'bg-green-100 text-green-700 hover:bg-green-200'">
            {{ userStore.currentUser.is_active ? 'Désactiver' : 'Activer' }}
          </button>

          <!-- Suspendre / Réactiver (is_suspended) — pas sur son propre compte -->
          <button v-if="!isSelf" @click="showSuspendConfirm = true" :disabled="userStore.loading"
            class="px-4 py-2 text-sm font-medium rounded-lg transition disabled:opacity-50" :class="userStore.currentUser.is_suspended
              ? 'bg-blue-100 text-blue-700 hover:bg-blue-200'
              : 'bg-red-100 text-red-700 hover:bg-red-200'">
            {{ userStore.currentUser.is_suspended ? '🔓 Lever la suspension' : '🔒 Suspendre' }}
          </button>

          <!-- Message si c'est son propre compte -->
          <span v-if="isSelf" class="px-4 py-2 text-sm text-gray-400 italic">
            Vous ne pouvez pas modifier votre propre statut
          </span>

        </div>

        <!-- Badge suspension visible dans la carte d'info -->
        <div v-if="userStore.currentUser.is_suspended"
          class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-center gap-2">
          🔒 <span>Ce compte est <strong>suspendu</strong> — l'utilisateur ne peut plus se connecter.</span>
        </div>
        <div v-else-if="!userStore.currentUser.is_active"
          class="mt-4 p-3 bg-orange-50 border border-orange-200 rounded-lg text-sm text-orange-700 flex items-center gap-2">
          ⚠️ <span>Ce compte est <strong>désactivé</strong> — l'utilisateur peut se connecter mais ne peut pas soumettre
            de demande.</span>
        </div>

        <!-- ── Modal confirmation suspension ── -->
        <Teleport to="body">
          <div v-if="showSuspendConfirm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">
                {{ userStore.currentUser.is_suspended ? 'Lever la suspension ?' : 'Suspendre ce compte ?' }}
              </h3>
              <p class="text-sm text-gray-600 mb-6">
                <template v-if="userStore.currentUser.is_suspended">
                  Le compte de <strong>{{ userStore.currentUser.name }}</strong> sera réactivé.
                  L'utilisateur pourra se reconnecter.
                </template>
                <template v-else>
                  Le compte de <strong>{{ userStore.currentUser.name }}</strong> sera suspendu.
                  L'utilisateur ne pourra plus se connecter jusqu'à levée de la suspension.
                </template>
              </p>
              <div class="flex gap-3">
                <button @click="showSuspendConfirm = false"
                  class="flex-1 px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                  Annuler
                </button>
                <button @click="handleSuspend" class="flex-1 px-4 py-2 text-sm text-white rounded-lg transition" :class="userStore.currentUser.is_suspended
                  ? 'bg-blue-600 hover:bg-blue-700'
                  : 'bg-red-600 hover:bg-red-700'">
                  {{ userStore.currentUser.is_suspended ? 'Confirmer la réactivation' : 'Confirmer la suspension' }}
                </button>
              </div>
            </div>
          </div>
        </Teleport>
      </div>

      <!-- ── Formulaire de modification (affiché si showEditForm = true) ─── -->
      <div v-if="showEditForm" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-[#042C53] mb-5">Modifier le compte</h2>

        <form @submit.prevent="handleUpdate" class="space-y-5">

          <!-- Nom -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
            <input v-model="form.name" type="text" required
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]" />
            <!-- Affichage de l'erreur de validation Laravel si elle existe -->
            <p v-if="userStore.errors.name" class="text-red-500 text-xs mt-1">
              {{ userStore.errors.name[0] }}
            </p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse e-mail</label>
            <input v-model="form.email" type="email" required
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]" />
            <p v-if="userStore.errors.email" class="text-red-500 text-xs mt-1">
              {{ userStore.errors.email[0] }}
            </p>
          </div>

          <!-- Rôle -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
            <select v-model="form.role_id" required
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C] bg-white">
              <option value="" disabled>— Sélectionner un rôle —</option>
              <!-- Les rôles sont chargés depuis userStore.roles (fetchRoles()) -->
              <option v-for="role in userStore.roles" :key="role.id" :value="role.id">
                {{ role.name }}
              </option>
            </select>
            <p v-if="userStore.errors.role_id" class="text-red-500 text-xs mt-1">
              {{ userStore.errors.role_id[0] }}
            </p>
          </div>

          <!-- Nouveau mot de passe (optionnel) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Nouveau mot de passe
              <span class="text-gray-400 font-normal">(laisser vide pour ne pas changer)</span>
            </label>
            <input v-model="form.password" type="password" placeholder="Minimum 8 caractères"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]" />
            <p v-if="userStore.errors.password" class="text-red-500 text-xs mt-1">
              {{ userStore.errors.password[0] }}
            </p>
          </div>

          <!-- Confirmation mot de passe (affichée seulement si on saisit un mdp) -->
          <div v-if="form.password">
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
            <input v-model="form.password_confirmation" type="password" placeholder="Répéter le nouveau mot de passe"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]" />
          </div>

          <!-- Bouton soumettre -->
          <div class="pt-2">
            <button type="submit" :disabled="userStore.loading"
              class="w-full bg-[#042C53] text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-[#0C447C] transition disabled:opacity-50">
              {{ userStore.loading ? 'Enregistrement...' : 'Enregistrer les modifications' }}
            </button>
          </div>

        </form>
      </div>
      
    </div>
  </div>
</template>