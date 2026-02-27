<template>
  <AppLayout title="Nuovo Cliente">
    <Head title="Nuovo Cliente" />

    <div class="max-w-3xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <Link :href="route('customers.index')" class="btn-ghost">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
          Indietro
        </Link>
        <h2 class="text-xl font-bold text-gray-900">Nuovo Cliente</h2>
      </div>

      <CustomerForm :form="form" :agents="agents" :tags="tags" :errors="form.errors" @submit="submit" />
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CustomerForm from '@/Components/CustomerForm.vue';

const props = defineProps({ agents: Array, tags: Array });

const form = useForm({
  first_name: '', last_name: '', email: '', phone: '', mobile: '',
  company: '', position: '', website: '', linkedin: '',
  address: '', city: '', country: 'Italy', postal_code: '',
  status: 'lead', priority: 'medium', source: '', notes: '',
  annual_value: null, assigned_to: null, tags: [],
});

function submit() {
  form.post(route('customers.store'));
}
</script>
