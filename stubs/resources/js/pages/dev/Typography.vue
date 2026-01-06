<template>
  <AuthLayout title="Typography & Components" subtitle="Preview of typography, colors, and PrimeVue components"
    maxWidth="5xl">
    <div class="space-y-12">
      <!-- Headings Section -->
      <section>
        <h2 class="font-heading-2 mb-6 pb-2 border-b border-surface-200">Headings (Manrope)</h2>
        <div class="space-y-4">
          <div>
            <h1 class="font-heading-1">Heading 1 - 42px desktop</h1>
            <code class="font-caption text-surface-500">font-heading-1 (3rem desktop, 2rem mobile)</code>
          </div>
          <div>
            <h2 class="font-heading-2">Heading 2 - 35px desktop</h2>
            <code class="font-caption text-surface-500">font-heading-2 (2.5rem desktop, 1.75rem mobile)</code>
          </div>
          <div>
            <h3 class="font-heading-3">Heading 3 - 30px desktop</h3>
            <code class="font-caption text-surface-500">font-heading-3 (2.125rem desktop, 1.5rem mobile)</code>
          </div>
          <div>
            <h4 class="font-heading-4">Heading 4 - 24px desktop</h4>
            <code class="font-caption text-surface-500">font-heading-4 (1.75rem desktop, 1.25rem mobile)</code>
          </div>
          <div>
            <h5 class="font-heading-5">Heading 5 - 21px desktop</h5>
            <code class="font-caption text-surface-500">font-heading-5 (1.5rem desktop, 1.125rem mobile)</code>
          </div>
        </div>
      </section>

      <!-- Body Text Section -->
      <section>
        <h2 class="font-heading-2 mb-6 pb-2 border-b border-surface-200">Body Text (Inter)</h2>
        <div class="space-y-4">
          <div>
            <p class="font-body-large">Body Large - The quick brown fox jumps over the lazy dog</p>
            <code class="font-caption text-surface-500">font-body-large (1.25rem = 18px desktop)</code>
          </div>
          <div>
            <p class="font-body-default">Body Default - The quick brown fox jumps over the lazy dog</p>
            <code class="font-caption text-surface-500">font-body-default (1rem = 14px base)</code>
          </div>
          <div>
            <p class="font-body-small">Body Small - The quick brown fox jumps over the lazy dog</p>
            <code class="font-caption text-surface-500">font-body-small (0.875rem = 12px)</code>
          </div>
          <div>
            <p class="font-caption">Caption - The quick brown fox jumps over the lazy dog</p>
            <code class="font-caption text-surface-500">font-caption (0.75rem = 10px)</code>
          </div>
        </div>
      </section>

      <!-- Lists Section -->
      <section>
        <h2 class="font-heading-2 mb-6 pb-2 border-b border-surface-200">Lists</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div>
            <h3 class="font-heading-5 mb-4">Unordered List</h3>
            <ul>
              <li>First item in the list</li>
              <li>Second item with more content
                <ul>
                  <li>Nested item one</li>
                  <li>Nested item two</li>
                </ul>
              </li>
              <li>Third item in the list</li>
            </ul>
            <code class="font-caption text-surface-500 mt-2 block">&lt;ul&gt; with &lt;li&gt;</code>
          </div>
          <div>
            <h3 class="font-heading-5 mb-4">Ordered List</h3>
            <ol>
              <li>First step in the process</li>
              <li>Second step with details
                <ol>
                  <li>Sub-step one</li>
                  <li>Sub-step two</li>
                </ol>
              </li>
              <li>Third step to complete</li>
            </ol>
            <code class="font-caption text-surface-500 mt-2 block">&lt;ol&gt; with &lt;li&gt;</code>
          </div>
        </div>
      </section>

      <!-- Color Palette Section -->
      <section>
        <h2 class="font-heading-2 mb-6 pb-2 border-b border-surface-200">Color Palette</h2>

        <h3 class="font-heading-5 mb-4">Primary Colors</h3>
        <div class="grid grid-cols-5 sm:grid-cols-11 gap-2 mb-8">
          <div v-for="color in primaryColors" :key="color.shade" class="text-center">
            <div class="h-12 rounded-lg mb-1" :class="color.class"></div>
            <span class="font-caption text-surface-600">{{ color.shade }}</span>
          </div>
        </div>

        <h3 class="font-heading-5 mb-4">Surface Colors</h3>
        <div class="grid grid-cols-5 sm:grid-cols-11 gap-2">
          <div v-for="color in surfaceColors" :key="color.shade" class="text-center">
            <div class="h-12 rounded-lg mb-1 border border-surface-200" :class="color.class"></div>
            <span class="font-caption text-surface-600">{{ color.shade }}</span>
          </div>
        </div>
      </section>

      <!-- Form Inputs Section -->
      <section>
        <h2 class="font-heading-2 mb-6 pb-2 border-b border-surface-200">Form Inputs</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label class="font-body-small font-medium text-surface-700">Text Input</label>
            <InputText v-model="formDemo.text" placeholder="Enter text..." fluid />
          </div>
          <div class="space-y-2">
            <label class="font-body-small font-medium text-surface-700">Password</label>
            <Password v-model="formDemo.password" placeholder="Enter password..." fluid toggleMask
              :feedback="false" />
          </div>
          <div class="space-y-2">
            <label class="font-body-small font-medium text-surface-700">Select</label>
            <Select v-model="formDemo.select" :options="selectOptions" optionLabel="label"
              optionValue="value" placeholder="Select an option..." fluid />
          </div>
          <div class="space-y-2">
            <label class="font-body-small font-medium text-surface-700">Textarea</label>
            <Textarea v-model="formDemo.textarea" placeholder="Enter description..." rows="3" fluid />
          </div>
          <div class="space-y-2">
            <label class="font-body-small font-medium text-surface-700">Invalid State</label>
            <InputText v-model="formDemo.invalid" placeholder="Invalid input..." fluid invalid />
            <small class="text-red-500 font-body-small">This field has an error</small>
          </div>
          <div class="space-y-4">
            <label class="font-body-small font-medium text-surface-700">Checkbox & Toggle</label>
            <div class="flex items-center gap-3">
              <Checkbox v-model="formDemo.checkbox" binary inputId="demo-checkbox" />
              <label for="demo-checkbox" class="font-body-default">Checkbox option</label>
            </div>
            <div class="flex items-center gap-3">
              <ToggleSwitch v-model="formDemo.toggle" inputId="demo-toggle" />
              <label for="demo-toggle" class="font-body-default">Toggle switch</label>
            </div>
          </div>
        </div>
      </section>

      <!-- Buttons Section -->
      <section>
        <h2 class="font-heading-2 mb-6 pb-2 border-b border-surface-200">Buttons</h2>

        <h3 class="font-heading-5 mb-4">Severities</h3>
        <div class="flex flex-wrap gap-3 mb-8">
          <Button label="Primary" />
          <Button label="Secondary" severity="secondary" />
          <Button label="Success" severity="success" />
          <Button label="Info" severity="info" />
          <Button label="Warn" severity="warn" />
          <Button label="Danger" severity="danger" />
          <Button label="Contrast" severity="contrast" />
        </div>

        <h3 class="font-heading-5 mb-4">Variants</h3>
        <div class="flex flex-wrap gap-3 mb-8">
          <Button label="Solid" />
          <Button label="Outlined" outlined />
          <Button label="Text" text />
          <Button label="Link" link />
        </div>

        <h3 class="font-heading-5 mb-4">Sizes</h3>
        <div class="flex flex-wrap gap-3 items-center mb-8">
          <Button label="Small" size="small" />
          <Button label="Default" />
          <Button label="Large" size="large" />
        </div>

        <h3 class="font-heading-5 mb-4">With Icons</h3>
        <div class="flex flex-wrap gap-3">
          <Button label="Save" icon="pi pi-check" />
          <Button label="Cancel" icon="pi pi-times" severity="secondary" />
          <Button icon="pi pi-search" aria-label="Search" />
          <Button icon="pi pi-cog" rounded outlined aria-label="Settings" />
        </div>
      </section>

      <!-- Data Table Section -->
      <section>
        <h2 class="font-heading-2 mb-6 pb-2 border-b border-surface-200">Data Table</h2>
        <DataTable :value="tableData" stripedRows>
          <Column field="name" header="Name" />
          <Column field="email" header="Email" />
          <Column field="role" header="Role">
            <template #body="{data}">
              <Tag :value="data.role" :severity="data.role === 'Admin' ? 'success' : 'info'" />
            </template>
          </Column>
          <Column field="status" header="Status">
            <template #body="{data}">
              <Tag :value="data.status" :severity="data.status === 'Active' ? 'success' : 'warn'" />
            </template>
          </Column>
        </DataTable>
      </section>

      <!-- Messages Section -->
      <section>
        <h2 class="font-heading-2 mb-6 pb-2 border-b border-surface-200">Messages</h2>
        <div class="space-y-3">
          <Message severity="success">Success message - Operation completed successfully</Message>
          <Message severity="info">Info message - Here is some useful information</Message>
          <Message severity="warn">Warning message - Please review before proceeding</Message>
          <Message severity="error">Error message - Something went wrong</Message>
        </div>
      </section>
    </div>
  </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/layouts/AuthLayout.vue';
