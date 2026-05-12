<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import Icon from '@/components/Icon.vue';

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
const showFilters = ref(false);

// Dropdown states and options for filters
const electionDropdownOpen = ref(false);
const positionDropdownOpen = ref(false);
const courseDropdownOpen = ref(false);
const yearDropdownOpen = ref(false);

const electionOptions = computed(() => [{ label: 'All Elections', value: null }, ...props.elections.map(e => ({ label: e.title, value: e.id }))]);
const positionOptions = computed(() => [{ label: 'All Positions', value: null }, ...props.positions.map(p => ({ label: p.name, value: p.id }))]);
const courseOptions = [
  { label: 'All Courses', value: null },
  { label: 'BSIT', value: 'BSIT' },
  { label: 'BSIS', value: 'BSIS' },
];
const yearOptions = [
  { label: 'All Years', value: null },
  { label: '1st Year', value: '1' },
  { label: '2nd Year', value: '2' },
  { label: '3rd Year', value: '3' },
  { label: '4th Year', value: '4' },
];

function selectElectionOption(opt: { label: string; value: number | null }) {
  filterElection.value = opt.value as number | null;
  electionDropdownOpen.value = false;
  applyFilters();
}

function selectPositionOption(opt: { label: string; value: number | null }) {
  filterPosition.value = opt.value as number | null;
  positionDropdownOpen.value = false;
  applyFilters();
}

function selectCourseOption(opt: { label: string; value: string | null }) {
  filterCourse.value = opt.value as string | null;
  courseDropdownOpen.value = false;
  applyFilters();
}

function selectYearOption(opt: { label: string; value: string | null }) {
  filterYear.value = opt.value as string | null;
  yearDropdownOpen.value = false;
  applyFilters();
}

function toggleElectionDropdown() {
  const next = !electionDropdownOpen.value;
  electionDropdownOpen.value = next;
  if (next) {
    positionDropdownOpen.value = false;
    courseDropdownOpen.value = false;
    yearDropdownOpen.value = false;
  }
}

function togglePositionDropdown() {
  const next = !positionDropdownOpen.value;
  positionDropdownOpen.value = next;
  if (next) {
    electionDropdownOpen.value = false;
    courseDropdownOpen.value = false;
    yearDropdownOpen.value = false;
  }
}

function toggleCourseDropdown() {
  const next = !courseDropdownOpen.value;
  courseDropdownOpen.value = next;
  if (next) {
    electionDropdownOpen.value = false;
    positionDropdownOpen.value = false;
    yearDropdownOpen.value = false;
  }
}

function toggleYearDropdown() {
  const next = !yearDropdownOpen.value;
  yearDropdownOpen.value = next;
  if (next) {
    electionDropdownOpen.value = false;
    positionDropdownOpen.value = false;
    courseDropdownOpen.value = false;
  }
}

// Computed
const activeFiltersCount = computed(() => {
  let count = 0;
  if (filterElection.value) count++;
  if (filterPosition.value) count++;
  if (filterPartylist.value) count++;
  if (filterCourse.value) count++;
  if (filterYear.value) count++;
  return count;
});

const hasActiveFilters = computed(() => {
  return search.value || activeFiltersCount.value > 0;
});

// Modals
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showViewModal = ref(false);
const showPasswordModal = ref(false);
const selectedCandidate = ref<Candidate | null>(null);
const generatedPassword = ref<string>('');

// Forms - refactored to use Inertia useForm for better CSRF and multipart support
const createForm = useForm({
  name: '',
  email: '',
  election_id: null as number | null,
  position_id: null as number | null,
  partylist: '',
  platform: '',
  course: 'BSIT' as 'BSIT' | 'BSIS',
  year_level: '1',
  section: '',
  photo: null as File | null,
});

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
  if (!createForm.election_id) {
    return [];
  }
  return props.positions.filter(position => position.election_id === createForm.election_id);
});

const availablePositionsForEdit = computed(() => {
  if (!editForm.election_id) {
    return [];
  }
  return props.positions.filter(position => position.election_id === editForm.election_id);
});

// Watchers to reset position when election changes
watch(() => createForm.election_id, (newVal, oldVal) => {
  if (oldVal && newVal !== oldVal) {
    createForm.position_id = null;
  }
});

