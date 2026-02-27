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

      <!-- Email Integration via IMAP -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-gray-900">Integrazione Email (Outlook / IMAP)</h3>
          <div v-if="$page.props.auth.user.is_imap_connected" class="flex items-center gap-2 text-xs text-green-600 bg-green-50 px-3 py-1 rounded-full">
            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
            Connesso
          </div>
        </div>

        <!-- Già connesso -->
        <div v-if="$page.props.auth.user.is_imap_connected">
          <div class="flex items-center gap-4 p-4 bg-green-50 rounded-xl border border-green-100 mb-4">
            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            </div>
            <div class="flex-1">
              <p class="font-medium text-gray-900">{{ $page.props.auth.user.imap_host }}</p>
              <p class="text-xs text-gray-500">
                Account: {{ user.email }}
                <span v-if="$page.props.auth.user.imap_last_sync_at" class="ml-2">
                  · Ultima sync: {{ formatDate($page.props.auth.user.imap_last_sync_at) }}
                </span>
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <button @click="syncNow" :disabled="syncing" class="btn-primary text-sm">
              <svg class="w-4 h-4" :class="syncing ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
              {{ syncing ? 'Sincronizzando...' : 'Sincronizza ora' }}
            </button>
            <button @click="showDisconnect = true" class="btn-ghost text-sm text-red-500 hover:text-red-700">
              Disconnetti
            </button>
            <button @click="editMode = true" class="btn-ghost text-sm">
              Modifica credenziali
            </button>
          </div>
        </div>

        <!-- Form configurazione IMAP -->
        <div v-if="!$page.props.auth.user.is_imap_connected || editMode">
          <!-- Info box -->
          <div class="bg-blue-50 rounded-xl p-4 mb-5 border border-blue-100">
            <h4 class="text-sm font-semibold text-blue-900 mb-2">📧 Come funziona senza Azure AD</h4>
            <p class="text-sm text-blue-700 mb-2">
              Usa il protocollo IMAP standard. Funziona con qualsiasi account Outlook, Office 365 o altra casella email.
            </p>
            <ul class="space-y-1 text-sm text-blue-700">
              <li>✅ Le email in arrivo vengono associate automaticamente ai clienti (per indirizzo email)</li>
              <li>✅ Ogni email appare nella timeline del cliente</li>
              <li>✅ Non richiede Azure AD / registrazione app</li>
              <li>✅ Funziona con Outlook, Gmail, Yahoo, e qualsiasi server IMAP</li>
            </ul>
          </div>

          <!-- Preset server comuni -->
          <div class="mb-4">
            <label class="label">Server preconfigurati</label>
            <div class="flex flex-wrap gap-2">
              <button type="button" v-for="preset in serverPresets" :key="preset.name"
                @click="applyPreset(preset)"
                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors"
              >
                {{ preset.name }}
              </button>
            </div>
          </div>

          <form @submit.prevent="saveImap" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="form-group col-span-2 sm:col-span-1">
                <label class="label">Server IMAP *</label>
                <input v-model="imapForm.imap_host" type="text" class="input" placeholder="outlook.office365.com" :class="{ 'border-red-300': imapForm.errors.imap_host }" />
                <p v-if="imapForm.errors.imap_host" class="text-red-500 text-xs mt-1">{{ imapForm.errors.imap_host }}</p>
              </div>
              <div class="form-group">
                <label class="label">Porta *</label>
                <input v-model.number="imapForm.imap_port" type="number" class="input" placeholder="993" />
              </div>
              <div class="form-group">
                <label class="label">Cifratura *</label>
                <select v-model="imapForm.imap_encryption" class="input">
                  <option value="ssl">SSL/TLS (porta 993)</option>
                  <option value="tls">STARTTLS (porta 143)</option>
                  <option value="none">Nessuna (porta 143)</option>
                </select>
              </div>
              <div class="form-group col-span-2">
                <label class="label">Email / Username *</label>
                <input v-model="imapForm.imap_username" type="email" class="input" placeholder="mario.rossi@azienda.it" />
              </div>
              <div class="form-group col-span-2">
                <label class="label">Password *</label>
                <input v-model="imapForm.imap_password" type="password" class="input" placeholder="La tua password email" />
                <p class="text-xs text-gray-400 mt-1">
                  💡 Per Office 365 con autenticazione moderna, usa una
                  <strong>App Password</strong> (Impostazioni account → Sicurezza → Password per app)
                </p>
              </div>
            </div>

            <!-- Test + Save buttons -->
            <div class="flex items-center gap-3 pt-2">
              <button
                type="button"
                @click="testConnection"
                :disabled="testing || !imapForm.imap_host"
                class="btn-secondary text-sm"
              >
                <svg class="w-4 h-4" :class="testing ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ testing ? 'Test...' : 'Testa connessione' }}
              </button>

              <div v-if="testResult" :class="['flex items-center gap-2 text-sm px-3 py-1.5 rounded-lg', testResult.success ? 'text-green-700 bg-green-50' : 'text-red-700 bg-red-50']">
                <span>{{ testResult.success ? '✅ Connessione OK!' : '❌ ' + testResult.error }}</span>
              </div>

              <button type="submit" :disabled="imapForm.processing" class="btn-primary text-sm ml-auto">
                {{ imapForm.processing ? 'Salvataggio...' : 'Salva e Connetti' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Disconnect confirm -->
      <Modal :show="showDisconnect" @close="showDisconnect = false" title="Disconnetti account email">
        <div class="p-6">
          <p class="text-sm text-gray-600 mb-5">Sei sicuro di voler disconnettere l'account email? Le email già sincronizzate rimarranno nella timeline dei clienti.</p>
          <div class="flex justify-end gap-3">
            <button @click="showDisconnect = false" class="btn-secondary">Annulla</button>
            <button @click="disconnectImap" class="btn-danger">Disconnetti</button>
          </div>
        </div>
      </Modal>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({ user: Object });
const page = usePage();

const editMode = ref(false);
const showDisconnect = ref(false);
const testing = ref(false);
const syncing = ref(false);
const testResult = ref(null);

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

const imapForm = useForm({
  imap_host: '',
  imap_port: 993,
  imap_encryption: 'ssl',
  imap_username: props.user.email || '',
  imap_password: '',
  imap_protocol: 'imap',
});

// Server IMAP preconfigurati più comuni
const serverPresets = [
  { name: 'Outlook / Office 365', host: 'outlook.office365.com', port: 993, encryption: 'ssl' },
  { name: 'Hotmail / Live', host: 'outlook.live.com', port: 993, encryption: 'ssl' },
  { name: 'Gmail', host: 'imap.gmail.com', port: 993, encryption: 'ssl' },
  { name: 'Aruba Mail', host: 'imaps.aruba.it', port: 993, encryption: 'ssl' },
  { name: 'Libero Mail', host: 'imapmail.libero.it', port: 993, encryption: 'ssl' },
  { name: 'Tin / Alice', host: 'imap.tin.it', port: 993, encryption: 'ssl' },
];

function applyPreset(preset) {
  imapForm.imap_host = preset.host;
  imapForm.imap_port = preset.port;
  imapForm.imap_encryption = preset.encryption;
  testResult.value = null;
}

async function testConnection() {
  testing.value = true;
  testResult.value = null;
  try {
    const response = await fetch(route('imap.test'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        imap_host: imapForm.imap_host,
        imap_port: imapForm.imap_port,
        imap_encryption: imapForm.imap_encryption,
        imap_username: imapForm.imap_username,
        imap_password: imapForm.imap_password,
      }),
    });
    testResult.value = await response.json();
  } catch (e) {
    testResult.value = { success: false, error: e.message };
  } finally {
    testing.value = false;
  }
}

function saveImap() {
  imapForm.post(route('imap.credentials'), {
    onSuccess: () => { editMode.value = false; testResult.value = null; },
  });
}

function syncNow() {
  syncing.value = true;
  router.post(route('imap.sync'), {}, {
    onFinish: () => { syncing.value = false; },
  });
}

function disconnectImap() {
  router.post(route('imap.disconnect'), {}, {
    onSuccess: () => { showDisconnect.value = false; },
  });
}

function updateProfile() {
  profileForm.put(route('users.update', props.user.id));
}

function updatePassword() {
  passwordForm.put(route('profile.password'), {
    onSuccess: () => passwordForm.reset(),
  });
}

function formatDate(date) {
  if (!date) return '';
  return new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }).format(new Date(date));
}

const roleLabels = { admin: 'Amministratore', manager: 'Manager', agent: 'Agente' };
const roleClasses = { admin: 'badge-red', manager: 'badge-purple', agent: 'badge-blue' };
const roleLabel = computed(() => roleLabels[props.user.roles?.[0]] || 'Utente');
const roleClass = computed(() => roleClasses[props.user.roles?.[0]] || 'badge-gray');
</script>
