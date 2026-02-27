<template>
  <AppLayout title="Il mio profilo">
    <Head title="Profilo" />

    <div class="max-w-2xl mx-auto space-y-6">
      <!-- Profile info -->
      <div class="card p-6">
        <h3 class="font-semibold text-gray-900 mb-5">Informazioni Profilo</h3>
        <div class="flex items-center gap-5 mb-6">
          <img :src="user.avatar_url" :alt="user.name" class="w-16 h-16 rounded-2xl object-cover" />
          <div>
            <p class="text-lg font-bold text-gray-900">{{ user.name }}</p>
            <p class="text-sm text-gray-500">{{ user.email }}</p>
            <span :class="['badge text-xs mt-1', roleClass]">{{ roleLabel }}</span>
          </div>
        </div>
        <form @submit.prevent="updateProfile" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="form-group col-span-2">
              <label class="label">Nome</label>
              <input v-model="profileForm.name" type="text" class="input" />
            </div>
            <div class="form-group">
              <label class="label">Telefono</label>
              <input v-model="profileForm.phone" type="tel" class="input" />
            </div>
            <div class="form-group">
              <label class="label">Posizione</label>
              <input v-model="profileForm.position" type="text" class="input" />
            </div>
          </div>
          <div class="flex justify-end">
            <button type="submit" :disabled="profileForm.processing" class="btn-primary">
              {{ profileForm.processing ? 'Salvataggio...' : 'Aggiorna Profilo' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Change password -->
      <div class="card p-6">
        <h3 class="font-semibold text-gray-900 mb-5">Cambia Password</h3>
        <form @submit.prevent="updatePassword" class="space-y-4">
          <div class="form-group">
            <label class="label">Password attuale</label>
            <input v-model="passwordForm.current_password" type="password" class="input" :class="{ 'border-red-300': passwordForm.errors.current_password }" />
            <p v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.current_password }}</p>
          </div>
          <div class="form-group">
            <label class="label">Nuova password</label>
            <input v-model="passwordForm.password" type="password" class="input" :class="{ 'border-red-300': passwordForm.errors.password }" />
            <p v-if="passwordForm.errors.password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.password }}</p>
          </div>
          <div class="form-group">
            <label class="label">Conferma password</label>
            <input v-model="passwordForm.password_confirmation" type="password" class="input" />
          </div>
          <div class="flex justify-end">
            <button type="submit" :disabled="passwordForm.processing" class="btn-primary">
              {{ passwordForm.processing ? 'Aggiornamento...' : 'Cambia Password' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Outlook integration -->
      <div class="card p-6">
        <h3 class="font-semibold text-gray-900 mb-3">Integrazione Microsoft Outlook</h3>
        <div v-if="user.is_microsoft_connected" class="flex items-center gap-4">
          <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900">Outlook connesso</p>
            <p class="text-sm text-gray-500">La sincronizzazione email è attiva</p>
          </div>
          <form method="POST" :action="route('outlook.disconnect')">
            <input type="hidden" name="_token" :value="csrfToken" />
            <button type="submit" class="btn-danger text-xs">Disconnetti</button>
          </form>
        </div>
        <div v-else>
          <p class="text-sm text-gray-500 mb-4">Connetti il tuo account Microsoft per sincronizzare automaticamente le email con i tuoi clienti nel CRM.</p>
          <div class="bg-blue-50 rounded-xl p-4 mb-4">
            <h4 class="text-sm font-semibold text-blue-900 mb-2">Come funziona:</h4>
            <ul class="space-y-1 text-sm text-blue-700">
              <li>✅ Le email in arrivo vengono associate automaticamente ai clienti</li>
              <li>✅ Ogni email appare nella timeline del cliente</li>
              <li>✅ Puoi rispondere direttamente dal CRM</li>
            </ul>
          </div>
          <a :href="route('outlook.connect')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            Connetti con Microsoft
          </a>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({ user: Object });
const page = usePage();
const csrfToken = computed(() => document.querySelector('meta[name="csrf-token"]')?.content || '');

const profileForm = useForm({
  name: props.user.name,
  phone: props.user.phone || '',
  position: props.user.position || '',
});

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

function updateProfile() {
  profileForm.put(route('users.update', props.user.id));
}

function updatePassword() {
  passwordForm.put(route('profile.password'), {
    onSuccess: () => passwordForm.reset(),
  });
}

const roleLabels = { admin: 'Amministratore', manager: 'Manager', agent: 'Agente' };
const roleClasses = { admin: 'badge-red', manager: 'badge-purple', agent: 'badge-blue' };
const roleLabel = computed(() => roleLabels[props.user.roles?.[0]] || 'Utente');
const roleClass = computed(() => roleClasses[props.user.roles?.[0]] || 'badge-gray');
</script>
