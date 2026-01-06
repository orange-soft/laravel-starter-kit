<template>
  <AuthLayout title="Reset your password"
    subtitle="Enter your email address and we'll send you a link to reset your password.">
    <Head title="Forgot Password" />

    <Message v-if="page.props.flash.success" severity="success" :closable="false" class="mb-6">
      {{ page.props.flash.success }}
    </Message>

    <form class="space-y-6" @submit.prevent="submit">
      <div>
        <label for="email" class="block font-body-small font-medium text-surface-700 mb-1">
          Email address
        </label>
        <InputText id="email" v-model="form.email" type="email" name="email" fluid
          :disabled="!!page.props.flash.success" :invalid="!!form.errors.email"
          autocomplete="email" @blur="form.validate('email')" />
        <FormError :message="form.errors.email" />
      </div>

      <Button v-if="!page.props.flash.success" type="submit" label="Send reset link"
        :loading="form.processing" fluid />
    </form>

    <template #footer>
      <Link :href="login.url()" class="font-body-small text-primary hover:underline">
        Back to login
      </Link>
    </template>
  </AuthLayout>
</template>

<script setup>
import FormError from '@/components/FormError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import {store as forgotPassword} from '@/actions/App/Http/Controllers/Auth/ForgotPasswordController';
import {create as login} from '@/actions/App/Http/Controllers/Auth/LoginController';
import {Head, Link, useForm, usePage} from '@inertiajs/vue3';
import {Button, InputText, Message} from 'primevue';

const page = usePage();

const form = useForm({
  email: '',
}).withPrecognition('post', forgotPassword.url());

function submit() {
  form.submit();
}
</script>
