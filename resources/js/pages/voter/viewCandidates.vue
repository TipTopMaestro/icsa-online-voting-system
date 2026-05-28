<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import VoterLayout from '@/layouts/VoterLayout.vue'

// Props from backend
interface Props {
  election: any
  candidates: any[]
  positions: any[]
  message: string | null
}

const props = defineProps<Props>()

// Debug: Log candidates data
console.log('Candidates data:', props.candidates)

// Search and filter state
const searchTerm = ref('')
const sortOption = ref('all')
const dropdownOpen = ref(false)

// Dropdown options - add "All Positions" + dynamic positions from backend
const options = computed(() => [
  { value: 'all', label: 'All Positions' },
  ...props.positions.map(p => ({ value: p.value, label: p.label }))
])

// Dropdown label
const sortOptionLabel = computed(() => {
  return options.value.find(o => o.value === sortOption.value)?.label || 'All Positions'
})

// Select option handler
const selectOption = (option: { value: string; label: string }) => {
  sortOption.value = option.value
  dropdownOpen.value = false
}

// Helper function to get photo URL
const getPhotoUrl = (photo: string) => {
  return `/storage/candidates/${photo}`
}

// Filter candidates
const filteredCandidates = computed(() => {
  const term = searchTerm.value.trim().toLowerCase()
  const list = props.candidates.filter(c => {
    if (!term) return true
    const hay = `${c.name ?? ''} ${c.position ?? ''} ${c.party ?? ''} ${c.course ?? ''} ${c.platform ?? ''}`.toLowerCase()
    return hay.includes(term)
  })

  // If a specific position is selected, filter by position
  if (sortOption.value !== 'all') {
    return list.filter(c => (c.position || '') === sortOption.value).sort((a, b) => (a.name || '').localeCompare(b.name || ''))
  }

  return list
})

const selectedCandidate = ref<any | null>(null)

const initials = (name: string) => name.split(' ').map(s => s[0]).slice(0,2).join('').toUpperCase()

const getCandidatePhoto = (photo: string | null) => {
  if (!photo) return '/images/default-avatar.png'
  return photo.startsWith('http') ? photo : `/storage/candidates/${photo}`
}

const openModal = (candidate: any) => {
  selectedCandidate.value = candidate
}

const closeModal = () => {
  selectedCandidate.value = null
}
</script>

