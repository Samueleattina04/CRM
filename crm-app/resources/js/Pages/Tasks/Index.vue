<template>
  <AppLayout title="Task & Reminder">
    <Head title="Task & Reminder" />

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="text-xl font-bold text-gray-900">Task & Reminder</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ tasks.total }} task totali</p>
      </div>
      <button @click="showAdd = true" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Nuovo Task
      </button>
    </div>

    <!-- Filter tabs -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-1">
      <button v-for="f in filterOptions" :key="f.value"
        @click="setFilter(f.value)"
        :class="['px-4 py-2 text-sm font-medium rounded-xl border transition-all whitespace-nowrap', activeFilter === f.value ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-200']"
      >
        {{ f.label }}
        <span v-if="f.count !== undefined" class="ml-1.5 text-xs opacity-70">({{ f.count }})</span>
      </button>
    </div>

    <!-- Tasks list -->
    <div class="space-y-3">
      <div
        v-for="task in tasks.data"
        :key="task.id"
        :class="['card p-5 transition-all hover:shadow-md', task.status === 'completed' ? 'opacity-60' : isOverdue(task) ? 'border-red-200 bg-red-50' : '']"
      >
        <div class="flex items-start gap-4">
          <!-- Checkbox -->
          <button
            @click="completeTask(task.id)"
            :class="['mt-0.5 w-6 h-6 rounded-lg border-2 flex-shrink-0 flex items-center justify-center transition-all', task.status === 'completed' ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300 hover:border-indigo-400 hover:bg-indigo-50']"
          >
            <svg v-if="task.status === 'completed'" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
          </button>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p :class="['font-semibold', task.status === 'completed' ? 'line-through text-gray-400' : 'text-gray-900']">{{ task.title }}</p>
                <p v-if="task.description" class="text-sm text-gray-500 mt-0.5">{{ task.description }}</p>
                <div v-if="task.customer" class="mt-1">
                  <Link :href="route('customers.show', task.customer.id)" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                    👤 {{ task.customer.full_name }}
                  </Link>
                </div>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <PriorityBadge :priority="task.priority" />
                <TaskTypeBadge :type="task.type" />
              </div>
            </div>

            <div class="flex items-center gap-4 mt-3 flex-wrap">
              <span v-if="task.due_date" :class="['flex items-center gap-1.5 text-xs font-medium', isOverdue(task) ? 'text-red-600' : 'text-gray-500']">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                {{ isOverdue(task) ? '⚠️ Scaduto: ' : '' }}{{ formatDateTime(task.due_date) }}
              </span>
              <span v-if="task.reminder_at" class="flex items-center gap-1.5 text-xs text-amber-600">
                🔔 {{ formatDateTime(task.reminder_at) }}
              </span>
              <span v-if="task.assigned_user" class="text-xs text-gray-400">
                👤 {{ task.assigned_user.name }}
              </span>
            </div>
          </div>

          <!-- Delete -->
          <button @click="deleteTask(task.id)" class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
          </button>
        </div>
      </div>

      <div v-if="!tasks.data.length" class="card p-12 text-center">
        <p class="text-4xl mb-3">🎉</p>
        <p class="text-gray-500 font-medium">Nessun task trovato!</p>
        <p class="text-sm text-gray-400 mt-1">Tutti i task sono stati completati.</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="tasks.last_page > 1" class="flex justify-center mt-6">
      <div class="flex items-center gap-2">
        <Link
          v-for="link in tasks.links"
          :key="link.label"
          :href="link.url || '#'"
          :class="['px-3 py-1.5 text-sm rounded-lg', link.active ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100', !link.url ? 'opacity-50 pointer-events-none' : '']"
          v-html="link.label"
        />
      </div>
    </div>

    <!-- Add Task Modal -->
    <Modal :show="showAdd" @close="showAdd = false" title="Nuovo Task">
      <form @submit.prevent="submitTask" class="space-y-4 p-6">
        <div class="form-group">
          <label class="label">Titolo *</label>
          <input v-model="taskForm.title" type="text" class="input" placeholder="Es: Richiamare Rossi" required />
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
        <div class="flex justify-end gap-3">
          <button type="button" @click="showAdd = false" class="btn-secondary">Annulla</button>
          <button type="submit" :disabled="taskForm.processing" class="btn-primary">Crea Task</button>
        </div>
      </form>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PriorityBadge from '@/Components/PriorityBadge.vue';
import Modal from '@/Components/Modal.vue';

const TaskTypeBadge = {
  props: ['type'],
  template: `<span class="badge badge-gray text-xs capitalize">{{ labels[type] || type }}</span>`,
  setup: (p) => ({ labels: { follow_up: 'Follow-up', call: 'Chiamata', email: 'Email', meeting: 'Meeting', other: 'Altro' } }),
};

const props = defineProps({ tasks: Object, filters: Object });

const showAdd = ref(false);
const activeFilter = ref(props.filters?.status || '');

const filterOptions = [
  { value: '', label: 'Tutti' },
  { value: 'pending', label: 'In sospeso' },
  { value: 'in_progress', label: 'In corso' },
  { value: 'completed', label: 'Completati' },
];

function setFilter(val) {
  activeFilter.value = val;
  router.get(route('tasks.index'), { status: val || undefined }, { preserveState: true, replace: true });
}

const taskForm = useForm({
  title: '', description: '', type: 'follow_up', priority: 'medium',
  due_date: '', reminder_at: '', customer_id: null,
});

function submitTask() {
  taskForm.post(route('tasks.store'), {
    onSuccess: () => { showAdd.value = false; taskForm.reset(); },
  });
}

function completeTask(id) {
  router.post(route('tasks.complete', id));
}

function deleteTask(id) {
  if (confirm('Eliminare questo task?')) {
    router.delete(route('tasks.destroy', id));
  }
}

function isOverdue(task) {
  return task.status === 'pending' && task.due_date && new Date(task.due_date) < new Date();
}

function formatDateTime(date) {
  if (!date) return '-';
  return new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(date));
}
</script>
