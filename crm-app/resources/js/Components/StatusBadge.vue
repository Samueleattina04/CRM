<template>
  <span :class="['badge text-xs', badgeClass]">{{ label }}</span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: String,
});

const config = {
  lead: { class: 'badge-blue', label: 'Lead' },
  prospect: { class: 'badge-yellow', label: 'Prospect' },
  active: { class: 'badge-green', label: 'Attivo' },
  inactive: { class: 'badge-gray', label: 'Inattivo' },
  churned: { class: 'badge-red', label: 'Perso' },
};

const badgeClass = computed(() => config[props.status]?.class || 'badge-gray');
const label = computed(() => config[props.status]?.label || props.status);
</script>
