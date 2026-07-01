<script setup>
/**
 * DocumentDetailView.vue
 * Détail d'une assignation pour le gestionnaire
 * Route : /gestionnaire/documents/:id
 *
 * Actions possibles :
 *  - Accepter → decision = 'manager_approved'
 *  - Rejeter  → decision = 'manager_rejected' + commentaire obligatoire
 */

import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useGestionnaireStore } from '@/stores/gestionnaireStore'
import { useToast } from 'primevue'

const route = useRoute()
const store = useGestionnaireStore()
const toast = useToast()

// Contrôle le panneau de décision
const showDecisionPanel = ref(false)
const decisionType = ref('')   // 'manager_approved' | 'manager_rejected'
const comment = ref('')

onMounted(() => {
  store.fetchDocument(route.params.id)
})

// Ouvrir le panneau avec le bon type de décision
function openDecision(type) {
  decisionType.value = type
  comment.value = ''
  showDecisionPanel.value = true
}

// Confirmer la décision
function confirmDecision() {
  if (decisionType.value === 'manager_rejected' && !comment.value.trim()) {
    toast.add({
      severity: 'warn',
      summary: 'Commentaire requis',
      detail: 'Un commentaire est obligatoire en cas de rejet.',
      life: 4000,
    })
    return
  }
  store.decide(route.params.id, decisionType.value, comment.value)
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'long', year: 'numeric',
  })
}
</script>

