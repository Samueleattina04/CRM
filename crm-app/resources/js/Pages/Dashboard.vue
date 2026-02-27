<template>
  <AppLayout title="Dashboard">
    <Head title="Dashboard" />

    <!-- Stats grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <StatCard
        title="Totale Clienti"
        :value="stats.total_customers"
        color="indigo"
        icon="users"
        :sub="`${stats.active_customers} attivi`"
      />
      <StatCard
        title="Nuovi Lead"
        :value="stats.new_leads"
        color="blue"
        icon="user-plus"
        sub="Da qualificare"
      />
      <StatCard
        title="Attività Oggi"
        :value="stats.activities_today"
        color="green"
        icon="bolt"
        sub="Interazioni"
      />
      <StatCard
        title="Task in Sospeso"
        :value="stats.pending_tasks"
        :color="stats.overdue_tasks > 0 ? 'red' : 'orange'"
        icon="clock"
        :sub="stats.overdue_tasks > 0 ? `${stats.overdue_tasks} scaduti!` : 'Da completare'"
      />
    </div>

    <!-- Pipeline value banner -->
    <div v-if="stats.pipeline_value > 0" class="card p-5 mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white border-0">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-indigo-100 text-sm font-medium">Valore Pipeline Attivo</p>
          <p class="text-3xl font-bold mt-1">{{ formatCurrency(stats.pipeline_value) }}</p>
        </div>
        <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center">
          <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Main grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- Left column - 2/3 -->
      <div class="xl:col-span-2 space-y-6">
        <!-- Activity chart -->
        <div class="card p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Attività ultimi 7 giorni</h3>
          </div>
          <div class="flex items-end gap-2 h-32">
            <div
              v-for="day in activityChart"
              :key="day.date"
              class="flex-1 flex flex-col items-center gap-1"
            >
              <span class="text-xs text-gray-500 font-medium">{{ day.count }}</span>
              <div
                class="w-full bg-indigo-500 rounded-t-md transition-all duration-500 min-h-[4px]"
                :style="{ height: chartBarHeight(day.count) }"
              ></div>
              <span class="text-xs text-gray-400">{{ day.date }}</span>
            </div>
          </div>
        </div>

        <!-- Recent customers -->
        <div class="card">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Clienti Recenti</h3>
            <Link :href="route('customers.index')" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Vedi tutti →</Link>
          </div>
          <div class="divide-y divide-gray-50">
            <div
              v-for="customer in recentCustomers"
              :key="customer.id"
              class="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 transition-colors"
            >
              <img :src="customer.avatar_url" :alt="customer.full_name" class="w-10 h-10 rounded-full object-cover flex-shrink-0" />
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <Link :href="route('customers.show', customer.id)" class="text-sm font-medium text-gray-900 hover:text-indigo-600 truncate">{{ customer.full_name }}</Link>
                  <StatusBadge :status="customer.status" />
                </div>
                <p class="text-xs text-gray-500 mt-0.5 truncate">{{ customer.company || customer.email }}</p>
              </div>
              <div class="text-right flex-shrink-0">
                <p v-if="customer.latest_activity" class="text-xs text-gray-400">{{ formatDate(customer.latest_activity.occurred_at) }}</p>
                <p v-if="customer.pending_tasks?.length" class="text-xs text-orange-500 font-medium mt-0.5">{{ customer.pending_tasks.length }} task</p>
              </div>
            </div>
            <div v-if="!recentCustomers.length" class="px-6 py-8 text-center text-gray-400 text-sm">
              Nessun cliente ancora. <Link :href="route('customers.create')" class="text-indigo-600 font-medium">Aggiungi il primo!</Link>
            </div>
          </div>
        </div>

        <!-- Today's activities -->
        <div class="card">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Attività di Oggi</h3>
            <Link :href="route('activities.index')" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Tutte →</Link>
          </div>
          <div class="divide-y divide-gray-50">
            <div v-for="activity in todayActivities" :key="activity.id" class="flex items-start gap-3 px-6 py-3.5">
              <div :class="['w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0', activityBg(activity.type)]">
                <span class="text-sm">{{ activityEmoji(activity.type) }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ activity.subject }}</p>
                <p class="text-xs text-gray-500 mt-0.5">
                  <Link :href="route('customers.show', activity.customer.id)" class="hover:text-indigo-600">{{ activity.customer?.full_name }}</Link>
                  · {{ formatTime(activity.occurred_at) }}
                </p>
              </div>
            </div>
            <div v-if="!todayActivities.length" class="px-6 py-8 text-center text-gray-400 text-sm">
              Nessuna attività registrata oggi.
            </div>
          </div>
        </div>
      </div>

      <!-- Right column - 1/3 -->
      <div class="space-y-6">
        <!-- Upcoming tasks -->
        <div class="card">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Task & Reminder</h3>
            <Link :href="route('tasks.index')" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Tutti →</Link>
          </div>
          <div class="divide-y divide-gray-50">
            <div v-for="task in upcomingTasks" :key="task.id" class="px-6 py-3.5">
              <div class="flex items-start gap-3">
                <div :class="['mt-0.5 w-5 h-5 rounded border-2 flex-shrink-0 flex items-center justify-center cursor-pointer hover:bg-indigo-50 transition-colors', task.status === 'completed' ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300']" @click="completeTask(task.id)">
                  <svg v-if="task.status === 'completed'" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900">{{ task.title }}</p>
                  <p v-if="task.customer" class="text-xs text-gray-500 truncate">{{ task.customer.full_name }}</p>
                  <div class="flex items-center gap-2 mt-1">
                    <span :class="['text-xs font-medium', isOverdue(task) ? 'text-red-600' : 'text-gray-400']">
                      {{ formatDate(task.due_date) }}
                    </span>
                    <PriorityBadge :priority="task.priority" />
                  </div>
                </div>
              </div>
            </div>
            <div v-if="!upcomingTasks.length" class="px-6 py-8 text-center text-gray-400 text-sm">
              Nessun task in sospeso. 🎉
            </div>
          </div>
        </div>

        <!-- Pipeline stages -->
        <div v-if="pipelineStages.length" class="card p-6">
          <h3 class="font-semibold text-gray-900 mb-4">Pipeline Vendite</h3>
          <div class="space-y-3">
            <div v-for="stage in pipelineStages" :key="stage.stage" class="flex items-center gap-3">
              <div class="w-2 h-2 rounded-full flex-shrink-0" :class="stageDot(stage.stage)"></div>
              <div class="flex-1">
                <div class="flex items-center justify-between text-xs mb-1">
                  <span class="text-gray-700 font-medium capitalize">{{ stageLabel(stage.stage) }}</span>
                  <span class="text-gray-500">{{ stage.count }} deal</span>
                </div>
                <div class="bg-gray-100 rounded-full h-1.5 overflow-hidden">
                  <div class="h-full rounded-full bg-indigo-500" :style="{ width: stageWidth(stage.total_value) }"></div>
                </div>
                <p class="text-xs text-gray-400 mt-0.5">{{ formatCurrency(stage.total_value) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Outlook connection -->
        <div class="card p-6">
          <h3 class="font-semibold text-gray-900 mb-3">Integrazione Email</h3>
          <div v-if="$page.props.auth.user.is_microsoft_connected" class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M21.179 12.794l.013.075a1.64 1.64 0 0 1-1.648 1.648H13.27v6.844a1.64 1.64 0 0 1-1.648 1.639H1.648A1.64 1.64 0 0 1 0 21.361V2.639A1.64 1.64 0 0 1 1.648 1H11.64a1.64 1.64 0 0 1 1.639 1.648v6.844h6.277a1.64 1.64 0 0 1 1.623 1.923z"/></svg>
            </div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">Outlook connesso</p>
              <p class="text-xs text-gray-500">Sincronizzazione email attiva</p>
            </div>
            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
          </div>
          <div v-else>
            <p class="text-sm text-gray-500 mb-3">Connetti Outlook per sincronizzare automaticamente le email con i tuoi clienti.</p>
            <a :href="route('outlook.connect')" class="btn-primary text-xs w-full justify-center">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
              Connetti Outlook
            </a>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import PriorityBadge from '@/Components/PriorityBadge.vue';

const props = defineProps({
  stats: Object,
  recentCustomers: Array,
  todayActivities: Array,
  upcomingTasks: Array,
  activityChart: Array,
  pipelineStages: Array,
});

const maxChartValue = computed(() => Math.max(...(props.activityChart?.map(d => d.count) || [1]), 1));

function chartBarHeight(count) {
  return count === 0 ? '4px' : `${Math.max(12, (count / maxChartValue.value) * 100)}px`;
}

const maxPipelineValue = computed(() => {
  return Math.max(...(props.pipelineStages?.map(s => parseFloat(s.total_value) || 0) || [1]), 1);
});

function stageWidth(value) {
  return `${Math.max(5, ((parseFloat(value) || 0) / maxPipelineValue.value) * 100)}%`;
}

function formatCurrency(val) {
  return new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(val || 0);
}

function formatDate(date) {
  if (!date) return '-';
  return new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: 'short' }).format(new Date(date));
}

