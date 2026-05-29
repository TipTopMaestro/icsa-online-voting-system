<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import Icon from '@/components/Icon.vue';
import { Search, Filter, ChevronDown, CheckCircle2, MoreHorizontal, User, Mail, GraduationCap, Calendar, Hash, Clock, Plus, Trash, Eye, PencilLine, Award, Inbox, X, Upload } from 'lucide-vue-next';

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
  links: any[];
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
const positionOptions = computed(() => {
    let base = [{ label: 'All Positions', value: null }];
    if (filterElection.value) {
        return [...base, ...props.positions.filter(p => p.election_id === filterElection.value).map(p => ({ label: p.name, value: p.id }))];
    }
    return [...base, ...props.positions.map(p => ({ label: p.name, value: p.id }))];
});

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
  filterElection.value = opt.value;
  electionDropdownOpen.value = false;
  filterPosition.value = null; 
  applyFilters();
}

function selectPositionOption(opt: { label: string; value: number | null }) {
  filterPosition.value = opt.value;
  positionDropdownOpen.value = false;
  applyFilters();
}

function selectCourseOption(opt: { label: string; value: string | null }) {
  filterCourse.value = opt.value;
  courseDropdownOpen.value = false;
  applyFilters();
}

function selectYearOption(opt: { label: string; value: string | null }) {
  filterYear.value = opt.value;
  yearDropdownOpen.value = false;
  applyFilters();
}

function toggleElectionDropdown() {
  electionDropdownOpen.value = !electionDropdownOpen.value;
  positionDropdownOpen.value = false;
  courseDropdownOpen.value = false;
  yearDropdownOpen.value = false;
}

function togglePositionDropdown() {
  positionDropdownOpen.value = !positionDropdownOpen.value;
  electionDropdownOpen.value = false;
  courseDropdownOpen.value = false;
  yearDropdownOpen.value = false;
}

function toggleCourseDropdown() {
  courseDropdownOpen.value = !courseDropdownOpen.value;
  electionDropdownOpen.value = false;
  positionDropdownOpen.value = false;
  yearDropdownOpen.value = false;
}

function toggleYearDropdown() {
  yearDropdownOpen.value = !yearDropdownOpen.value;
  electionDropdownOpen.value = false;
  positionDropdownOpen.value = false;
  courseDropdownOpen.value = false;
}

// Modal State
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
const showPasswordModal = ref(false);
const selectedCandidate = ref<Candidate | null>(null);
const generatedPassword = ref('');

// Forms
const createForm = useForm({
  name: '',
  email: '',
  election_id: null as number | null,
  position_id: null as number | null,
  partylist: '',
  platform: '',
  photo: null as File | null,
  course: 'BSIT',
  year_level: '1',
  section: '',
});

const editForm = useForm({
  name: '',
  email: '',
  election_id: null as number | null,
  position_id: null as number | null,
  partylist: '',
  platform: '',
  photo: null as File | null,
  course: '',
  year_level: '',
  section: '',
});

// Helper: Available positions for forms
const availablePositionsForCreate = computed(() => {
  if (!createForm.election_id) return [];
  return props.positions.filter(p => p.election_id === createForm.election_id);
});

const availablePositionsForEdit = computed(() => {
  if (!editForm.election_id) return [];
  return props.positions.filter(p => p.election_id === editForm.election_id);
});

// Methods
function applyFilters() {
  router.get('/admin/candidates', {
    search: search.value,
    election_id: filterElection.value,
    position_id: filterPosition.value,
    partylist: filterPartylist.value,
    course: filterCourse.value,
    year_level: filterYear.value,
  }, {
    preserveState: true,
    replace: true,
  });
}

function clearFilters() {
  search.value = '';
  filterElection.value = null;
  filterPosition.value = null;
  filterPartylist.value = '';
  filterCourse.value = null;
  filterYear.value = null;
  applyFilters();
}

function openCreateModal() {
  createForm.reset();
  showCreateModal.value = true;
}

function closeCreateModal() {
  showCreateModal.value = false;
}

function handlePhotoCreate(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (file) createForm.photo = file;
}

