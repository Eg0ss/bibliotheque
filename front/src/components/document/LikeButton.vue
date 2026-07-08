<script setup>
import { ref } from 'vue'
import { useReferenceStore } from '@/stores/referenceStore'

const props = defineProps({
  referenceId: { type: [String, Number], required: true },
  isLiked: { type: Boolean, default: false },
  likesCount: { type: Number, default: 0 },
})

const referenceStore = useReferenceStore()
const isProcessing = ref(false)

async function handleLike() {
  if (isProcessing.value) return
  isProcessing.value = true
  try {
    await referenceStore.likeReference(props.referenceId)
  } finally {
    isProcessing.value = false
  }
}
</script>

<template>
  <button
    type="button"
    :disabled="isProcessing"
    @click="handleLike"
    class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-50"
    :class="isLiked
      ? 'border-rose-200 bg-rose-50 text-rose-600'
      : 'border-slate-200 bg-white text-slate-600 hover:border-rose-200 hover:text-rose-600'"
  >
    <!-- Coeur plein si liké, vide sinon -->
    <svg v-if="isLiked" class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
      <path d="M12 21s-7.5-4.6-10-9.1C.6 8.3 2.3 5 5.6 5c1.9 0 3.4 1 4.4 2.5C11 6 12.5 5 14.4 5 17.7 5 19.4 8.3 22 11.9 19.5 16.4 12 21 12 21Z" />
    </svg>
    <svg v-else class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
    </svg>
    <span>{{ likesCount }}</span>
  </button>
</template>