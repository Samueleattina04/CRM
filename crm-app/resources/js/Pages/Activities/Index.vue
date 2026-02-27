<template>
  <AppLayout title="Attività">
    <Head title="Attività" />

    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-xl font-bold text-gray-900">Registro Attività</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ activities.total }} attività totali</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-6">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <select v-model="filterType" @change="applyFilters" class="input text-sm">
          <option value="">Tutti i tipi</option>
          <option value="email">Email</option>
          <option value="email_incoming">Email Ricevuta</option>
          <option value="email_outgoing">Email Inviata</option>
          <option value="call">Chiamata</option>
          <option value="whatsapp">WhatsApp</option>
          <option value="sms">SMS</option>
          <option value="meeting">Meeting</option>
          <option value="note">Nota</option>
        </select>
        <input v-model="filterDateFrom" @change="applyFilters" type="date" class="input text-sm" placeholder="Da" />
        <input v-model="filterDateTo" @change="applyFilters" type="date" class="input text-sm" placeholder="A" />
        <button @click="clearFilters" class="btn-secondary text-sm justify-center">Reset</button>
      </div>
    </div>

    <!-- Activities list -->
    <div class="space-y-3">
      <div v-for="activity in activities.data" :key="activity.id" class="card p-5 hover:shadow-md transition-all">
        <div class="flex items-start gap-4">
          <div :class="['w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0', activityBg(activity.type)]">
            <span class="text-lg">{{ activityEmoji(activity.type) }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="flex items-center gap-2 flex-wrap mb-1">
                  <span class="text-xs font-semibold uppercase tracking-wide" :class="activityTextColor(activity.type)">
                    {{ activityLabel(activity.type) }}
                  </span>
                  <span v-if="activity.direction === 'inbound'" class="text-xs text-blue-500 bg-blue-50 px-1.5 py-0.5 rounded">📥 In entrata</span>
                  <span v-if="activity.direction === 'outbound'" class="text-xs text-green-500 bg-green-50 px-1.5 py-0.5 rounded">📤 In uscita</span>
                </div>
                <h4 class="font-semibold text-gray-900">{{ activity.subject }}</h4>
                <p v-if="activity.body" class="text-sm text-gray-500 mt-1 line-clamp-2">{{ activity.body }}</p>
                <div class="flex items-center gap-4 mt-2 flex-wrap">
                  <Link v-if="activity.customer" :href="route('customers.show', activity.customer.id)" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                    👤 {{ activity.customer.full_name }}
                  </Link>
                  <span v-if="activity.user" class="text-xs text-gray-400">by {{ activity.user.name }}</span>
                  <span v-if="activity.duration_minutes" class="text-xs text-gray-400">⏱ {{ activity.duration_minutes }} min</span>
                </div>
                <div v-if="activity.attachments?.length" class="flex items-center gap-2 mt-2 flex-wrap">
                  <a v-for="att in activity.attachments" :key="att.id" :href="att.url" target="_blank" class="text-xs text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-lg hover:bg-indigo-100 transition-colors">
                    📎 {{ att.name }}
                  </a>
                </div>
              </div>
              <div class="text-right flex-shrink-0">
                <p class="text-xs text-gray-400">{{ formatDateTime(activity.occurred_at) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!activities.data.length" class="card p-12 text-center">
        <p class="text-4xl mb-3">📭</p>
        <p class="text-gray-500">Nessuna attività trovata.</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="activities.last_page > 1" class="flex justify-center mt-6">
      <div class="flex items-center gap-2">
        <Link v-for="link in activities.links" :key="link.label" :href="link.url || '#'"
          :class="['px-3 py-1.5 text-sm rounded-lg', link.active ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100', !link.url ? 'opacity-50 pointer-events-none' : '']"
          v-html="link.label"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({ activities: Object, filters: Object });

const filterType = ref(props.filters?.type || '');
const filterDateFrom = ref(props.filters?.date_from || '');
const filterDateTo = ref(props.filters?.date_to || '');

function applyFilters() {
  router.get(route('activities.index'), {
    type: filterType.value || undefined,
    date_from: filterDateFrom.value || undefined,
    date_to: filterDateTo.value || undefined,
  }, { preserveState: true, replace: true });
}

function clearFilters() {
  filterType.value = ''; filterDateFrom.value = ''; filterDateTo.value = '';
  router.get(route('activities.index'), {}, { replace: true });
}

const activityBgs = {
  email: 'bg-blue-100', email_incoming: 'bg-blue-100', email_outgoing: 'bg-indigo-100',
  call: 'bg-green-100', whatsapp: 'bg-emerald-100', sms: 'bg-yellow-100',
  meeting: 'bg-purple-100', note: 'bg-gray-100', task: 'bg-orange-100',
};
const activityEmojis = { email: '📧', email_incoming: '📩', email_outgoing: '📤', call: '📞', whatsapp: '💬', sms: '📱', meeting: '👥', note: '📝', task: '✅' };
const activityLabels = { email: 'Email', email_incoming: 'Email Ricevuta', email_outgoing: 'Email Inviata', call: 'Chiamata', whatsapp: 'WhatsApp', sms: 'SMS', meeting: 'Meeting', note: 'Nota', task: 'Task' };
const activityTextColors = { email: 'text-blue-600', email_incoming: 'text-blue-600', email_outgoing: 'text-indigo-600', call: 'text-green-600', whatsapp: 'text-emerald-600', sms: 'text-yellow-600', meeting: 'text-purple-600', note: 'text-gray-500', task: 'text-orange-600' };

function activityBg(type) { return activityBgs[type] || 'bg-gray-100'; }
function activityEmoji(type) { return activityEmojis[type] || '📌'; }
function activityLabel(type) { return activityLabels[type] || type; }
function activityTextColor(type) { return activityTextColors[type] || 'text-gray-600'; }

function formatDateTime(date) {
  if (!date) return '-';
  return new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(date));
}
</script>