function submitCreate() {
  createForm.post('/admin/candidates', {
    onSuccess: (page) => {
        if (page.props.flash?.candidate_password) {
            generatedPassword.value = page.props.flash.candidate_password as string;
            showCreateModal.value = false;
            showPasswordModal.value = true;
        } else {
            closeCreateModal();
        }
    },
  });
}

function openEditModal(candidate: Candidate) {
  selectedCandidate.value = candidate;
  editForm.name = candidate.user.name;
  editForm.email = candidate.user.email;
  editForm.election_id = candidate.election_id;
  editForm.position_id = candidate.position_id;
  editForm.partylist = candidate.partylist;
  editForm.platform = candidate.platform;
  editForm.course = candidate.course;
  editForm.year_level = candidate.year_level;
  editForm.section = candidate.section || '';
  editForm.photo = null;
  showEditModal.value = true;
}

function closeEditModal() {
  showEditModal.value = false;
  selectedCandidate.value = null;
}

function handlePhotoEdit(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (file) editForm.photo = file;
}

function submitEdit() {
  if (!selectedCandidate.value) return;
  editForm.post(`/admin/candidates/${selectedCandidate.value.id}`, {
    forceFormData: true,
    onSuccess: () => {
      closeEditModal();
    },
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
    onSuccess: () => closeDeleteModal(),
  });
}

function closePasswordModal() {
    showPasswordModal.value = false;
    generatedPassword.value = '';
}

function copyPassword() {
    navigator.clipboard.writeText(generatedPassword.value);
}

function getPhotoUrl(photo: string | null) {
  return photo ? `/storage/candidates/${photo}` : '/images/profile.png';
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}
</script>

