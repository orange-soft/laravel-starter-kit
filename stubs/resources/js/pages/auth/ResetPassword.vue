<template>
  <AuthLayout title="Set new password" subtitle="Enter your new password below.">
    <Head title="Reset Password" />

    <form class="space-y-6" @submit.prevent="submit">
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
          Confirm password
        </label>
        <Password id="password_confirmation" v-model="form.password_confirmation"
          name="password_confirmation" :feedback="false" fluid toggleMask
          autocomplete="new-password" />
      </div>

      <Button type="submit" label="Reset password" :loading="form.processing" fluid />
    </form>
  </AuthLayout>
</template>

<script setup>
import FormError from '@/components/FormError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import {store as resetPassword} from '@/actions/App/Http/Controllers/Auth/ResetPasswordController';
import {Head, useForm} from '@inertiajs/vue3';
import {Button, Password} from 'primevue';

const props = defineProps({
  email: {
    type: String,
    required: true,
  },
  token: {
    type: String,
    required: true,
  },
});

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
}).withPrecognition('post', resetPassword.url());

function submit() {
  form.submit();
}
</script>
