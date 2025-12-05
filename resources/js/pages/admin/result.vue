<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Icon from '@/components/Icon.vue';
import Modal from '@/components/Modal.vue'
import ModalTrigger from '@/components/ModalTrigger.vue'

// Props from backend
const props = defineProps({
    elections: {
        type: Array,
        default: () => []
    },
    selectedElection: {
        type: Object,
        default: null
    },
    positions: {
        type: Array,
        default: () => []
    },
    results: {
        type: Object,
        default: () => ({})
    },
    statistics: {
        type: Object,
        default: null
    }
})

const breadcrumbs = [
    { 
        title: 'Results', 
        href: '/admin/result' 
    }
]

const currentElection = computed(() => props.selectedElection)
const showElectionModal = ref(false)
const selectedPosition = ref('all')
const sortBy = ref('position') // 'position' or 'votes'

// Statistics computed properties
const totalVoters = computed(() => props.statistics?.totalVoters || 0)
const totalVotesCast = computed(() => props.statistics?.votedCount || 0)
const voterTurnout = computed(() => props.statistics?.turnoutPercentage || 0)

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
    
    const filtered = {}
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

// Select different election
function selectElection(election) {
    router.get('/admin/result', { election_id: election.id }, {
        preserveState: false,
        preserveScroll: false
    })
    showElectionModal.value = false
}

// Get candidate photo URL with fallback
function getCandidatePhoto(photo) {
    return photo || '/images/profile.png'
}

// Print results
function printResults() {
    window.print()
}

// Format date for print
function formatPrintDate(date) {
    if (!date) return ''
    return new Date(date).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    })
}
</script>

