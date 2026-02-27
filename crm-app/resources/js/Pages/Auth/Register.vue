<template>
  <Head title="Registrazione" />
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-indigo-50 p-4">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
      <div class="text-center mb-8">
        <div class="w-14 h-14 bg-indigo-600 rounded-xl flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Crea il tuo account</h1>
        <p class="text-gray-500 mt-1">Inizia a gestire i tuoi clienti oggi</p>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="form-group sm:col-span-2">
            <label class="label">Nome completo *</label>
            <input v-model="form.name" type="text" placeholder="Mario Rossi" class="input" :class="{ 'border-red-300': form.errors.name }" />
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
          </div>

          <div class="form-group sm:col-span-2">
            <label class="label">Email *</label>
            <input v-model="form.email" type="email" placeholder="mario@azienda.it" class="input" :class="{ 'border-red-300': form.errors.email }" />
            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
          </div>

          <div class="form-group">
            <label class="label">Telefono</label>
            <input v-model="form.phone" type="tel" placeholder="+39 02 1234567" class="input" />
          </div>

          <div class="form-group">
            <label class="label">Ruolo aziendale</label>
            <input v-model="form.position" type="text" placeholder="Sales Manager" class="input" />
          </div>

          <div class="form-group">
            <label class="label">Password *</label>
            <input v-model="form.password" type="password" placeholder="••••••••" class="input" :class="{ 'border-red-300': form.errors.password }" />
            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
          </div>

          <div class="form-group">
            <label class="label">Conferma password *</label>
            <input v-model="form.password_confirmation" type="password" placeholder="••••••••" class="input" />
          </div>
        </div>

        <button type="submit" :disabled="form.processing" class="btn-primary w-full justify-center py-2.5 mt-2">
          <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          {{ form.processing ? 'Registrazione...' : 'Crea Account' }}
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-gray-500">
        Hai già un account?
        <Link :href="route('login')" class="text-indigo-600 hover:text-indigo-700 font-medium">Accedi</Link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  email: '',
  phone: '',
  position: '',
  password: '',
  password_confirmation: '',
});

function submit() {
  form.post(route('register.post'));
}
</script>
