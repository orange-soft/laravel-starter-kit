<template>
  <small v-if="message" class="text-red-500 font-body-small">{{ message }}</small>
</template>

<script setup>
defineProps({
  message: {
    type: String,
    default: null,
  },
});
</script>
