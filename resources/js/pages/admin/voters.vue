// ...existing code...
<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

type Voter = {
  id: string;
  name: string;
  email?: string;
  course?: string;
  year?: string;
  status?: 'Active' | 'Inactive';
};

const breadcrumbs: BreadcrumbItem[] = [
  
  { title: 'Voters', href: '/admin/voters' },
];

/* UI state (UI-only, no backend calls) */
const search = ref('');
const filterCourse = ref<string | null>(null);
const filterYear = ref<string | null>(null);

/* Sample static rows (placeholders) */
const sampleVoters = ref<Voter[]>([
  { id: 'V-2024001', name: 'Ana Reyes', email: 'ana.reyes@example.edu', course: 'BSIT', year: '2', status: 'Active' },
  { id: 'V-2024002', name: 'Jon Tan', email: 'jon.tan@example.edu', course: 'BSIS', year: '3', status: 'Inactive' },
  { id: 'V-2024003', name: 'Liza Santos', email: 'liza.santos@example.edu', course: 'BSIT', year: '1', status: 'Active' },
]);

/* Pagination (UI-only) */
const currentPage = ref(1);
const perPage = 5;
const filteredSource = computed(() =>
  sampleVoters.value.filter((v) => {
    const q = search.value.trim().toLowerCase();
    if (q && ![v.name, v.id, v.email].join(' ').toLowerCase().includes(q)) return false;
    if (filterCourse.value && v.course !== filterCourse.value) return false;
    if (filterYear.value && v.year !== filterYear.value) return false;
    return true;
  }),
);
const totalPages = computed(() => Math.max(1, Math.ceil(filteredSource.value.length / perPage)));
const pageVoters = computed(() => {
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
const activeVoter = reactive<Voter>({
  id: '',
  name: '',
  email: '',
  course: '',
  year: '',
  status: 'Active',
});

function openCreate() {
  modalMode.value = 'create';
  Object.assign(activeVoter, { id: '', name: '', email: '', course: '', year: '', status: 'Active' });
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

/* UI-only save/delete (modify local list only) */
function saveVoter() {
  if (modalMode.value === 'edit') {
    const idx = sampleVoters.value.findIndex(s => s.id === activeVoter.id);
    if (idx >= 0) sampleVoters.value[idx] = { ...activeVoter };
  } else {
    const newId = activeVoter.id || `V-${String(Math.floor(Math.random() * 900000) + 1000)}`;
    sampleVoters.value.unshift({ ...activeVoter, id: newId });
    currentPage.value = 1;
  }
  closeModal();
}

</script>

<template>
  <Head title="Voter Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Voter Management</h1>
        <p class="text-gray-600 mt-1">Manage all registered voters in the system.</p>
      </div>

      <!-- Search & Filters -->
      <section class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
          <div class="flex items-center flex-1 bg-slate-50 rounded-lg px-3 py-2 gap-2">
            <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
            </svg>
            <input v-model="search" type="search" placeholder="Search voter by name, email or ID" class="bg-transparent w-full text-sm placeholder:text-gray-400 focus:outline-none" />
          </div>

          <div class="flex gap-2 items-center">
            <select v-model="filterCourse" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
              <option :value="null">All Courses</option>
              <option value="BSIT">BSIT</option>
              <option value="BSIS">BSIS</option>
            </select>

            <select v-model="filterYear" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
              <option :value="null">All Year Levels</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
            </select>
          </div>
        </div>
      </section>

      <!-- Table -->
      <section class="bg-white rounded-xl shadow-md p-4 border border-slate-200">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-100">
            <thead>
              <tr class="text-left text-sm text-slate-600">
                <th class="py-3 px-4 font-semibold">SchoolID</th>
                <th class="py-3 px-4 font-semibold">Name</th>
                <th class="py-3 px-4 font-semibold">Email</th>
                <th class="py-3 px-4 font-semibold">Course / Year</th>
                <th class="py-3 px-4 font-semibold">Status</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
              <tr v-for="v in pageVoters" :key="v.id" class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 font-medium text-slate-800">{{ v.id }}</td>

                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-100 text-slate-700 font-medium">
                      {{ v.name.split(' ').map(n=>n[0]).slice(0,2).join('').toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                      <div class="font-medium text-slate-800 truncate max-w-xs">{{ v.name }}</div>
                      <div class="text-xs text-slate-500">Student</div>
                    </div>
                  </div>
                </td>

                <td class="py-3 px-4 max-w-xs truncate">{{ v.email }}</td>

                <td class="py-3 px-4 max-w-xs truncate">{{ v.course }} / {{ v.year }}</td>

                <td class="py-3 px-4">
                  <span
                    v-if="v.status === 'Active'"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                  >
                    <span class="w-2 h-2 rounded-full mr-2 bg-green-500"></span>
                    Active
                  </span>

                  <span
                    v-else
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700"
                  >
                    <span class="w-2 h-2 rounded-full mr-2 bg-slate-400"></span>
                    Inactive
                  </span>
                </td>
              </tr>

             
            </tbody>
          </table>
        </div>

        <!-- Pagination (UI-only) -->
        <div class="mt-4 flex items-center justify-end gap-2">
          <nav class="inline-flex items-center gap-2" role="navigation" aria-label="Pagination">
            <button @click="prevPage" type="button" class="px-3 py-1 rounded-md text-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-100">Previous</button>

            <button
              v-for="p in totalPages"
              :key="p"
              @click="goToPage(p)"
              :class="['px-3 py-1 rounded-md text-sm border', currentPage === p ? 'bg-slate-800 text-white border-transparent' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100']"
            >
              {{ p }}
            </button>

            <button @click="nextPage" type="button" class="px-3 py-1 rounded-md text-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-100">Next</button>
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
              {{ modalMode === 'create' ? 'Add Voter' : modalMode === 'edit' ? 'Edit Voter' : 'Voter Details' }}
            </h3>
            <p class="text-sm text-gray-600 mt-1">Voter information (UI only).</p>
          </div>

          <button @click="closeModal" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveVoter" class="mt-4 grid grid-cols-1 gap-3">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
            <input v-model="activeVoter.name" :disabled="modalMode === 'view'" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">School ID</label>
            <input v-model="activeVoter.id" :disabled="modalMode === 'view'" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input v-model="activeVoter.email" :disabled="modalMode === 'view'" type="email" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Course</label>
              <select v-model="activeVoter.course" :disabled="modalMode === 'view'" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <option value="">Select course</option>
                <option value="BSIT">BSIT</option>
                <option value="BSIS">BSIS</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Year</label>
              <select v-model="activeVoter.year" :disabled="modalMode === 'view'" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <option value="">Select year</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
            <select v-model="activeVoter.status" :disabled="modalMode === 'view'" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
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
