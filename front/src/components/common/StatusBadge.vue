<script setup>
const props = defineProps({
  tone: {
    type: String,
    default: 'neutral'
  }
})

const styles = {
  success: 'bg-green-50 text-green-700',
  warning: 'bg-amber-50 text-amber-700',
  danger: 'bg-red-50 text-red-700',
  info: 'bg-blue-50 text-blue-700',
  neutral: 'bg-slate-100 text-slate-700',
  gold: 'bg-amber-50 text-amber-700'
}
</script>

<template>
  <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" :class="styles[tone]">
    <slot />
  </span>
</template>
