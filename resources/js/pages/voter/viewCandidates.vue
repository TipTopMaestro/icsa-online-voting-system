<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import VoterLayout from '@/layouts/VoterLayout.vue'
import { Search, Filter, User, Info, Vote, X, ChevronDown, Award } from 'lucide-vue-next';

// Props from backend
interface Props {
  election: any
  candidates: any[]
  positions: any[]
  message: string | null
}

const props = defineProps<Props>()

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

// Group candidates by position
const groupedCandidates = computed(() => {
  const groups: Record<string, any[]> = {}
  
  // Get unique positions from the filtered list
  filteredCandidates.value.forEach(c => {
    if (!groups[c.position]) {
      groups[c.position] = []
    }
    groups[c.position].push(c)
  })
  
  // Sort positions based on the order they appear in props.positions
  const sortedGroups: Record<string, any[]> = {}
  props.positions.forEach(p => {
    if (groups[p.label]) {
      sortedGroups[p.label] = groups[p.label]
    }
  })

  // Catch any positions not in the props.positions list (though there shouldn't be any)
  Object.keys(groups).forEach(pos => {
    if (!sortedGroups[pos]) {
      sortedGroups[pos] = groups[pos]
    }
  })
  
  return sortedGroups
})

const selectedCandidate = ref<any | null>(null)

