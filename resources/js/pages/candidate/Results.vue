<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Icon from '@/components/Icon.vue';
import { ChevronDown, Calendar, BarChart3, Users, CheckCircle2, Flag, Award, X, Inbox } from 'lucide-vue-next';
import Modal from '@/components/Modal.vue'
import ModalTrigger from '@/components/ModalTrigger.vue'
import CandidateLayout from '@/layouts/CandidateLayout.vue'

interface Election {
  id: number;
  title: string;
  description: string;
  status: string;
  startDate: string;
  is_active: boolean;
}

interface Candidate {
  id: number;
  name: string;
  photo: string;
  votes: number;
  percentage: number;
  partylist: string | null;
  course: string;
  year_level: string;
  section: string;
  isWinner: boolean;
}

interface Position {
  id: number;
  name: string;
}

interface Statistics {
  totalVoters: number;
  votedCount: number;
  abstainedCount: number;
  turnoutPercentage: number;
  totalPositions: number;
  totalCandidates: number;
}

type ResultMap = Record<string, Candidate[]>;

// Props from backend
const props = defineProps<{
    elections: Election[];
    selectedElection: Election | null;
    positions: Position[];
    results: ResultMap;
    statistics: Statistics | null;
}>()

const showElectionModal = ref(false)
const selectedPosition = ref('all')
const sortBy = ref('position') // 'position' or 'votes'

// Dropdown open states for styled panels
const positionDropdownOpen = ref(false)
const sortDropdownOpen = ref(false)

// Position options (preserve original values: use position.name)
const positionOptions = computed(() => [
    { value: 'all', label: 'All Positions' },
    ...props.positions.map(p => ({ value: p.name, label: p.name }))
])

const selectedPositionLabel = computed(() => {
    return positionOptions.value.find(o => o.value === selectedPosition.value)?.label || 'All Positions'
})

// Sort options
const sortOptions = [
    { value: 'position', label: 'Sort by Position' },
    { value: 'votes', label: 'Sort by Total Votes' }
]

const sortByLabel = computed(() => {
    return sortOptions.find(o => o.value === sortBy.value)?.label || 'Sort by Position'
})

function togglePositionDropdown(): void {
    positionDropdownOpen.value = !positionDropdownOpen.value
    if (positionDropdownOpen.value) sortDropdownOpen.value = false
}

function toggleSortDropdown(): void {
    sortDropdownOpen.value = !sortDropdownOpen.value
    if (sortDropdownOpen.value) positionDropdownOpen.value = false
}

function selectPositionOption(option: { value: string; label: string }): void {
    selectedPosition.value = option.value
    positionDropdownOpen.value = false
}

function selectSortOption(option: { value: string; label: string }): void {
    sortBy.value = option.value
    sortDropdownOpen.value = false
}

const currentElection = computed(() => props.selectedElection)

// Last updated time
const lastUpdated = computed(() => {
    return new Date().toLocaleString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric',
        hour: '2-digit', 
        minute: '2-digit'
    })
})

// Filter results based on selected position
const filteredResults = computed(() => {
    if (selectedPosition.value === 'all') return props.results
    
    const filtered: ResultMap = {}
    if (props.results[selectedPosition.value]) {
        filtered[selectedPosition.value] = props.results[selectedPosition.value]
    }
    return filtered
})

// Sorted results
const sortedResults = computed(() => {
    const entries = Object.entries(filteredResults.value)
    
    if (sortBy.value === 'votes') {
        return entries.sort((a, b) => {
            const aTotal = a[1].reduce((sum, c) => sum + c.votes, 0)
            const bTotal = b[1].reduce((sum, c) => sum + c.votes, 0)
            return bTotal - aTotal
        })
    }
    
    return entries // Default: by position order
})

function selectElection(election: Election) {
    router.get('/candidate/results', { election_id: election.id }, {
        preserveState: false,
        preserveScroll: false
    })
    showElectionModal.value = false
}

function getCandidatePhoto(photo: string) {
    return photo || '/images/profile.png'
}
</script>

