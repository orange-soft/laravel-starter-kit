import './bootstrap';
import '../css/app.css';
import {createInertiaApp} from '@inertiajs/vue3';
import {definePreset} from '@primeuix/themes';
import Aura from '@primeuix/themes/aura';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import PrimeVue from 'primevue/config';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';
import {createApp, h} from 'vue';

const pages = import.meta.glob('./pages/**/*.vue');

createInertiaApp({
  title: (title) => {
    const titles = title?.split('|').map((part) => part.trim());
    titles.push(import.meta.env.VITE_APP_NAME || 'Laravel');
    return titles.filter((t) => t.length).join(' | ');
  },
  resolve: (name) => {
    return resolvePageComponent(`./pages/${name}.vue`, pages);
  },
  setup({el, App, props, plugin}) {
    createApp({render: () => h(App, props)})
      .use(plugin)
      .use(PrimeVue, {
        ripple: true,
        theme: {
          preset: definePreset(Aura, {
            semantic: {
              colorScheme: {
                light: {
                  surface: {
                    0: '#ffffff',
                    50: '{slate.50}',
                    100: '{slate.100}',
                    200: '{slate.200}',
                    300: '{slate.300}',
                    400: '{slate.400}',
                    500: '{slate.500}',
                    600: '{slate.600}',
                    700: '{slate.700}',
                    800: '{slate.800}',
                    900: '{slate.900}',
                    950: '{slate.950}'
                  }
                },
              },
              primary: {
                '50': '#fef5ee',
                '100': '#fce9d8',
                '200': '#f8cfb0',
                '300': '#f3ad7e',
                '400': '#ed814a',
                '500': '#e85b1f',
                '600': '#da481c',
                '700': '#b53519',
                '800': '#902c1c',
                '900': '#74271a',
                '950': '#3f100b',
              },
            },
          }),
          options: {
            cssLayer: {
              name: 'primevue',
              order: 'theme, base, primevue',
            },
            darkModeSelector: false,
          },
        },
      })
      .use(ToastService)
      .use(ConfirmationService)
      .mount(el);
  },
});