const initials = (name: string) => name.split(' ').map(s => s[0]).slice(0,2).join('').toUpperCase()

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
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8 min-h-[calc(100vh-64px)]">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-foreground">Candidates</h1>
            <p class="text-sm text-gray-500 dark:text-muted-foreground mt-1">{{ election?.title || 'Election' }}</p>
        </div>

        <!-- No Active Election Message -->
        <div v-if="!election" class="max-w-2xl mx-auto text-center py-10 md:py-20">
          <div class="bg-white dark:bg-card rounded-2xl shadow-sm p-8 md:p-12 border dark:border-border">
            <div class="w-16 h-16 bg-gray-50 dark:bg-muted/50 rounded-full flex items-center justify-center mx-auto mb-6">
                <Users class="w-8 h-8 text-gray-300 dark:text-muted-foreground" />
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2 dark:text-foreground">No Active Election</h3>
            <p class="text-sm text-gray-600 dark:text-muted-foreground">{{ message }}</p>
            <div class="mt-8">
                <Link href="/voter/dashboard" class="text-sm font-bold text-primary hover:underline uppercase tracking-widest">Return to Dashboard</Link>
            </div>
          </div>
        </div>

        <!-- Candidates List (when election is active) -->
        <template v-else>
            <!-- Filters -->
            <div class="mb-10 bg-white dark:bg-card border dark:border-border p-4 rounded-2xl shadow-sm">
                <div class="flex flex-col lg:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1 relative group">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors" />
                        <input 
                            v-model="searchTerm" 
                            type="text" 
                            placeholder="Search by name, party, or platform..." 
                            class="w-full bg-gray-50 dark:bg-background border-none rounded-xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 transition-all dark:text-foreground"
                        />
                    </div>

                    <!-- Position Filter -->
                    <div class="relative w-full lg:w-64">
                        <button
                            @click="dropdownOpen = !dropdownOpen"
                            class="w-full flex items-center justify-between px-4 py-2.5 bg-gray-50 dark:bg-background rounded-xl text-sm font-medium dark:text-foreground border-none focus:ring-2 focus:ring-primary/20 transition-all"
                        >
                            <div class="flex items-center gap-2">
                                <Filter class="w-3.5 h-3.5 text-primary" />
                                <span class="truncate">{{ sortOptionLabel }}</span>
                            </div>
                            <ChevronDown class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': dropdownOpen }" />
                        </button>

                        <div
                            v-show="dropdownOpen"
                            class="absolute left-0 right-0 mt-2 bg-white dark:bg-purple-900 border-2 border-slate-100 dark:border-purple-600 rounded-xl shadow-xl z-50 overflow-hidden animate-in fade-in zoom-in-95 duration-200"
                            @click.outside="dropdownOpen = false"
                        >
                            <div class="max-h-60 overflow-y-auto py-1">
                                <div
                                    v-for="option in options"
                                    :key="option.value"
                                    @click="selectOption(option)"
                                    class="px-4 py-2 hover:bg-primary/10 dark:hover:bg-purple-800 cursor-pointer text-sm transition-colors"
                                    :class="sortOption === option.value ? 'text-primary font-bold bg-primary/5 dark:bg-purple-800' : 'text-gray-700 dark:text-purple-100'"
                                >
                                    {{ option.label }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Candidates Grouped by Position -->
            <div v-if="Object.keys(groupedCandidates).length > 0" class="space-y-20 pb-1">
                <div v-for="(candidates, positionName) in groupedCandidates" :key="positionName" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <!-- Group Header -->
                    <div class="flex items-center gap-6 mb-10 relative z-10">
                        <div class="flex-shrink-0 w-16 h-16 rounded-3xl bg-primary/10 flex items-center justify-center border border-primary/20 shadow-md">
                            <Award class="w-8 h-8 text-primary" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <h2 class="text-2xl md:text-2xl font-black text-gray-900 dark:text-foreground uppercase tracking-tighter leading-none">{{ positionName }}</h2>
                            <div class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full bg-primary animate-pulse"></div>
                                <p class="text-[11px] md:text-xs font-black text-primary/70 uppercase tracking-[0.25em]">{{ candidates.length }} Nominee{{ candidates.length > 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        <div class="flex-1 h-px bg-gradient-to-r from-gray-200 to-transparent dark:from-border/50 dark:to-transparent ml-8"></div>
                    </div>

                    <!-- Candidates Grid for this Position -->
                    <div class="grid gap-x-8 gap-y-20 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div 
                            v-for="c in candidates" 
                            :key="c.id" 
                            class="group flex flex-col items-center bg-white dark:bg-card rounded-2xl shadow-sm border border-gray-100 dark:border-border pt-14 pb-6 px-5 text-center hover:shadow-xl hover:shadow-primary/5 hover:-translate-y-1 transition-all relative"
                        >
                            <!-- Avatar -->
                            <div class="absolute -top-10 left-1/2 -translate-x-1/2">
                                <div class="relative">
                                    <img 
                                        v-if="c.image" 
                                        :src="c.image" 
                                        class="h-20 w-20 md:h-24 md:w-24 rounded-full object-cover ring-4 ring-white dark:ring-card shadow-lg border-2 border-primary/20 group-hover:border-primary/50 transition-colors" 
                                    />
                                    <div v-else class="h-20 w-20 md:h-24 md:w-24 rounded-full bg-gradient-to-tr from-primary/10 to-primary/30 flex items-center justify-center text-primary font-black text-2xl md:text-3xl ring-4 ring-white dark:ring-card shadow-lg">
                                        {{ initials(c.name) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Name and Title -->
                            <div class="flex-1">
                                <h3 class="text-base md:text-lg font-black text-gray-900 dark:text-foreground leading-snug group-hover:text-primary transition-colors">{{ c.name }}</h3>
                                <p class="text-[10px] md:text-xs font-bold text-gray-400 dark:text-muted-foreground uppercase tracking-widest mt-1.5">{{ c.party || 'Independent' }}</p>
                                
                                <div class="mt-4 flex flex-wrap justify-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-primary/10 text-primary border border-primary/10 uppercase">
                                        {{ c.course || 'IT' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 mt-6 w-full">
                                <button 
                                    @click="openModal(c)" 
                                    class="flex-1 px-3 py-2 bg-gray-50 dark:bg-muted/50 text-gray-700 dark:text-foreground rounded-xl font-bold text-[11px] hover:bg-primary/10 hover:text-primary transition-all border border-transparent hover:border-primary/10"
                                >
                                    <div class="flex items-center justify-center gap-1.5">
                                        <Info class="w-3.5 h-3.5" />
                                        VIEW INFO
                                    </div>
                                </button>
                                <Link 
                                    :href="`/voter/vote?highlight=${c.id}`" 
                                    class="flex-1 px-3 py-2 bg-primary text-primary-foreground rounded-xl font-bold text-[11px] hover:bg-primary/90 transition-all shadow-md shadow-primary/10"
                                >
                                    <div class="flex items-center justify-center gap-1.5">
                                        <Vote class="w-3.5 h-3.5" />
                                        VOTE NOW
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty results -->
            <div v-else class="text-center py-20 bg-white dark:bg-card border dark:border-border rounded-3xl shadow-sm">
                <div class="w-16 h-16 bg-gray-50 dark:bg-muted/50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Search class="w-8 h-8 text-gray-300" />
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-foreground">No matches found</h3>
                <p class="text-sm text-gray-500 mt-1">Try adjusting your search or position filter</p>
                <button @click="searchTerm = ''; sortOption = 'all'" class="mt-6 text-sm font-bold text-primary uppercase tracking-widest">Clear all filters</button>
            </div>
        </template>
      </div>
    </VoterLayout>
  </div>

  <!-- View Candidate Modal (Modern Scaling) -->
  <div v-if="selectedCandidate" class="fixed inset-0 z-[100] flex items-center justify-center p-4 animate-in fade-in duration-300">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>

    <div class="relative bg-white dark:bg-card rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col z-50 border dark:border-border animate-in slide-in-from-bottom-10 duration-300">
      <!-- Modal Header -->
      <div class="h-28 md:h-36 bg-gradient-to-r from-primary to-purple-600 relative overflow-hidden">
        <!-- Abstract pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-32 h-32 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-48 h-48 bg-white rounded-full translate-x-1/3 translate-y-1/3"></div>
        </div>
        <button @click="closeModal" class="absolute top-4 right-4 p-2 bg-white/20 hover:bg-white/30 backdrop-blur-md rounded-full text-white transition-colors">
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="px-6 pb-6 flex flex-col flex-1 overflow-y-auto scrollbar-hide">
        <!-- Profile Identity -->
        <div class="flex flex-col items-center -mt-14 md:-mt-18 mb-8">
            <div class="relative">
                <img 
                    v-if="selectedCandidate.image" 
                    :src="selectedCandidate.image" 
                    class="w-28 h-28 md:w-36 md:h-36 rounded-full object-cover border-4 border-white dark:border-card shadow-2xl"
                />
                <div 
                    v-else 
                    class="w-28 h-28 md:w-36 md:h-36 rounded-full bg-gradient-to-tr from-primary/20 to-primary flex items-center justify-center text-white font-black text-3xl md:text-5xl border-4 border-white dark:border-card shadow-2xl"
                >
                    {{ initials(selectedCandidate.name) }}
                </div>
            </div>
            <div class="mt-5 text-center px-4">
                <h4 class="text-xl md:text-3xl font-black text-gray-900 dark:text-foreground tracking-tight">{{ selectedCandidate.name }}</h4>
                <p class="text-xs md:text-sm font-bold text-primary mt-1.5 uppercase tracking-widest flex items-center justify-center gap-2">
                    <Award class="w-4 h-4" />
                    {{ selectedCandidate.position }}
                </p>
                <p class="text-xs text-gray-400 dark:text-muted-foreground mt-2">{{ selectedCandidate.email }}</p>
            </div>
        </div>

        <!-- Meta Grid -->
        <div class="grid grid-cols-2 gap-3 mb-8">
          <div class="p-4 bg-gray-50 dark:bg-muted/30 rounded-2xl border dark:border-border text-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Affiliation</p>
            <p class="text-sm font-bold text-gray-900 dark:text-foreground truncate">{{ selectedCandidate.party || 'Independent' }}</p>
          </div>
          <div class="p-4 bg-gray-50 dark:bg-muted/30 rounded-2xl border dark:border-border text-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Program</p>
            <p class="text-sm font-bold text-gray-900 dark:text-foreground truncate">{{ selectedCandidate.course }}</p>
          </div>
        </div>

        <!-- Campaign Statement -->
        <div class="space-y-4">
          <h5 class="text-[10px] font-black text-gray-400 dark:text-muted-foreground uppercase tracking-widest flex items-center gap-2 px-1">
            <Icon name="file-text" class="w-3 h-3 text-primary" />
            Campaign Platform
          </h5>
          <div class="p-5 md:p-6 bg-primary/5 dark:bg-primary/10 rounded-2xl border border-primary/10 relative">
            <div class="absolute -top-3 -left-1 text-4xl text-primary/20 font-serif">"</div>
            <p class="text-sm md:text-base leading-relaxed text-gray-800 dark:text-gray-300 whitespace-pre-wrap italic">
              {{ selectedCandidate.platform || 'This candidate has not provided a campaign statement yet.' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Modal Footer Actions -->
      <div class="p-6 border-t dark:border-border bg-gray-50/50 dark:bg-muted/10 flex flex-col sm:flex-row gap-3">
        <button 
          @click="closeModal"
          class="flex-1 h-12 border-2 border-gray-200 dark:border-border rounded-xl text-xs font-bold text-gray-500 dark:text-muted-foreground uppercase tracking-widest hover:bg-gray-100 dark:hover:bg-muted transition-colors order-2 sm:order-1"
        >
          CLOSE PROFILE
        </button>
        <Link 
          :href="`/voter/vote?highlight=${selectedCandidate.id}`" 
          class="flex-1 h-12 bg-primary text-primary-foreground rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-primary/90 transition-all shadow-lg shadow-primary/20 inline-flex items-center justify-center gap-2 order-1 sm:order-2"
        >
          <Vote class="w-4 h-4" />
          VOTE FOR CANDIDATE
        </Link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