<template>
  <Head title="Results" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      
      <!-- No Election -->
      <div v-if="!selectedElection" class="flex items-center justify-center min-h-[60vh]">
        <div class="text-center">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
            <Icon name="bar-chart-2" class="w-8 h-8 text-gray-400 dark:text-gray-500" />
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Election Selected</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">Please select an election to view results</p>
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
                Print Results
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
              <h3 class="text-1xl font-bold text-gray-900 dark:text-gray-100 uppercase">{{ positionName }}</h3>
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
                      'flex-shrink-0 w-10 h-15 flex items-center justify-center font-bold text-xl',
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

      <!-- Print-Only Professional Format -->
      <div class="print-only">
        <!-- Header with Logo -->
        <div class="print-header">
          <div class="print-header-content">
            <img src="/images/dnscLogo.png" alt="DNSC Logo" class="print-logo">
            <div class="print-header-text">
              <h1>DAVAO DEL NORTE STATE COLLEGE</h1>
              <p class="print-tagline">"Inspiring Change, Creating Futures"</p>
            </div>
            <div class="print-contact">
              <p>president@dnsc.edu.ph</p>
              <p>dnsc.edu.ph</p>
              <p>@dnscedu</p>
              <p>New Visayas, Panabo City, 8105</p>
            </div>
          </div>
        </div>

        <!-- Document Title -->
        <div class="print-title-section">
          <h2 class="print-doc-title">OFFICIAL ELECTION RESULTS</h2>
          <p class="print-election-name">{{ selectedElection?.title || 'Election Results' }}</p>
          <p class="print-dates">
            {{ formatPrintDate(selectedElection?.start_date) }} - {{ formatPrintDate(selectedElection?.end_date) }} | 
            Generated: {{ formatPrintDate(new Date()) }}
          </p>
        </div>

        <!-- Summary Section -->
        <div class="print-summary">
          <h3 class="print-section-title">ELECTION SUMMARY</h3>
          <table class="print-summary-table">
            <tr>
              <td><strong>Total Registered Voters:</strong></td>
              <td>{{ totalVoters.toLocaleString() }}</td>
            </tr>
            <tr>
              <td><strong>Total Votes Cast:</strong></td>
              <td>{{ totalVotesCast.toLocaleString() }}</td>
            </tr>
            <tr>
              <td><strong>Voter Turnout:</strong></td>
              <td>{{ voterTurnout }}%</td>
            </tr>
          </table>
        </div>

        <!-- Results by Position (List Style) -->
        <div class="print-results">
          <h3 class="print-section-title">RESULTS BY POSITION</h3>
          
          <div v-for="[positionName, candidates] in sortedResults" :key="positionName" class="print-position">
            <h4 class="print-position-title">{{ positionName }}</h4>
            
            <div class="print-results-list">
              <div 
                v-for="(candidate, index) in candidates" 
                :key="candidate.id"
                class="print-candidate-item"
              >
                <div class="print-rank">{{ index + 1 }}</div>
                <div class="print-candidate-details">
                  <div class="print-candidate-name">{{ candidate.name }}</div>
                  <div class="print-candidate-party">{{ candidate.partylist || 'Independent' }}</div>
                </div>
                <div class="print-candidate-stats">
                  <div class="print-votes">{{ candidate.votes.toLocaleString() }} votes</div>
                  <div class="print-percentage">{{ candidate.percentage.toFixed(1) }}%</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Signatures Section -->
        <div class="print-signatures">
          <div class="print-signature-line">
            <div class="print-signature-space">_________________________</div>
            <div class="print-signature-label">Prepared by</div>
          </div>
          <div class="print-signature-line">
            <div class="print-signature-space">_________________________</div>
            <div class="print-signature-label">Certified by (Election Committee)</div>
          </div>
          <div class="print-signature-line">
            <div class="print-signature-space">_________________________</div>
            <div class="print-signature-label">Date Certified</div>
          </div>
        </div>

        <!-- Footer -->
        <div class="print-footer">
          <div class="print-footer-left">FOR OFFICIAL USE ONLY</div>
          <div class="print-footer-right">Page <span class="page-number"></span></div>
        </div>

        <!-- School Footer Banner -->
        <div class="print-footer-banner">
          <div class="print-footer-section">
            <h5>VISION</h5>
            <p>An institution of learning recognized in cultural development in the ASEAN region</p>
          </div>
          <div class="print-footer-divider"></div>
          <div class="print-footer-section">
            <h5>MISSION</h5>
            <p>DNSC shall provide highly quality, specialized instruction, research and extension, empower communities, and uphold good governance.</p>
          </div>
          <div class="print-footer-divider"></div>
          <div class="print-footer-section">
            <h5>CORE VALUES</h5>
            <p>Stewardship, Nationalism, Integrity and Trustworthiness, Rule of Law and Good Governance</p>
          </div>
          <div class="print-footer-logos">
            <div class="footer-logo-placeholder"></div>
            <div class="footer-logo-placeholder"></div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Hide print section on screen */
.print-only {
  display: none;
}

