import { defineStore } from 'pinia'
import { ref } from 'vue'
import typeApi from '../api/typeApi'
import router from '../router'
import { useToast } from 'primevue/usetoast'

export const useTypeStore = defineStore('type', () => {

  const types      = ref([])
  const pagination = ref(null)
  const loading    = ref(false)
  const errors     = ref({})

  const toast = useToast()

  async function fetchTypes(page = 1) {
    loading.value = true
    try {
      const res    = await typeApi.getAllAdmin(page)
      types.value  = res.data.data
      pagination.value = res.data.meta
    } catch {
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les types.', life: 4000 })
    } finally {
      loading.value = false
    }
  }

  async function createType(formData) {
    loading.value = true
    errors.value  = {}
    try {
      await typeApi.create(formData)
      toast.add({ severity: 'success', summary: 'Type créé', detail: 'Le type a été créé avec succès.', life: 4000 })
      router.push('/admin/types')
    } catch (error) {
      if (error.response?.status === 422) {
        errors.value = error.response.data.errors
        toast.add({ severity: 'warn', summary: 'Formulaire invalide', detail: 'Veuillez corriger les erreurs.', life: 4000 })
      } else {
        toast.add({ severity: 'error', summary: 'Erreur serveur', detail: 'Une erreur est survenue.', life: 5000 })
      }
    } finally {
      loading.value = false
    }
  }

  return { types, pagination, loading, errors, fetchTypes, createType }
})