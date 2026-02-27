<template>
  <AppLayout title="Gestione Utenti">
    <Head title="Utenti" />

    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-xl font-bold text-gray-900">Gestione Utenti</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ users.length }} utenti registrati</p>
      </div>
    </div>

    <div class="card overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Utente</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Ruolo</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Stato</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Outlook</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Registrato</th>
            <th class="w-24"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 group">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <img :src="user.avatar_url" :alt="user.name" class="w-9 h-9 rounded-full object-cover" />
                <div>
                  <p class="text-sm font-semibold text-gray-900">{{ user.name }}</p>
                  <p class="text-xs text-gray-400">{{ user.email }}</p>
                  <p v-if="user.position" class="text-xs text-gray-400">{{ user.position }}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 hidden sm:table-cell">
              <div v-if="editingUser === user.id">
                <select v-model="editRole" class="input text-xs py-1" @change="updateRole(user.id)">
                  <option v-for="role in roles" :key="role.id" :value="role.name">{{ roleLabel(role.name) }}</option>
                </select>
              </div>
              <div v-else class="flex items-center gap-2">
                <span :class="['badge text-xs', roleClass(user.roles[0])]">{{ roleLabel(user.roles[0]) }}</span>
                <button @click="startEdit(user)" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-indigo-600 transition-all">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </button>
              </div>
            </td>
            <td class="px-6 py-4 hidden md:table-cell">
              <button @click="toggleActive(user)" :class="['w-10 h-5 rounded-full transition-all', user.is_active ? 'bg-green-500' : 'bg-gray-200']">
                <span :class="['block w-4 h-4 bg-white rounded-full shadow transition-transform mx-0.5', user.is_active ? 'translate-x-5' : 'translate-x-0']"></span>
              </button>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell">
              <span v-if="user.is_microsoft_connected" class="flex items-center gap-1.5 text-xs text-blue-600">
                <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                Connesso
              </span>
              <span v-else class="text-xs text-gray-400">Non connesso</span>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell text-xs text-gray-400">{{ formatDate(user.created_at) }}</td>
            <td class="px-6 py-4"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({ users: Array, roles: Array });

const editingUser = ref(null);
const editRole = ref('');

function startEdit(user) {
  editingUser.value = user.id;
  editRole.value = user.roles[0] || 'agent';
}

function updateRole(userId) {
  router.put(route('users.update', userId), { role: editRole.value }, {
    onSuccess: () => { editingUser.value = null; },
  });
}

function toggleActive(user) {
  router.put(route('users.update', user.id), { is_active: !user.is_active });
}

const roleLabels = { admin: 'Amministratore', manager: 'Manager', agent: 'Agente' };
const roleClasses = { admin: 'badge-red', manager: 'badge-purple', agent: 'badge-blue' };
function roleLabel(r) { return roleLabels[r] || r; }
function roleClass(r) { return roleClasses[r] || 'badge-gray'; }

function formatDate(date) {
  return new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(date));
}
</script>
