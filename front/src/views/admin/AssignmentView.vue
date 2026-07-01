<!-- GET /api/admin/assignments  |  POST /api/admin/assignments -->
<script setup>
import { onMounted, ref, watch } from 'vue'
import { useAssignmentStore } from '@/stores/assignmentStore'
import { useRoute } from 'vue-router'

const store = useAssignmentStore()
const route = useRoute()

// ── Modal state ──────────────────────────────────────────────
const showModal       = ref(false)
const selectedRequest = ref('')    // depot_request_id pré-sélectionné
const selectedGest    = ref('')    // gestionnaire choisi dans le select
const instructions    = ref('')    // instructions optionnelles

// Si on arrive depuis PendingRequestsView avec ?depot_request_id=X
// → on ouvre le modal directement avec la demande pré-sélectionnée
watch(() => route.query.depot_request_id, (id) => {
  if (id) {
    selectedRequest.value = id
    openModal()
  }
}, { immediate: true })

onMounted(async () => {
  await store.fetchAssignments()
  await store.fetchPendingRequests()  // pour le <select> du modal
  await store.fetchGestionnaires()    // pour le <select> du modal
})

function openModal() {
  showModal.value = true
}

function closeModal() {
  showModal.value       = false
  selectedRequest.value = ''
  selectedGest.value    = ''
  instructions.value    = ''
}

async function handleAssign() {
  if (!selectedRequest.value || !selectedGest.value) return

  const success = await store.assignRequest({
    depot_request_id: selectedRequest.value,
    assigned_to     : selectedGest.value,
    instructions    : instructions.value || null,
  })

  if (success) closeModal()
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' })
}
</script>

<template>
  <div>

    <!-- En-tête -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-[#042C53]">Assignations</h1>
        <p class="text-sm text-gray-500 mt-1">
          Gérez l'assignation des demandes de dépôt aux gestionnaires.
        </p>
      </div>
      <button
        @click="openModal"
        class="bg-[#042C53] text-white text-sm px-4 py-2 rounded-lg
               hover:bg-[#0C447C] transition flex items-center gap-2"
      >
        ➕ Nouvelle assignation
      </button>
    </div>

    <!-- Chargement -->
    <div v-if="store.loading" class="text-center py-16 text-gray-400">⏳ Chargement...</div>

    <!-- Liste vide -->
    <div v-else-if="store.assignments.length === 0"
      class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
      <div class="text-5xl mb-3">📋</div>
      <p class="text-gray-400">Aucune assignation pour le moment.</p>
    </div>

    <!-- Tableau des assignations -->
    <div v-else class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Document</th>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Demandeur</th>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Gestionnaire assigné</th>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Assignée par</th>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Instructions</th>
            <th class="text-left px-5 py-3 text-gray-600 font-medium">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="a in store.assignments" :key="a.id" class="hover:bg-gray-50">
            <td class="px-5 py-4">
              <p class="font-medium text-gray-900 truncate max-w-[160px]">
                {{ a.depot_request?.reference?.title ?? '—' }}
              </p>
              <p class="text-xs text-gray-400">{{ a.depot_request?.reference?.author }}</p>
            </td>
            <td class="px-5 py-4 text-gray-600">{{ a.depot_request?.user?.name ?? '—' }}</td>
            <td class="px-5 py-4">
              <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700
                           text-xs font-medium px-2 py-1 rounded-full">
                👤 {{ a.assigned_to?.name ?? '—' }}
              </span>
            </td>
            <td class="px-5 py-4 text-gray-500 text-xs">{{ a.assigned_by?.name ?? '—' }}</td>
            <td class="px-5 py-4 text-gray-400 text-xs max-w-[180px] truncate">
              {{ a.instructions ?? '—' }}
            </td>
            <td class="px-5 py-4 text-gray-400 text-xs">{{ formatDate(a.created_at) }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="store.pagination && store.pagination.last_page > 1"
        class="px-5 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
        <span>Page {{ store.pagination.current_page }} / {{ store.pagination.last_page }}</span>
        <div class="flex gap-2">
          <button @click="store.fetchAssignments(store.pagination.current_page - 1)"
            :disabled="store.pagination.current_page === 1"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-40">
            ← Précédent
          </button>
          <button @click="store.fetchAssignments(store.pagination.current_page + 1)"
            :disabled="store.pagination.current_page === store.pagination.last_page"
            class="px-3 py-1 rounded border hover:bg-gray-50 disabled:opacity-40">
            Suivant →
          </button>
        </div>
      </div>
    </div>

    <!-- ── MODAL d'assignation ── -->
    <Teleport to="body">
      <div v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        @click.self="closeModal">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">

          <!-- Header modal -->
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-[#042C53]">Assigner une demande</h2>
            <button @click="closeModal"
              class="text-gray-400 hover:text-gray-600 text-xl leading-none">✕</button>
          </div>

          <!-- Corps modal -->
          <div class="px-6 py-5 space-y-5">

            <!-- Select : demande à assigner -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Demande de dépôt <span class="text-red-500">*</span>
              </label>
              <select v-model="selectedRequest"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-[#0C447C] bg-white">
                <option value="" disabled>Sélectionner une demande</option>
                <option
                  v-for="req in store.pendingRequests.filter(r => r.status === 'pending')"
                  :key="req.id"
                  :value="req.id"
                >
                  {{ req.id }} — {{ req.reference?.title ?? '?' }}
                  ({{ req.user?.name }})
                </option>
              </select>
            </div>

            <!-- Select : gestionnaire -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Gestionnaire assigné <span class="text-red-500">*</span>
              </label>
              <select v-model="selectedGest"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-[#0C447C] bg-white">
                <option value="" disabled>Sélectionner un gestionnaire</option>
                <option v-for="g in store.gestionnaires" :key="g.id" :value="g.id">
                  {{ g.name }}
                </option>
              </select>
            </div>

            <!-- Instructions (optionnelles) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Instructions
                <span class="text-gray-400 font-normal">(optionnelles)</span>
              </label>
              <textarea v-model="instructions" rows="3"
                placeholder="Indications particulières pour le gestionnaire..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-[#0C447C] resize-none"/>
            </div>
          </div>

          <!-- Footer modal -->
          <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3">
            <button @click="closeModal"
              class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg
                     hover:bg-gray-50 transition">
              Annuler
            </button>
            <button
              @click="handleAssign"
              :disabled="!selectedRequest || !selectedGest || store.loadingAssign"
              class="px-5 py-2 text-sm bg-[#042C53] text-white rounded-lg
                     hover:bg-[#0C447C] transition disabled:opacity-50"
            >
              {{ store.loadingAssign ? 'Assignation...' : 'Confirmer l\'assignation' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>