<template>
  <div class="max-w-3xl">

    <!-- Retour -->
    <div class="flex items-center gap-4 mb-6">
      <RouterLink to="/gestionnaire/documents" class="text-gray-400 hover:text-gray-600 text-sm">
        ← Documents à vérifier
      </RouterLink>
      <h1 class="text-2xl font-bold text-[#042C53]">Détail du document</h1>
    </div>

    <!-- Chargement -->
    <div v-if="store.loading && !store.currentDocument" class="text-center py-16 text-gray-400">
      ⏳ Chargement...
    </div>

    <div v-else-if="store.currentDocument">

      <!-- ── Fiche de la référence ──────────────────────────────────── -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">

        <div class="flex items-start justify-between gap-4 mb-5">
          <div>
            <h2 class="text-xl font-bold text-[#042C53]">
              {{ store.currentDocument.depot_request?.reference?.title }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
              ✍️ {{ store.currentDocument.depot_request?.reference?.author }}
            </p>
          </div>
          <!-- Badges type + catégorie -->
          <div class="flex flex-col items-end gap-1 flex-shrink-0">
            <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded-full">
              {{ store.currentDocument.depot_request?.reference?.type?.name }}
            </span>
            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
              {{ store.currentDocument.depot_request?.reference?.category?.name }}
            </span>
          </div>
        </div>

        <!-- Grille de métadonnées -->
        <div class="grid grid-cols-2 gap-4 text-sm mb-5">
          <div>
            <p class="text-gray-400 text-xs mb-0.5">Éditeur</p>
            <p class="font-medium text-gray-700">
              {{ store.currentDocument.depot_request?.reference?.publisher ?? '—' }}
            </p>
          </div>
          <div>
            <p class="text-gray-400 text-xs mb-0.5">Année</p>
            <p class="font-medium text-gray-700">
              {{ store.currentDocument.depot_request?.reference?.publication_year ?? '—' }}
            </p>
          </div>
          <div>
            <p class="text-gray-400 text-xs mb-0.5">Langue</p>
            <p class="font-medium text-gray-700 uppercase">
              {{ store.currentDocument.depot_request?.reference?.language ?? '—' }}
            </p>
          </div>
          <div>
            <p class="text-gray-400 text-xs mb-0.5">ISBN</p>
            <p class="font-medium text-gray-700">
              {{ store.currentDocument.depot_request?.reference?.isbn ?? '—' }}
            </p>
          </div>
        </div>

        <!-- Résumé -->
        <div v-if="store.currentDocument.depot_request?.reference?.abstract" class="bg-gray-50 rounded-lg p-4 mb-5">
          <p class="text-xs text-gray-400 mb-1">Résumé</p>
          <p class="text-sm text-gray-700">
            {{ store.currentDocument.depot_request?.reference?.abstract }}
          </p>
        </div>

        <!-- Fichiers joints -->
        <div>
          <p class="text-xs text-gray-400 mb-2">Fichiers joints</p>
          <div v-if="store.currentDocument.depot_request?.reference?.documents?.length" class="flex flex-wrap gap-2">
            <span v-for="doc in store.currentDocument.depot_request.reference.documents" :key="doc.id"
              class="inline-flex items-center gap-1 text-xs bg-blue-50 text-blue-700 px-3 py-1.5 rounded-full">
              📄 {{ doc.original_name }}
              <span class="text-blue-400">
                ({{ doc.file_size ? (doc.file_size / 1024 / 1024).toFixed(2) + ' Mo' : '—' }})
              </span>
            </span>
          </div>
          <p v-else class="text-sm text-gray-400 italic">Aucun fichier joint.</p>
        </div>
      </div>

      <!-- ── Infos de soumission ────────────────────────────────────── -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 text-sm">
        <div class="flex flex-wrap gap-6 text-gray-600">
          <div>
            <span class="text-gray-400">Soumis par</span>
            <span class="ml-2 font-medium">
              {{ store.currentDocument.depot_request?.user?.name }}
            </span>
          </div>
          <div>
            <span class="text-gray-400">Assigné par</span>
            <span class="ml-2 font-medium">
              {{ store.currentDocument.assigned_by?.name }}
            </span>
          </div>
          <div>
            <span class="text-gray-400">Assigné le</span>
            <span class="ml-2 font-medium">
              {{ formatDate(store.currentDocument.created_at) }}
            </span>
          </div>
        </div>

        <!-- Instructions admin si présentes -->
        <div v-if="store.currentDocument.instructions"
          class="mt-3 p-3 bg-amber-50 border border-amber-100 rounded-lg text-amber-800 text-sm">
          <span class="font-medium">📝 Instructions de l'admin :</span>
          {{ store.currentDocument.instructions }}
        </div>
      </div>

      <!-- ── Boutons d'action ───────────────────────────────────────── -->
      <div class="flex gap-3">
        <button @click="openDecision('manager_approved')"
          class="flex-1 bg-green-600 text-white py-3 rounded-xl font-semibold hover:bg-green-700 transition">
          ✅ Accepter
        </button>
        <button @click="openDecision('manager_rejected')"
          class="flex-1 bg-red-600 text-white py-3 rounded-xl font-semibold hover:bg-red-700 transition">
          ❌ Rejeter
        </button>
      </div>

      <!-- ── Panneau de décision (modal) ────────────────────────────── -->
      <div v-if="showDecisionPanel" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-lg w-full mx-4 shadow-xl">

          <h3 class="text-lg font-semibold mb-1"
            :class="decisionType === 'manager_approved' ? 'text-green-700' : 'text-red-700'">
            {{ decisionType === 'manager_approved' ? '✅ Confirmer l\'acceptation' : '❌ Confirmer le rejet' }}
          </h3>

          <p class="text-sm text-gray-500 mb-4">
            {{ decisionType === 'manager_approved'
              ? 'La référence sera soumise à l\'administrateur pour décision finale.'
              : 'La référence sera rejetée. Un commentaire est obligatoire.' }}
          </p>

          <!-- Commentaire -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Commentaire
              <span v-if="decisionType === 'manager_rejected'" class="text-red-500">*</span>
              <span v-else class="text-gray-400 font-normal">(optionnel)</span>
            </label>
            <textarea v-model="comment" rows="3" placeholder="Motif de la décision..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C] resize-none"></textarea>
          </div>

          <div class="flex gap-3">
            <button @click="showDecisionPanel = false"
              class="flex-1 border border-gray-300 text-gray-600 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
              Annuler
            </button>
            <button @click="confirmDecision" :disabled="store.loading"
              class="flex-1 py-2 rounded-lg text-sm font-semibold text-white transition disabled:opacity-50"
              :class="decisionType === 'manager_approved' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'">
              {{ store.loading ? 'Enregistrement...' : 'Confirmer' }}
            </button>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>