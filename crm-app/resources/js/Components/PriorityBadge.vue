<template>
  <span :class="['badge text-xs', badgeClass]">{{ label }}</span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({ priority: String });

const config = {
  low: { class: 'badge-gray', label: 'Bassa' },
  medium: { class: 'badge-blue', label: 'Media' },
  high: { class: 'badge-yellow', label: 'Alta' },
  urgent: { class: 'badge-red', label: 'Urgente' },
};

const badgeClass = computed(() => config[props.priority]?.class || 'badge-gray');
const label = computed(() => config[props.priority]?.label || props.priority);
</script>
