<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Pencil, Trash } from 'lucide-vue-next';

type Candidate = {
  id: string;
  name: string;
  party?: string;
  platform?: string;
  department?: string;
  position?: string;
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/' },
  { title: 'Candidates', href: '/admin/candidates' },
];

const search = ref('');
const filterPosition = ref<string | null>(null);

const sampleCandidates = ref<Candidate[]>([
  { id: 'C-001', name: 'Ava Santos', party: 'Unity', platform: 'Student Welfare', department: 'Computer Science', position: 'President' },
  { id: 'C-002', name: 'Liam Reyes', party: 'Progress', platform: 'Academic Reform', department: 'Engineering', position: 'Vice President' },
  { id: 'C-003', name: 'Maya Lopez', party: 'Forward', platform: 'Green Campus', department: 'Business', position: 'Secretary' },
  { id: 'C-004', name: 'Noah Cruz', party: 'Unity', platform: 'Transparency', department: 'Computer Science', position: 'Treasurer' },
  { id: 'C-005', name: 'Zoe Ramirez', party: 'Progress', platform: 'Student Activities', department: 'Engineering', position: 'Auditor' },
]);

const positions = computed(() => Array.from(new Set(sampleCandidates.value.map(c => c.position).filter(Boolean))) as string[]);

/* Pagination (UI-only) */
const currentPage = ref(1);
const perPage = 5;
const filteredSource = computed(() =>
  sampleCandidates.value.filter((c) => {
    const q = search.value.trim().toLowerCase();
    if (q && ![c.name, c.id].join(' ').toLowerCase().includes(q)) return false;
    if (filterPosition.value && c.position !== filterPosition.value) return false;
    return true;
  }),
);
const totalPages = computed(() => Math.max(1, Math.ceil(filteredSource.value.length / perPage)));

const pageCandidates = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredSource.value.slice(start, start + perPage);
});

function goToPage(page: number) {
  if (page < 1 || page > totalPages.value) return;
  currentPage.value = page;
}
function prevPage() { goToPage(currentPage.value - 1); }
function nextPage() { goToPage(currentPage.value + 1); }

/* Modal UI (UI-only) */
const showModal = ref(false);
const modalMode = ref<'view' | 'edit' | 'create'>('view');
const activeCandidate = reactive<Candidate>({
  id: '',
  name: '',
  party: '',
  platform: '',
  department: '',
  position: '',
});

function initials(name = '') {
  return name.split(' ').map(s => s[0]).slice(0,2).join('').toUpperCase();
}

function openCreate() {
  modalMode.value = 'create';
  Object.assign(activeCandidate, { id: '', name: '', party: '', platform: '', department: '', position: '' });
  showModal.value = true;
}

function openView(c: Candidate) {
  modalMode.value = 'view';
  Object.assign(activeCandidate, c);
  showModal.value = true;
}

