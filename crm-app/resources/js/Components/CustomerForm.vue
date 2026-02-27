<template>
  <form @submit.prevent="$emit('submit')" class="space-y-6">
    <!-- Sezione Anagrafica -->
    <div class="card p-6">
      <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <span class="w-6 h-6 bg-indigo-100 rounded-md flex items-center justify-center text-xs">👤</span>
        Informazioni Personali
      </h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="form-group">
          <label class="label">Nome *</label>
          <input v-model="form.first_name" type="text" class="input" :class="{ 'border-red-300': errors.first_name }" placeholder="Mario" />
          <p v-if="errors.first_name" class="text-red-500 text-xs mt-1">{{ errors.first_name }}</p>
        </div>
        <div class="form-group">
          <label class="label">Cognome *</label>
          <input v-model="form.last_name" type="text" class="input" placeholder="Rossi" />
        </div>
        <div class="form-group">
          <label class="label">Azienda</label>
          <input v-model="form.company" type="text" class="input" placeholder="Nome Azienda s.r.l." />
        </div>
        <div class="form-group">
          <label class="label">Ruolo</label>
          <input v-model="form.position" type="text" class="input" placeholder="CEO, Direttore Vendite..." />
        </div>
      </div>
    </div>

    <!-- Contatti -->
    <div class="card p-6">
      <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <span class="w-6 h-6 bg-blue-100 rounded-md flex items-center justify-center text-xs">📞</span>
        Contatti
      </h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="form-group sm:col-span-2">
          <label class="label">Email</label>
          <input v-model="form.email" type="email" class="input" :class="{ 'border-red-300': errors.email }" placeholder="mario@azienda.it" />
          <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
        </div>
        <div class="form-group">
          <label class="label">Telefono fisso</label>
          <input v-model="form.phone" type="tel" class="input" placeholder="+39 02 1234567" />
        </div>
        <div class="form-group">
          <label class="label">Cellulare</label>
          <input v-model="form.mobile" type="tel" class="input" placeholder="+39 333 1234567" />
        </div>
        <div class="form-group">
          <label class="label">Website</label>
          <input v-model="form.website" type="url" class="input" placeholder="https://www.azienda.it" />
        </div>
        <div class="form-group">
          <label class="label">LinkedIn</label>
          <input v-model="form.linkedin" type="url" class="input" placeholder="https://linkedin.com/in/..." />
        </div>
      </div>
    </div>

    <!-- Indirizzo -->
    <div class="card p-6">
      <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <span class="w-6 h-6 bg-green-100 rounded-md flex items-center justify-center text-xs">📍</span>
        Indirizzo
      </h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="form-group sm:col-span-2">
          <label class="label">Via / Indirizzo</label>
          <input v-model="form.address" type="text" class="input" placeholder="Via Roma, 1" />
        </div>
        <div class="form-group">
          <label class="label">Città</label>
          <input v-model="form.city" type="text" class="input" placeholder="Milano" />
        </div>
        <div class="form-group">
          <label class="label">CAP</label>
          <input v-model="form.postal_code" type="text" class="input" placeholder="20100" />
        </div>
        <div class="form-group">
          <label class="label">Paese</label>
          <input v-model="form.country" type="text" class="input" placeholder="Italy" />
        </div>
      </div>
    </div>

    <!-- CRM Settings -->
    <div class="card p-6">
      <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <span class="w-6 h-6 bg-purple-100 rounded-md flex items-center justify-center text-xs">⚙️</span>
        Configurazione CRM
      </h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="form-group">
          <label class="label">Stato *</label>
          <select v-model="form.status" class="input">
            <option value="lead">Lead</option>
            <option value="prospect">Prospect</option>
            <option value="active">Attivo</option>
            <option value="inactive">Inattivo</option>
            <option value="churned">Perso</option>
          </select>
        </div>
        <div class="form-group">
          <label class="label">Priorità *</label>
          <select v-model="form.priority" class="input">
            <option value="low">Bassa</option>
            <option value="medium">Media</option>
            <option value="high">Alta</option>
          </select>
        </div>
        <div class="form-group">
          <label class="label">Fonte</label>
          <input v-model="form.source" type="text" class="input" placeholder="LinkedIn, Referral, Web..." />
        </div>
        <div class="form-group">
          <label class="label">Valore Annuo (€)</label>
          <input v-model="form.annual_value" type="number" min="0" step="100" class="input" placeholder="10000" />
        </div>
        <div v-if="agents?.length" class="form-group">
          <label class="label">Assegnato a</label>
          <select v-model="form.assigned_to" class="input">
            <option :value="null">Non assegnato</option>
            <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
          </select>
        </div>
      </div>

      <!-- Tags -->
      <div v-if="tags?.length" class="mt-4">
        <label class="label">Tag</label>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="tag in tags"
            :key="tag.id"
            type="button"
            @click="toggleTag(tag.id)"
            :class="['px-3 py-1.5 text-xs font-medium rounded-full border transition-all', form.tags.includes(tag.id) ? 'border-transparent text-white' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300']"
            :style="form.tags.includes(tag.id) ? { backgroundColor: tag.color, borderColor: tag.color } : {}"
          >
            {{ tag.name }}
          </button>
        </div>
      </div>

      <!-- Notes -->
      <div class="mt-4 form-group">
        <label class="label">Note</label>
        <textarea v-model="form.notes" rows="4" class="input" placeholder="Note aggiuntive sul cliente..."></textarea>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-3">
      <Link :href="route('customers.index')" class="btn-secondary">Annulla</Link>
      <button type="submit" :disabled="form.processing" class="btn-primary">
        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        {{ mode === 'edit' ? 'Aggiorna Cliente' : 'Crea Cliente' }}
      </button>
    </div>
  </form>
</template>

<script setup>
const props = defineProps({
  form: Object,
  agents: Array,
  tags: Array,
  errors: Object,
  mode: { type: String, default: 'create' },
});

defineEmits(['submit']);

function toggleTag(id) {
  const idx = props.form.tags.indexOf(id);
  if (idx === -1) props.form.tags.push(id);
  else props.form.tags.splice(idx, 1);
}
</script>
