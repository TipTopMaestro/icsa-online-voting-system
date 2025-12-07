<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

// Interfaces
interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  role: string;
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
  { label: 'All Year Levels', value: null },
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

function selectCourseOption(option: { label: string; value: string | null }) {
  filterCourse.value = option.value as string | null;
  courseDropdownOpen.value = false;
  applyFilters();
}

function selectYearOption(option: { label: string; value: string | null }) {
  filterYear.value = option.value as string | null;
  yearDropdownOpen.value = false;
  applyFilters();
}

function selectVotedOption(option: { label: string; value: boolean | null }) {
  filterVoted.value = option.value as boolean | null;
  votedDropdownOpen.value = false;
  applyFilters();
}

function toggleCourseDropdown() {
  const next = !courseDropdownOpen.value;
  courseDropdownOpen.value = next;
  if (next) {
    yearDropdownOpen.value = false;
    votedDropdownOpen.value = false;
  }
}

function toggleYearDropdown() {
  const next = !yearDropdownOpen.value;
  yearDropdownOpen.value = next;
  if (next) {
    courseDropdownOpen.value = false;
    votedDropdownOpen.value = false;
  }
}

function toggleVotedDropdown() {
  const next = !votedDropdownOpen.value;
  votedDropdownOpen.value = next;
  if (next) {
    courseDropdownOpen.value = false;
    yearDropdownOpen.value = false;
  }
}

// Methods
function applyFilters() {
  const params = {
    search: search.value || undefined,
    course: filterCourse.value || undefined,
    year_level: filterYear.value || undefined,
    has_voted: filterVoted.value !== null ? filterVoted.value : undefined,
  };
  
  console.log('Applying filters:', params);
  
  router.get('/admin/voters', params, {
    preserveState: true,
    preserveScroll: true,
  });
}

function clearFilters() {
  search.value = '';
  filterCourse.value = null;
  filterYear.value = null;
  filterVoted.value = null;
  router.get('/admin/voters');
}