function formatTime(date) {
  if (!date) return '';
  return new Intl.DateTimeFormat('it-IT', { hour: '2-digit', minute: '2-digit' }).format(new Date(date));
}

function isOverdue(task) {
  return task.status === 'pending' && task.due_date && new Date(task.due_date) < new Date();
}

function completeTask(id) {
  router.post(route('tasks.complete', id));
}

const activityColors = {
  email: 'bg-blue-100', email_incoming: 'bg-blue-100', email_outgoing: 'bg-indigo-100',
  call: 'bg-green-100', whatsapp: 'bg-emerald-100', sms: 'bg-yellow-100',
  meeting: 'bg-purple-100', note: 'bg-gray-100', task: 'bg-orange-100',
};

const activityEmojis = {
  email: '📧', email_incoming: '📩', email_outgoing: '📤',
  call: '📞', whatsapp: '💬', sms: '📱',
  meeting: '👥', note: '📝', task: '✅',
};

function activityBg(type) { return activityColors[type] || 'bg-gray-100'; }
function activityEmoji(type) { return activityEmojis[type] || '📌'; }

const stageLabels = { new: 'Nuovo', contacted: 'Contattato', qualified: 'Qualificato', proposal: 'Proposta', negotiation: 'Negoziazione' };
function stageLabel(s) { return stageLabels[s] || s; }
function stageDot(s) {
  const c = { new: 'bg-blue-500', contacted: 'bg-yellow-500', qualified: 'bg-indigo-500', proposal: 'bg-purple-500', negotiation: 'bg-orange-500' };
  return c[s] || 'bg-gray-400';
}
</script>