<template>
    <div>
        <Head title="Election Results" />

        <CandidateLayout>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8 min-h-[calc(100vh-64px)]">
                
                <!-- No Election -->
                <div v-if="!selectedElection" class="flex items-center justify-center min-h-[60vh]">
                    <div class="text-center bg-white dark:bg-card border dark:border-border p-10 md:p-20 rounded-3xl shadow-sm max-w-lg w-full">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 dark:bg-muted/50 mb-6">
                            <BarChart3 class="w-10 h-10 text-gray-300 dark:text-muted-foreground" />
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-foreground mb-2">No Results Available</h3>
                        <p class="text-sm text-gray-500 dark:text-muted-foreground mb-8">Electoral data will appear here once an election is finalized or ongoing.</p>
                        <button @click="showElectionModal = true" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-primary/90 transition-all shadow-lg shadow-primary/10">Select an Election</button>
                    </div>
                </div>

                <!-- Election Results -->
                <template v-else>
                    <!-- Header Section -->
                    <div class="mb-8">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <h1 class="text-xl md:text-3xl font-black text-gray-900 dark:text-foreground tracking-tight uppercase">
                                        {{ currentElection?.title }}
                                    </h1>
                                    <span 
                                        :class="[
                                            'inline-flex items-center gap-1.5 px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full border',
                                            currentElection?.is_active 
                                                ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-100 dark:border-green-800'
                                                : 'bg-gray-50 text-gray-600 dark:bg-muted dark:text-gray-400 border-gray-100 dark:border-border'
                                        ]"
                                    >
                                        <span v-if="currentElection?.is_active" class="w-1.5 h-1.5 rounded-full bg-green-600 dark:bg-green-400 animate-pulse" />
                                        {{ currentElection?.is_active ? 'Live' : 'Archived' }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-muted-foreground mt-2 font-medium max-w-2xl line-clamp-2 md:line-clamp-none">{{ currentElection?.description }}</p>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <!-- Election Selector -->
                                <button
                                    @click="showElectionModal = true"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-card border-2 border-slate-200 dark:border-border rounded-xl shadow-sm hover:border-primary/50 transition-all text-xs font-bold uppercase tracking-widest dark:text-foreground"
                                >
                                    <Calendar class="w-4 h-4 text-primary" />
                                    Change Election
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Bar -->
                    <div v-if="statistics" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                        <div class="bg-white dark:bg-card p-4 rounded-2xl border dark:border-border shadow-sm">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Electoral Turnout</p>
                            <div class="flex items-end gap-2">
                                <span class="text-xl font-black text-gray-900 dark:text-foreground">{{ statistics.turnoutPercentage }}%</span>
                                <span class="text-[10px] text-primary font-bold mb-1">{{ statistics.votedCount }}/{{ statistics.totalVoters }}</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-muted h-1 rounded-full mt-2 overflow-hidden">
                                <div class="bg-primary h-full rounded-full transition-all duration-1000" :style="{ width: statistics.turnoutPercentage + '%' }"></div>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-card p-4 rounded-2xl border dark:border-border shadow-sm">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Votes</p>
                            <p class="text-xl font-black text-gray-900 dark:text-foreground">{{ statistics.votedCount.toLocaleString() }}</p>
                            <div class="flex items-center gap-1.5 mt-2">
                                <Users class="w-3 h-3 text-primary" />
                                <span class="text-[10px] text-gray-500 font-medium">Cast Ballots</span>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-card p-4 rounded-2xl border dark:border-border shadow-sm">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Positions</p>
                            <p class="text-xl font-black text-gray-900 dark:text-foreground">{{ statistics.totalPositions }}</p>
                            <div class="flex items-center gap-1.5 mt-2">
                                <Award class="w-3 h-3 text-accent" />
                                <span class="text-[10px] text-gray-500 font-medium">Open Roles</span>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-card p-4 rounded-2xl border dark:border-border shadow-sm">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Runners</p>
                            <p class="text-xl font-black text-gray-900 dark:text-foreground">{{ statistics.totalCandidates }}</p>
                            <div class="flex items-center gap-1.5 mt-2">
                                <Flag class="w-3 h-3 text-green-500" />
                                <span class="text-[10px] text-gray-500 font-medium">All Candidates</span>
                            </div>
                        </div>
                    </div>

                    <!-- Filters Bar -->
                    <div class="mb-6 flex flex-col md:flex-row gap-3">
                        <!-- Position Filter -->
                        <div class="relative flex-1 md:max-w-xs">
                            <button
                                @click="togglePositionDropdown"
                                class="w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-card border-2 border-slate-200 dark:border-border rounded-xl text-left shadow-sm hover:border-primary/50 transition-all text-xs font-bold uppercase tracking-widest dark:text-foreground"
                            >
                                <span class="truncate">{{ selectedPositionLabel }}</span>
                                <ChevronDown class="w-4 h-4 text-primary transition-transform duration-200" :class="{ 'rotate-180': positionDropdownOpen }" />
                            </button>

                            <div v-show="positionDropdownOpen" class="absolute z-50 mt-2 w-full bg-white dark:bg-purple-900 border-2 border-slate-200 dark:border-purple-600 rounded-xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200" @click.outside="positionDropdownOpen = false">
                                <div class="max-h-60 overflow-y-auto py-1">
                                    <div
                                        v-for="option in positionOptions"
                                        :key="option.value"
                                        @click="selectPositionOption(option)"
                                        class="px-4 py-2.5 cursor-pointer hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors text-xs font-bold uppercase tracking-wider"
                                        :class="selectedPosition === option.value ? 'text-primary bg-primary/5' : 'text-gray-700 dark:text-purple-100'"
                                    >
                                        {{ option.label }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sort Filter -->
                        <div class="relative flex-1 md:max-w-xs">
                            <button
                                @click="toggleSortDropdown"
                                class="w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-card border-2 border-slate-200 dark:border-border rounded-xl text-left shadow-sm hover:border-primary/50 transition-all text-xs font-bold uppercase tracking-widest dark:text-foreground"
                            >
                                <span class="truncate">{{ sortByLabel }}</span>
                                <ChevronDown class="w-4 h-4 text-primary transition-transform duration-200" :class="{ 'rotate-180': sortDropdownOpen }" />
                            </button>

                            <div v-show="sortDropdownOpen" class="absolute z-50 mt-2 w-full bg-white dark:bg-purple-900 border-2 border-slate-200 dark:border-purple-600 rounded-xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200" @click.outside="sortDropdownOpen = false">
                                <div class="py-1">
                                    <div
                                        v-for="option in sortOptions"
                                        :key="option.value"
                                        @click="selectSortOption(option)"
                                        class="px-4 py-2.5 cursor-pointer hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors text-xs font-bold uppercase tracking-wider"
                                        :class="sortBy === option.value ? 'text-primary bg-primary/5' : 'text-gray-700 dark:text-purple-100'"
                                    >
                                        {{ option.label }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 md:flex-none flex items-center justify-end px-2">
                             <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                Updated: {{ lastUpdated }}
                            </p>
                        </div>
                    </div>

                    <!-- Results Section -->
                    <div class="space-y-8">
                        <!-- Empty State -->
                        <div v-if="sortedResults.length === 0" class="text-center py-20 bg-white dark:bg-card border dark:border-border rounded-3xl shadow-sm">
                            <div class="w-16 h-16 bg-gray-50 dark:bg-muted/50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Inbox class="w-8 h-8 text-gray-300" />
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-foreground">No matches found</h3>
                            <p class="text-sm text-gray-500 mt-1">Try changing your position filter</p>
                        </div>

                        <!-- Results Cards -->
                        <div v-for="[positionName, candidates] in sortedResults" :key="positionName" class="bg-white dark:bg-card rounded-3xl shadow-sm border border-gray-100 dark:border-border overflow-hidden">
                            <!-- Position Header -->
                            <div class="px-6 md:px-8 py-5 bg-gray-50/50 dark:bg-card/50 border-b border-gray-100 dark:border-border flex items-center justify-between">
                                <h3 class="text-sm md:text-base font-black text-gray-900 dark:text-foreground uppercase tracking-widest">{{ positionName }}</h3>
                                <div class="px-3 py-1 rounded-full bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest">
                                    {{ candidates.length }} Candidates
                                </div>
                            </div>

                            <!-- Candidates List -->
                            <div class="divide-y divide-gray-100 dark:divide-border">
                                <div v-if="candidates.length === 0" class="px-6 py-12 text-center">
                                    <p class="text-sm text-gray-500 dark:text-muted-foreground italic tracking-wide">No candidates for this position</p>
                                </div>

                                <div 
                                    v-for="(candidate, index) in candidates" 
                                    :key="candidate.id"
                                    class="relative overflow-hidden hover:bg-gray-50/50 dark:hover:bg-accent/10 transition-colors"
                                >
                                    <!-- Progress Bar Background (Subtle) -->
                                    <div class="absolute inset-0 flex items-center">
                                        <div 
                                        :class="[
                                            'h-full transition-all duration-1000 ease-out',
                                            index === 0 ? 'bg-primary/5 dark:bg-primary/10' : 'bg-gray-100/50 dark:bg-muted/20'
                                        ]"
                                        :style="{ width: candidate.percentage + '%' }"
                                        ></div>
                                    </div>

                                    <!-- Content (above progress bar) -->
                                    <div class="relative flex items-center gap-4 px-4 md:px-8 py-5">
                                        <!-- Rank Badge -->
                                        <div 
                                            :class="[
                                                'flex-shrink-0 w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-2xl font-black text-lg shadow-sm',
                                                index === 0 
                                                    ? 'bg-primary text-primary-foreground shadow-primary/20 rotate-3 hover:rotate-0 transition-transform' 
                                                    : 'bg-gray-50 dark:bg-muted text-gray-500 dark:text-gray-400 border dark:border-border'
                                            ]"
                                        >
                                            {{ index + 1 }}
                                        </div>

                                        <!-- Candidate Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-3 mb-1">
                                                <span class="text-sm md:text-lg font-black text-gray-900 dark:text-foreground uppercase tracking-tight truncate">
                                                    {{ candidate.name }}
                                                </span>
                                                <span v-if="index === 0" class="flex-shrink-0 px-2 py-0.5 rounded bg-accent/10 text-accent text-[9px] font-black uppercase tracking-widest border border-accent/20">
                                                    Winner
                                                </span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                                    {{ candidate.partylist || 'Independent' }}
                                                </span>
                                                <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-border hidden sm:inline"></span>
                                                <span class="text-[10px] font-bold text-primary uppercase tracking-widest">
                                                    {{ candidate.course }}
                                                </span>
                                            </div>
                                            
                                            <!-- Mini progress line (visual anchor) -->
                                            <div class="w-full bg-gray-100 dark:bg-muted h-1 rounded-full mt-3 overflow-hidden">
                                                <div 
                                                    :class="[
                                                        'h-full rounded-full transition-all duration-1000',
                                                        index === 0 ? 'bg-primary' : 'bg-gray-300 dark:bg-gray-600'
                                                    ]"
                                                    :style="{ width: candidate.percentage + '%' }"
                                                ></div>
                                            </div>
                                        </div>

                                        <!-- Vote Count -->
                                        <div class="flex-shrink-0 text-right">
                                            <p class="text-lg md:text-2xl font-black text-gray-900 dark:text-foreground leading-none">
                                                {{ candidate.votes.toLocaleString() }}
                                            </p>
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">
                                                {{ candidate.percentage }}% share
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Election Selector Modal -->
                <Modal v-model="showElectionModal">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Select Election</h2>
                                <p class="text-xs text-gray-500 mt-1">Browse historical or active results</p>
                            </div>
                            <button 
                                @click="showElectionModal = false"
                                class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-muted text-gray-400 transition-colors"
                            >
                                <X class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-2 scrollbar-hide">
                            <button
                                v-for="election in elections"
                                :key="election.id"
                                @click="selectElection(election)"
                                :class="[
                                    'w-full text-left p-5 rounded-2xl border-2 transition-all group',
                                    election.id === selectedElection?.id
                                        ? 'border-primary bg-primary/5 shadow-lg shadow-primary/5'
                                        : 'border-slate-100 dark:border-border hover:border-primary/30 hover:bg-gray-50 dark:hover:bg-muted/50'
                                ]"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <h3 class="font-black text-sm text-gray-900 dark:text-foreground uppercase tracking-tight truncate group-hover:text-primary transition-colors">{{ election.title }}</h3>
                                            <span 
                                                v-if="election.is_active"
                                                class="px-2 py-0.5 text-[9px] font-black bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded uppercase tracking-widest border border-green-200 dark:border-green-800"
                                            >
                                                Live
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1.5">
                                                <Calendar class="w-3 h-3 text-gray-400" />
                                                <span class="text-[10px] font-bold text-gray-500 dark:text-muted-foreground uppercase">{{ election.startDate }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="election.id === selectedElection?.id" class="flex-shrink-0 w-8 h-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center shadow-lg shadow-primary/20">
                                        <CheckCircle2 class="w-4 h-4" />
                                    </div>
                                </div>
                            </button>

                            <div v-if="elections.length === 0" class="text-center py-12 bg-gray-50 dark:bg-muted/20 rounded-3xl">
                                <BarChart3 class="w-8 h-8 text-gray-300 mx-auto mb-2" />
                                <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">No history found</p>
                            </div>
                        </div>
                    </div>
                </Modal>
            </div>
        </CandidateLayout> 
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
