<template>
  <AppLayout :title="`Modifica - ${customer.full_name}`">
    <Head :title="`Modifica ${customer.full_name}`" />

    <div class="max-w-3xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <Link :href="route('customers.show', customer.id)" class="btn-ghost">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
          Indietro
        </Link>
        <h2 class="text-xl font-bold text-gray-900">Modifica: {{ customer.full_name }}</h2>
      </div>

      <CustomerForm :form="form" :agents="agents" :tags="tags" :errors="form.errors" @submit="submit" mode="edit" />
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CustomerForm from '@/Components/CustomerForm.vue';

const props = defineProps({ customer: Object, agents: Array, tags: Array });

const form = useForm({
  first_name: props.customer.first_name,
  last_name: props.customer.last_name,
  email: props.customer.email || '',
  phone: props.customer.phone || '',
  mobile: props.customer.mobile || '',
  company: props.customer.company || '',
  position: props.customer.position || '',
  website: props.customer.website || '',
  linkedin: props.customer.linkedin || '',
  address: props.customer.address || '',
  city: props.customer.city || '',
  country: props.customer.country || 'Italy',
  postal_code: props.customer.postal_code || '',
  status: props.customer.status,
  priority: props.customer.priority,
  source: props.customer.source || '',
  notes: props.customer.notes || '',
  annual_value: props.customer.annual_value || null,
  assigned_to: props.customer.assigned_to || null,
  tags: props.customer.tags?.map(t => t.id) || [],
});

function submit() {
  form.put(route('customers.update', props.customer.id));
}
</script>
