<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Icon from '@/components/Icon.vue';
import Modal from '@/components/Modal.vue'
import ModalTrigger from '@/components/ModalTrigger.vue'
import VoterLayout from '@/layouts/VoterLayout.vue'

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
    router.get(route('voter.result'), { election_id: election.id }, {
        preserveState: false,
        preserveScroll: false
    })
    showElectionModal.value = false
}

function printResults() {
    window.print();
}

function getCandidatePhoto(photo: string) {
    return photo || '/images/profile.png'
}
</script>

<template>
    <div>
        <Head title="Voter Results" />

        <div class="min-h-screen bg-gray-100">
            <VoterLayout>
                <div class="p-6 max-w-7xl mx-auto">
                    
                    <!-- No Election -->
                    <div v-if="!selectedElection" class="flex items-center justify-center min-h-[60vh]">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                                <Icon name="bar-chart-2" class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Results Available</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">There are no election results to display</p>
                        </div>
                    </div>

                    <!-- Election Results -->
                    <template v-else>
                        <!-- Header Section -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {{ currentElection.title }}
                                    </h1>
                                    <span 
                                        :class="[
                                            'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md',
                                            currentElection.is_active 
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                                        ]"
                                    >
                                        <span v-if="currentElection.is_active" class="w-1.5 h-1.5 rounded-full bg-green-600 dark:bg-green-400 animate-pulse" />
                                        {{ currentElection.is_active ? 'Live' : 'Ended' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <!-- Position Filter Dropdown -->
                                    <select 
                                        v-model="selectedPosition"
                                        class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    >
                                        <option value="all">All Positions</option>
                                        <option v-for="position in positions" :key="position.id" :value="position.name">
                                            {{ position.name }}
                                        </option>
                                    </select>

                                    <!-- Sort Dropdown -->
                                    <select 
                                        v-model="sortBy"
                                        class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    >
                                        <option value="position">Sort by Position</option>
                                        <option value="votes">Sort by Total Votes</option>
                                    </select>

                                    <!-- Election Selector -->
                                    <ModalTrigger v-model="showElectionModal">
                                        <button
                                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                                        >
                                            <Icon name="calendar" class="w-4 h-4" />
                                            Change Election
                                        </button>
                                    </ModalTrigger>

                                    <!-- Print Button -->
                                    <button
                                        @click="printResults"
                                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-800 rounded-lg transition-colors print:hidden"
                                    >
                                        <Icon name="printer" class="w-4 h-4" />
                                        Print
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ currentElection.description }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    Last updated: {{ lastUpdated }}
                                </p>
                            </div>
                        </div>

                        <!-- Results Section -->
                        <div class="space-y-6">
                            <!-- Empty State -->
                            <div v-if="sortedResults.length === 0" class="flex items-center justify-center min-h-[40vh]">
                                <div class="text-center">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                                        <Icon name="inbox" class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Results Found</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No results available for the selected filter</p>
                                </div>
                            </div>

                            <!-- Results Cards -->
                            <div v-for="[positionName, candidates] in sortedResults" :key="positionName" class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                                <!-- Position Header -->
                                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 uppercase">{{ positionName }}</h3>
                                </div>

                                <!-- Candidates List -->
                                <div class="divide-y divide-gray-200 dark:divide-gray-800">
                                    <div v-if="candidates.length === 0" class="px-6 py-12 text-center">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">No candidates for this position</p>
                                    </div>

                                    <div 
                                        v-for="(candidate, index) in candidates" 
                                        :key="candidate.id"
                                        class="relative overflow-hidden hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors"
                                    >
                                        <!-- Progress Bar Background -->
                                        <div class="absolute inset-0 flex items-center">
                                          <div 
                                            :class="[
                                              'h-full transition-all duration-500',
                                              index === 0 ? 'bg-purple-100 dark:bg-purple-900/20' : 'bg-gray-100 dark:bg-gray-800/30'
                                            ]"
                                            :style="{ width: candidate.percentage + '%' }"
                                          ></div>
                                        </div>

                                        <!-- Content (above progress bar) -->
                                        <div class="relative flex items-center">
                                          <!-- Rank Number -->
                                          <div 
                                            :class="[
                                              'flex-shrink-0 w-14 h-14 flex items-center justify-center font-bold text-xl',
                                              index === 0 ? 'bg-purple-600 text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                                            ]"
                                          >
                                            {{ index + 1 }}
                                          </div>

                                          <!-- Candidate Name & Party -->
                                          <div class="flex-1 px-6 py-4">
                                            <span class="text-lg font-bold text-gray-900 dark:text-gray-100 uppercase">
                                              {{ candidate.name }}
                                            </span>
                                            <span class="ml-2 text-base text-gray-600 dark:text-gray-400">
                                              ({{ candidate.partylist || 'IND' }})
                                            </span>
                                          </div>

                                          <!-- Vote Count -->
                                          <div class="flex-shrink-0 px-6 py-4 flex items-center gap-3">
                                            <span class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                              {{ candidate.votes.toLocaleString() }}
                                            </span>
                                            <Icon name="flag" class="w-5 h-5 text-gray-400 dark:text-gray-500" />
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
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Select Election</h2>
                                <button 
                                    @click="showElectionModal = false"
                                    class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    <Icon name="x" class="w-5 h-5 text-gray-500" />
                                </button>
                            </div>

                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                <button
                                    v-for="election in elections"
                                    :key="election.id"
                                    @click="selectElection(election)"
                                    :class="[
                                        'w-full text-left p-4 rounded-lg border transition-colors',
                                        election.id === selectedElection?.id
                                            ? 'border-purple-300 bg-purple-50 dark:border-purple-700 dark:bg-purple-900/20'
                                            : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50'
                                    ]"
                                >
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ election.title }}</h3>
                                                <span 
                                                    v-if="election.is_active"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded"
                                                >
                                                    <span class="w-1 h-1 rounded-full bg-green-600 animate-pulse" />
                                                    Live
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ election.startDate }}</p>
                                        </div>
                                        <Icon 
                                            v-if="election.id === selectedElection?.id"
                                            name="check-circle" 
                                            class="w-5 h-5 text-purple-600 dark:text-purple-400 flex-shrink-0" 
                                        />
                                    </div>
                                </button>

                                <div v-if="elections.length === 0" class="text-center py-8">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No elections found</p>
                                </div>
                            </div>
                        </div>
                    </Modal>
                </div>
            </VoterLayout> 
        </div>
    </div>
</template>

<style scoped>
/* Print Styles */
@media print {
  .print\:hidden {
    display: none !important;
  }
  
  body {
    background: white;
  }
  
  @page {
    margin: 1cm;
  }
}
</style>
