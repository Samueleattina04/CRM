<template>
  <Head title="Accedi" />
  <div class="min-h-screen flex">
    <!-- Left panel - decorative -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 relative overflow-hidden flex-col items-center justify-center p-12">
      <!-- Decorative circles -->
      <div class="absolute top-20 left-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>
      <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl"></div>

      <div class="relative z-10 text-center">
        <div class="w-20 h-20 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-8 ring-1 ring-white/20">
          <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <h1 class="text-4xl font-bold text-white mb-4">CRM Pro</h1>
        <p class="text-indigo-200 text-lg leading-relaxed max-w-sm mx-auto">
          Gestisci i tuoi clienti, traccia ogni interazione e aumenta le vendite.
        </p>

        <div class="mt-12 grid grid-cols-3 gap-4 text-center">
          <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
            <div class="text-2xl font-bold text-white">∞</div>
            <div class="text-xs text-indigo-300 mt-1">Clienti</div>
          </div>
          <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
            <div class="text-2xl font-bold text-white">📧</div>
            <div class="text-xs text-indigo-300 mt-1">Outlook Sync</div>
          </div>
          <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
            <div class="text-2xl font-bold text-white">📊</div>
            <div class="text-xs text-indigo-300 mt-1">Analytics</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right panel - form -->
    <div class="flex-1 flex items-center justify-center p-8 bg-white">
      <div class="w-full max-w-md">
        <!-- Mobile logo -->
        <div class="lg:hidden text-center mb-8">
          <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900">CRM Pro</h2>
        </div>

        <div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Bentornato!</h2>
          <p class="text-gray-500 mb-8">Inserisci le tue credenziali per accedere.</p>

          <form @submit.prevent="submit" class="space-y-5">
            <div class="form-group">
              <label class="label">Email</label>
              <input
                v-model="form.email"
                type="email"
                autocomplete="email"
                placeholder="mario.rossi@azienda.it"
                class="input"
                :class="{ 'border-red-300 focus:ring-red-500': form.errors.email }"
              />
              <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
            </div>

            <div class="form-group">
              <label class="label">Password</label>
              <div class="relative">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="current-password"
                  placeholder="••••••••"
                  class="input pr-10"
                  :class="{ 'border-red-300 focus:ring-red-500': form.errors.password }"
                />
                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <svg v-if="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                </button>
              </div>
            </div>

            <div class="flex items-center justify-between">
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <span class="text-sm text-gray-600">Ricordami</span>
              </label>
            </div>

            <button
              type="submit"
              :disabled="form.processing"
              class="btn-primary w-full justify-center py-2.5"
            >
              <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ form.processing ? 'Accesso in corso...' : 'Accedi' }}
            </button>
          </form>

          <p class="mt-6 text-center text-sm text-gray-500">
            Non hai un account?
            <Link :href="route('register')" class="text-indigo-600 hover:text-indigo-700 font-medium">Registrati</Link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const showPassword = ref(false);

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

function submit() {
  form.post(route('login.post'), {
    onFinish: () => form.reset('password'),
  });
}
</script>
