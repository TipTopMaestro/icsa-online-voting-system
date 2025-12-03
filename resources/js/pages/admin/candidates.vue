<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

// Interfaces
interface User {
  id: number;
  name: string;
  email: string;
  role: string;
}

interface Election {
  id: number;
  title: string;
}

interface Position {
  id: number;
  name: string;
  election_id: number;
}

interface Candidate {
  id: number;
  user_id: number;
  election_id: number;
  position_id: number;
  partylist: string;
  platform: string;
  photo: string;
  course: string;
  year_level: string;
  section: string;
  votes_count: number;
  created_at: string;
  updated_at: string;
  user: User;
  election: Election;
  position: Position;
}

interface PaginatedCandidates {
  data: Candidate[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

interface Filters {
  search?: string;
  election_id?: number;
  position_id?: number;
  partylist?: string;
  course?: string;
  year_level?: string;
}

// Props
const props = defineProps<{
  candidates: PaginatedCandidates;
  elections: Election[];
  positions: Position[];
  filters: Filters;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Candidates', href: '/admin/candidates' },
];

// State
const search = ref(props.filters.search || '');
const filterElection = ref<number | null>(props.filters.election_id || null);
const filterPosition = ref<number | null>(props.filters.position_id || null);
const filterPartylist = ref(props.filters.partylist || '');
const filterCourse = ref<string | null>(props.filters.course || null);
const filterYear = ref<string | null>(props.filters.year_level || null);

// Modals
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showViewModal = ref(false);
const selectedCandidate = ref<Candidate | null>(null);

// Forms - simplified without Inertia useForm
const createFormData = ref({
  name: '',
  email: '',
  election_id: null as number | null,
  position_id: null as number | null,
  partylist: '',
  platform: '',
  course: 'BSIT' as 'BSIT' | 'BSIS',
  year_level: '1',
  section: '',
});

const createFormPhoto = ref<File | null>(null);
const createFormProcessing = ref(false);
const createFormErrors = ref<Record<string, string>>({});

const editForm = useForm({
  name: '',
  election_id: null as number | null,
  position_id: null as number | null,
  partylist: '',
  platform: '',
  photo: null as File | null,
  course: 'BSIT' as 'BSIT' | 'BSIS',
  year_level: '1',
  section: '',
});

// Computed properties to filter positions based on selected election
const availablePositionsForCreate = computed(() => {
  if (!createFormData.value.election_id) {
    return [];
  }
  return props.positions.filter(position => position.election_id === createFormData.value.election_id);
});

const availablePositionsForEdit = computed(() => {
  if (!editForm.election_id) {
    return [];
  }
  return props.positions.filter(position => position.election_id === editForm.election_id);
});

// Watchers to reset position when election changes
watch(() => createFormData.value.election_id, (newElectionId) => {
  createFormData.value.position_id = null;
});

watch(() => editForm.election_id, (newElectionId) => {
  editForm.position_id = null;
});

// Methods
function applyFilters() {
  const params = {
    search: search.value || undefined,
    election_id: filterElection.value || undefined,
    position_id: filterPosition.value || undefined,
    partylist: filterPartylist.value || undefined,
    course: filterCourse.value || undefined,
    year_level: filterYear.value || undefined,
  };

  router.get('/admin/candidates', params, {
    preserveState: true,
    preserveScroll: true,
  });
}

function clearFilters() {
  search.value = '';
  filterElection.value = null;
  filterPosition.value = null;
  filterPartylist.value = '';
  filterCourse.value = null;
  filterYear.value = null;
  router.get('/admin/candidates');
}

function openCreateModal() {
  createFormData.value = {
    name: '',
    email: '',
    election_id: null,
    position_id: null,
    partylist: '',
    platform: '',
    course: 'BSIT',
    year_level: '1',
    section: '',
  };
  createFormPhoto.value = null;
  createFormErrors.value = {};
  showCreateModal.value = true;
}

function closeCreateModal() {
  showCreateModal.value = false;
  createFormData.value = {
    name: '',
    email: '',
    election_id: null,
    position_id: null,
    partylist: '',
    platform: '',
    course: 'BSIT',
    year_level: '1',
    section: '',
  };
  createFormPhoto.value = null;
  createFormErrors.value = {};
}

function handlePhotoCreate(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    createFormPhoto.value = target.files[0];
  }
}

function submitCreate() {
  createFormProcessing.value = true;
  createFormErrors.value = {};

  const formData = new FormData();
  formData.append('name', createFormData.value.name);
  formData.append('email', createFormData.value.email);
  formData.append('election_id', String(createFormData.value.election_id || ''));
  formData.append('position_id', String(createFormData.value.position_id || ''));
  formData.append('partylist', createFormData.value.partylist);
  formData.append('platform', createFormData.value.platform);
  formData.append('course', createFormData.value.course);
  formData.append('year_level', createFormData.value.year_level);
  formData.append('section', createFormData.value.section || '');
  
  if (createFormPhoto.value) {
    formData.append('photo', createFormPhoto.value);
  }

  // Get CSRF token from the page
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  
  console.log('Submitting with CSRF:', csrfToken);

  fetch('/admin/candidates', {
    method: 'POST',
    body: formData,
    headers: {
      'X-CSRF-TOKEN': csrfToken || '',
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    },
    credentials: 'same-origin',
  })
    .then(response => {
      console.log('Response status:', response.status);
      if (!response.ok) {
        return response.json().then(data => {
          throw data;
        });
      }
      return response.json();
    })
    .then(() => {
      createFormProcessing.value = false;
      closeCreateModal();
      // Reload the page to show new candidate
      router.reload();
    })
    .catch(error => {
      createFormProcessing.value = false;
      console.error('Create error:', error);
      if (error.errors) {
        createFormErrors.value = error.errors;
      } else {
        alert('Failed to create candidate: ' + (error.message || 'Unknown error'));
      }
    });
}

function openEditModal(candidate: Candidate) {
  selectedCandidate.value = candidate;
  editForm.name = candidate.user.name;
  editForm.election_id = candidate.election_id;
  editForm.position_id = candidate.position_id;
  editForm.partylist = candidate.partylist;
  editForm.platform = candidate.platform;
  editForm.course = candidate.course as 'BSIT' | 'BSIS';
  editForm.year_level = candidate.year_level;
  editForm.section = candidate.section;
  editForm.photo = null;
  showEditModal.value = true;
}

function closeEditModal() {
  showEditModal.value = false;
  selectedCandidate.value = null;
  editForm.reset();
  editForm.clearErrors();
}

function handlePhotoEdit(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    editForm.photo = target.files[0];
  }
}

