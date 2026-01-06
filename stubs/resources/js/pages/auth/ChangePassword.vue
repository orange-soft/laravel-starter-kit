<template>
  <AuthLayout title="Change your password"
    subtitle="You must change your password before continuing.">
    <Head title="Change Password" />

    <form class="space-y-6" @submit.prevent="submit">
      <div>
        <label for="current_password"
          class="block font-body-small font-medium text-surface-700 mb-1">
          Current password
        </label>
        <Password id="current_password" v-model="form.current_password" name="current_password"
          :feedback="false" fluid toggleMask :invalid="!!form.errors.current_password"
          autocomplete="current-password" @blur="form.validate('current_password')" />
        <FormError :message="form.errors.current_password" />
      </div>

      <div>
        <label for="password" class="block font-body-small font-medium text-surface-700 mb-1">
          New password
        </label>
        <Password id="password" v-model="form.password" name="password" :feedback="true"
          fluid toggleMask :invalid="!!form.errors.password" autocomplete="new-password"
          @blur="form.validate('password')" />
        <FormError :message="form.errors.password" />
      </div>

      <div>
        <label for="password_confirmation"
          class="block font-body-small font-medium text-surface-700 mb-1">
          Confirm new password
        </label>
        <Password id="password_confirmation" v-model="form.password_confirmation"
          name="password_confirmation" :feedback="false" fluid toggleMask
          autocomplete="new-password" />
      </div>

      <Button type="submit" label="Change password" :loading="form.processing" fluid />
    </form>

    <template #footer>
      <button type="button" class="font-body-small text-surface-500 hover:text-surface-700"
        @click="logout">
        Log out
      </button>
    </template>
  </AuthLayout>
</template>

<script setup>
import FormError from '@/components/FormError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import {store as changePassword} from '@/actions/App/Http/Controllers/Auth/ChangePasswordController';
import {Head, router, useForm} from '@inertiajs/vue3';
import {Button, Password} from 'primevue';

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
}).withPrecognition('post', changePassword.url());

function submit() {
  form.submit();
}

function logout() {
  router.post('/logout');
}
</script>
