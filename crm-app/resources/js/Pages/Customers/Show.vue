<template>
  <AppLayout :title="customer.full_name">
    <Head :title="customer.full_name" />

    <!-- Back + Actions -->
    <div class="flex items-center justify-between mb-6">
      <Link :href="route('customers.index')" class="btn-ghost">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        Clienti
      </Link>
      <div class="flex items-center gap-2">
        <button @click="showSendEmail = true" v-if="$page.props.auth.user.is_microsoft_connected" class="btn-secondary text-xs">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
          Invia Email
        </button>
        <Link :href="route('customers.edit', customer.id)" class="btn-primary text-xs">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
          Modifica
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- Left: Customer info -->
      <div class="space-y-5">
        <!-- Profile card -->
        <div class="card p-6">
          <div class="flex items-center gap-4 mb-5">
            <img :src="customer.avatar_url" :alt="customer.full_name" class="w-16 h-16 rounded-2xl object-cover ring-4 ring-indigo-50" />
            <div>
              <h2 class="text-lg font-bold text-gray-900">{{ customer.full_name }}</h2>
              <p v-if="customer.position" class="text-sm text-gray-500">{{ customer.position }}</p>
              <p v-if="customer.company" class="text-sm text-indigo-600 font-medium">{{ customer.company }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2 flex-wrap">
            <StatusBadge :status="customer.status" />
            <PriorityBadge :priority="customer.priority" />
            <span v-for="tag in customer.tags" :key="tag.id" class="badge text-xs" :style="{ backgroundColor: tag.color + '20', color: tag.color }">
              {{ tag.name }}
            </span>
          </div>
        </div>

        <!-- Contact details -->
        <div class="card p-5 space-y-3">
          <h3 class="font-semibold text-gray-900 text-sm">Contatti</h3>
          <ContactRow v-if="customer.email" icon="email" :value="customer.email" :href="`mailto:${customer.email}`" />
          <ContactRow v-if="customer.phone" icon="phone" :value="customer.phone" :href="`tel:${customer.phone}`" />
          <ContactRow v-if="customer.mobile" icon="mobile" :value="customer.mobile" :href="`tel:${customer.mobile}`" />
          <ContactRow v-if="customer.website" icon="web" :value="customer.website" :href="customer.website" external />
          <ContactRow v-if="customer.linkedin" icon="linkedin" :value="'LinkedIn'" :href="customer.linkedin" external />
          <div v-if="customer.address || customer.city" class="flex items-start gap-3">
            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </div>
            <p class="text-sm text-gray-700">{{ [customer.address, customer.city, customer.country].filter(Boolean).join(', ') }}</p>
          </div>
        </div>

        <!-- Stats -->
        <div class="card p-5">
          <h3 class="font-semibold text-gray-900 text-sm mb-3">Statistiche</h3>
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-indigo-50 rounded-xl p-3 text-center">
              <p class="text-2xl font-bold text-indigo-700">{{ customer.activities?.length || 0 }}</p>
              <p class="text-xs text-indigo-600 mt-0.5">Attività</p>
            </div>
            <div class="bg-orange-50 rounded-xl p-3 text-center">
              <p class="text-2xl font-bold text-orange-700">{{ pendingTasksCount }}</p>
              <p class="text-xs text-orange-600 mt-0.5">Task aperti</p>
            </div>
          </div>
          <div v-if="customer.annual_value" class="mt-3 bg-green-50 rounded-xl p-3">
            <p class="text-sm text-green-700 font-medium">Valore stimato</p>
            <p class="text-xl font-bold text-green-800">{{ formatCurrency(customer.annual_value) }}</p>
          </div>
          <div v-if="customer.last_contact_at" class="mt-3 text-xs text-gray-500">
            Ultimo contatto: {{ formatDateTime(customer.last_contact_at) }}
          </div>
        </div>

        <!-- Notes -->
        <div v-if="customer.notes" class="card p-5">
          <h3 class="font-semibold text-gray-900 text-sm mb-2">Note</h3>
          <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap">{{ customer.notes }}</p>
        </div>

        <!-- Assigned user -->
        <div v-if="customer.assigned_user" class="card p-5">
          <h3 class="font-semibold text-gray-900 text-sm mb-3">Responsabile</h3>
          <div class="flex items-center gap-3">
            <img :src="customer.assigned_user.avatar_url" class="w-8 h-8 rounded-full" />
            <div>
              <p class="text-sm font-medium text-gray-900">{{ customer.assigned_user.name }}</p>
              <p class="text-xs text-gray-500">{{ customer.assigned_user.email }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Center + Right: Timeline + Tasks -->
      <div class="xl:col-span-2 space-y-6">
        <!-- Quick actions bar -->
        <div class="card p-4">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="text-sm font-medium text-gray-700 mr-2">Aggiungi:</span>
            <button v-for="qAction in quickActions" :key="qAction.type"
              @click="openActivity(qAction.type)"
              class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border transition-all hover:shadow-sm"
              :class="qAction.class"
            >
              <span>{{ qAction.emoji }}</span>
              {{ qAction.label }}
            </button>
            <button @click="showAddTask = true" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-orange-200 text-orange-700 bg-orange-50 hover:bg-orange-100 transition-all">
              🔔 Reminder
            </button>
          </div>
        </div>

        <!-- Tabs -->
        <div class="card">
          <div class="flex border-b border-gray-100">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="['px-5 py-3 text-sm font-medium transition-all border-b-2 -mb-px', activeTab === tab.id ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700']"
            >
              {{ tab.label }}
              <span v-if="tab.count !== undefined" class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full" :class="activeTab === tab.id ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'">{{ tab.count }}</span>
            </button>
          </div>

          <!-- Timeline tab -->
          <div v-if="activeTab === 'timeline'" class="p-6">
            <div v-if="customer.activities?.length" class="relative">
              <!-- Timeline line -->
              <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-gray-100"></div>

              <div class="space-y-6">
                <div v-for="activity in customer.activities" :key="activity.id" class="flex gap-4 relative">
                  <!-- Icon -->
                  <div :class="['w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ring-4 ring-white z-10', activityBg(activity.type)]">
                    <span class="text-base">{{ activityEmoji(activity.type) }}</span>
                  </div>

                  <!-- Content -->
                  <div class="flex-1 bg-gray-50 hover:bg-gray-100 rounded-xl p-4 transition-colors group">
                    <div class="flex items-start justify-between gap-3">
                      <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                          <span class="text-xs font-semibold uppercase tracking-wide" :class="activityTextColor(activity.type)">
                            {{ activityLabel(activity.type) }}
                          </span>
                          <span v-if="activity.direction === 'inbound'" class="text-xs text-blue-500">📥 in entrata</span>
                          <span v-if="activity.direction === 'outbound'" class="text-xs text-green-500">📤 in uscita</span>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900 mt-1">{{ activity.subject }}</h4>
                        <p v-if="activity.body" class="text-sm text-gray-600 mt-1 line-clamp-3">{{ activity.body }}</p>
                        <div v-if="activity.attachments?.length" class="flex items-center gap-2 mt-2 flex-wrap">
                          <a v-for="att in activity.attachments" :key="att.id" :href="att.url" target="_blank"
                            class="flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                            {{ att.name }}
                          </a>
                        </div>
                      </div>
                      <div class="text-right flex-shrink-0">
                        <p class="text-xs text-gray-400">{{ formatDateTime(activity.occurred_at) }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ activity.user?.name }}</p>
                        <div class="flex items-center gap-1 mt-2 opacity-0 group-hover:opacity-100 transition-opacity justify-end">
                          <button @click="deleteActivity(activity.id)" class="p-1 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="py-12 text-center">
              <p class="text-4xl mb-3">📭</p>
              <p class="text-gray-500 font-medium">Nessuna attività registrata</p>
              <p class="text-sm text-gray-400 mt-1">Inizia aggiungendo una nota, chiamata o email.</p>
            </div>
          </div>

          <!-- Tasks tab -->
          <div v-if="activeTab === 'tasks'" class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-gray-900">Task e Reminder</h3>
              <button @click="showAddTask = true" class="btn-primary text-xs">+ Aggiungi</button>
            </div>
            <div v-if="customer.tasks?.length" class="space-y-3">
              <div v-for="task in customer.tasks" :key="task.id" :class="['p-4 rounded-xl border transition-colors', task.status === 'completed' ? 'bg-gray-50 border-gray-100 opacity-60' : isTaskOverdue(task) ? 'bg-red-50 border-red-200' : 'bg-white border-gray-200 hover:border-indigo-200']">
                <div class="flex items-start gap-3">
                  <button
                    @click="completeTask(task.id)"
                    :class="['mt-0.5 w-5 h-5 rounded border-2 flex-shrink-0 flex items-center justify-center transition-all', task.status === 'completed' ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300 hover:border-indigo-400']"
                  >
                    <svg v-if="task.status === 'completed'" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                  </button>
                  <div class="flex-1">
                    <p :class="['text-sm font-medium', task.status === 'completed' ? 'line-through text-gray-400' : 'text-gray-900']">{{ task.title }}</p>
                    <p v-if="task.description" class="text-xs text-gray-500 mt-0.5">{{ task.description }}</p>
                    <div class="flex items-center gap-3 mt-2 flex-wrap">
                      <span v-if="task.due_date" :class="['text-xs font-medium', isTaskOverdue(task) ? 'text-red-600' : 'text-gray-500']">
                        📅 {{ formatDateTime(task.due_date) }}
                      </span>
                      <PriorityBadge :priority="task.priority" />
                      <span v-if="task.assigned_user" class="text-xs text-gray-400">👤 {{ task.assigned_user.name }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="py-8 text-center text-gray-400">
              <p class="text-3xl mb-2">📋</p>
              <p>Nessun task per questo cliente.</p>
            </div>
          </div>

          <!-- Pipeline tab -->
          <div v-if="activeTab === 'pipeline'" class="p-6">
            <div v-if="customer.pipelines?.length" class="space-y-3">
              <div v-for="deal in customer.pipelines" :key="deal.id" class="p-4 border border-gray-200 rounded-xl hover:border-indigo-200 transition-colors">
                <div class="flex items-center justify-between">
                  <h4 class="font-medium text-gray-900">{{ deal.name }}</h4>
                  <span class="text-lg font-bold text-indigo-700">{{ formatCurrency(deal.value) }}</span>
                </div>
                <div class="flex items-center gap-3 mt-2">
                  <span class="badge badge-indigo capitalize">{{ deal.stage }}</span>
                  <span class="text-xs text-gray-500">{{ deal.probability }}% probabilità</span>
                  <span v-if="deal.expected_close_date" class="text-xs text-gray-400">Chiusura: {{ formatDate(deal.expected_close_date) }}</span>
                </div>
              </div>
            </div>
            <div v-else class="py-8 text-center text-gray-400">
              <p class="text-3xl mb-2">💼</p>
              <p>Nessuna trattativa attiva.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Activity Modal -->
    <Modal :show="showAddActivity" @close="showAddActivity = false" :title="activityModalTitle">
      <form @submit.prevent="submitActivity" class="space-y-4 p-6">
        <input type="hidden" v-model="activityForm.type" />

        <!-- Direction toggle (call/whatsapp/sms/email) -->
        <div v-if="['call','whatsapp','sms','email'].includes(activityForm.type)" class="form-group">
          <label class="label">Direzione</label>
          <div class="flex gap-2">
            <button type="button"
              @click="activityForm.direction = 'inbound'"
              :class="['flex-1 py-2.5 px-3 rounded-lg border-2 text-sm font-medium transition-all flex items-center justify-center gap-2',
                activityForm.direction === 'inbound'
                  ? 'border-blue-500 bg-blue-50 text-blue-700'
                  : 'border-gray-200 text-gray-500 hover:border-gray-300']"
            >
              📥 <span>{{ directionInboundLabel }}</span>
            </button>
            <button type="button"
              @click="activityForm.direction = 'outbound'"
              :class="['flex-1 py-2.5 px-3 rounded-lg border-2 text-sm font-medium transition-all flex items-center justify-center gap-2',
                activityForm.direction === 'outbound'
                  ? 'border-green-500 bg-green-50 text-green-700'
                  : 'border-gray-200 text-gray-500 hover:border-gray-300']"
            >
              📤 <span>{{ directionOutboundLabel }}</span>
            </button>
          </div>
        </div>

        <!-- Phone number (call/whatsapp/sms) -->
        <div v-if="['call','whatsapp','sms'].includes(activityForm.type)" class="form-group">
          <label class="label">Numero di telefono</label>
          <div class="flex gap-2">
            <input v-model="activityForm.phone_number" type="tel" class="input flex-1" placeholder="+39 000 000 0000" />
            <button v-if="customer.mobile" type="button"
              @click="activityForm.phone_number = customer.mobile"
              class="px-3 py-2 text-xs bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition-colors whitespace-nowrap"
              :title="customer.mobile"
            >📱 Cellulare</button>
            <button v-if="customer.phone" type="button"
              @click="activityForm.phone_number = customer.phone"
              class="px-3 py-2 text-xs bg-gray-50 text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors whitespace-nowrap"
              :title="customer.phone"
            >☎️ Fisso</button>
          </div>
        </div>

        <div class="form-group">
          <label class="label">Oggetto / Titolo *</label>
          <input v-model="activityForm.subject" type="text" class="input" :placeholder="activitySubjectPlaceholder" required />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="form-group">
            <label class="label">Data e ora *</label>
            <input v-model="activityForm.occurred_at" type="datetime-local" class="input" required />
          </div>
          <div class="form-group">
            <label class="label">Stato</label>
            <select v-model="activityForm.status" class="input">
              <option value="completed">Completata</option>
              <option value="scheduled">Programmata</option>
              <option value="pending">In sospeso</option>
            </select>
          </div>
        </div>

        <div v-if="['call','meeting'].includes(activityForm.type)" class="form-group">
          <label class="label">Durata (minuti)</label>
          <input v-model="activityForm.duration_minutes" type="number" min="0" class="input" placeholder="5" />
        </div>

        <div class="form-group">
          <label class="label">Note / Contenuto</label>
          <textarea v-model="activityForm.body" rows="3" class="input" :placeholder="activityBodyPlaceholder"></textarea>
        </div>

        <div class="form-group">
          <label class="label">Allegati</label>
          <input type="file" ref="fileInput" multiple class="input text-xs" @change="handleFiles" />
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="showAddActivity = false" class="btn-secondary">Annulla</button>
          <button type="submit" :disabled="activityForm.processing" class="btn-primary">
            {{ activityForm.processing ? 'Salvataggio...' : 'Salva' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Add Task Modal -->
    <Modal :show="showAddTask" @close="showAddTask = false" title="Nuovo Task / Reminder">
      <form @submit.prevent="submitTask" class="space-y-4 p-6">
        <div class="form-group">
          <label class="label">Titolo *</label>
          <input v-model="taskForm.title" type="text" class="input" placeholder="Es: Richiamare il 5 marzo" required />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="form-group">
            <label class="label">Tipo</label>
            <select v-model="taskForm.type" class="input">
              <option value="follow_up">Follow-up</option>
              <option value="call">Chiamata</option>
              <option value="email">Email</option>
              <option value="meeting">Meeting</option>
              <option value="other">Altro</option>
            </select>
          </div>
          <div class="form-group">
            <label class="label">Priorità</label>
            <select v-model="taskForm.priority" class="input">
              <option value="low">Bassa</option>
              <option value="medium">Media</option>
              <option value="high">Alta</option>
              <option value="urgent">Urgente</option>
            </select>
          </div>
          <div class="form-group">
            <label class="label">Scadenza</label>
            <input v-model="taskForm.due_date" type="datetime-local" class="input" />
          </div>
          <div class="form-group">
            <label class="label">Reminder</label>
            <input v-model="taskForm.reminder_at" type="datetime-local" class="input" />
          </div>
        </div>
        <div class="form-group">
          <label class="label">Note</label>
          <textarea v-model="taskForm.description" rows="3" class="input"></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="showAddTask = false" class="btn-secondary">Annulla</button>
          <button type="submit" :disabled="taskForm.processing" class="btn-primary">
            {{ taskForm.processing ? 'Salvataggio...' : 'Crea Task' }}
          </button>
        </div>
      </form>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import PriorityBadge from '@/Components/PriorityBadge.vue';
import Modal from '@/Components/Modal.vue';
import ContactRow from '@/Components/ContactRow.vue';

const props = defineProps({
  customer: Object,
  agents: Array,
  tags: Array,
});

const activeTab = ref('timeline');
const showAddActivity = ref(false);
const showAddTask = ref(false);
const showSendEmail = ref(false);

const tabs = computed(() => [
  { id: 'timeline', label: 'Timeline', count: props.customer.activities?.length },
  { id: 'tasks', label: 'Task', count: props.customer.tasks?.filter(t => t.status !== 'completed').length },
  { id: 'pipeline', label: 'Pipeline', count: props.customer.pipelines?.length },
]);

const pendingTasksCount = computed(() => props.customer.tasks?.filter(t => t.status === 'pending').length || 0);

const activityForm = useForm({
  type: 'note',
  subject: '',
  body: '',
  direction: 'internal',
  status: 'completed',
  duration_minutes: null,
  phone_number: null,
  email_from: null,
  email_to: null,
  occurred_at: new Date().toISOString().slice(0, 16),
  attachments: [],
});

const taskForm = useForm({
  customer_id: props.customer.id,
  title: '',
  description: '',
  type: 'follow_up',
  priority: 'medium',
  due_date: '',
  reminder_at: '',
  assigned_to: null,
});

const quickActions = [
  { type: 'call', label: 'Chiamata', emoji: '📞', class: 'border-green-200 text-green-700 bg-green-50 hover:bg-green-100' },
  { type: 'email', label: 'Email', emoji: '📧', class: 'border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100' },
  { type: 'whatsapp', label: 'WhatsApp', emoji: '💬', class: 'border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100' },
  { type: 'sms', label: 'SMS', emoji: '📱', class: 'border-yellow-200 text-yellow-700 bg-yellow-50 hover:bg-yellow-100' },
  { type: 'meeting', label: 'Meeting', emoji: '👥', class: 'border-purple-200 text-purple-700 bg-purple-50 hover:bg-purple-100' },
  { type: 'note', label: 'Nota', emoji: '📝', class: 'border-gray-200 text-gray-700 bg-gray-50 hover:bg-gray-100' },
];

const activityLabels = {
  email: 'Email', email_incoming: 'Email Ricevuta', email_outgoing: 'Email Inviata',
  call: 'Chiamata', whatsapp: 'WhatsApp', sms: 'SMS',
  meeting: 'Meeting', note: 'Nota', task: 'Task',
};

const activityBgs = {
  email: 'bg-blue-100', email_incoming: 'bg-blue-100', email_outgoing: 'bg-indigo-100',
  call: 'bg-green-100', whatsapp: 'bg-emerald-100', sms: 'bg-yellow-100',
  meeting: 'bg-purple-100', note: 'bg-gray-100', task: 'bg-orange-100',
};

const activityTextColors = {
  email: 'text-blue-600', email_incoming: 'text-blue-600', email_outgoing: 'text-indigo-600',
  call: 'text-green-600', whatsapp: 'text-emerald-600', sms: 'text-yellow-600',
  meeting: 'text-purple-600', note: 'text-gray-500', task: 'text-orange-600',
};

const activityEmojis = {
  email: '📧', email_incoming: '📩', email_outgoing: '📤',
  call: '📞', whatsapp: '💬', sms: '📱',
  meeting: '👥', note: '📝', task: '✅',
};

const activityModalTitle = computed(() => {
  const m = { call: 'Registra Chiamata', email: 'Registra Email', email_outgoing: 'Invia/Registra Email', whatsapp: 'Registra WhatsApp', sms: 'Registra SMS', meeting: 'Registra Meeting', note: 'Aggiungi Nota' };
  return m[activityForm.type] || 'Aggiungi Attività';
});

const directionInboundLabel = computed(() => {
  const m = { call: 'Ho ricevuto la chiamata', whatsapp: 'Ha scritto lui/lei', sms: 'Ha scritto lui/lei', email: 'Email ricevuta' };
  return m[activityForm.type] || 'In entrata';
});

const directionOutboundLabel = computed(() => {
  const m = { call: 'Ho chiamato io', whatsapp: 'Ho scritto io', sms: 'Ho scritto io', email: 'Email inviata' };
  return m[activityForm.type] || 'In uscita';
});

const activitySubjectPlaceholder = computed(() => {
  const m = { call: 'Es: Chiamata aggiornamento ordine', whatsapp: 'Es: Discussione preventivo', sms: 'Es: Conferma appuntamento', meeting: 'Es: Riunione trimestrale', email: 'Es: Follow-up proposta', note: 'Es: Nota interna' };
  return m[activityForm.type] || 'Oggetto';
});

const activityBodyPlaceholder = computed(() => {
  const m = {
    call: 'Di cosa avete parlato? Prossimi passi...',
    whatsapp: 'Contenuto del messaggio o riassunto della conversazione...',
    sms: 'Testo del messaggio...',
    meeting: 'Argomenti discussi, decisioni prese, prossimi passi...',
    email: 'Contenuto o riassunto dell\'email...',
    note: 'Appunti, osservazioni interne...',
  };
  return m[activityForm.type] || 'Dettagli sull\'interazione...';
});

function openActivity(type) {
  activityForm.reset();
  activityForm.type = type;
  activityForm.occurred_at = new Date().toISOString().slice(0, 16);
  activityForm.status = 'completed';
  activityForm.direction = type === 'note' ? 'internal' : 'inbound';
  // Pre-fill phone number from customer
  if (['call','whatsapp','sms'].includes(type)) {
    activityForm.phone_number = props.customer.mobile || props.customer.phone || '';
  }
  showAddActivity.value = true;
}

function handleFiles(e) {
  activityForm.attachments = Array.from(e.target.files);
}

function submitActivity() {
  activityForm.post(route('activities.store', props.customer.id), {
    forceFormData: true,
    onSuccess: () => { showAddActivity.value = false; activityForm.reset(); },
  });
}

function submitTask() {
  taskForm.post(route('tasks.store'), {
    onSuccess: () => { showAddTask.value = false; taskForm.reset(); taskForm.customer_id = props.customer.id; },
  });
}

function completeTask(id) {
  router.post(route('tasks.complete', id));
}

function deleteActivity(id) {
  if (confirm('Eliminare questa attività?')) {
    router.delete(route('activities.destroy', id));
  }
}

function isTaskOverdue(task) {
  return task.status === 'pending' && task.due_date && new Date(task.due_date) < new Date();
}

function formatDateTime(date) {
  if (!date) return '-';
  return new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(date));
}

function formatDate(date) {
  if (!date) return '';
  return new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(date));
}

function formatCurrency(val) {
  return new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(val || 0);
}

function activityBg(type) { return activityBgs[type] || 'bg-gray-100'; }
function activityEmoji(type) { return activityEmojis[type] || '📌'; }
function activityLabel(type) { return activityLabels[type] || type; }
function activityTextColor(type) { return activityTextColors[type] || 'text-gray-600'; }
</script>