function submitEdit() {
  if (!selectedCandidate.value) return;

  editForm.processing = true;
  editForm.clearErrors();

  const formData = new FormData();
  formData.append('_method', 'PUT');
  formData.append('name', editForm.name);
  formData.append('election_id', String(editForm.election_id || ''));
  formData.append('position_id', String(editForm.position_id || ''));
  formData.append('partylist', editForm.partylist);
  formData.append('platform', editForm.platform);
  formData.append('course', editForm.course);
  formData.append('year_level', editForm.year_level);
  formData.append('section', editForm.section || '');
  
  if (editForm.photo) {
    formData.append('photo', editForm.photo);
  }

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  fetch(`/admin/candidates/${selectedCandidate.value.id}`, {
    method: 'POST',
    body: formData,
    headers: {
      'X-CSRF-TOKEN': csrfToken || '',
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    },
    credentials: 'same-origin',
  })
    .then(response => {
      if (!response.ok) {
        return response.json().then(data => {
          throw data;
        });
      }
      return response.json();
    })
    .then(() => {
      editForm.processing = false;
      closeEditModal();
      router.reload();
    })
    .catch(error => {
      editForm.processing = false;
      if (error.errors) {
        Object.keys(error.errors).forEach(key => {
          editForm.setError(key as any, error.errors[key][0]);

        });
      } else {
        alert('Failed to update candidate: ' + (error.message || 'Unknown error'));
      }
    });
}

function openDeleteModal(candidate: Candidate) {
  selectedCandidate.value = candidate;
  showDeleteModal.value = true;
}

function closeDeleteModal() {
  showDeleteModal.value = false;
  selectedCandidate.value = null;
}

