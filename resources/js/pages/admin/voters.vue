<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Search, Filter, ChevronDown, CheckCircle2, MoreHorizontal, User, Mail, GraduationCap, Calendar, Hash, Clock } from 'lucide-vue-next';

// Interfaces
interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  role: string;
  photo: string | null;
}

interface VoterProfile {
  id: number;
  user_id: number;
  student_id: string;
  course: string;
  year_level: string;
  section: string;
  has_voted: boolean;
  created_at: string;
  updated_at: string;
  user: User;
}

interface PaginatedVoters {
  data: VoterProfile[];
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
  course?: string;
  year_level?: string;
  has_voted?: boolean | null;
}

interface ActiveElection {
  id: number;
  title: string;
}

// Props
const props = defineProps<{
  voters: PaginatedVoters;
  filters: Filters;
  activeElection?: ActiveElection | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Voters', href: '/admin/voters' },
];

// State
const search = ref(props.filters.search || '');
const filterCourse = ref<string | null>(props.filters.course || null);
const filterYear = ref<string | null>(props.filters.year_level || null);
const filterVoted = ref<boolean | null>(props.filters.has_voted ?? null);
const showFilters = ref(false);

// Dropdown state & options
const courseDropdownOpen = ref(false);
const yearDropdownOpen = ref(false);
const votedDropdownOpen = ref(false);

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

const votedOptions = [
  { label: 'All Status', value: null },
  { label: 'Voted', value: true },
  { label: 'Not Voted', value: false },
];

function toggleCourseDropdown() {
  courseDropdownOpen.value = !courseDropdownOpen.value;
  yearDropdownOpen.value = false;
  votedDropdownOpen.value = false;
}

function toggleYearDropdown() {
  yearDropdownOpen.value = !yearDropdownOpen.value;
  courseDropdownOpen.value = false;
  votedDropdownOpen.value = false;
}

function toggleVotedDropdown() {
  votedDropdownOpen.value = !votedDropdownOpen.value;
  courseDropdownOpen.value = false;
  yearDropdownOpen.value = false;
}

function selectCourseOption(option: { label: string; value: string | null }) {
  filterCourse.value = option.value;
  courseDropdownOpen.value = false;
  applyFilters();
}

function selectYearOption(option: { label: string; value: string | null }) {
  filterYear.value = option.value;
  yearDropdownOpen.value = false;
  applyFilters();
}

function selectVotedOption(option: { label: string; value: boolean | null }) {
  filterVoted.value = option.value;
  votedDropdownOpen.value = false;
  applyFilters();
}

function applyFilters() {
  router.get('/admin/voters', {
    search: search.value,
    course: filterCourse.value,
    year_level: filterYear.value,
    has_voted: filterVoted.value,
  }, {
    preserveState: true,
    replace: true,
  });
}

function clearFilters() {
  search.value = '';
  filterCourse.value = null;
  filterYear.value = null;
  filterVoted.value = null;
  applyFilters();
}

function formatDate(dateString: string) {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric' 
  });
}
</script>

