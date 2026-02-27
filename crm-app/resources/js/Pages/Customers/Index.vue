<template>
  <AppLayout title="Clienti">
    <Head title="Clienti" />

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="text-xl font-bold text-gray-900">Clienti</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ customers.total }} clienti totali</p>
      </div>
      <Link :href="route('customers.create')" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Nuovo Cliente
      </Link>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="lg:col-span-2">
          <input
            v-model="searchQuery"
            @input="debouncedSearch"
            type="text"
            placeholder="Cerca per nome, email, azienda..."
            class="input"
          />
        </div>
        <select v-model="filterStatus" @change="applyFilters" class="input">
          <option value="">Tutti gli stati</option>
          <option value="lead">Lead</option>
          <option value="prospect">Prospect</option>
          <option value="active">Attivo</option>
          <option value="inactive">Inattivo</option>
          <option value="churned">Perso</option>
        </select>
        <select v-model="filterPriority" @change="applyFilters" class="input">
          <option value="">Tutte le priorità</option>
          <option value="low">Bassa</option>
          <option value="medium">Media</option>
          <option value="high">Alta</option>
        </select>
        <button @click="clearFilters" class="btn-secondary justify-center">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          Reset
        </button>
      </div>
    </div>

    <!-- Customers table -->
    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Azienda</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Contatto</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stato</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Ultima Attività</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Task</th>
              <th class="w-16"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr
              v-for="customer in customers.data"
              :key="customer.id"
              class="hover:bg-gray-50 transition-colors group"
            >
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <img :src="customer.avatar_url" :alt="customer.full_name" class="w-9 h-9 rounded-full object-cover flex-shrink-0" />
                  <div>
                    <Link :href="route('customers.show', customer.id)" class="text-sm font-semibold text-gray-900 hover:text-indigo-600">
                      {{ customer.full_name }}
                    </Link>
                    <p v-if="customer.email" class="text-xs text-gray-400 mt-0.5">{{ customer.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 hidden sm:table-cell">
                <p class="text-sm text-gray-700">{{ customer.company || '-' }}</p>
                <p v-if="customer.position" class="text-xs text-gray-400">{{ customer.position }}</p>
              </td>
              <td class="px-6 py-4 hidden md:table-cell">
                <p class="text-sm text-gray-700">{{ customer.phone || customer.mobile || '-' }}</p>
              </td>
              <td class="px-6 py-4">
                <StatusBadge :status="customer.status" />
              </td>
              <td class="px-6 py-4 hidden lg:table-cell">
                <p v-if="customer.latest_activity" class="text-xs text-gray-500">
                  {{ formatDate(customer.latest_activity.occurred_at) }}
                </p>
                <p v-else class="text-xs text-gray-300">-</p>
              </td>
              <td class="px-6 py-4 hidden lg:table-cell">
                <span v-if="customer.pending_tasks?.length" class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full font-medium">
                  {{ customer.pending_tasks.length }}
                </span>
                <span v-else class="text-xs text-gray-300">-</span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <Link :href="route('customers.show', customer.id)" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Vedi">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  </Link>
                  <Link :href="route('customers.edit', customer.id)" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Modifica">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                  </Link>
                </div>
              </td>
            </tr>
            <tr v-if="!customers.data.length">
              <td colspan="7" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center gap-3">
                  <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                  </div>
                  <p class="text-gray-500 font-medium">Nessun cliente trovato</p>
                  <p class="text-sm text-gray-400">Prova a modificare i filtri o aggiungi un nuovo cliente.</p>
                  <Link :href="route('customers.create')" class="btn-primary mt-2">Aggiungi Cliente</Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="customers.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-gray-100">
        <p class="text-sm text-gray-500">
          {{ customers.from }}-{{ customers.to }} di {{ customers.total }}
        </p>
        <div class="flex items-center gap-2">
          <Link
            v-for="link in customers.links"
            :key="link.label"
            :href="link.url || '#'"
            :class="['px-3 py-1.5 text-sm rounded-lg transition-colors', link.active ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100', !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none' : '']"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
  customers: Object,
  agents: Array,
  tags: Array,
  filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const filterStatus = ref(props.filters?.status || '');
const filterPriority = ref(props.filters?.priority || '');

let searchTimeout = null;
function debouncedSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(applyFilters, 400);
}

function applyFilters() {
  router.get(route('customers.index'), {
    search: searchQuery.value || undefined,
    status: filterStatus.value || undefined,
    priority: filterPriority.value || undefined,
  }, { preserveState: true, replace: true });
}

function clearFilters() {
  searchQuery.value = '';
  filterStatus.value = '';
  filterPriority.value = '';
  router.get(route('customers.index'), {}, { replace: true });
}

function formatDate(date) {
  if (!date) return '';
  return new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: 'short', year: '2-digit' }).format(new Date(date));
}
</script>
