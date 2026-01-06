<template>
  <AuthLayout title="Verify your email"
    subtitle="Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just emailed to you.">
    <Head title="Verify Email" />

    <div class="space-y-6">
      <p class="font-body-default text-surface-600">
        If you didn't receive the email, we will gladly send you another.
      </p>

      <form @submit.prevent="submit">
        <Button type="submit" label="Resend verification email" :loading="form.processing" fluid />
      </form>
    </div>

    <template #footer>
      <button type="button" class="font-body-small text-surface-500 hover:text-surface-700"
        @click="logout">
        Log out
      </button>
    </template>
  </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/layouts/AuthLayout.vue';
import {resend} from '@/actions/App/Http/Controllers/Auth/VerifyEmailController';
import {Head, router, useForm} from '@inertiajs/vue3';
import {Button} from 'primevue';

const form = useForm({});

function submit() {
  form.post(resend.url());
}

function logout() {
  router.post('/logout');
}
</script>