<template>
  <div>
    <Head title="Voters Directory" />
    <AppLayout :breadcrumbs="breadcrumbs">
      <div class="flex h-full flex-1 flex-col gap-4 md:gap-8 p-4 md:p-8 min-h-[calc(100vh-64px)]">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-xl md:text-3xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Voter Directory</h1>
            <p class="text-muted-foreground mt-1 text-[11px] md:text-sm font-medium">Manage and monitor verified student voters.</p>
          </div>
          <div class="flex items-center gap-3">
            <div class="px-4 py-2 rounded-xl bg-primary/10 text-primary border border-primary/20">
              <span class="text-[10px] font-black uppercase tracking-widest">{{ voters.total.toLocaleString() }} Total</span>
            </div>
          </div>
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
                  placeholder="Search voter by name, email or student ID" 
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
              <div class="hidden lg:grid grid-cols-3 gap-3">
                <!-- Course Dropdown -->
                <div class="relative min-w-[140px]">
                  <button type="button" @click.stop="toggleCourseDropdown()" class="w-full h-12 px-4 rounded-2xl border-2 border-gray-100 dark:border-border bg-white dark:bg-card text-left text-[10px] font-black uppercase tracking-widest flex items-center justify-between dark:text-foreground hover:border-primary/50 transition-all">
                    <span class="truncate">{{ courseOptions.find(o => String(o.value) === String(filterCourse))?.label ?? 'Course' }}</span>
                    <ChevronDown class="w-3.5 h-3.5 text-primary" :class="{ 'rotate-180': courseDropdownOpen }" />
                  </button>
                  <div v-if="courseDropdownOpen" class="absolute z-[120] mt-2 w-44 bg-white dark:bg-purple-900 border-2 border-slate-100 dark:border-purple-600 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200" @click.outside="courseDropdownOpen = false">
                    <div class="py-1">
                      <div v-for="opt in courseOptions" :key="String(opt.value)" @click="selectCourseOption(opt)" class="px-4 py-3 cursor-pointer hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors text-[10px] font-black uppercase tracking-widest" :class="filterCourse === opt.value ? 'text-primary bg-primary/5' : 'text-gray-700 dark:text-purple-100'">{{ opt.label }}</div>
                    </div>
                  </div>
                </div>

                <!-- Year Dropdown -->
                <div class="relative min-w-[140px]">
                  <button type="button" @click.stop="toggleYearDropdown()" class="w-full h-12 px-4 rounded-2xl border-2 border-gray-100 dark:border-border bg-white dark:bg-card text-left text-[10px] font-black uppercase tracking-widest flex items-center justify-between dark:text-foreground hover:border-primary/50 transition-all">
                    <span class="truncate">{{ yearOptions.find(o => String(o.value) === String(filterYear))?.label ?? 'Year' }}</span>
                    <ChevronDown class="w-3.5 h-3.5 text-primary" :class="{ 'rotate-180': yearDropdownOpen }" />
                  </button>
                  <div v-if="yearDropdownOpen" class="absolute z-[120] mt-2 w-44 bg-white dark:bg-purple-900 border-2 border-slate-100 dark:border-purple-600 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200" @click.outside="yearDropdownOpen = false">
                    <div class="py-1">
                      <div v-for="opt in yearOptions" :key="String(opt.value)" @click="selectYearOption(opt)" class="px-4 py-3 cursor-pointer hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors text-[10px] font-black uppercase tracking-widest" :class="filterYear === opt.value ? 'text-primary bg-primary/5' : 'text-gray-700 dark:text-purple-100'">{{ opt.label }}</div>
                    </div>
                  </div>
                </div>

                <!-- Voted Dropdown -->
                <div class="relative min-w-[140px]">
                  <button type="button" @click.stop="toggleVotedDropdown()" class="w-full h-12 px-4 rounded-2xl border-2 border-gray-100 dark:border-border bg-white dark:bg-card text-left text-[10px] font-black uppercase tracking-widest flex items-center justify-between dark:text-foreground hover:border-primary/50 transition-all">
                    <span class="truncate">{{ votedOptions.find(o => String(o.value) === String(filterVoted))?.label ?? 'Status' }}</span>
                    <ChevronDown class="w-3.5 h-3.5 text-primary" :class="{ 'rotate-180': votedDropdownOpen }" />
                  </button>
                  <div v-if="votedDropdownOpen" class="absolute z-[120] mt-2 w-44 bg-white dark:bg-purple-900 border-2 border-slate-100 dark:border-purple-600 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200" @click.outside="votedDropdownOpen = false">
                    <div class="py-1">
                      <div v-for="opt in votedOptions" :key="String(opt.value)" @click="selectVotedOption(opt)" class="px-4 py-3 cursor-pointer hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors text-[10px] font-black uppercase tracking-widest" :class="filterVoted === opt.value ? 'text-primary bg-primary/5' : 'text-gray-700 dark:text-purple-100'">{{ opt.label }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <button @click="clearFilters" class="hidden lg:block h-12 px-6 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-muted transition-all">Reset</button>
            </div>

            <!-- Mobile Advanced Filters -->
            <div v-if="showFilters" class="lg:hidden grid grid-cols-1 sm:grid-cols-2 gap-3 animate-in slide-in-from-top-2 duration-200">
               <div class="space-y-3">
                  <div v-for="(opt, i) in [
                    { label: 'Course', items: courseOptions, current: filterCourse, select: selectCourseOption },
                    { label: 'Year', items: yearOptions, current: filterYear, select: selectYearOption },
                    { label: 'Status', items: votedOptions, current: filterVoted, select: (o: any) => selectVotedOption({ label: '', value: o.value === 'true' ? true : o.value === 'false' ? false : null }) }
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

        <!-- Table Section -->
        <section class="bg-white dark:bg-card border border-gray-100 dark:border-border rounded-3xl shadow-sm overflow-hidden flex flex-col">
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-gray-50/50 dark:bg-muted/30 border-b border-gray-100 dark:border-border">
                <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                  <th class="px-6 py-4">Voter Identity</th>
                  <th class="px-6 py-4">Academic Placement</th>
                  <th class="px-6 py-4">Verification</th>
                  <th class="px-6 py-4">Status</th>
                  <th class="px-6 py-4 text-right">Joined On</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 dark:divide-border text-[13px]">
                <tr v-for="voter in voters.data" :key="voter.id" class="hover:bg-gray-50/30 dark:hover:bg-muted/5 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                      <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-black text-xs border border-primary/20 overflow-hidden">
                        <img v-if="voter.user.photo" :src="voter.user.photo" class="h-full w-full object-cover" />
                        <span v-else>{{ voter.user.name.charAt(0).toUpperCase() }}</span>
                      </div>
                      <div>
                        <p class="font-bold text-gray-900 dark:text-foreground leading-none">{{ voter.user.name }}</p>
                        <p class="text-[10px] text-gray-400 font-medium mt-1">{{ voter.user.email }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex flex-col gap-0.5">
                      <span class="font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">{{ voter.student_id }}</span>
                      <span class="text-[10px] text-gray-400 font-black uppercase">{{ voter.course }} — {{ voter.year_level }} {{ voter.section }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="voter.user.email_verified_at" class="inline-flex items-center gap-1.5 text-[10px] font-black text-emerald-600 uppercase tracking-widest">
                      <div class="w-1.5 h-1.5 rounded-full bg-current"></div>
                      Verified
                    </span>
                    <span v-else class="inline-flex items-center gap-1.5 text-[10px] font-black text-amber-500 uppercase tracking-widest">
                      <div class="w-1.5 h-1.5 rounded-full bg-current"></div>
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div 
                      :class="voter.has_voted ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : 'bg-gray-50 text-gray-500 border-gray-100 dark:bg-muted dark:text-gray-400 dark:border-border'"
                      class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border"
                    >
                      {{ voter.has_voted ? 'BALLOT CAST' : 'VOTE PENDING' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right whitespace-nowrap">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ formatDate(voter.created_at) }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Info -->
          <div class="px-6 py-4 bg-gray-50/30 dark:bg-muted/10 border-t border-gray-100 dark:border-border flex flex-col sm:flex-row items-center justify-between gap-4 mt-auto">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
              Showing {{ voters.from }} to {{ voters.to }} of {{ voters.total }} Entries
            </p>
            <div class="flex items-center gap-1">
              <button 
                v-for="link in props.voters.links" 
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
    </AppLayout>
  </div>
</template>

<style scoped>
::-webkit-scrollbar { height: 4px; width: 4px; }
::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
</style>
