<template>
  <div class="flex items-center gap-3">
    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
      <span class="text-sm">{{ iconEmoji }}</span>
    </div>
    <a v-if="href" :href="href" :target="external ? '_blank' : undefined" class="text-sm text-indigo-600 hover:text-indigo-700 truncate">{{ value }}</a>
    <span v-else class="text-sm text-gray-700 truncate">{{ value }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  icon: String,
  value: String,
  href: String,
  external: Boolean,
});

const emojiMap = { email: '📧', phone: '📞', mobile: '📱', web: '🌐', linkedin: '💼' };
const iconEmoji = computed(() => emojiMap[props.icon] || '📌');
</script>
