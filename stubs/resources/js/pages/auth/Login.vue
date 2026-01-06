<template>
  <AuthLayout title="Sign in to your account">
    <Head title="Login" />

    <Message v-if="sessionError" severity="warn" :closable="false" class="mb-6">
      {{ sessionError }}
    </Message>

    <form class="space-y-6" @submit.prevent="submit">
      <div>
        <label for="email" class="block font-body-small font-medium text-surface-700 mb-1">
          Email address
        </label>
        <InputText id="email" v-model="form.email" type="email" name="email" fluid
          :invalid="!!form.errors.email" autocomplete="email" />
        <FormError :message="form.errors.email" />
      </div>

      <div>
        <label for="password" class="block font-body-small font-medium text-surface-700 mb-1">
          Password
        </label>
        <Password id="password" v-model="form.password" name="password" :feedback="false"
          fluid toggleMask :invalid="!!form.errors.password" autocomplete="current-password" />
        <FormError :message="form.errors.password" />
      </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Checkbox v-model="form.remember" inputId="remember" name="remember" binary />
          <label for="remember" class="font-body-small text-surface-600">Remember me</label>
        </div>

        <Link :href="forgotPassword.url()" class="font-body-small text-primary hover:underline">
          Forgot password?
        </Link>
      </div>

      <Button type="submit" label="Sign in" :loading="form.processing" fluid />
    </form>
  </AuthLayout>
</template>

<script setup>
import FormError from '@/components/FormError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import {create as forgotPassword} from '@/actions/App/Http/Controllers/Auth/ForgotPasswordController';
import {store as login} from '@/actions/App/Http/Controllers/Auth/LoginController';
import {Head, Link, useForm, usePage} from '@inertiajs/vue3';
import {Button, Checkbox, InputText, Message, Password} from 'primevue';
import {computed} from 'vue';

const page = usePage();
const sessionError = computed(() => {
  // Check for session error from backend (via withErrors)
  if (page.props.errors?.session) {
    return page.props.errors.session;
  }
  // Fallback: check query param
  const url = new URL(page.url, window.location.origin);
  if (url.searchParams.get('session_expired') === '1') {
    return 'Your session has expired. Please sign in again.';
  }
  return null;
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

function submit() {
  form.post(login.url());
}
</script>