function confirmDelete() {
  if (!selectedCandidate.value) return;

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  fetch(`/admin/candidates/${selectedCandidate.value.id}`, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': csrfToken || '',
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    },
    credentials: 'same-origin',
  })
    .then(response => {
      if (!response.ok) {
        return response.json().then(data => {
          throw data;
        });
      }
      return response.json();
    })
    .then(() => {
      closeDeleteModal();
      router.reload();
    })
    .catch(error => {
      alert('Failed to delete candidate: ' + (error.message || 'Unknown error'));
    });
}

function openViewModal(candidate: Candidate) {
  selectedCandidate.value = candidate;
  showViewModal.value = true;
}

function closeViewModal() {
  showViewModal.value = false;
  selectedCandidate.value = null;
}

function goToPage(page: number) {
  if (page < 1 || page > props.candidates.last_page) return;
  router.get('/admin/candidates', {
    ...props.filters,
    page,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
}

function getPhotoUrl(photo: string): string {
  return `/storage/candidates/${photo}`;
}
</script>

<template>
  <Head title="Candidate Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">Candidate Management</h1>
          <p class="text-gray-600 mt-1">Manage election candidates in the system.</p>
        </div>
        <button 
          @click="openCreateModal"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium"
        >
          + Add Candidate
        </button>
      </div>

      <!-- Search & Filters -->
      <section class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-slate-200">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="lg:col-span-3">
            <div class="flex items-center bg-slate-50 rounded-lg px-3 py-2 gap-2">
              <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
              </svg>
              <input 
                v-model="search" 
                @keyup.enter="applyFilters"
                type="search" 
                placeholder="Search by name or partylist" 
                class="bg-transparent w-full text-sm placeholder:text-gray-400 focus:outline-none" 
              />
            </div>
          </div>

          <select v-model="filterElection" @change="applyFilters" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
            <option :value="null">All Elections</option>
            <option v-for="election in props.elections" :key="election.id" :value="election.id">
              {{ election.title }}
            </option>
          </select>

          <select v-model="filterPosition" @change="applyFilters" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
            <option :value="null">All Positions</option>
            <option v-for="position in props.positions" :key="position.id" :value="position.id">
              {{ position.name }}
            </option>
          </select>

          <input 
            v-model="filterPartylist" 
            @keyup.enter="applyFilters"
            type="text" 
            placeholder="Filter by partylist"
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
          />

          <select v-model="filterCourse" @change="applyFilters" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
            <option :value="null">All Courses</option>
            <option value="BSIT">BSIT</option>
            <option value="BSIS">BSIS</option>
          </select>

          <select v-model="filterYear" @change="applyFilters" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
            <option :value="null">All Years</option>
            <option value="1">1st Year</option>
            <option value="2">2nd Year</option>
            <option value="3">3rd Year</option>
            <option value="4">4th Year</option>
          </select>

          <div class="flex gap-2">
            <button @click="applyFilters" class="flex-1 px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700 text-sm">
              Search
            </button>
            <button @click="clearFilters" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-900 border border-slate-300 rounded-lg">
              Clear
            </button>
          </div>
        </div>
      </section>

      <!-- Empty State -->
      <section v-if="props.candidates.data.length === 0" class="bg-white rounded-xl shadow-sm p-12 text-center border border-slate-200">
        <div class="text-slate-400 mb-4">
          <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-slate-900 mb-2">No candidates found</h3>
        <p class="text-slate-600 mb-4">Start by adding candidates for your elections.</p>
        <button 
          @click="openCreateModal"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
        >
          + Add Candidate
        </button>
      </section>

      <!-- Table -->
      <section v-else class="bg-white rounded-xl shadow-md p-4 border border-slate-200">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead>
              <tr class="text-left text-sm text-slate-600">
                <th class="py-3 px-4 font-semibold">Photo</th>
                <th class="py-3 px-4 font-semibold">Name</th>
                <th class="py-3 px-4 font-semibold">Position</th>
                <th class="py-3 px-4 font-semibold">Election</th>
                <th class="py-3 px-4 font-semibold">Partylist</th>
                <th class="py-3 px-4 font-semibold">Course/Year</th>
                <th class="py-3 px-4 font-semibold">Votes</th>
                <th class="py-3 px-4 font-semibold">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
              <tr v-for="candidate in props.candidates.data" :key="candidate.id" class="hover:bg-slate-50 transition">
                <td class="py-3 px-4">
                  <img 
                    :src="getPhotoUrl(candidate.photo)" 
                    :alt="candidate.user.name"
                    class="w-12 h-12 rounded-full object-cover border-2 border-slate-200"
                  />
                </td>

                <td class="py-3 px-4">
                  <div class="font-medium text-slate-800">{{ candidate.user.name }}</div>
                  <div class="text-xs text-slate-500">{{ candidate.user.email }}</div>
                </td>

                <td class="py-3 px-4">{{ candidate.position.name }}</td>

                <td class="py-3 px-4">
                  <div class="text-sm">{{ candidate.election.title }}</div>
                </td>

                <td class="py-3 px-4">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ candidate.partylist }}
                  </span>
                </td>

                <td class="py-3 px-4">
                  {{ candidate.course }} {{ candidate.year_level }}{{ candidate.section }}
                </td>

                <td class="py-3 px-4">
                  <span class="font-semibold text-slate-900">{{ candidate.votes_count }}</span>
                </td>

                <td class="py-3 px-4">
                  <div class="flex gap-2">
                    <button 
                      @click="openViewModal(candidate)"
                      class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md transition"
                      title="View details"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                      View
                    </button>
                    <button 
                      @click="openEditModal(candidate)"
                      class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-md transition"
                      title="Edit"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                      Edit
                    </button>
                    <button 
                      @click="openDeleteModal(candidate)"
                      class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 rounded-md transition"
                      title="Delete"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex items-center justify-between">
          <div class="text-sm text-slate-600">
            Showing {{ props.candidates.from ?? 0 }} to {{ props.candidates.to ?? 0 }} of {{ props.candidates.total }} candidates
          </div>
          <nav class="inline-flex items-center gap-2">
            <button 
              @click="goToPage(props.candidates.current_page - 1)"
              :disabled="props.candidates.current_page === 1"
              class="px-3 py-1 rounded-md text-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>

            <button
              v-for="page in props.candidates.last_page"
              :key="page"
              v-show="page === 1 || page === props.candidates.last_page || Math.abs(page - props.candidates.current_page) <= 1"
              @click="goToPage(page)"
              :class="[
                'px-3 py-1 rounded-md text-sm border',
                page === props.candidates.current_page
                  ? 'bg-slate-800 text-white border-transparent'
                  : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100'
              ]"
            >
              {{ page }}
            </button>

            <button 
              @click="goToPage(props.candidates.current_page + 1)"
              :disabled="props.candidates.current_page === props.candidates.last_page"
              class="px-3 py-1 rounded-md text-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </nav>
        </div>
      </section>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/40" @click="closeCreateModal"></div>

      <div class="relative bg-white rounded-xl shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 z-50">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-slate-900">Add New Candidate</h3>
            <p class="text-sm text-gray-600 mt-1">Fill in the candidate information</p>
          </div>
          <button @click="closeCreateModal" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitCreate" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Name *</label>
              <input 
                v-model="createFormData.name" 
                type="text" 
                required
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
              />
              <div v-if="createFormErrors.name" class="text-red-600 text-xs mt-1">{{ createFormErrors.name }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Email *</label>
              <input 
                v-model="createFormData.email" 
                type="email" 
                required
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
              />
              <div v-if="createFormErrors.email" class="text-red-600 text-xs mt-1">{{ createFormErrors.email }}</div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Election *</label>
              <select 
                v-model="createFormData.election_id" 
                required
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
              >
                <option :value="null">Select election</option>
                <option v-for="election in props.elections" :key="election.id" :value="election.id">
                  {{ election.title }}
                </option>
              </select>
              <div v-if="createFormErrors.election_id" class="text-red-600 text-xs mt-1">{{ createFormErrors.election_id }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Position *</label>
              <select 
                v-model="createFormData.position_id" 
                required
                :disabled="!createFormData.election_id"
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
              >
                <option :value="null">{{ createFormData.election_id ? 'Select position' : 'Select election first' }}</option>
                <option v-for="position in availablePositionsForCreate" :key="position.id" :value="position.id">
                  {{ position.name }}
                </option>
              </select>
              <div v-if="createFormErrors.position_id" class="text-red-600 text-xs mt-1">{{ createFormErrors.position_id }}</div>
              <p v-if="createFormData.election_id && availablePositionsForCreate.length === 0" class="text-amber-600 text-xs mt-1">
                 No positions available for this election. Please add positions first.
              </p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Partylist *</label>
            <input 
              v-model="createFormData.partylist" 
              type="text" 
              required
              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
            />
            <div v-if="createFormErrors.partylist" class="text-red-600 text-xs mt-1">{{ createFormErrors.partylist }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Platform *</label>
            <textarea 
              v-model="createFormData.platform" 
              required
              rows="4"
              placeholder="Minimum 50 characters"
              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
            ></textarea>
            <div v-if="createFormErrors.platform" class="text-red-600 text-xs mt-1">{{ createFormErrors.platform }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Photo * (JPG/PNG, max 2MB)</label>
            <input 
              @change="handlePhotoCreate"
              type="file" 
              accept="image/jpeg,image/png"
              required
              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
            />
            <div v-if="createFormErrors.photo" class="text-red-600 text-xs mt-1">{{ createFormErrors.photo }}</div>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Course *</label>
              <select 
                v-model="createFormData.course" 
                required
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
              >
                <option value="BSIT">BSIT</option>
                <option value="BSIS">BSIS</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Year *</label>
              <select 
                v-model="createFormData.year_level" 
                required
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
              >
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Section</label>
              <input 
                v-model="createFormData.section" 
                type="text" 
                placeholder="e.g., A"
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
              />
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t">
            <button 
              type="button" 
              @click="closeCreateModal" 
              class="px-4 py-2 rounded-md bg-slate-100 text-slate-700 text-sm hover:bg-slate-200"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="createFormProcessing"
              class="px-4 py-2 rounded-md bg-green-600 text-white text-sm hover:bg-green-700 disabled:opacity-50"
            >
              {{ createFormProcessing ? 'Creating...' : 'Create Candidate' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Modal (Similar structure, omitted for brevity - can be added) -->
    <div v-if="showEditModal && selectedCandidate" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/40" @click="closeEditModal"></div>

      <div class="relative bg-white rounded-xl shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 z-50">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-slate-900">Edit Candidate</h3>
            <p class="text-sm text-gray-600 mt-1">Update candidate information</p>
          </div>
          <button @click="closeEditModal" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitEdit" class="space-y-4">
          <!-- Current Photo -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Current Photo</label>
            <img 
              :src="getPhotoUrl(selectedCandidate.photo)" 
              :alt="selectedCandidate.user.name"
              class="w-24 h-24 rounded-lg object-cover border-2 border-slate-200"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Name *</label>
              <input 
                v-model="editForm.name" 
                type="text" 
                required
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
              />
              <div v-if="editForm.errors.name" class="text-red-600 text-xs mt-1">{{ editForm.errors.name }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">New Photo (optional)</label>
              <input 
                @change="handlePhotoEdit"
                type="file" 
                accept="image/jpeg,image/png"
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
              />
              <div v-if="editForm.errors.photo" class="text-red-600 text-xs mt-1">{{ editForm.errors.photo }}</div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Election *</label>
              <select 
                v-model="editForm.election_id" 
                required
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
              >
                <option :value="null">Select election</option>
                <option v-for="election in props.elections" :key="election.id" :value="election.id">
                  {{ election.title }}
                </option>
              </select>
              <div v-if="editForm.errors.election_id" class="text-red-600 text-xs mt-1">{{ editForm.errors.election_id }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Position *</label>
              <select 
                v-model="editForm.position_id" 
                required
                :disabled="!editForm.election_id"
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
              >
                <option :value="null">{{ editForm.election_id ? 'Select position' : 'Select election first' }}</option>
                <option v-for="position in availablePositionsForEdit" :key="position.id" :value="position.id">
                  {{ position.name }}
                </option>
              </select>
              <div v-if="editForm.errors.position_id" class="text-red-600 text-xs mt-1">{{ editForm.errors.position_id }}</div>
              <p v-if="editForm.election_id && availablePositionsForEdit.length === 0" class="text-amber-600 text-xs mt-1">
                ⚠️ No positions available for this election. Please add positions first.
              </p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Partylist *</label>
            <input 
              v-model="editForm.partylist" 
              type="text" 
              required
              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
            />
            <div v-if="editForm.errors.partylist" class="text-red-600 text-xs mt-1">{{ editForm.errors.partylist }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Platform *</label>
            <textarea 
              v-model="editForm.platform" 
              required
              rows="4"
              placeholder="Minimum 50 characters"
              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
            ></textarea>
            <div v-if="editForm.errors.platform" class="text-red-600 text-xs mt-1">{{ editForm.errors.platform }}</div>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Course *</label>
              <select 
                v-model="editForm.course" 
                required
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
              >
                <option value="BSIT">BSIT</option>
                <option value="BSIS">BSIS</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Year *</label>
              <select 
                v-model="editForm.year_level" 
                required
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
              >
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Section</label>
              <input 
                v-model="editForm.section" 
                type="text" 
                placeholder="e.g., A"
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
              />
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t">
            <button 
              type="button" 
              @click="closeEditModal" 
              class="px-4 py-2 rounded-md bg-slate-100 text-slate-700 text-sm hover:bg-slate-200"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="editForm.processing"
              class="px-4 py-2 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50"
            >
              {{ editForm.processing ? 'Updating...' : 'Update Candidate' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Modal -->
    <div v-if="showViewModal && selectedCandidate" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/40" @click="closeViewModal"></div>

      <div class="relative bg-white rounded-xl shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto p-6 z-50">
        <div class="flex items-start justify-between mb-6">
          <h3 class="text-xl font-semibold text-slate-900">Candidate Details</h3>
          <button @click="closeViewModal" class="text-slate-400 hover:text-slate-600">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="space-y-6">
          <!-- Photo and Basic Info -->
          <div class="flex items-start gap-6">
            <img 
              :src="getPhotoUrl(selectedCandidate.photo)" 
              :alt="selectedCandidate.user.name"
              class="w-32 h-32 rounded-lg object-cover border-2 border-slate-200"
            />
            <div class="flex-1">
              <h4 class="text-2xl font-bold text-slate-900">{{ selectedCandidate.user.name }}</h4>
              <p class="text-slate-600 mt-1">{{ selectedCandidate.user.email }}</p>
              <div class="flex gap-2 mt-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                  {{ selectedCandidate.partylist }}
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                  {{ selectedCandidate.course }} {{ selectedCandidate.year_level }}{{ selectedCandidate.section }}
                </span>
              </div>
            </div>
          </div>

          <!-- Divider -->
          <div class="border-t border-slate-200"></div>

          <!-- Election & Position Info -->
          <div class="grid grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-slate-500 mb-1">Election</label>
              <p class="text-lg font-semibold text-slate-900">{{ selectedCandidate.election.title }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-500 mb-1">Position</label>
              <p class="text-lg font-semibold text-slate-900">{{ selectedCandidate.position.name }}</p>
            </div>
          </div>

          <!-- Platform -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Platform / Agenda</label>
            <div class="bg-slate-50 rounded-lg p-4 text-slate-700 text-sm leading-relaxed">
              {{ selectedCandidate.platform }}
            </div>
          </div>

          <!-- Stats -->
          <div class="bg-green-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-green-800">Total Votes Received</span>
              <span class="text-3xl font-bold text-green-900">{{ selectedCandidate.votes_count }}</span>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
          <button 
            @click="closeViewModal" 
            class="px-4 py-2 rounded-md bg-slate-100 text-slate-700 text-sm hover:bg-slate-200"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/40" @click="closeDeleteModal"></div>

      <div class="relative bg-white rounded-xl shadow-lg w-full max-w-md p-6 z-50">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Delete Candidate</h3>
        
        <div v-if="selectedCandidate">
          <p class="text-sm text-slate-600 mb-4">Are you sure you want to delete this candidate?</p>
          <div class="bg-slate-50 rounded-lg p-3 mb-4">
            <div class="font-medium">{{ selectedCandidate.user.name }}</div>
            <div class="text-sm text-slate-600">{{ selectedCandidate.position.name }} - {{ selectedCandidate.election.title }}</div>
          </div>
        </div>

        <div class="flex justify-end gap-2">
          <button 
            @click="closeDeleteModal" 
            class="px-4 py-2 rounded-md bg-slate-100 text-slate-700 text-sm hover:bg-slate-200"
          >
            Cancel
          </button>
          <button 
            @click="confirmDelete"
            class="px-4 py-2 rounded-md bg-red-600 text-white text-sm hover:bg-red-700"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- View Modal (can be added similarly) -->

  </AppLayout>
</template>