function goToPage(page: number) {
  if (page < 1 || page > props.voters.last_page) return;
  router.get('/admin/voters', {
    ...props.filters,
    page,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
}
</script>

<template>
  <Head title="Voter Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Voter Management</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
          View and manage all registered voters in the system.
          <span v-if="activeElection" class="ml-2 text-primary font-medium">
            (Voting status for: {{ activeElection.title }})
          </span>
        </p>
      </div>

      <!-- Search & Filters -->
      <section class="bg-card rounded-xl shadow-sm p-4 mb-6 border">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
          <div class="flex items-center flex-1 bg-muted/50 rounded-lg px-3 py-2 gap-2">
            <svg class="w-5 h-5 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
            </svg>
            <input 
              v-model="search" 
              @keyup.enter="applyFilters"
              type="search" 
              placeholder="Search voter by name, email or student ID" 
              class="bg-transparent w-full text-sm focus:outline-none" 
            />
          </div>

          <div class="flex gap-2 items-center">
            <div class="relative">
              <button
                type="button"
                @click.stop="toggleCourseDropdown()"
                class="w-full flex items-center justify-between px-4 py-2 rounded-xl border border-slate-300 bg-white text-left shadow-sm focus:ring-2 focus:ring-purple-800 text-sm"
                style="min-width: 9rem;"
              >
                <span>
                  {{ courseOptions.find(o => o.value === filterCourse)?.label ?? 'All Courses' }}
                </span>
                <svg class="w-4 h-4 text-slate-600 transition-transform duration-200" :class="{ 'rotate-180': courseDropdownOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <div v-if="courseDropdownOpen" class="absolute z-50 mt-2 w-44 rounded-xl border-2 border-purple-800 bg-white shadow-xl overflow-hidden">
                <div v-for="opt in courseOptions" :key="String(opt.value)" @click="selectCourseOption(opt)" class="px-4 py-2 cursor-pointer hover:bg-purple-100 text-sm">
                  {{ opt.label }}
                </div>
              </div>
            </div>

            <div class="relative">
              <button
                type="button"
                @click.stop="toggleYearDropdown()"
                class="w-full flex items-center justify-between px-4 py-2 rounded-xl border border-slate-300 bg-white text-left shadow-sm focus:ring-2 focus:ring-purple-800 text-sm"
                style="min-width: 9rem;"
              >
                <span>
                  {{ yearOptions.find(o => o.value === filterYear)?.label ?? 'All Year Levels' }}
                </span>
                <svg class="w-4 h-4 text-slate-600 transition-transform duration-200" :class="{ 'rotate-180': yearDropdownOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <div v-if="yearDropdownOpen" class="absolute z-50 mt-2 w-44 rounded-xl border-2 border-purple-800 bg-white shadow-xl overflow-hidden">
                <div v-for="opt in yearOptions" :key="String(opt.value)" @click="selectYearOption(opt)" class="px-4 py-2 cursor-pointer hover:bg-purple-100 text-sm">
                  {{ opt.label }}
                </div>
              </div>
            </div>

            <div class="relative">
              <button
                type="button"
                @click.stop="toggleVotedDropdown()"
                class="w-full flex items-center justify-between px-4 py-2 rounded-xl border border-slate-300 bg-white text-left shadow-sm focus:ring-2 focus:ring-purple-800 text-sm"
                style="min-width: 9rem;"
              >
                <span>
                  {{ votedOptions.find(o => o.value === filterVoted)?.label ?? 'All Status' }}
                </span>
                <svg class="w-4 h-4 text-slate-600 transition-transform duration-200" :class="{ 'rotate-180': votedDropdownOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <div v-if="votedDropdownOpen" class="absolute z-50 mt-2 w-44 rounded-xl border-2 border-purple-800 bg-white shadow-xl overflow-hidden">
                <div v-for="opt in votedOptions" :key="String(opt.value)" @click="selectVotedOption(opt)" class="px-4 py-2 cursor-pointer hover:bg-purple-100 text-sm">
                  {{ opt.label }}
                </div>
              </div>
            </div>

            

            <button @click="clearFilters" class="px-3 py-2 text-sm hover:bg-accent rounded-lg transition">
              Clear
            </button>
          </div>
        </div>
      </section>

      <!-- Empty State -->
      <section v-if="props.voters.data.length === 0" class="bg-card rounded-xl shadow-sm p-12 text-center border">
        <div class="text-muted-foreground mb-4">
          <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </div>
        <h3 class="text-lg font-semibold mb-2">No voters found</h3>
        <p class="text-muted-foreground">No voters match your search criteria or no voters have registered yet.</p>
      </section>

      <!-- Table -->
      <section v-else class="bg-card rounded-xl shadow-md p-4 border">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-border">
            <thead>
              <tr class="text-left text-sm text-muted-foreground">
                <th class="py-3 px-4 font-semibold">Student ID</th>
                <th class="py-3 px-4 font-semibold">Name</th>
                <th class="py-3 px-4 font-semibold">Email</th>
                <th class="py-3 px-4 font-semibold">Course / Year</th>
                <th class="py-3 px-4 font-semibold">Voting Status</th>
                <th class="py-3 px-4 font-semibold">Registered Date</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-border text-sm">
              <tr v-for="voter in props.voters.data" :key="voter.id" class="hover:bg-muted/50 transition">
                <td class="py-3 px-4 font-medium">{{ voter.student_id }}</td>

                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-muted font-medium">
                      {{ voter.user.name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                      <div class="font-medium truncate max-w-xs">{{ voter.user.name }}</div>
                      <div class="text-xs text-muted-foreground">{{ voter.user.role }}</div>
                    </div>
                  </div>
                </td>

                <td class="py-3 px-4">
                  <div class="max-w-xs truncate">{{ voter.user.email }}</div>
                </td>

                <td class="py-3 px-4 max-w-xs truncate">
                  {{ voter.course }} {{ voter.year_level }}{{ voter.section }}
                </td>

                <td class="py-3 px-4">
                  <span
                    v-if="voter.has_voted"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-50 text-green-800 dark:bg-green-500/10 dark:text-green-400"
                  >
                    <span class="w-2 h-2 rounded-full mr-2 bg-green-500 dark:bg-green-400"></span>
                    Voted
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-muted text-foreground"
                  >
                    <span class="w-2 h-2 rounded-full mr-2 bg-muted-foreground"></span>
                    Not Voted
                  </span>
                </td>

                <td class="py-3 px-4 text-xs text-muted-foreground">
                  {{ new Date(voter.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex items-center justify-between">
          <div class="text-sm text-muted-foreground">
            Showing {{ props.voters.from ?? 0 }} to {{ props.voters.to ?? 0 }} of {{ props.voters.total }} voters
          </div>
          <nav class="inline-flex items-center gap-2" role="navigation" aria-label="Pagination">
            <button 
              @click="goToPage(props.voters.current_page - 1)" 
              :disabled="props.voters.current_page === 1"
              type="button" 
              class="px-3 py-1 rounded-md text-sm border bg-card hover:bg-accent disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>

            <button
              v-for="page in props.voters.last_page"
              :key="page"
              v-show="page === 1 || page === props.voters.last_page || Math.abs(page - props.voters.current_page) <= 1"
              @click="goToPage(page)"
              :class="[
                'px-3 py-1 rounded-md text-sm border',
                page === props.voters.current_page
                  ? 'bg-[#5A2D6F] text-white'
                  : 'bg-card hover:bg-[#4b255c]'
              ]"
            >
              {{ page }}
            </button>

            <button 
              @click="goToPage(props.voters.current_page + 1)" 
              :disabled="props.voters.current_page === props.voters.last_page"
              type="button" 
              class="px-3 py-1 rounded-md text-sm border bg-card hover:bg-accent disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </nav>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
