<template>
  <Head :title="title" />
  <div class="min-h-screen flex flex-col bg-surface-50">
    <div class="flex-1 flex flex-col items-center justify-center px-4 py-8">
      <div class="w-full" :class="maxWidthClass">
        <div class="text-center mb-8">
          <h1 class="font-heading-3 text-surface-900">{{ title }}</h1>
          <p v-if="subtitle" class="font-body-default text-surface-600 mt-2">{{ subtitle }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-surface-200 p-8">
          <slot />
        </div>

        <div v-if="$slots.footer" class="mt-6 text-center">
          <slot name="footer" />
        </div>
      </div>
    </div>

    <footer class="py-4 text-center">
      <p class="font-caption text-surface-400">&copy; {{ currentYear }} Orangesoft</p>
    </footer>
  </div>
</template>

<script setup>
import {Head} from '@inertiajs/vue3';
import {computed} from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: 'Welcome',
  },
  subtitle: {
    type: String,
    default: null,
  },
  maxWidth: {
    type: String,
    default: 'md',
  },
});

const currentYear = new Date().getFullYear();

const maxWidthClass = computed(() => {
  return {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    '3xl': 'max-w-3xl',
    '4xl': 'max-w-4xl',
    '5xl': 'max-w-5xl',
    '6xl': 'max-w-6xl',
    full: 'max-w-full',
  }[props.maxWidth];
});
</script>
