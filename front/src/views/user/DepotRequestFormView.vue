<script setup>
/**
 * DepotRequestFormView.vue
 * Formulaire de soumission d'une demande de dépôt
 * Route : /mon-espace/depots/nouveau
 *
 * Formulaire en 2 étapes :
 *  Étape 1 → Métadonnées (titre, auteur, catégorie, type, résumé...)
 *  Étape 2 → Fichiers (PDF + couverture optionnelle)
 */

import { ref, reactive, onMounted }    from 'vue'
import { RouterLink }                  from 'vue-router'
import { useDepotRequestStore }        from '@/stores/depotRequestStore'

const store = useDepotRequestStore()

// Étape active du formulaire (1 ou 2)
const currentStep = ref(1)

// Données du formulaire
const form = reactive({
  title            : '',
  author           : '',
  publisher        : '',
  publication_year : '',
  language         : 'fr',
  isbn             : '',
  abstract         : '',
  category_id      : '',
  type_id          : '',
})

// Fichiers (séparés car non réactifs avec reactive())
const pdfFile   = ref(null)
const coverFile = ref(null)

// Aperçu de l'image de couverture
const coverPreview = ref(null)

// ── Au chargement : récupérer catégories et types ───────────────────────
onMounted(() => {
  store.fetchFormOptions()
})

// ── Gestion du fichier PDF ───────────────────────────────────────────────
function onPdfChange(event) {
  pdfFile.value = event.target.files[0] || null
}

// ── Gestion de l'image de couverture ────────────────────────────────────
function onCoverChange(event) {
  const file = event.target.files[0]
  if (!file) return
  coverFile.value = file

  // Créer un aperçu local avec FileReader
  const reader = new FileReader()
  reader.onload = (e) => { coverPreview.value = e.target.result }
  reader.readAsDataURL(file)
}

// ── Passer à l'étape 2 (avec validation basique de l'étape 1) ───────────
function nextStep() {
  // Vérification minimale avant de passer à l'étape suivante
  if (!form.title || !form.author || !form.category_id || !form.type_id) {
    alert('Veuillez remplir les champs obligatoires (Titre, Auteur, Catégorie, Type).')
    return
  }
  currentStep.value = 2
}

// ── Soumettre le formulaire ──────────────────────────────────────────────
function handleSubmit() {
  // PDF optionnel : on soumet même sans fichier
  const formData = new FormData()

  // Ajouter toutes les métadonnées (on ignore les valeurs vides)
  Object.entries(form).forEach(([key, value]) => {
    if (value !== '' && value !== null) {
      formData.append(key, value)
    }
  })

  // Ajouter les fichiers seulement s'ils ont été sélectionnés
  if (pdfFile.value) {
    formData.append('file', pdfFile.value)
  }
  if (coverFile.value) {
    formData.append('cover_image', coverFile.value)
  }

  store.submitRequest(formData)
}
</script>

