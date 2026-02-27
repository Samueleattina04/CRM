<template>
  <div class="card p-5">
    <div class="flex items-start justify-between">
      <div class="flex-1">
        <p class="text-sm font-medium text-gray-500">{{ title }}</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ value }}</p>
        <p v-if="sub" class="text-xs mt-1" :class="subColor">{{ sub }}</p>
      </div>
      <div :class="['w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0', iconBg]">
        <span class="text-lg">{{ iconEmoji }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: String,
  value: [String, Number],
  color: { type: String, default: 'indigo' },
  icon: String,
  sub: String,
});

const iconBgMap = {
  indigo: 'bg-indigo-100',
  blue: 'bg-blue-100',
  green: 'bg-green-100',
  orange: 'bg-orange-100',
  red: 'bg-red-100',
  purple: 'bg-purple-100',
};

const subColorMap = {
  indigo: 'text-indigo-600',
  blue: 'text-blue-600',
  green: 'text-green-600',
  orange: 'text-orange-600',
  red: 'text-red-600',
  purple: 'text-purple-600',
};

const emojiMap = {
  users: '👥', 'user-plus': '➕', bolt: '⚡', clock: '🕐',
  check: '✅', money: '💰', chart: '📊',
};

const iconBg = computed(() => iconBgMap[props.color] || 'bg-gray-100');
const subColor = computed(() => subColorMap[props.color] || 'text-gray-500');
const iconEmoji = computed(() => emojiMap[props.icon] || '📊');
</script>
