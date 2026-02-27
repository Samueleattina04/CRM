<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <aside
      :class="['fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-gray-900 transition-transform duration-300 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']"
    >
      <!-- Logo -->
      <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-700/50">
        <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <span class="text-white font-bold text-lg tracking-tight">CRM Pro</span>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <NavItem :href="route('dashboard')" icon="home" label="Dashboard" :active="isRoute('dashboard')" />
        <NavItem :href="route('customers.index')" icon="users" label="Clienti" :active="isRoute('customers.*')" />
        <NavItem :href="route('activities.index')" icon="lightning-bolt" label="Attività" :active="isRoute('activities.*')" />
        <NavItem :href="route('tasks.index')" icon="check-circle" label="Task & Reminder" :active="isRoute('tasks.*')" />

        <div class="pt-4 pb-2">
          <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Configurazione</p>
        </div>

        <NavItem v-if="isAdmin" :href="route('users.index')" icon="user-group" label="Utenti" :active="isRoute('users.*')" />
        <NavItem :href="route('profile')" icon="user-circle" label="Profilo" :active="isRoute('profile')" />
      </nav>

      <!-- User info bottom -->
      <div class="p-4 border-t border-gray-700/50">
        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 cursor-pointer group" @click="profileMenuOpen = !profileMenuOpen">
          <img :src="$page.props.auth.user.avatar_url" :alt="$page.props.auth.user.name" class="w-8 h-8 rounded-full object-cover ring-2 ring-indigo-500/30" />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-white truncate">{{ $page.props.auth.user.name }}</p>
            <p class="text-xs text-gray-400 truncate">{{ userRole }}</p>
          </div>
          <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
          </svg>
        </div>

        <!-- Dropdown -->
        <Transition name="fade">
          <div v-if="profileMenuOpen" class="mt-2 py-1 bg-gray-800 rounded-lg border border-gray-700">
            <Link :href="route('profile')" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-700 rounded-md mx-1">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
              Il mio profilo
            </Link>
            <button @click="logout" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-gray-700 rounded-md mx-1">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
              Esci
            </button>
          </div>
        </Transition>
      </div>
    </aside>

    <!-- Overlay mobile -->
    <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false" />

    <!-- Main content -->
    <div class="flex-1 lg:pl-64 flex flex-col min-h-screen">
      <!-- Top bar -->
      <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-sm border-b border-gray-100 px-4 lg:px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <button class="lg:hidden p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg" @click="sidebarOpen = !sidebarOpen">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
          </button>
          <h1 class="text-lg font-semibold text-gray-800">{{ title }}</h1>
        </div>

        <div class="flex items-center gap-3">
          <!-- Outlook sync button -->
          <button
            v-if="$page.props.auth.user.is_microsoft_connected"
            @click="syncOutlook"
            :disabled="syncing"
            class="btn-ghost text-xs"
            title="Sincronizza email Outlook"
          >
            <svg class="w-4 h-4" :class="syncing ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            {{ syncing ? 'Sync...' : 'Sync Email' }}
          </button>

          <!-- Quick add task -->
          <button @click="$emit('openQuickTask')" class="btn-ghost text-xs hidden sm:flex">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Nuovo Task
          </button>

          <!-- User avatar -->
          <img :src="$page.props.auth.user.avatar_url" class="w-8 h-8 rounded-full object-cover ring-2 ring-indigo-500/20" />
        </div>
      </header>

      <!-- Flash messages -->
      <Transition name="slide-up">
        <div v-if="flashMessage" class="mx-4 lg:mx-6 mt-4">
          <div
            :class="['flex items-center gap-3 p-4 rounded-xl text-sm font-medium', flashType === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200']"
          >
            <svg v-if="flashType === 'success'" class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <svg v-else class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ flashMessage }}
            <button @click="flashMessage = null" class="ml-auto p-0.5 hover:opacity-70">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>
        </div>
      </Transition>

      <!-- Page content -->
      <main class="flex-1 p-4 lg:p-6">
        <slot />
      </main>
    </div>

    <!-- Logout form -->
    <form ref="logoutForm" method="POST" :action="route('logout')" class="hidden">
      <input type="hidden" name="_token" :value="csrfToken" />
    </form>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import NavItem from '@/Components/NavItem.vue';

const props = defineProps({
  title: { type: String, default: '' },
});
defineEmits(['openQuickTask']);

const page = usePage();
const sidebarOpen = ref(false);
const profileMenuOpen = ref(false);
const flashMessage = ref(null);
const flashType = ref('success');
const syncing = ref(false);
const logoutForm = ref(null);
const csrfToken = computed(() => document.querySelector('meta[name="csrf-token"]')?.content || '');

const isAdmin = computed(() => {
  const roles = page.props.auth?.user?.roles || [];
  return roles.includes('admin') || roles.includes('manager');
});

const userRole = computed(() => {
  const roles = page.props.auth?.user?.roles || [];
  const roleMap = { admin: 'Amministratore', manager: 'Manager', agent: 'Agente' };
  return roleMap[roles[0]] || roles[0] || 'Utente';
});

function isRoute(pattern) {
  const current = route().current();
  if (pattern.endsWith('.*')) {
    const base = pattern.replace('.*', '');
    return current?.startsWith(base);
  }
  return current === pattern;
}

function logout() {
  logoutForm.value?.submit();
}

async function syncOutlook() {
  syncing.value = true;
  router.post(route('outlook.sync'), {}, {
    onFinish: () => { syncing.value = false; },
  });
}

watch(() => page.props.flash, (flash) => {
  if (flash?.success) {
    flashMessage.value = flash.success;
    flashType.value = 'success';
    setTimeout(() => { flashMessage.value = null; }, 5000);
  } else if (flash?.error) {
    flashMessage.value = flash.error;
    flashType.value = 'error';
    setTimeout(() => { flashMessage.value = null; }, 7000);
  }
}, { immediate: true, deep: true });
</script>