<template>
  <div class="max-w-2xl">

    <!-- En-tête -->
    <div class="flex items-center gap-4 mb-6">
      <RouterLink to="/mon-espace/depots" class="text-gray-400 hover:text-gray-600 text-sm">
        ← Mes demandes
      </RouterLink>
      <h1 class="text-2xl font-bold text-[#042C53]">Soumettre un document</h1>
    </div>

    <!-- Indicateur d'étapes -->
    <div class="flex items-center gap-4 mb-8">
      <!-- Étape 1 -->
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition"
          :class="currentStep >= 1 ? 'bg-[#042C53] text-white' : 'bg-gray-200 text-gray-500'"
        >1</div>
        <span class="text-sm font-medium"
          :class="currentStep >= 1 ? 'text-[#042C53]' : 'text-gray-400'"
        >Métadonnées</span>
      </div>

      <!-- Trait de liaison -->
      <div class="flex-1 h-px" :class="currentStep >= 2 ? 'bg-[#042C53]' : 'bg-gray-200'"></div>

      <!-- Étape 2 -->
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition"
          :class="currentStep >= 2 ? 'bg-[#042C53] text-white' : 'bg-gray-200 text-gray-500'"
        >2</div>
        <span class="text-sm font-medium"
          :class="currentStep >= 2 ? 'text-[#042C53]' : 'text-gray-400'"
        >Fichiers</span>
      </div>
    </div>

    <!-- ── ÉTAPE 1 : Métadonnées ─────────────────────────────────────── -->
    <div v-if="currentStep === 1" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <h2 class="text-lg font-semibold text-[#042C53] mb-5">Informations bibliographiques</h2>

      <div class="space-y-5">

        <!-- Titre -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Titre <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.title"
            type="text"
            placeholder="Titre complet du document"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
          />
          <p v-if="store.errors.title" class="text-red-500 text-xs mt-1">
            {{ store.errors.title[0] }}
          </p>
        </div>

        <!-- Auteur -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Auteur(s) <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.author"
            type="text"
            placeholder="Nom Prénom (séparés par des virgules si plusieurs)"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
          />
          <p v-if="store.errors.author" class="text-red-500 text-xs mt-1">
            {{ store.errors.author[0] }}
          </p>
        </div>

        <!-- Catégorie + Type sur la même ligne -->
        <div class="grid grid-cols-2 gap-4">

          <!-- Catégorie -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Catégorie <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.category_id"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
            >
              <option value="" disabled>— Choisir —</option>
              <option v-for="cat in store.categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
            <p v-if="store.errors.category_id" class="text-red-500 text-xs mt-1">
              {{ store.errors.category_id[0] }}
            </p>
          </div>

          <!-- Type de document -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Type <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.type_id"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
            >
              <option value="" disabled>— Choisir —</option>
              <option v-for="type in store.types" :key="type.id" :value="type.id">
                {{ type.name }}
              </option>
            </select>
            <p v-if="store.errors.type_id" class="text-red-500 text-xs mt-1">
              {{ store.errors.type_id[0] }}
            </p>
          </div>
        </div>

        <!-- Éditeur + Année sur la même ligne -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Éditeur / Institution</label>
            <input
              v-model="form.publisher"
              type="text"
              placeholder="Ex: Université d'Abomey-Calavi"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Année de publication</label>
            <input
              v-model="form.publication_year"
              type="number"
              placeholder="Ex: 2024"
              min="1900"
              :max="new Date().getFullYear()"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
            />
            <p v-if="store.errors.publication_year" class="text-red-500 text-xs mt-1">
              {{ store.errors.publication_year[0] }}
            </p>
          </div>
        </div>

        <!-- Langue + ISBN -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Langue</label>
            <select
              v-model="form.language"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
            >
              <option value="fr">Français</option>
              <option value="en">Anglais</option>
              <option value="ar">Arabe</option>
              <option value="es">Espagnol</option>
              <option value="pt">Portugais</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ISBN <span class="text-gray-400 font-normal">(optionnel)</span></label>
            <input
              v-model="form.isbn"
              type="text"
              placeholder="Ex: 978-3-16-148410-0"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C]"
            />
          </div>
        </div>

        <!-- Résumé -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Résumé <span class="text-gray-400 font-normal">(optionnel)</span>
          </label>
          <textarea
            v-model="form.abstract"
            rows="4"
            placeholder="Brève description du contenu du document..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0C447C] resize-none"
          ></textarea>
          <!-- Compteur de caractères -->
          <p class="text-xs text-gray-400 text-right mt-1">{{ form.abstract.length }} / 3000</p>
        </div>

        <!-- Bouton suivant -->
        <div class="pt-2">
          <button
            @click="nextStep"
            class="w-full bg-[#042C53] text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-[#0C447C] transition"
          >
            Suivant → Ajouter les fichiers
          </button>
        </div>

      </div>
    </div>

    <!-- ── ÉTAPE 2 : Fichiers ─────────────────────────────────────────── -->
    <div v-if="currentStep === 2" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <h2 class="text-lg font-semibold text-[#042C53] mb-5">Fichiers</h2>

      <div class="space-y-6">

        <!-- Fichier PDF -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Fichier PDF <span class="text-red-500">*</span>
            <span class="text-gray-400 font-normal ml-1">(optionnel — PDF, 20 Mo max)</span>
          </label>

          <!-- Zone de drop stylisée -->
          <label
            class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer transition"
            :class="pdfFile ? 'border-[#042C53] bg-blue-50' : 'border-gray-300 hover:border-[#042C53] hover:bg-gray-50'"
          >
            <div v-if="!pdfFile" class="text-center">
              <p class="text-2xl mb-1">📄</p>
              <p class="text-sm text-gray-500">Cliquez ou glissez votre fichier PDF ici</p>
            </div>
            <div v-else class="text-center">
              <p class="text-2xl mb-1">✅</p>
              <p class="text-sm font-medium text-[#042C53]">{{ pdfFile.name }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ (pdfFile.size / 1024 / 1024).toFixed(2) }} Mo</p>
            </div>
            <input type="file" accept=".pdf" class="hidden" @change="onPdfChange" />
          </label>

          <p v-if="store.errors.file" class="text-red-500 text-xs mt-1">
            {{ store.errors.file[0] }}
          </p>
        </div>

        <!-- Image de couverture -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Image de couverture
            <span class="text-gray-400 font-normal ml-1">(optionnel — JPG, PNG, 2 Mo max)</span>
          </label>

          <div class="flex gap-4 items-start">
            <!-- Zone d'upload -->
            <label
              class="flex flex-col items-center justify-center w-32 h-40 border-2 border-dashed rounded-lg cursor-pointer transition flex-shrink-0"
              :class="coverPreview ? 'border-[#042C53]' : 'border-gray-300 hover:border-[#042C53]'"
            >
              <!-- Aperçu si image sélectionnée -->
              <img v-if="coverPreview" :src="coverPreview" class="w-full h-full object-cover rounded-lg" />
              <div v-else class="text-center p-2">
                <p class="text-2xl mb-1">🖼️</p>
                <p class="text-xs text-gray-400">Ajouter une couverture</p>
              </div>
              <input type="file" accept="image/*" class="hidden" @change="onCoverChange" />
            </label>

            <!-- Info -->
            <div class="text-sm text-gray-500 pt-2">
              <p class="font-medium text-gray-700 mb-1">Recommandations :</p>
              <ul class="list-disc list-inside space-y-1 text-xs">
                <li>Format portrait (ratio 3:4)</li>
                <li>Résolution minimale : 300 × 400 px</li>
                <li>Formats acceptés : JPG, PNG, WebP</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Récapitulatif de l'étape 1 -->
        <div class="bg-gray-50 rounded-lg p-4 text-sm">
          <p class="font-medium text-gray-700 mb-2">Récapitulatif</p>
          <div class="space-y-1 text-gray-600">
            <p><span class="font-medium">Titre :</span> {{ form.title }}</p>
            <p><span class="font-medium">Auteur :</span> {{ form.author }}</p>
            <p><span class="font-medium">Catégorie :</span> {{ store.categories.find(c => c.id == form.category_id)?.name }}</p>
            <p><span class="font-medium">Type :</span> {{ store.types.find(t => t.id == form.type_id)?.name }}</p>
          </div>
        </div>

        <!-- Boutons -->
        <div class="flex gap-3 pt-2">
          <button
            @click="currentStep = 1"
            class="flex-1 border border-gray-300 text-gray-600 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition"
          >
            ← Retour
          </button>
          <button
            @click="handleSubmit"
            :disabled="store.loading"
            class="flex-1 bg-[#042C53] text-white py-2.5 rounded-lg text-sm font-semibold hover:bg-[#0C447C] transition disabled:opacity-50"
          >
            {{ store.loading ? 'Envoi en cours...' : '📤 Soumettre la demande' }}
          </button>
        </div>

      </div>
    </div>

  </div>
</template>