watch(() => editForm.election_id, (newVal, oldVal) => {
  if (oldVal && newVal !== oldVal) {
    editForm.position_id = null;
  }
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
  createForm.reset();
  createForm.clearErrors();
  showCreateModal.value = true;
}

function closeCreateModal() {
  showCreateModal.value = false;
  createForm.reset();
  createForm.clearErrors();
}

function handlePhotoCreate(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    createForm.photo = target.files[0];
  }
}

function submitCreate() {
  createForm.post('/admin/candidates', {
    onSuccess: (page) => {
      // Access the generated password from the shared props (flash)
      const flash = page.props.flash as any;
      if (flash && flash.generated_password) {
        generatedPassword.value = flash.generated_password;
        showPasswordModal.value = true;
      }
      
      closeCreateModal();
    },
    onError: (errors) => {
      console.error('Create error:', errors);
    },
    forceFormData: true,
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

  // For file uploads in Laravel via PUT, we use POST with _method=PUT
  editForm.transform((data) => ({
    ...data,
    _method: 'PUT',
  })).post(`/admin/candidates/${selectedCandidate.value.id}`, {
    onSuccess: () => {
      closeEditModal();
    },
    onError: (errors) => {
      console.error('Edit error:', errors);
    },
    forceFormData: true,
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

  router.delete(`/admin/candidates/${selectedCandidate.value.id}`, {
    onSuccess: () => {
      closeDeleteModal();
    },
    onError: (errors) => {
      alert('Failed to delete candidate');
    }
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

function closePasswordModal() {
  showPasswordModal.value = false;
  generatedPassword.value = '';
}

function copyPassword() {
  navigator.clipboard.writeText(generatedPassword.value);
  alert('Password copied to clipboard!');
}
</script>

<template>
  <Head title="Candidate Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Candidate Management</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage election candidates in the system.</p>
        </div>
        <button 
          @click="openCreateModal"
          class="inline-flex items-center gap-2 px-4 py-2 bg-[#5A2D6F] hover:bg-[#4b255c] dark:bg-[#5A2D6F] dark:hover:bg-[#4b255c] text-white text-sm font-medium rounded-md transition-colors"
        >
          <Icon name="plus" class="h-4 w-4" />
          Add Candidate
        </button>
      </div>

      <!-- Modern Search & Filters -->
      <section :class="['bg-card rounded-xl shadow-sm border mb-6 relative transition-all duration-200', showFilters ? 'z-40 shadow-md' : 'z-10']">
        <!-- Search Bar -->
        <div class="p-4 relative z-20">
          <div class="flex items-center gap-3">
            <!-- Search Input -->
            <div class="flex-1 relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </div>
              <input 
                v-model="search" 
                @keyup.enter="applyFilters"
                type="search" 
                placeholder="Search candidates by name, email, or partylist..." 
                class="w-full pl-10 pr-4 py-2.5 text-sm bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary transition-all"
              />
            </div>

            <!-- Filter Toggle Button -->
            <button 
              @click="showFilters = !showFilters"
              :class="[
                'relative flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-all',
                hasActiveFilters 
                  ? 'bg-primary text-primary-foreground hover:bg-primary/90 dark:bg-purple-700 dark:hover:bg-purple-600' 
                  : 'bg-muted text-foreground hover:bg-muted/80 dark:bg-purple-950/40 dark:text-purple-200 dark:hover:bg-purple-900/50'
              ]"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
              </svg>
              <span>Filters</span>
              <span 
                v-if="activeFiltersCount > 0" 
                class="absolute -top-1 -right-1 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-blue-600 dark:bg-purple-500 rounded-full"
              >
                {{ activeFiltersCount }}
              </span>
              <svg 
                :class="['w-4 h-4 transition-transform', showFilters ? 'rotate-180' : '']" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>

            <!-- Quick Actions -->
            <button 
              v-if="hasActiveFilters"
              @click="clearFilters" 
              class="px-4 py-2.5 border bg-card hover:bg-accent rounded-lg text-sm font-medium transition-all flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Clear
            </button>
          </div>

          <!-- Active Filters Tags -->
          <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mt-3">
            <span v-if="search" class="inline-flex items-center gap-1 px-3 py-1 bg-blue-50 text-blue-700 dark:bg-purple-800/50 dark:text-purple-200 text-xs font-medium rounded-full">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              Search: "{{ search }}"
            </span>
            <span v-if="filterElection" class="inline-flex items-center gap-1 px-3 py-1 bg-purple-50 text-purple-700 dark:bg-purple-800/50 dark:text-purple-200 text-xs font-medium rounded-full">
              Election: {{ props.elections.find(e => e.id === filterElection)?.title }}
            </span>
            <span v-if="filterPosition" class="inline-flex items-center gap-1 px-3 py-1 bg-green-50 text-green-700 dark:bg-purple-800/50 dark:text-purple-200 text-xs font-medium rounded-full">
              Position: {{ props.positions.find(p => p.id === filterPosition)?.name }}
            </span>
            <span v-if="filterPartylist" class="inline-flex items-center gap-1 px-3 py-1 bg-orange-50 text-orange-700 dark:bg-purple-800/50 dark:text-purple-200 text-xs font-medium rounded-full">
              Partylist: "{{ filterPartylist }}"
            </span>
            <span v-if="filterCourse" class="inline-flex items-center gap-1 px-3 py-1 bg-pink-50 text-pink-700 dark:bg-purple-800/50 dark:text-purple-200 text-xs font-medium rounded-full">
              Course: {{ filterCourse }}
            </span>
            <span v-if="filterYear" class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-50 text-indigo-700 dark:bg-purple-800/50 dark:text-purple-200 text-xs font-medium rounded-full">
              Year: {{ filterYear }}{{ ['st', 'nd', 'rd', 'th'][parseInt(filterYear) - 1] }}
            </span>
          </div>
        </div>

        <!-- Expandable Filters -->
        <transition
          enter-active-class="transition-opacity duration-200 ease-out"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition-opacity duration-150 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div v-show="showFilters" class="border-t bg-muted/50 relative z-30">
            <div class="p-4">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Election Filter -->
                <div :class="['relative', electionDropdownOpen ? 'z-[60]' : 'z-10']">
                  <label class="block text-xs font-medium text-muted-foreground mb-1.5">Election</label>
                  <div class="relative">
                    <button type="button" @click.stop="toggleElectionDropdown()" class="w-full text-left px-3 py-2 pr-10 text-sm bg-background dark:bg-purple-950/40 border dark:border-purple-700 rounded-lg flex items-center justify-between dark:text-purple-100">
                      <span>{{ electionOptions.find(o => o.value === filterElection)?.label ?? 'All Elections' }}</span>
                      <svg class="w-4 h-4 text-muted-foreground dark:text-purple-300 transition-transform duration-200" :class="{ 'rotate-180': electionDropdownOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div v-if="electionDropdownOpen" class="absolute left-0 mt-2 w-full bg-white dark:bg-purple-900 border-2 dark:border-purple-600 rounded-lg shadow-xl z-[100]">
                      <ul class="py-1 max-h-60 overflow-y-auto">
                        <li v-for="opt in electionOptions" :key="String(opt.value)" class="px-3 py-2 hover:bg-primary/10 dark:hover:bg-purple-800 cursor-pointer text-sm dark:text-purple-100" @click="selectElectionOption(opt)">
                          {{ opt.label }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Position Filter -->
                <div :class="['relative', positionDropdownOpen ? 'z-[60]' : 'z-10']">
                  <label class="block text-xs font-medium text-muted-foreground mb-1.5">Position</label>
                  <div class="relative">
                    <button type="button" @click.stop="togglePositionDropdown()" class="w-full text-left px-3 py-2 pr-10 text-sm bg-background dark:bg-purple-950/40 border dark:border-purple-700 rounded-lg flex items-center justify-between dark:text-purple-100">
                      <span>{{ positionOptions.find(o => o.value === filterPosition)?.label ?? 'All Positions' }}</span>
                      <svg class="w-4 h-4 text-muted-foreground dark:text-purple-300 transition-transform duration-200" :class="{ 'rotate-180': positionDropdownOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div v-if="positionDropdownOpen" class="absolute left-0 mt-2 w-full bg-white dark:bg-purple-900 border-2 dark:border-purple-600 rounded-lg shadow-xl z-[100]">
                      <ul class="py-1 max-h-60 overflow-y-auto">
                        <li v-for="opt in positionOptions" :key="String(opt.value)" class="px-3 py-2 hover:bg-primary/10 dark:hover:bg-purple-800 cursor-pointer text-sm dark:text-purple-100" @click="selectPositionOption(opt)">
                          {{ opt.label }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Partylist Filter -->
                <div class="relative z-10">
                  <label class="block text-xs font-medium text-muted-foreground mb-1.5">Partylist</label>
                  <input 
                    v-model="filterPartylist" 
                    @keyup.enter="applyFilters"
                    type="text" 
                    placeholder="Enter partylist name..."
                    class="w-full px-3 py-2 text-sm bg-background dark:bg-purple-950/40 border dark:border-purple-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary dark:text-purple-100"
                  />
                </div>

                <!-- Course Filter -->
                <div :class="['relative', courseDropdownOpen ? 'z-[60]' : 'z-10']">
                  <label class="block text-xs font-medium text-muted-foreground mb-1.5">Course</label>
                  <div class="relative">
                    <button type="button" @click.stop="toggleCourseDropdown()" class="w-full text-left px-3 py-2 pr-10 text-sm bg-background dark:bg-purple-950/40 border dark:border-purple-700 rounded-lg flex items-center justify-between dark:text-purple-100">
                      <span>{{ courseOptions.find(o => o.value === filterCourse)?.label ?? 'All Courses' }}</span>
                      <svg class="w-4 h-4 text-muted-foreground dark:text-purple-300 transition-transform duration-200" :class="{ 'rotate-180': courseDropdownOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div v-if="courseDropdownOpen" class="absolute left-0 mt-2 w-full bg-white dark:bg-purple-900 border-2 dark:border-purple-600 rounded-lg shadow-xl z-[100]">
                      <ul class="py-1 max-h-60 overflow-y-auto">
                        <li v-for="opt in courseOptions" :key="String(opt.value)" class="px-3 py-2 hover:bg-primary/10 dark:hover:bg-purple-800 cursor-pointer text-sm dark:text-purple-100" @click="selectCourseOption(opt)">
                          {{ opt.label }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Year Level Filter -->
                <div :class="['relative', yearDropdownOpen ? 'z-[60]' : 'z-10']">
                  <label class="block text-xs font-medium text-muted-foreground mb-1.5">Year Level</label>
                  <div class="relative">
                    <button type="button" @click.stop="toggleYearDropdown()" class="w-full text-left px-3 py-2 pr-10 text-sm bg-background dark:bg-purple-950/40 border dark:border-purple-700 rounded-lg flex items-center justify-between dark:text-purple-100">
                      <span>{{ yearOptions.find(o => o.value === filterYear)?.label ?? 'All Years' }}</span>
                      <svg class="w-4 h-4 text-muted-foreground dark:text-purple-300 transition-transform duration-200" :class="{ 'rotate-180': yearDropdownOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div v-if="yearDropdownOpen" class="absolute left-0 mt-2 w-full bg-white dark:bg-purple-900 border-2 dark:border-purple-600 rounded-lg shadow-xl z-[100]">
                      <ul class="py-1 max-h-60 overflow-y-auto">
                        <li v-for="opt in yearOptions" :key="String(opt.value)" class="px-3 py-2 hover:bg-primary/10 dark:hover:bg-purple-800 cursor-pointer text-sm dark:text-purple-100" @click="selectYearOption(opt)">
                          {{ opt.label }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </transition>
      </section>

      <!-- Empty State -->
      <section v-if="props.candidates.data.length === 0" class="bg-card rounded-xl shadow-sm p-12 text-center border">
        <div class="text-muted-foreground mb-4">
          <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <h3 class="text-lg font-semibold mb-2">No candidates found</h3>
        <p class="text-muted-foreground mb-4">Start by adding candidates for your elections.</p>
        <button 
          @click="openCreateModal"
          class="inline-flex items-center gap-2 px-4 py-2 bg-[#5A2D6F] hover:bg-[#4b255c] dark:bg-[#5A2D6F] dark:hover:bg-[#4b255c] text-white text-sm font-medium rounded-md transition-colors"
        >
          <Icon name="plus" class="h-4 w-4" />
          Add Candidate
        </button>
      </section>

      <!-- Table -->
      <section v-else class="bg-card rounded-xl shadow-md p-4 border">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-border">
            <thead>
              <tr class="text-left text-sm text-muted-foreground">
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

            <tbody class="divide-y divide-border text-sm">
              <tr v-for="candidate in props.candidates.data" :key="candidate.id" class="hover:bg-muted/50 transition">
                <td class="py-3 px-4">
                  <img 
                    :src="getPhotoUrl(candidate.photo)" 
                    :alt="candidate.user.name"
                    class="w-12 h-12 rounded-full object-cover border-2"
                  />
                </td>

                <td class="py-3 px-4">
                  <div class="font-medium">{{ candidate.user.name }}</div>
                  <div class="text-xs text-muted-foreground">{{ candidate.user.email }}</div>
                </td>

                <td class="py-3 px-4">{{ candidate.position.name }}</td>

                <td class="py-3 px-4">
                  <div class="text-sm">{{ candidate.election.title }}</div>
                </td>

                <td class="py-3 px-4">
                  <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-muted">
                    {{ candidate.partylist }}
                  </span>
                </td>

                <td class="py-3 px-4">
                  {{ candidate.course }} {{ candidate.year_level }}{{ candidate.section }}
                </td>

                <td class="py-3 px-4">
                  <span class="font-semibold">{{ candidate.votes_count }}</span>
                </td>

                <td class="py-3 px-4">
                  <div class="flex gap-2">
                    <button 
                      @click="openViewModal(candidate)"
                      class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-400 transition"
                      style="cursor: pointer;"
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
                      class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 dark:text-blue-400 transition"
                      style="cursor: pointer;"
                      title="Edit"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                      Edit
                    </button>
                    <button 
                      @click="openDeleteModal(candidate)"
                      class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-400 transition"
                      style="cursor: pointer;"
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
          <div class="text-sm text-muted-foreground">
            Showing {{ props.candidates.from ?? 0 }} to {{ props.candidates.to ?? 0 }} of {{ props.candidates.total }} candidates
          </div>
          <nav class="inline-flex items-center gap-2">
            <button 
              @click="goToPage(props.candidates.current_page - 1)"
              :disabled="props.candidates.current_page === 1"
              class="px-3 py-1 rounded-md text-sm border bg-card hover:bg-accent disabled:opacity-50 disabled:cursor-not-allowed"
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
                  ? 'bg-[#5A2D6F] text-white'
                  : 'bg-card hover:bg-[#4b255c]'
              ]"
            >
              {{ page }}
            </button>

            <button 
              @click="goToPage(props.candidates.current_page + 1)"
              :disabled="props.candidates.current_page === props.candidates.last_page"
              class="px-3 py-1 rounded-md text-sm border bg-card hover:bg-accent disabled:opacity-50 disabled:cursor-not-allowed"
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

      <div class="relative bg-card rounded-xl shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 z-50">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold">Add New Candidate</h3>
            <p class="text-sm text-muted-foreground mt-1">Fill in the candidate information</p>
          </div>
          <button @click="closeCreateModal" class="text-muted-foreground hover:text-foreground">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitCreate" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Name *</label>
              <input 
                v-model="createForm.name" 
                type="text" 
                required
                class="w-full rounded-lg border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              />
              <div v-if="createForm.errors.name" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.name }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Email *</label>
              <input 
                v-model="createForm.email" 
                type="email" 
                required
                class="w-full rounded-lg border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              />
              <div v-if="createForm.errors.email" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.email }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Election *</label>
              <select 
                v-model="createForm.election_id" 
                required
                class="w-full rounded-lg border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              >
                <option :value="null">Select election</option>
                <option v-for="election in props.elections" :key="election.id" :value="election.id">
                  {{ election.title }}
                </option>
              </select>
              <div v-if="createForm.errors.election_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.election_id }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Position *</label>
              <select 
                v-model="createForm.position_id" 
                required
                :disabled="!createForm.election_id"
                class="w-full rounded-lg border bg-background px-3 py-2 text-sm disabled:bg-muted disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-primary"
              >
                <option :value="null">{{ createForm.election_id ? 'Select position' : 'Select election first' }}</option>
                <option v-for="position in availablePositionsForCreate" :key="position.id" :value="position.id">
                  {{ position.name }}
                </option>
              </select>
              <div v-if="createForm.errors.position_id" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.position_id }}</div>
              <p v-if="createForm.election_id && availablePositionsForCreate.length === 0" class="text-amber-600 dark:text-amber-400 text-xs mt-1">
                 No positions available for this election. Please add positions first.
              </p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Partylist *</label>
            <input 
              v-model="createForm.partylist" 
              type="text" 
              required
              class="w-full rounded-lg border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
            />
            <div v-if="createForm.errors.partylist" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.partylist }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Platform *</label>
            <textarea 
              v-model="createForm.platform" 
              required
              rows="4"
              placeholder="Minimum 50 characters"
              class="w-full rounded-lg border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
            ></textarea>
            <div v-if="createForm.errors.platform" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.platform }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Photo * (JPG/PNG, max 2MB)</label>
            <input 
              @change="handlePhotoCreate"
              type="file" 
              accept="image/jpeg,image/png"
              required
              class="w-full rounded-lg border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
            />
            <div v-if="createForm.errors.photo" class="text-red-600 dark:text-red-400 text-xs mt-1">{{ createForm.errors.photo }}</div>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Course *</label>
              <select 
                v-model="createForm.course" 
                required
                class="w-full rounded-lg border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              >
                <option value="BSIT">BSIT</option>
                <option value="BSIS">BSIS</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Year *</label>
              <select 
                v-model="createForm.year_level" 
                required
                class="w-full rounded-lg border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              >
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Section</label>
              <input 
                v-model="createForm.section" 
                type="text" 
                placeholder="e.g., A"
                class="w-full rounded-lg border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              />
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t">
            <button 
              type="button" 
              @click="closeCreateModal" 
              class="px-4 py-2 rounded-md bg-muted hover:bg-muted/80 text-sm"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="createForm.processing"
              class="px-4 py-2 bg-[#5A2D6F] hover:bg-[#4b255c] text-white text-sm font-medium rounded-md transition"
            >
              {{ createForm.processing ? 'Creating...' : 'Create Candidate' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Modal (Similar structure, omitted for brevity - can be added) -->
    <div v-if="showEditModal && selectedCandidate" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/40" @click="closeEditModal"></div>

      <div class="relative bg-card rounded-xl shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 z-50">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold">Edit Candidate</h3>
            <p class="text-sm text-muted-foreground mt-1">Update candidate information</p>
          </div>
          <button @click="closeEditModal" class="text-muted-foreground hover:text-foreground">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitEdit" class="space-y-4">
          <!-- Current Photo -->
          <div>
            <label class="block text-sm font-medium mb-2">Current Photo</label>
            <img 
              :src="getPhotoUrl(selectedCandidate.photo)" 
              :alt="selectedCandidate.user.name"
              class="w-24 h-24 rounded-lg object-cover border-2"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
              class="px-4 py-2 rounded-md bg-muted hover:bg-muted/80 text-sm"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="editForm.processing"
              class="px-4 py-2 rounded-md bg-blue-600 dark:bg-blue-500 text-white text-sm hover:bg-blue-700 dark:hover:bg-blue-600 disabled:opacity-50"
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

      <div class="relative bg-card rounded-xl shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto p-6 z-50">
        <div class="flex items-start justify-between mb-6">
          <h3 class="text-xl font-semibold">Candidate Details</h3>
          <button @click="closeViewModal" class="text-muted-foreground hover:text-foreground">
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
              class="w-32 h-32 rounded-lg object-cover border-2"
            />
            <div class="flex-1">
              <h4 class="text-2xl font-bold">{{ selectedCandidate.user.name }}</h4>
              <p class="text-muted-foreground mt-1">{{ selectedCandidate.user.email }}</p>
              <div class="flex gap-2 mt-3">
                <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-blue-50 text-blue-800 dark:bg-blue-500/10 dark:text-blue-400">
                  {{ selectedCandidate.partylist }}
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-purple-50 text-purple-800 dark:bg-purple-500/10 dark:text-purple-400">
                  {{ selectedCandidate.course }} {{ selectedCandidate.year_level }}{{ selectedCandidate.section }}
                </span>
              </div>
            </div>
          </div>

          <!-- Divider -->
          <div class="border-t"></div>

          <!-- Election & Position Info -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div>
              <label class="block text-sm font-medium text-muted-foreground mb-1">Election</label>
              <p class="text-lg font-semibold">{{ selectedCandidate.election.title }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-muted-foreground mb-1">Position</label>
              <p class="text-lg font-semibold">{{ selectedCandidate.position.name }}</p>
            </div>
          </div>

          <!-- Platform -->
          <div>
            <label class="block text-sm font-medium mb-2">Platform / Agenda</label>
            <div class="bg-muted/50 rounded-lg p-4 text-sm leading-relaxed break-words whitespace-pre-wrap">
              {{ selectedCandidate.platform }}
            </div>
          </div>

          <!-- Stats -->
          <div class="bg-green-50 dark:bg-green-500/10 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-green-800 dark:text-green-400">Total Votes Received</span>
              <span class="text-3xl font-bold text-green-900 dark:text-green-400">{{ selectedCandidate.votes_count }}</span>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
          <button 
            @click="closeViewModal" 
            class="px-4 py-2 rounded-md bg-muted hover:bg-muted/80 text-sm"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/40" @click="closeDeleteModal"></div>

      <div class="relative bg-card rounded-xl shadow-lg w-full max-w-md p-6 z-50">
        <h3 class="text-lg font-semibold mb-4">Delete Candidate</h3>
        
        <div v-if="selectedCandidate">
          <p class="text-sm text-muted-foreground mb-4">Are you sure you want to delete this candidate?</p>
          <div class="bg-muted/50 rounded-lg p-3 mb-4">
            <div class="font-medium">{{ selectedCandidate.user.name }}</div>
            <div class="text-sm text-muted-foreground">{{ selectedCandidate.position.name }} - {{ selectedCandidate.election.title }}</div>
          </div>
        </div>

        <div class="flex justify-end gap-2">
          <button 
            @click="closeDeleteModal" 
            class="px-4 py-2 rounded-md bg-muted hover:bg-muted/80 text-sm"
          >
            Cancel
          </button>
          <button 
            @click="confirmDelete"
            class="px-4 py-2 rounded-md bg-red-600 dark:bg-red-500 text-white text-sm hover:bg-red-700 dark:hover:bg-red-600"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Password Display Modal -->
    <div v-if="showPasswordModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/40" @click="closePasswordModal"></div>

      <div class="relative bg-card rounded-xl shadow-lg w-full max-w-md p-6 z-50">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-500/10 mb-4">
            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          
          <h3 class="text-lg font-semibold mb-2">Candidate Created Successfully!</h3>
          <p class="text-sm text-muted-foreground mb-4">The candidate account has been created and login credentials have been sent to their email.</p>
          
          <div class="bg-muted/50 rounded-lg p-4 mb-4">
            <div class="text-sm text-muted-foreground mb-2">Email sent to:</div>
            <div class="text-base font-medium mb-3">{{ generatedPassword ? '✓ Credentials sent successfully' : '' }}</div>
            
            <div class="text-sm text-muted-foreground mb-2">Generated Password (for reference):</div>
            <div class="flex items-center justify-between bg-background rounded-md p-3 border">
              <code class="text-lg font-mono font-bold">{{ generatedPassword }}</code>
              <button 
                @click="copyPassword"
                class="ml-2 p-2 hover:bg-accent rounded transition"
                title="Copy password"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-lg p-3 mb-4">
            <div class="flex items-start gap-2">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              <p class="text-xs text-blue-800 dark:text-blue-400">
                <strong>Email sent!</strong> The candidate will receive their login credentials at their registered email address. You can also share this password manually if needed.
              </p>
            </div>
          </div>

          <button 
            @click="closePasswordModal"
            class="w-full px-4 py-2 rounded-md bg-green-600 dark:bg-green-500 text-white text-sm hover:bg-green-700 dark:hover:bg-green-600"
          >
            Got it!
          </button>
        </div>
      </div>
    </div>

  </AppLayout>
</template>
