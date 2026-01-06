<template>
  <PrimeToast position="top-right" />
</template>

<script setup>
import {usePage} from '@inertiajs/vue3';
import {Toast as PrimeToast, useToast} from 'primevue';
import {watch} from 'vue';

const page = usePage();
const toast = useToast();

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) {
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: flash.success,
        life: 5000,
      });
    }

    if (flash?.error) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: flash.error,
        life: 5000,
      });
    }
  },
  {immediate: true, deep: true}
);
</script>