<template>
  <div>
    <Head title="View Candidates" />
    <VoterLayout>
        <!-- Page body -->
    <main class="min-h-screen bg-gray-50 py-6 sm:py-8 lg:py-10">
      <div class="max-w-1xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- No Active Election Message -->
        <div v-if="!election" class="max-w-2xl mx-auto text-center py-12">
          <div class="bg-white rounded-2xl shadow-lg p-8">
            <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">No Active Election</h3>
            <p class="text-gray-600">{{ message }}</p>
          </div>
        </div>

        <!-- Candidates List (when election is active) -->
        <template v-else>
        <!-- Sort then Search (compact) -->
        <div class="mb-6 sm:mb-10 px-2 sm:px-6">
          <div class="flex flex-col sm:flex-row sm:items-center gap-3">
          <div class="relative w-full sm:w-44">
        <!-- Button -->
        <button
        @click="dropdownOpen = !dropdownOpen"
        class="w-full flex items-center justify-between px-4 py-2 rounded-xl
              border border-slate-300 bg-white text-left shadow-sm
              focus:ring-2 focus:ring-purple-800 text-sm"
      >
        <span>{{ sortOptionLabel }}</span>

        <!-- Arrow Icon -->
        <svg
          class="w-6 h-6 text-purple-800 transition-transform duration-200"
          :class="{ 'rotate-180': dropdownOpen }"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 9l-7 7-7-7" />
        </svg>
        </button>

        <!-- Options Panel -->
        <div
          v-show="dropdownOpen"
          class="absolute z-50 mt-2 w-full rounded-xl border-2 border-purple-800 bg-white shadow-xl overflow-hidden ring-1 ring-purple-50"
        >
          <div
            v-for="option in options"
            :key="option.value"
            @click="selectOption(option)"
            class="px-4 py-2 cursor-pointer hover:bg-purple-100 text-sm"
          >
            {{ option.label }}
          </div>
        </div>
      </div>

            <div class="w-full sm:w-44 relative">
              <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M21 21l-4.35-4.40" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="11" cy="11" r="8" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <input v-model="searchTerm" type="search" placeholder="Search candidates..." aria-label="Search" class="bg-white w-full px-4 py-2 pl-10 border border-slate-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-800 text-sm" />
            </div>
          </div>
        </div>

        <!-- Candidates Grid -->
        <div class="grid gap-6 sm:gap-6 lg:gap-8 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 px-2 sm:px-0">
          <div v-for="c in filteredCandidates" :key="c.id" class="flex flex-col items-center bg-white rounded-2xl shadow-lg pt-16 pb-6 px-4 sm:px-6 text-center hover:shadow-xl transition-shadow relative mt-8">
            <!-- Avatar (overlapping) -->
            <img v-if="c.image" :src="c.image" :alt="c.name" class="absolute -top-12 left-1/2 transform -translate-x-1/2 h-24 w-24 sm:h-28 sm:w-28 rounded-full object-cover ring-4 ring-white shadow-lg" />
            <div v-else class="absolute -top-12 left-1/2 transform -translate-x-1/2 h-24 w-24 sm:h-28 sm:w-28 rounded-full bg-gradient-to-tr from-purple-200 to-purple-600 flex items-center justify-center text-white font-bold text-3xl sm:text-4xl ring-4 ring-white shadow-lg">
              {{ initials(c.name) }}
            </div>

            <!-- Name and Title -->
            <h3 class="text-xl font-bold text-slate-900 mt-2 px-2">{{ c.name }}</h3>
            <p class="text-sm text-slate-600 font-medium mt-1">{{ c.position }}</p>
            <p v-if="c.party" class="text-xs text-purple-600 font-medium mt-1">{{ c.party }}</p>

            <!-- Buttons -->
            <div class="flex gap-3 mt-6 w-full">
              <button @click="openModal(c)" class="flex-1 px-4 py-2 bg-purple-100 text-purple-700 rounded-lg font-medium hover:bg-purple-200 transition-colors text-sm">View Info</button>
              <Link :href="`/voter/vote?highlight=${c.id}`" class="flex-1 px-4 py-2 bg-purple-800 text-white rounded-lg font-medium hover:bg-purple-900 transition-colors text-sm inline-flex items-center justify-center">Vote</Link>
            </div>
          </div>
        </div>
        </template>
      </div>
    </main>

    <!-- View Candidate Modal -->
    <div v-if="selectedCandidate" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/40" @click="closeModal"></div>

      <div class="relative bg-white dark:bg-card rounded-xl shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto p-6 z-50 border dark:border-border">
        <!-- Header -->
        <div class="flex items-start justify-between mb-6">
          <h3 class="text-xl font-semibold dark:text-foreground">Candidate Details</h3>
          <button @click="closeModal" class="text-muted-foreground hover:text-foreground transition">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="space-y-6">
          <!-- Photo and Basic Info -->
          <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
            <img 
              v-if="selectedCandidate.image" 
              :src="selectedCandidate.image" 
              :alt="selectedCandidate.name"
              class="w-32 h-32 rounded-full object-cover border-4 border-purple-100 dark:border-primary/20"
            />
            <div 
              v-else 
              class="w-32 h-32 rounded-full bg-gradient-to-tr from-purple-200 to-purple-600 flex items-center justify-center text-white font-bold text-3xl border-4 border-purple-100 dark:border-primary/20"
            >
              {{ initials(selectedCandidate.name) }}
            </div>
            <div class="flex-1 text-center sm:text-left">
              <h4 class="text-2xl font-bold mb-2 dark:text-foreground">{{ selectedCandidate.name }}</h4>
              <p class="text-muted-foreground mb-3">{{ selectedCandidate.email }}</p>
              <div class="flex flex-wrap gap-2 justify-center sm:justify-start">
                <span v-if="selectedCandidate.party" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 dark:bg-primary/10 text-purple-700 dark:text-primary">
                  {{ selectedCandidate.party }}
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-muted text-gray-700 dark:text-muted-foreground">
                  {{ selectedCandidate.course }}
                </span>
              </div>
            </div>
          </div>

          <!-- Details Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t dark:border-border">
            <div>
              <label class="block text-sm font-medium text-muted-foreground mb-1">Position</label>
              <p class="font-semibold dark:text-foreground">{{ selectedCandidate.position }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-muted-foreground mb-1">Election</label>
              <p class="font-semibold dark:text-foreground">{{ election.title }}</p>
            </div>
          </div>

          <!-- Platform -->
          <div class="pt-4 border-t dark:border-border">
            <label class="block text-sm font-medium text-muted-foreground mb-2">Platform</label>
            <div class="bg-gray-50 dark:bg-muted/30 rounded-lg p-4">
              <p class="text-sm leading-relaxed dark:text-foreground whitespace-pre-wrap">
                {{ selectedCandidate.platform || 'No platform provided' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 pt-4 border-t dark:border-border flex gap-3">
          <button 
            @click="closeModal"
            class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-border rounded-lg text-gray-700 dark:text-foreground hover:bg-gray-50 dark:hover:bg-muted transition font-medium"
          >
            Close
          </button>
          <Link 
            :href="`/voter/vote?highlight=${selectedCandidate.id}`" 
            class="flex-1 px-4 py-2.5 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition inline-flex items-center justify-center"
          >
            Vote for this Candidate
          </Link>
        </div>
      </div>
    </div>
    </VoterLayout>
  </div>
</template>