/* Print Styles */
@media print {
  /* Hide everything except print section */
  body * {
    visibility: hidden;
  }
  
  .print-only,
  .print-only * {
    visibility: visible;
  }
  
  .print-only {
    display: block;
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    background: white;
    color: black;
  }

  /* Page setup */
  @page {
    size: A4;
    margin: 0.5in 0.75in 0.75in 0.75in;
  }

  /* Header */
  .print-header {
    border-bottom: 3px solid #2d5016;
    padding-bottom: 15px;
    margin-bottom: 20px;
  }

  .print-header-content {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
  }

  .print-logo {
    width: 70px;
    height: 70px;
    object-fit: contain;
  }

  .print-header-text {
    flex: 1;
    text-align: left;
  }

  .print-header-text h1 {
    font-size: 18px;
    font-weight: bold;
    color: #2d5016;
    margin: 0 0 2px 0;
    letter-spacing: 0.5px;
  }

  .print-tagline {
    font-size: 11px;
    color: #666;
    margin: 0;
    font-style: italic;
  }

  .print-contact {
    text-align: right;
    font-size: 9px;
    color: #666;
    line-height: 1.4;
  }

  .print-contact p {
    margin: 0;
  }

  /* Title Section */
  .print-title-section {
    text-align: center;
    margin: 25px 0 20px 0;
    padding: 15px 0;
    border-bottom: 2px solid #ddd;
  }

  .print-doc-title {
    font-size: 20px;
    font-weight: bold;
    color: #1a1a1a;
    margin: 0 0 8px 0;
    letter-spacing: 1px;
  }

  .print-election-name {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0 0 5px 0;
  }

  .print-dates {
    font-size: 11px;
    color: #666;
    margin: 0;
  }

  /* Summary Section */
  .print-summary {
    margin: 20px 0;
    padding: 15px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 4px;
  }

  .print-section-title {
    font-size: 14px;
    font-weight: bold;
    color: #2d5016;
    margin: 0 0 10px 0;
    padding-bottom: 5px;
    border-bottom: 2px solid #2d5016;
  }

  .print-summary-table {
    width: 100%;
    font-size: 11px;
  }

  .print-summary-table td {
    padding: 4px 8px;
  }

  .print-summary-table td:first-child {
    width: 60%;
  }

  .print-summary-table td:last-child {
    text-align: right;
    font-weight: 600;
  }

  /* Results Section */
  .print-results {
    margin: 20px 0;
  }

  .print-position {
    margin-bottom: 25px;
    page-break-inside: avoid;
  }

  .print-position-title {
    font-size: 13px;
    font-weight: bold;
    color: #1a1a1a;
    margin: 15px 0 8px 0;
    padding: 8px 10px;
    background: #f0f0f0;
    border-left: 4px solid #2d5016;
  }

  .print-results-list {
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
  }

  .print-candidate-item {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #e5e5e5;
    gap: 12px;
  }

  .print-candidate-item:last-child {
    border-bottom: none;
  }

  .print-candidate-item:first-child {
    background: #e8f5e9;
    font-weight: 600;
  }

  .print-candidate-item:nth-child(even) {
    background: #f9f9f9;
  }

  .print-rank {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #2d5016;
    color: white;
    font-weight: bold;
    font-size: 11px;
    border-radius: 4px;
    flex-shrink: 0;
  }

  .print-candidate-item:first-child .print-rank {
    background: #1b5e20;
  }

  .print-candidate-details {
    flex: 1;
  }

  .print-candidate-name {
    font-size: 11px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 2px;
  }

  .print-candidate-party {
    font-size: 9px;
    color: #666;
  }

  .print-candidate-stats {
    text-align: right;
    flex-shrink: 0;
  }

  .print-votes {
    font-size: 11px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 2px;
  }

  .print-percentage {
    font-size: 9px;
    color: #666;
  }

  /* Signatures Section */
  .print-signatures {
    margin-top: 40px;
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding: 20px 0;
  }

  .print-signature-line {
    flex: 1;
    text-align: center;
  }

  .print-signature-space {
    border-bottom: 1px solid #333;
    margin-bottom: 5px;
    padding-bottom: 30px;
  }

  .print-signature-label {
    font-size: 10px;
    color: #666;
  }

  /* Footer */
  .print-footer {
    position: fixed;
    bottom: 90px;
    left: 0.75in;
    right: 0.75in;
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-top: 1px solid #ddd;
    font-size: 9px;
    color: #666;
  }

  .print-footer-left {
    font-weight: 600;
    color: #d32f2f;
  }

  /* Footer Banner */
  .print-footer-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #2d5016;
    color: white;
    padding: 10px 0.75in;
    display: flex;
    align-items: stretch;
    gap: 15px;
    height: 70px;
  }

  .print-footer-section {
    flex: 1;
  }

  .print-footer-section h5 {
    font-size: 8px;
    font-weight: bold;
    margin: 0 0 3px 0;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  .print-footer-section p {
    font-size: 6.5px;
    margin: 0;
    line-height: 1.4;
    opacity: 0.95;
  }

  .print-footer-divider {
    width: 1px;
    background: rgba(255, 255, 255, 0.3);
    margin: 5px 0;
  }

  .print-footer-logos {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-left: 15px;
    border-left: 1px solid rgba(255, 255, 255, 0.3);
  }

  .footer-logo-placeholder {
    width: 45px;
    height: 45px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
  }

  /* Page counter */
  .page-number::after {
    content: counter(page);
  }

  /* Hide screen elements */
  .print\:hidden {
    display: none !important;
  }
}
</style>