import {Button, Checkbox, Column, DataTable, InputText, Message, Password, Select, Tag, Textarea, ToggleSwitch} from 'primevue';
import {ref} from 'vue';

const primaryColors = [
  {shade: '50', class: 'bg-primary-50'},
  {shade: '100', class: 'bg-primary-100'},
  {shade: '200', class: 'bg-primary-200'},
  {shade: '300', class: 'bg-primary-300'},
  {shade: '400', class: 'bg-primary-400'},
  {shade: '500', class: 'bg-primary-500'},
  {shade: '600', class: 'bg-primary-600'},
  {shade: '700', class: 'bg-primary-700'},
  {shade: '800', class: 'bg-primary-800'},
  {shade: '900', class: 'bg-primary-900'},
  {shade: '950', class: 'bg-primary-950'},
];

const surfaceColors = [
  {shade: '50', class: 'bg-surface-50'},
  {shade: '100', class: 'bg-surface-100'},
  {shade: '200', class: 'bg-surface-200'},
  {shade: '300', class: 'bg-surface-300'},
  {shade: '400', class: 'bg-surface-400'},
  {shade: '500', class: 'bg-surface-500'},
  {shade: '600', class: 'bg-surface-600'},
  {shade: '700', class: 'bg-surface-700'},
  {shade: '800', class: 'bg-surface-800'},
  {shade: '900', class: 'bg-surface-900'},
  {shade: '950', class: 'bg-surface-950'},
];

const formDemo = ref({
  text: '',
  password: '',
  select: null,
  textarea: '',
  invalid: '',
  checkbox: false,
  toggle: false,
});

const selectOptions = [
  {label: 'Option 1', value: '1'},
  {label: 'Option 2', value: '2'},
  {label: 'Option 3', value: '3'},
];

const tableData = [
  {name: 'John Doe', email: 'john@example.com', role: 'Admin', status: 'Active'},
  {name: 'Jane Smith', email: 'jane@example.com', role: 'User', status: 'Active'},
  {name: 'Bob Wilson', email: 'bob@example.com', role: 'User', status: 'Inactive'},
];
</script>
