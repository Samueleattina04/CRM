<template>
  <Link
    :href="href"
    :class="[
      'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200',
      active
        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25'
        : 'text-gray-400 hover:text-white hover:bg-gray-800'
    ]"
  >
    <component :is="iconComponent" class="w-5 h-5 flex-shrink-0" />
    {{ label }}
    <span v-if="badge" class="ml-auto bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[1.25rem] text-center">{{ badge }}</span>
  </Link>
</template>

<script setup>
import { computed } from 'vue';
import {
  HomeIcon, UsersIcon, BoltIcon, CheckCircleIcon,
  UserGroupIcon, UserCircleIcon, Cog6ToothIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  href: String,
  icon: String,
  label: String,
  active: Boolean,
  badge: [String, Number],
});

const iconMap = {
  'home': HomeIcon,
  'users': UsersIcon,
  'lightning-bolt': BoltIcon,
  'check-circle': CheckCircleIcon,
  'user-group': UserGroupIcon,
  'user-circle': UserCircleIcon,
  'cog': Cog6ToothIcon,
};

const iconComponent = computed(() => iconMap[props.icon] || HomeIcon);
</script>
