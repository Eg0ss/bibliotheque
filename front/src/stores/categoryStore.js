// src/stores/categoryStore.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import categoryApi from '../api/categoryApi'
import router from '../router'
import { useToast } from 'primevue/usetoast'

export const useCategoryStore = defineStore('category', () => {

  const categories = ref([])
  const pagination = ref(null)
  const loading    = ref(false)
  const errors     = ref({})

  const toast = useToast()

  // Charger la liste paginée
  async function fetchCategories(page = 1) {
    loading.value = true
    try {
      const response     = await categoryApi.getAll(page)
      categories.value   = response.data.data
      pagination.value   = response.data.meta
    } catch {
      toast.add({
        severity: 'error',
        summary : 'Erreur',
        detail  : 'Impossible de charger les catégories.',
        life    : 4000,
      })
    } finally {
      loading.value = false
    }
  }

  // Créer une catégorie
  async function createCategory(formData) {
    loading.value = true
    errors.value  = {}
    try {
      await categoryApi.create(formData)

      toast.add({
        severity: 'success',
        summary : 'Catégorie créée',
        detail  : 'La catégorie a été créée avec succès.',
        life    : 4000,
      })

      router.push('/admin/categories')

    } catch (error) {
      if (error.response?.status === 422) {
        errors.value = error.response.data.errors
        toast.add({
          severity: 'warn',
          summary : 'Formulaire invalide',
          detail  : 'Veuillez corriger les erreurs.',
          life    : 4000,
        })
      } else {
        toast.add({
          severity: 'error',
          summary : 'Erreur serveur',
          detail  : 'Une erreur est survenue.',
          life    : 5000,
        })
      }
    } finally {
      loading.value = false
    }
  }

  return {
    categories, pagination, loading, errors,
    fetchCategories, createCategory,
  }
})