<script setup>
/**
 * ProcessedRequestsView.vue
 * Demandes traitées par les gestionnaires — vue Admin
 * Route : /admin/demandes-traitees
 *
 * Actions : Publier / Rejeter définitivement / Resoumettre au gestionnaire
 */

import { ref, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import apiClient from '@/api/axios'

const toast = useToast()
const requests = ref([])
const pagination = ref(null)
const loading = ref(false)

// Modal de décision
const showModal = ref(false)
const selectedId = ref(null)
const decisionType = ref('')
const comment = ref('')

async function fetchRequests(page = 1) {
  loading.value = true
  try {
    const res = await apiClient.get('/api/admin/depot-requests/traites', { params: { page } })
    requests.value = res.data.data
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      total: res.data.total,
    }
  } catch {
    toast.add({
      severity: 'error', summary: 'Erreur',
      detail: 'Impossible de charger les demandes.', life: 4000
    })
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchRequests())

function openModal(id, type) {
  selectedId.value = id
  decisionType.value = type
  comment.value = ''
  showModal.value = true
}

async function confirmDecision() {
  if (decisionType.value === 'admin_rejected' && !comment.value.trim()) {
    toast.add({
      severity: 'warn',
      summary: 'Commentaire requis',
      detail: 'Un commentaire est obligatoire en cas de rejet.',
      life: 4000,
    })
    return
  }
  loading.value = true
  try {
    const res = await apiClient.post(
      `/api/admin/depot-requests/${selectedId.value}/decision`,
      { decision: decisionType.value, comment: comment.value }
    )
    toast.add({
      severity: 'success', summary: 'Décision enregistrée',
      detail: res.data.message, life: 4000
    })
    showModal.value = false
    fetchRequests()   // recharger la liste
  } catch (error) {
    toast.add({
      severity: 'error', summary: 'Erreur',
      detail: error.response?.data?.message ?? 'Une erreur est survenue.', life: 5000
    })
  } finally {
    loading.value = false
  }
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'long', year: 'numeric',
  })
}

const statusConfig = {
  manager_approved: { label: 'Validé (gestionnaire)', bg: 'bg-blue-100', text: 'text-blue-700' },
  rejected: { label: 'Rejeté', bg: 'bg-red-100', text: 'text-red-700' },
  published: { label: 'Publié', bg: 'bg-green-100', text: 'text-green-700' },
}

const modalConfig = {
  published: { label: 'Publier', color: 'bg-green-600 hover:bg-green-700' },
  admin_rejected: { label: 'Rejeter', color: 'bg-red-600 hover:bg-red-700' },
  resubmitted: { label: 'Resoumettre', color: 'bg-amber-500 hover:bg-amber-600' },
}
</script>

<template>
  <div>

    <div class="mb-6">
      <h1 class="text-2xl font-bold text-[#042C53]">Demandes traitées</h1>
      <p class="text-sm text-gray-500 mt-1">
        Références validées ou rejetées par les gestionnaires, en attente de décision finale.
      </p>
    </div>

    <div v-if="loading && !requests.length" class="text-center py-16 text-gray-400">
      ⏳ Chargement...
    </div>

    <div v-else-if="!requests.length" class="bg-white rounded-xl border border-gray-100 p-12 text-center">
      <div class="text-5xl mb-3">📭</div>
      <p class="text-gray-400">Aucune demande traitée pour le moment.</p>
    </div>

    <div v-else class="space-y-4">
      <div v-for="req in requests" :key="req.id" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1 min-w-0">
            <h2 class="font-semibold text-[#042C53] truncate">
              {{ req.reference?.title ?? '—' }}
            </h2>
            <p class="text-sm text-gray-500 mt-0.5">✍️ {{ req.reference?.author ?? '—' }}</p>
            <div class="flex flex-wrap gap-2 mt-2">
              <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">
                {{ req.reference?.type?.name ?? '—' }}
              </span>
              <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                {{ req.reference?.category?.name ?? '—' }}
              </span>
            </div>
            <div class="flex gap-4 mt-2 text-xs text-gray-400">
              <span>👤 Soumis par <strong class="text-gray-600">{{ req.user?.name }}</strong></span>
              <span>👮 Gestionnaire : <strong class="text-gray-600">{{ req.assignment?.assigned_to?.name ?? '—'
                  }}</strong></span>
              <span>🗓️ {{ formatDate(req.updated_at) }}</span>
            </div>
            <!-- Commentaire du gestionnaire si présent -->
            <div v-if="req.latest_validation_step?.comment"
              class="mt-2 text-xs text-gray-500 border-l-2 border-blue-200 pl-2 italic">
              Commentaire gestionnaire : "{{ req.latest_validation_step.comment }}"
            </div>
          </div>

          <div class="flex flex-col items-end gap-2 flex-shrink-0">
            <!-- Badge statut -->
            <span class="px-3 py-1 rounded-full text-xs font-medium" :class="[
              statusConfig[req.status]?.bg ?? 'bg-gray-100',
              statusConfig[req.status]?.text ?? 'text-gray-600'
            ]">
              {{ statusConfig[req.status]?.label ?? req.status }}
            </span>

            <!-- Actions (seulement si manager_approved) -->
            <div v-if="req.status === 'manager_approved'" class="flex flex-col gap-1 mt-1">
              <button @click="openModal(req.id, 'published')"
                class="text-xs px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                ✅ Publier
              </button>
              <button @click="openModal(req.id, 'resubmitted')"
                class="text-xs px-3 py-1.5 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition">
                🔄 Resoumettre
              </button>
              <button @click="openModal(req.id, 'admin_rejected')"
                class="text-xs px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                ❌ Rejeter
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination?.last_page > 1" class="flex items-center justify-between pt-2 text-sm text-gray-500">
        <span>Page {{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <div class="flex gap-2">
          <button @click="fetchRequests(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
            class="px-3 py-1.5 rounded-lg border hover:bg-gray-50 disabled:opacity-40 transition">
            ← Précédent
          </button>
          <button @click="fetchRequests(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1.5 rounded-lg border hover:bg-gray-50 disabled:opacity-40 transition">
            Suivant →
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de décision -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 max-w-lg w-full mx-4 shadow-xl">
        <h3 class="text-lg font-semibold mb-1 text-[#042C53]">
          Confirmer : {{ modalConfig[decisionType]?.label }}
        </h3>
        <p class="text-sm text-gray-500 mb-4">
          <span v-if="decisionType === 'published'">La référence sera publiée dans le catalogue public.</span>
          <span v-else-if="decisionType === 'resubmitted'">La référence sera renvoyée au gestionnaire pour
            re-vérification.</span>
          <span v-else>La référence sera rejetée définitivement.</span>
        </p>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Commentaire
            <span v-if="decisionType === 'admin_rejected'" class="text-red-500">*</span>
            <span v-else class="text-gray-400 font-normal">(optionnel)</span>
          </label>
          <textarea v-model="comment" rows="3" placeholder="Motif / instructions..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C] resize-none">
          </textarea>
        </div>
        <div class="flex gap-3">
          <button @click="showModal = false"
            class="flex-1 border border-gray-300 text-gray-600 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
            Annuler
          </button>
          <button @click="confirmDecision" :disabled="loading"
            class="flex-1 py-2 rounded-lg text-sm font-semibold text-white transition disabled:opacity-50"
            :class="modalConfig[decisionType]?.color">
            {{ loading ? 'En cours...' : 'Confirmer' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>