<template>
  <div>
    <Head title="Candidates Directory" />
    <AppLayout :breadcrumbs="breadcrumbs">
      <div class="flex h-full flex-1 flex-col gap-4 md:gap-8 p-4 md:p-8 min-h-[calc(100vh-64px)]">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-xl md:text-3xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Candidate Hub</h1>
            <p class="text-muted-foreground mt-1 text-[11px] md:text-sm font-medium">Manage runners, partylists, and campaign platforms.</p>
          </div>
          <button 
            @click="openCreateModal"
            class="flex items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest bg-primary text-primary-foreground hover:bg-primary/90 transition-all active:scale-95"
          >
            <Plus class="h-4 w-4" />
            Add Candidate
          </button>
        </div>

        <!-- Filters Section -->
        <section class="bg-white dark:bg-card border border-gray-100 dark:border-border rounded-3xl p-4 md:p-6 shadow-sm">
          <div class="flex flex-col gap-4">
            <div class="flex flex-col lg:flex-row gap-4">
              <!-- Search -->
              <div class="flex-1 relative group">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                  <Search class="w-4 h-4" />
                </div>
                <input 
                  v-model="search" 
                  @keyup.enter="applyFilters"
                  type="search" 
                  placeholder="Search by name, email, or party..." 
                  class="w-full bg-gray-50 dark:bg-background border-none rounded-2xl pl-11 pr-4 h-12 text-sm focus:ring-2 focus:ring-primary/20 transition-all dark:text-foreground font-medium" 
                />
              </div>

              <!-- Filter Toggle (Mobile) -->
              <button @click="showFilters = !showFilters" class="lg:hidden h-12 px-4 rounded-2xl border-2 border-gray-100 dark:border-border flex items-center justify-between text-[10px] font-black uppercase tracking-widest text-gray-500">
                <div class="flex items-center gap-2">
                  <Filter class="w-3.5 h-3.5" />
                  <span>{{ showFilters ? 'Hide Advanced Filters' : 'Show Advanced Filters' }}</span>
                </div>
                <ChevronDown class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showFilters }" />
              </button>

              <!-- Desktop Filter Grid -->
              <div class="hidden lg:grid grid-cols-5 gap-3">
                <!-- Election Filter -->
                <div class="relative min-w-[140px]">
                  <button type="button" @click="toggleElectionDropdown" class="w-full h-12 px-4 rounded-2xl border-2 border-gray-100 dark:border-border bg-white dark:bg-card text-left text-[10px] font-black uppercase tracking-widest flex items-center justify-between dark:text-foreground hover:border-primary/50 transition-all">
                    <span class="truncate">{{ electionOptions.find(o => String(o.value) === String(filterElection))?.label ?? 'Election' }}</span>
                    <ChevronDown class="w-3.5 h-3.5 text-primary" :class="{ 'rotate-180': electionDropdownOpen }" />
                  </button>
                  <div v-if="electionDropdownOpen" class="absolute z-[120] mt-2 w-56 bg-white dark:bg-purple-900 border-2 border-slate-100 dark:border-purple-600 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200" @click.outside="electionDropdownOpen = false">
                    <div class="max-h-60 overflow-y-auto py-1">
                      <div v-for="opt in electionOptions" :key="String(opt.value)" @click="selectElectionOption(opt)" class="px-4 py-3 cursor-pointer hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors text-[10px] font-black uppercase tracking-widest" :class="filterElection === opt.value ? 'text-primary bg-primary/5' : 'text-gray-700 dark:text-purple-100'">{{ opt.label }}</div>
                    </div>
                  </div>
                </div>

                <!-- Position Filter -->
                <div class="relative min-w-[140px]">
                  <button type="button" @click="togglePositionDropdown" class="w-full h-12 px-4 rounded-2xl border-2 border-gray-100 dark:border-border bg-white dark:bg-card text-left text-[10px] font-black uppercase tracking-widest flex items-center justify-between dark:text-foreground hover:border-primary/50 transition-all">
                    <span class="truncate">{{ positionOptions.find(o => String(o.value) === String(filterPosition))?.label ?? 'Position' }}</span>
                    <ChevronDown class="w-3.5 h-3.5 text-primary" :class="{ 'rotate-180': positionDropdownOpen }" />
                  </button>
                  <div v-if="positionDropdownOpen" class="absolute z-[120] mt-2 w-56 bg-white dark:bg-purple-900 border-2 border-slate-100 dark:border-purple-600 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200" @click.outside="positionDropdownOpen = false">
                    <div class="max-h-60 overflow-y-auto py-1">
                      <div v-for="opt in positionOptions" :key="String(opt.value)" @click="selectPositionOption(opt)" class="px-4 py-3 cursor-pointer hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors text-[10px] font-black uppercase tracking-widest" :class="filterPosition === opt.value ? 'text-primary bg-primary/5' : 'text-gray-700 dark:text-purple-100'">{{ opt.label }}</div>
                    </div>
                  </div>
                </div>

                <!-- Course Filter -->
                <div class="relative min-w-[120px]">
                  <button type="button" @click="toggleCourseDropdown" class="w-full h-12 px-4 rounded-2xl border-2 border-gray-100 dark:border-border bg-white dark:bg-card text-left text-[10px] font-black uppercase tracking-widest flex items-center justify-between dark:text-foreground hover:border-primary/50 transition-all">
                    <span class="truncate">{{ courseOptions.find(o => String(o.value) === String(filterCourse))?.label ?? 'Course' }}</span>
                    <ChevronDown class="w-3.5 h-3.5 text-primary" :class="{ 'rotate-180': courseDropdownOpen }" />
                  </button>
                  <div v-if="courseDropdownOpen" class="absolute z-[120] mt-2 w-44 bg-white dark:bg-purple-900 border-2 border-slate-100 dark:border-purple-600 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200" @click.outside="courseDropdownOpen = false">
                    <div class="py-1">
                      <div v-for="opt in courseOptions" :key="String(opt.value)" @click="selectCourseOption(opt)" class="px-4 py-3 cursor-pointer hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors text-[10px] font-black uppercase tracking-widest" :class="filterCourse === opt.value ? 'text-primary bg-primary/5' : 'text-gray-700 dark:text-purple-100'">{{ opt.label }}</div>
                    </div>
                  </div>
                </div>

                <!-- Year Filter -->
                <div class="relative min-w-[120px]">
                  <button type="button" @click="toggleYearDropdown" class="w-full h-12 px-4 rounded-2xl border-2 border-gray-100 dark:border-border bg-white dark:bg-card text-left text-[10px] font-black uppercase tracking-widest flex items-center justify-between dark:text-foreground hover:border-primary/50 transition-all">
                    <span class="truncate">{{ yearOptions.find(o => String(o.value) === String(filterYear))?.label ?? 'Year' }}</span>
                    <ChevronDown class="w-3.5 h-3.5 text-primary" :class="{ 'rotate-180': yearDropdownOpen }" />
                  </button>
                  <div v-if="yearDropdownOpen" class="absolute z-[120] mt-2 w-44 bg-white dark:bg-purple-900 border-2 border-slate-100 dark:border-purple-600 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200" @click.outside="yearDropdownOpen = false">
                    <div class="py-1">
                      <div v-for="opt in yearOptions" :key="String(opt.value)" @click="selectYearOption(opt)" class="px-4 py-3 cursor-pointer hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors text-[10px] font-black uppercase tracking-widest" :class="filterYear === opt.value ? 'text-primary bg-primary/5' : 'text-gray-700 dark:text-purple-100'">{{ opt.label }}</div>
                    </div>
                  </div>
                </div>

                <button @click="clearFilters" class="h-12 px-6 rounded-2xl dark:bg-mutedtext-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-muted transition-all">Reset</button>
              </div>
            </div>

            <!-- Mobile Advanced Filters -->
            <div v-if="showFilters" class="lg:hidden grid grid-cols-1 sm:grid-cols-2 gap-3 animate-in slide-in-from-top-2 duration-200">
               <div class="space-y-3">
                  <div v-for="(opt, i) in [
                    { label: 'Election', items: electionOptions, current: filterElection, select: (o: any) => selectElectionOption({ label: '', value: o.value === 'null' ? null : Number(o.value) }) },
                    { label: 'Position', items: positionOptions, current: filterPosition, select: (o: any) => selectPositionOption({ label: '', value: o.value === 'null' ? null : Number(o.value) }) },
                    { label: 'Course', items: courseOptions, current: filterCourse, select: (o: any) => selectCourseOption({ label: '', value: o.value === 'null' ? null : o.value }) },
                    { label: 'Year', items: yearOptions, current: filterYear, select: (o: any) => selectYearOption({ label: '', value: o.value === 'null' ? null : o.value }) }
                  ]" :key="i" class="space-y-1">
                    <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-1">{{ opt.label }}</label>
                    <select :value="String(opt.current)" @change="(e) => opt.select({ label: '', value: (e.target as HTMLSelectElement).value })" class="w-full h-11 px-4 rounded-xl border-2 border-gray-100 dark:border-border bg-gray-50 dark:bg-background text-xs font-bold appearance-none">
                      <option v-for="item in opt.items" :key="String(item.value)" :value="String(item.value)">{{ item.label }}</option>
                    </select>
                  </div>
               </div>
               <button @click="clearFilters" class="h-11 w-full bg-gray-100 dark:bg-muted rounded-xl text-[10px] font-black uppercase tracking-widest">Clear All</button>
            </div>
          </div>
        </section>

        <!-- Main Content (Table) -->
        <section class="bg-white dark:bg-card border border-gray-100 dark:border-border rounded-3xl shadow-sm overflow-hidden flex flex-col">
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-gray-50/50 dark:bg-muted/30 border-b border-gray-100 dark:border-border">
                <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                  <th class="px-6 py-4">Identity</th>
                  <th class="px-6 py-4">Deployment</th>
                  <th class="px-6 py-4">Partylist</th>
                  <th class="px-6 py-4">Academics</th>
                  <th class="px-6 py-4 text-center">Performance</th>
                  <th class="px-6 py-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 dark:divide-border text-[13px]">
                <tr v-for="candidate in candidates.data" :key="candidate.id" class="hover:bg-gray-50/30 dark:hover:bg-muted/5 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                      <img :src="getPhotoUrl(candidate.photo)" class="h-10 w-10 rounded-full object-cover border border-primary/20" />
                      <div>
                        <p class="font-bold text-gray-900 dark:text-foreground leading-none">{{ candidate.user.name }}</p>
                        <p class="text-[10px] text-gray-400 font-medium mt-1">{{ candidate.user.email }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex flex-col gap-0.5">
                      <span class="font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider text-[11px]">{{ candidate.position.name }}</span>
                      <span class="text-[9px] text-gray-400 font-black uppercase tracking-widest">{{ candidate.election.title }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-primary/5 text-primary border border-primary/10">
                      {{ candidate.partylist || 'Independent' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-[10px] font-bold text-gray-500 uppercase">{{ candidate.course }} — {{ candidate.year_level }}{{ candidate.section }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div class="flex items-center justify-center gap-2">
                      <Award class="w-3.5 h-3.5 text-accent" />
                      <span class="font-black text-gray-900 dark:text-foreground">{{ candidate.votes_count }}</span>
                      <span class="text-[9px] text-gray-400 font-black uppercase tracking-widest">Votes</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-1">
                      <button @click="openViewModal(candidate)" class="p-2 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition-colors"><Eye class="w-4 h-4" /></button>
                      <button @click="openEditModal(candidate)" class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-colors"><PencilLine class="w-4 h-4" /></button>
                      <button @click="openDeleteModal(candidate)" class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors"><Trash class="w-4 h-4" /></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="px-6 py-4 bg-gray-50/30 dark:bg-muted/10 border-t border-gray-100 dark:border-border flex flex-col sm:flex-row items-center justify-between gap-4 mt-auto">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Showing {{ candidates.from }} to {{ candidates.to }} of {{ candidates.total }} Nominees</p>
            <div class="flex items-center gap-1">
              <button 
                v-for="link in props.candidates.links" 
                :key="link.label"
                @click="link.url ? router.visit(link.url) : null"
                :disabled="!link.url || link.active"
                :class="[
                  'h-8 px-3 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all',
                  link.active ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-gray-400 hover:text-primary hover:bg-primary/5',
                  !link.url ? 'opacity-30 cursor-not-allowed' : ''
                ]"
                v-html="link.label"
              />
            </div>
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
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitCreate" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Name *</label>
                <input v-model="createForm.name" type="text" required class="w-full rounded-lg border bg-background px-3 py-2 text-sm" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Email *</label>
                <input v-model="createForm.email" type="email" required class="w-full rounded-lg border bg-background px-3 py-2 text-sm" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Election *</label>
                <select v-model="createForm.election_id" required class="w-full rounded-lg border bg-background px-3 py-2 text-sm">
                  <option :value="null">Select election</option>
                  <option v-for="election in props.elections" :key="election.id" :value="election.id">{{ election.title }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Position *</label>
                <select v-model="createForm.position_id" required :disabled="!createForm.election_id" class="w-full rounded-lg border bg-background px-3 py-2 text-sm">
                  <option :value="null">Select position</option>
                  <option v-for="position in availablePositionsForCreate" :key="position.id" :value="position.id">{{ position.name }}</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Partylist *</label>
              <input v-model="createForm.partylist" type="text" required class="w-full rounded-lg border bg-background px-3 py-2 text-sm" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Platform *</label>
              <textarea v-model="createForm.platform" required rows="4" class="w-full rounded-lg border bg-background px-3 py-2 text-sm"></textarea>
            </div>

            <div class="grid grid-cols-3 gap-4">
              <div><label class="block text-sm font-medium mb-1">Course *</label><select v-model="createForm.course" required class="w-full rounded-lg border bg-background px-3 py-2 text-sm"><option value="BSIT">BSIT</option><option value="BSIS">BSIS</option></select></div>
              <div><label class="block text-sm font-medium mb-1">Year *</label><select v-model="createForm.year_level" required class="w-full rounded-lg border bg-background px-3 py-2 text-sm"><option value="1">1st Year</option><option value="2">2nd Year</option><option value="3">3rd Year</option><option value="4">4th Year</option></select></div>
              <div><label class="block text-sm font-medium mb-1">Section</label><input v-model="createForm.section" type="text" class="w-full rounded-lg border bg-background px-3 py-2 text-sm" /></div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Photo *</label>
              <input @change="handlePhotoCreate" type="file" accept="image/*" required class="w-full rounded-lg border bg-background px-3 py-2 text-sm" />
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t">
              <button type="button" @click="closeCreateModal" class="px-4 py-2 rounded-md bg-muted hover:bg-muted/80 text-sm">Cancel</button>
              <button type="submit" :disabled="createForm.processing" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-md">Create Candidate</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Edit Modal -->
      <div v-if="showEditModal && selectedCandidate" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/40" @click="closeEditModal"></div>

        <div class="relative bg-card rounded-xl shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 z-50">
          <div class="flex items-start justify-between mb-4">
            <h3 class="text-lg font-semibold">Edit Candidate</h3>
            <button @click="closeEditModal" class="text-muted-foreground hover:text-foreground"><X class="w-5 h-5" /></button>
          </div>

          <form @submit.prevent="submitEdit" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Name *</label>
                <input v-model="editForm.name" type="text" required class="w-full rounded-lg border bg-background px-3 py-2 text-sm" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Email *</label>
                <input v-model="editForm.email" type="email" required class="w-full rounded-lg border bg-background px-3 py-2 text-sm" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div><label class="block text-sm font-medium mb-1">Election *</label><select v-model="editForm.election_id" required class="w-full rounded-lg border bg-background px-3 py-2 text-sm"><option v-for="e in elections" :key="e.id" :value="e.id">{{ e.title }}</option></select></div>
              <div><label class="block text-sm font-medium mb-1">Position *</label><select v-model="editForm.position_id" required class="w-full rounded-lg border bg-background px-3 py-2 text-sm"><option v-for="p in availablePositionsForEdit" :key="p.id" :value="p.id">{{ p.name }}</option></select></div>
            </div>

            <div><label class="block text-sm font-medium mb-1">Partylist *</label><input v-model="editForm.partylist" type="text" required class="w-full rounded-lg border bg-background px-3 py-2 text-sm" /></div>
            <div><label class="block text-sm font-medium mb-1">Platform *</label><textarea v-model="editForm.platform" required rows="4" class="w-full rounded-lg border bg-background px-3 py-2 text-sm"></textarea></div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div><label class="block text-sm font-medium mb-1">Course *</label><select v-model="editForm.course" required class="w-full rounded-lg border bg-background px-3 py-2 text-sm"><option value="BSIT">BSIT</option><option value="BSIS">BSIS</option></select></div>
              <div><label class="block text-sm font-medium mb-1">Year *</label><select v-model="editForm.year_level" required class="w-full rounded-lg border bg-background px-3 py-2 text-sm"><option value="1">1st Year</option><option value="2">2nd Year</option><option value="3">3rd Year</option><option value="4">4th Year</option></select></div>
              <div><label class="block text-sm font-medium mb-1">Section</label><input v-model="editForm.section" type="text" class="w-full rounded-lg border bg-background px-3 py-2 text-sm" /></div>
            </div>

            <div><label class="block text-sm font-medium mb-1">New Photo (optional)</label><input @change="handlePhotoEdit" type="file" accept="image/*" class="w-full rounded-lg border bg-background px-3 py-2 text-sm" /></div>

            <div class="flex justify-end gap-2 pt-4 border-t">
              <button type="button" @click="closeEditModal" class="px-4 py-2 rounded-md bg-muted hover:bg-muted/80 text-sm">Cancel</button>
              <button type="submit" :disabled="editForm.processing" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-md">Update Candidate</button>
            </div>
          </form>
        </div>
      </div>

      <!-- View Modal -->
      <div v-if="showViewModal && selectedCandidate" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/40" @click="closeViewModal"></div>

        <div class="relative bg-card rounded-xl shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto p-6 z-50">
          <div class="flex items-start justify-between mb-6">
            <h3 class="text-xl font-semibold uppercase">Candidate Details</h3>
            <button @click="closeViewModal" class="text-muted-foreground hover:text-foreground"><X class="w-6 h-6" /></button>
          </div>

          <div class="space-y-6">
            <div class="flex items-start gap-6">
              <img :src="getPhotoUrl(selectedCandidate.photo)" class="w-32 h-32 rounded-full object-cover border-4 border-primary/10 shadow-lg" />
              <div class="flex-1">
                <h4 class="text-2xl font-bold uppercase tracking-tight">{{ selectedCandidate.user.name }}</h4>
                <p class="text-muted-foreground font-medium">{{ selectedCandidate.user.email }}</p>
                <div class="flex flex-wrap gap-2 mt-4">
                  <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-primary/10 text-primary border border-primary/20">{{ selectedCandidate.partylist || 'Independent' }}</span>
                  <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-muted text-foreground border">{{ selectedCandidate.course }} {{ selectedCandidate.year_level }}{{ selectedCandidate.section }}</span>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-6 border-t dark:border-border">
              <div><label class="block text-sm font-medium text-muted-foreground mb-1 uppercase tracking-widest text-[10px]">Election System</label><p class="font-bold text-gray-900 dark:text-foreground">{{ selectedCandidate.election.title }}</p></div>
              <div><label class="block text-sm font-medium text-muted-foreground mb-1 uppercase tracking-widest text-[10px]">Assigned Role</label><p class="font-bold text-gray-900 dark:text-foreground">{{ selectedCandidate.position.name }}</p></div>
            </div>

            <div><label class="block text-sm font-medium mb-2 uppercase tracking-widest text-[10px] text-muted-foreground">Platform Statement</label><div class="bg-muted/50 rounded-lg p-4 text-sm leading-relaxed italic break-words whitespace-pre-wrap">"{{ selectedCandidate.platform }}"</div></div>

            <div class="bg-primary/5 border border-primary/10 rounded-xl p-4 flex items-center justify-between"><span class="text-sm font-bold text-primary uppercase tracking-widest">Total Verified Votes</span><span class="text-3xl font-black text-primary">{{ selectedCandidate.votes_count }}</span></div>
          </div>

          <div class="flex justify-end gap-2 mt-6 pt-4 border-t dark:border-border"><button @click="closeViewModal" class="px-8 py-2 bg-gray-900 dark:bg-primary text-white rounded-lg text-sm font-bold uppercase">Close</button></div>
        </div>
      </div>

      <!-- Delete Modal -->
      <div v-if="showDeleteModal && selectedCandidate" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/40" @click="closeDeleteModal"></div>

        <div class="relative bg-card rounded-xl shadow-lg w-full max-w-md p-6 z-50 text-center">
          <div class="w-16 h-16 bg-red-50 dark:bg-red-900/10 rounded-full flex items-center justify-center mx-auto mb-4"><Trash class="w-8 h-8 text-red-600" /></div>
          <h3 class="text-lg font-bold uppercase mb-2">Confirm Removal</h3>
          <p class="text-sm text-gray-500 mb-6">Are you sure you want to remove <span class="font-bold text-gray-900 dark:text-white">{{ selectedCandidate.user.name }}</span>? This action is terminal.</p>
          <div class="flex flex-col gap-2">
            <button @click="confirmDelete" class="w-full py-2 bg-red-600 text-white rounded-lg font-bold uppercase">Terminate Nomination</button>
            <button @click="closeDeleteModal" class="w-full py-2 text-gray-400 font-bold uppercase">Cancel</button>
          </div>
        </div>
      </div>

      <!-- Password Modal -->
      <div v-if="showPasswordModal" class="fixed inset-0 z-[150] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closePasswordModal"></div>

        <div class="relative bg-card rounded-xl shadow-2xl w-full max-w-md p-8 z-[160] text-center">
          <div class="w-16 h-16 bg-emerald-50 dark:bg-emerald-900/10 rounded-full flex items-center justify-center mx-auto mb-6"><CheckCircle2 class="w-8 h-8 text-emerald-600" /></div>
          <h3 class="text-xl font-bold uppercase mb-2">Created Successfully</h3>
          <p class="text-sm text-gray-500 mb-6">Credentials have been sent to the nominee's email.</p>
          
          <div class="bg-muted/50 rounded-xl p-4 mb-6">
            <p class="text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Reference Password</p>
            <div class="flex items-center justify-between bg-white dark:bg-background rounded-lg p-3 border dark:border-border">
              <code class="font-mono font-bold text-lg text-primary">{{ generatedPassword }}</code>
              <button @click="copyPassword" class="p-2 hover:bg-gray-100 rounded-lg transition-colors"><Icon name="copy" class="w-4 h-4" /></button>
            </div>
          </div>

          <button @click="closePasswordModal" class="w-full py-3 bg-primary text-white rounded-xl font-black uppercase text-xs tracking-widest shadow-lg shadow-primary/20">Got It</button>
        </div>
      </div>

    </AppLayout>
  </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