function openEdit(c: Candidate) {
  modalMode.value = 'edit';
  Object.assign(activeCandidate, c);
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

/* UI-only save (updates local list) */
function saveCandidate() {
  if (modalMode.value === 'edit') {
    const idx = sampleCandidates.value.findIndex(s => s.id === activeCandidate.id);
    if (idx >= 0) sampleCandidates.value[idx] = { ...activeCandidate };
  } else {
    const newId = activeCandidate.id || `C-${String(Math.floor(Math.random() * 900) + 100)}`;
    sampleCandidates.value.unshift({ ...activeCandidate, id: newId });
    currentPage.value = 1;
  }
  closeModal();
}

function deleteCandidate(id: string) {
  // UI-only: local removal
  sampleCandidates.value = sampleCandidates.value.filter(c => c.id !== id);
  if (currentPage.value > totalPages.value) currentPage.value = totalPages.value;
}
</script>

<template>
  <Head title="Candidate Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <!-- Page header -->
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Candidate Management</h1>
        <p class="text-gray-600 mt-1">Manage candidates and their details for elections.</p>
      </div>

      <!-- Controls -->
      <section class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-slate-100">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
          <div class="flex items-center flex-1 bg-slate-50 rounded-lg px-3 py-2 gap-2">
            <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
            </svg>
            <input v-model="search" type="search" placeholder="Search name or candidate ID" class="bg-transparent w-full text-sm placeholder:text-gray-400 focus:outline-none" />
          </div>

          <div class="flex gap-2 items-center">
            <select v-model="filterPosition" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
              <option :value="null">All Positions</option>
              <option v-for="p in positions" :key="p" :value="p">{{ p }}</option>
            </select>

            <button @click="openCreate" class="inline-flex items-center gap-2 bg-slate-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-700 transition">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Add Candidate
            </button>
          </div>
        </div>
      </section>

      <!-- Table -->
      <section class="bg-white rounded-xl shadow-sm p-4 border border-slate-100">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-100">
            <thead>
              <tr class="text-left text-sm text-slate-600">
                <th class="py-3 pr-4 font-semibold">Candidate</th>
                <th class="py-3 pr-4 font-semibold">Position</th>
                <th class="py-3 pr-4 font-semibold">Department</th>
                <th class="py-3 pr-4 font-semibold">Partylist</th>
                <th class="py-3 pr-4 font-semibold">Platform</th>
                <th class="py-3 pr-4 font-semibold">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
              <tr v-for="c in pageCandidates" :key="c.id" class="hover:bg-slate-50 transition">
                <td class="py-3 pr-4 flex items-center gap-3">
                  <div class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-100 text-slate-700 font-medium">
                    {{ initials(c.name) }}
                  </div>
                  <div class="min-w-0">
                    <div class="font-medium text-slate-800 truncate max-w-xs">{{ c.name }}</div>
                    <div class="text-xs text-slate-500">{{ c.id }}</div>
                  </div>
                </td>

                <td class="py-3 pr-4">{{ c.position }}</td>
                <td class="py-3 pr-4 max-w-xs truncate">{{ c.department }}</td>
                <td class="py-3 pr-4 max-w-xs truncate">{{ c.party }}</td>
                <td class="py-3 pr-4 max-w-xs truncate">{{ c.platform }}</td>

                <td class="py-3 pr-4">
                  <div class="flex gap-2">
                    <button @click="openView(c)" class="inline-flex items-center gap-2 px-3 py-1 rounded-md text-sm bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                      View
                    </button>

                    <button @click="openEdit(c)" class="inline-flex items-center gap-2 px-3 py-1 rounded-md text-sm bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                      <Pencil class="w-4 h-4" />
                      Edit
                    </button>

                    <button @click="deleteCandidate(c.id)" class="inline-flex items-center gap-2 px-3 py-1 rounded-md text-sm bg-red-50 text-red-700 hover:bg-red-100 transition">
                      <Trash class="w-4 h-4" />
                      Delete
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="filteredSource.length === 0">
                <td class="py-10 text-center text-slate-500" colspan="6">
                  <div class="space-y-2">
                    <div class="text-lg font-medium">No candidates yet</div>
                    <div class="text-sm text-slate-500">Add candidates using the "Add Candidate" button</div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="mt-4 flex items-center justify-end gap-2">
          <nav class="inline-flex items-center gap-1" role="navigation" aria-label="Pagination">
            <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1 rounded-md text-sm border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 disabled:opacity-50">Previous</button>

            <button
              v-for="p in totalPages"
              :key="p"
              @click="goToPage(p)"
              :class="['px-3 py-1 rounded-md text-sm border', currentPage === p ? 'bg-slate-800 text-white border-transparent' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50']"
            >
              {{ p }}
            </button>

            <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1 rounded-md text-sm border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 disabled:opacity-50">Next</button>
          </nav>
        </div>
      </section>
    </div>

    <!-- Modal (UI only) -->
    <div v-if="showModal" class="fixed inset-0 z-40 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/40" @click="closeModal"></div>

      <div class="relative bg-white rounded-xl shadow-lg w-full max-w-lg mx-4 p-6 z-50">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="text-lg font-semibold text-slate-900">
              {{ modalMode === 'create' ? 'Add Candidate' : modalMode === 'edit' ? 'Edit Candidate' : 'Candidate Details' }}
            </h3>
            <p class="text-sm text-gray-600 mt-1">Candidate information (UI only).</p>
          </div>

          <button @click="closeModal" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveCandidate" class="mt-4 grid grid-cols-1 gap-3">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
            <input v-model="activeCandidate.name" :disabled="modalMode === 'view'" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Candidate ID</label>
            <input v-model="activeCandidate.id" :disabled="modalMode === 'view'" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Department</label>
              <input v-model="activeCandidate.department" :disabled="modalMode === 'view'" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Position</label>
              <input v-model="activeCandidate.position" :disabled="modalMode === 'view'" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Partylist</label>
            <input v-model="activeCandidate.party" :disabled="modalMode === 'view'" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Platform</label>
            <input v-model="activeCandidate.platform" :disabled="modalMode === 'view'" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          </div>

          <div class="mt-4 flex justify-end gap-2">
            <button type="button" @click="closeModal" class="px-4 py-2 rounded-md bg-slate-100 text-slate-700 text-sm hover:bg-slate-200">Close</button>
            <button v-if="modalMode !== 'view'" type="submit" class="px-4 py-2 rounded-md bg-slate-800 text-white text-sm hover:bg-slate-700">Save</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>