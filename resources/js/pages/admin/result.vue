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
const positionDropdownOpen = ref(false)
const sortDropdownOpen = ref(false)

// Options computed for positions dropdown
const positionOptions = computed(() => [
  { value: 'all', label: 'All Positions' },
  ...props.positions.map(p => ({ value: p.name, label: p.name }))
])

const sortOptions = [
  { value: 'position', label: 'Sort by Position' },
  { value: 'votes', label: 'Sort by Total Votes' }
]

function selectPositionOption(option) {
  selectedPosition.value = option.value
  positionDropdownOpen.value = false
}

function selectSortOption(option) {
  sortBy.value = option.value
  sortDropdownOpen.value = false
}

function togglePositionDropdown() {
  const next = !positionDropdownOpen.value
  positionDropdownOpen.value = next
  if (next) sortDropdownOpen.value = false
}

function toggleSortDropdown() {
  const next = !sortDropdownOpen.value
  sortDropdownOpen.value = next
  if (next) positionDropdownOpen.value = false
}

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

// Generate unique document reference number
const documentRefNumber = computed(() => {
    if (!currentElection.value) return ''
    const date = new Date()
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const electionId = String(currentElection.value.id).padStart(4, '0')
    return `DNSC-OVS-${year}${month}-${electionId}`
})

// Calculate position-specific statistics
function getPositionStatistics(positionName, candidates) {
    const totalVotes = candidates.reduce((sum, c) => sum + c.votes, 0)
    return {
        totalVotes,
        totalVoters: totalVoters.value,
        validVotes: totalVotes
    }
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

      <!-- Election Results (Screen Only) -->
      <template v-else>
        <div class="screen-only">
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
              <!-- Position Filter (styled dropdown) -->
              <div class="relative w-full sm:w-44">
                <button @click="togglePositionDropdown()" class="w-full flex items-center justify-between px-4 py-2 rounded-xl border border-slate-300 bg-white text-left shadow-sm focus:ring-2 focus:ring-purple-800 text-sm">
                  <span>{{ positionOptions.find(o => o.value === selectedPosition)?.label || 'All Positions' }}</span>
                  <svg class="w-4 h-4 text-slate-600 transition-transform duration-200" :class="{ 'rotate-180': positionDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                <div v-show="positionDropdownOpen" class="absolute z-50 mt-2 w-full rounded-xl border-2 border-purple-800 bg-white shadow-xl overflow-hidden">
                  <div v-for="opt in positionOptions" :key="opt.value" @click="selectPositionOption(opt)" class="px-4 py-2 cursor-pointer hover:bg-purple-100 text-sm">
                    {{ opt.label }}
                  </div>
                </div>
              </div>

              <!-- Sort Dropdown (styled) -->
              <div class="relative w-full sm:w-44">
                <button @click="toggleSortDropdown()" class="w-full flex items-center justify-between px-4 py-2 rounded-xl border border-slate-300 bg-white text-left shadow-sm focus:ring-2 focus:ring-purple-800 text-sm">
                  <span>{{ sortOptions.find(o => o.value === sortBy)?.label }}</span>
                  <svg class="w-4 h-4 text-slate-600 transition-transform duration-200" :class="{ 'rotate-180': sortDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                <div v-show="sortDropdownOpen" class="absolute z-50 mt-2 w-full rounded-xl border-2 border-purple-800 bg-white shadow-xl overflow-hidden">
                  <div v-for="opt in sortOptions" :key="opt.value" @click="selectSortOption(opt)" class="px-4 py-2 cursor-pointer hover:bg-purple-100 text-sm">
                    {{ opt.label }}
                  </div>
                </div>
              </div>

              <!-- Election Selector -->
              <ModalTrigger v-model="showElectionModal">
                <button
                  class="inline-flex items-center justify-center gap-2 w-44 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-slate-300 dark:border-gray-700 rounded-xl shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800 focus:ring-2 focus:ring-purple-800 transition-colors"
                >
                  <Icon name="calendar" class="w-4 h-4" />
                  Change Election
                </button>
              </ModalTrigger>

              <!-- Print Button -->
              <button
                @click="printResults"
                
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#5A2D6F] hover:bg-[#4b255c] dark:bg-[#5A2D6F] dark:hover:bg-[#4b255c] text-white text-sm font-medium rounded-md transition-colors"
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
                      index === 0 ? 'bg-purple-800 text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
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

      <!-- Print-Only Certificate Format (Based on TC Elections Form 11) -->
      <div class="print-only">
        <!-- Header with Logo (repeats on every page) -->
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

        <!-- Document Title & Reference -->
        <div class="print-title-section">
          <div class="print-doc-ref">Document No.: {{ documentRefNumber }}</div>
          <h2 class="print-doc-title">CERTIFICATE OF RESULTS OF ELECTION</h2>
          <p class="print-election-name">{{ selectedElection?.title || 'Election Results' }}</p>
          <p class="print-dates">
            {{ formatPrintDate(selectedElection?.start_date) }} to {{ formatPrintDate(selectedElection?.end_date) }}
          </p>
        </div>

        <!-- Certification Statement -->
        <div class="print-certification">
          <p class="cert-intro">This is to certify that the election for <strong>{{ selectedElection?.title || 'Student Officers' }}</strong> held on <strong>{{ formatPrintDate(selectedElection?.start_date) }}</strong> to <strong>{{ formatPrintDate(selectedElection?.end_date) }}</strong> at Davao del Norte State College resulted in the following winners:</p>
        </div>

        <!-- Results Table (Winners Only) -->
        <div class="print-results">
          <table class="print-results-table">
            <thead>
              <tr>
                <th class="col-position">POSITION</th>
                <th class="col-name">NAME OF WINNING CANDIDATE</th>
                <th class="col-party">PARTY/ORGANIZATION</th>
                <th class="col-votes">VOTES RECEIVED</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="[positionName, candidates] in sortedResults" :key="positionName">
                <td class="col-position">{{ positionName }}</td>
                <td class="col-name">{{ candidates[0]?.name || 'No Winner' }}</td>
                <td class="col-party">{{ candidates[0]?.partylist || 'Independent' }}</td>
                <td class="col-votes">{{ candidates[0]?.votes.toLocaleString() || '0' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Election Statistics -->
        <div class="print-statistics">
          <p><strong>Total Number of Registered Voters:</strong> {{ totalVoters.toLocaleString() }}</p>
          <p><strong>Total Number of Voters Who Voted:</strong> {{ totalVotesCast.toLocaleString() }}</p>
          <p><strong>Voter Turnout:</strong> {{ voterTurnout }}%</p>
        </div>

        <!-- Final Certification Statement -->
        <div class="print-final-certification">
          <p>This is to certify that the above are the true and correct results of the election as canvassed and tallied by the Election Committee and the Online Voting System of Davao del Norte State College.</p>
        </div>

        <!-- Date Generated -->
        <div class="print-date-generated">
          <p>Given this {{ formatPrintDate(new Date()) }} at Davao del Norte State College, New Visayas, Panabo City, Philippines.</p>
        </div>

        <!-- Signatures Section -->
        <div class="print-signatures">
          <div class="print-signature-block">
            <p class="signature-label">Certified by:</p>
            <div class="signature-line"></div>
            <p class="signature-name">Election Committee Chairperson</p>
            <p class="signature-date">Date: _______________</p>
          </div>
          
          <div class="print-signature-block">
            <p class="signature-label">Noted by:</p>
            <div class="signature-line"></div>
            <p class="signature-name">College President</p>
            <p class="signature-date">Date: _______________</p>
          </div>
        </div>

        <!-- Footer (repeats on every page) -->
        <div class="print-footer-page">
          <div class="print-footer-left">FOR OFFICIAL USE ONLY - CONFIDENTIAL</div>
          <div class="print-footer-right">Page <span class="page-number"></span></div>
        </div>

        <!-- School Footer Banner (repeats on every page) -->
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
            <img src="/images/dnscLogo.png" alt="DNSC Logo" class="footer-logo-img">
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
  /* Hide screen-only content when printing */
  .screen-only {
    display: none !important;
  }

  /* Reset and hide everything except print section */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
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

  /* Page setup with proper margins for header/footer */
  @page {
    size: A4 portrait;
    margin: 0.6in 0.6in 1.1in 0.6in;
  }

  /* Header (repeats on every page) */
  .print-header {
    border-bottom: 3px solid #2d5016;
    padding-bottom: 30px;
    margin-bottom: 15px;
  }

  .print-header-content {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
  }

  .print-logo {
    width: 65px;
    height: 65px;
    object-fit: contain;
    flex-shrink: 0;
  }

  .print-header-text {
    flex: 1;
    text-align: left;
  }

  .print-header-text h1 {
    font-size: 17px;
    font-weight: bold;
    color: #2d5016;
    margin: 0 0 2px 0;
    letter-spacing: 0.5px;
  }

  .print-tagline {
    font-size: 10px;
    color: #555;
    margin: 0;
    font-style: italic;
  }

  .print-contact {
    text-align: right;
    font-size: 8px;
    color: #666;
    line-height: 1.5;
    flex-shrink: 0;
  }

  .print-contact p {
    margin: 0;
  }

  /* Title Section with Document Reference */
  .print-title-section {
    text-align: center;
    margin: 15px 0 20px 0;
    padding: 10px 0;
    page-break-inside: avoid;
  }

  .print-doc-ref {
    font-size: 9px;
    color: #333;
    margin-bottom: 8px;
    font-weight: 500;
  }

  .print-doc-title {
    font-size: 18px;
    font-weight: bold;
    color: #000;
    margin: 8px 0;
    letter-spacing: 1px;
    text-transform: uppercase;
  }

  .print-election-name {
    font-size: 14px;
    font-weight: 600;
    color: #000;
    margin: 6px 0;
  }

  .print-dates {
    font-size: 10px;
    color: #333;
    margin: 4px 0;
  }

  /* Certification Statement */
  .print-certification {
    margin: 15px 0;
    padding: 0;
    page-break-inside: avoid;
  }

  .cert-intro {
    font-size: 10px;
    color: #000;
    line-height: 1.6;
    margin: 0;
    text-align: justify;
    text-indent: 50px;
  }

  /* Results Table (Winners Only) */
  .print-results {
    margin: 20px 0;
  }

  .print-results-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
    font-size: 10px;
    border: 2px solid #000;
  }

  .print-results-table thead {
    background: #fff;
  }

  .print-results-table th {
    padding: 8px 6px;
    text-align: left;
    font-weight: bold;
    color: #000;
    border: 1px solid #000;
    font-size: 9px;
    text-transform: uppercase;
  }

  .print-results-table tbody tr {
    border: 1px solid #000;
  }

  .print-results-table td {
    padding: 8px 6px;
    border: 1px solid #000;
    font-size: 10px;
    vertical-align: top;
  }

  /* Column widths */
  .col-position {
    width: 25%;
    font-weight: bold;
  }

  .col-name {
    width: 30%;
  }

  .col-party {
    width: 25%;
  }

  .col-votes {
    width: 20%;
    text-align: center;
  }

  /* Election Statistics */
  .print-statistics {
    margin: 20px 0;
    padding: 0;
    page-break-inside: avoid;
  }

  .print-statistics p {
    font-size: 10px;
    color: #000;
    margin: 6px 0;
    line-height: 1.5;
  }

  /* Final Certification Statement */
  .print-final-certification {
    margin: 25px 0 15px 0;
    padding: 0;
    page-break-inside: avoid;
  }

  .print-final-certification p {
    font-size: 10px;
    color: #000;
    line-height: 1.6;
    margin: 0;
    text-align: justify;
    text-indent: 50px;
  }

  /* Date Generated */
  .print-date-generated {
    margin: 15px 0;
    padding: 0;
  }

  .print-date-generated p {
    font-size: 10px;
    color: #000;
    line-height: 1.5;
    margin: 0;
    text-align: justify;
    text-indent: 50px;
  }

  /* Signatures Section */
  .print-signatures {
    margin-top: 40px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    gap: 40px;
    padding: 0;
    page-break-inside: avoid;
  }

  .print-signature-block {
    flex: 1;
  }

  .signature-label {
    font-size: 10px;
    font-weight: normal;
    color: #000;
    margin-bottom: 40px;
  }

  .signature-line {
    border-top: 1px solid #000;
    margin-bottom: 3px;
  }

  .signature-name {
    font-size: 10px;
    font-weight: bold;
    color: #000;
    margin: 0;
    text-align: center;
  }

  .signature-date {
    font-size: 9px;
    color: #000;
    margin-top: 5px;
    text-align: center;
  }

  /* Page Footer (repeats on every page) */
  .print-footer-page {
    position: fixed;
    bottom: 85px;
    left: 0.6in;
    right: 0.6in;
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    border-top: 1px solid #ccc;
    font-size: 8px;
    color: #666;
    background: white;
  }

  .print-footer-left {
    font-weight: 600;
    color: #c62828;
    letter-spacing: 0.3px;
  }

  .print-footer-right {
    font-weight: 500;
  }

  /* School Footer Banner (repeats on every page) */
  .print-footer-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #2d5016;
    color: white;
    padding: 8px 0.6in;
    display: flex;
    align-items: center;
    gap: 12px;
    height: 65px;
  }

  .print-footer-section {
    flex: 1;
  }

  .print-footer-section h5 {
    font-size: 7.5px;
    font-weight: bold;
    margin: 0 0 3px 0;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  .print-footer-section p {
    font-size: 6px;
    margin: 0;
    line-height: 1.3;
    opacity: 0.95;
  }

  .print-footer-divider {
    width: 1px;
    background: rgba(255, 255, 255, 0.3);
    height: 45px;
  }

  .print-footer-logos {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding-left: 12px;
    border-left: 1px solid rgba(255, 255, 255, 0.3);
    min-width: 60px;
  }

  .footer-logo-img {
    width: 38px;
    height: 38px;
    object-fit: contain;
    opacity: 0.9;
  }

  /* Page counter */
  .page-number::after {
    content: counter(page);
  }

  /* Page break controls for scalability */
  .print-position-section {
    page-break-inside: avoid;
  }

  .print-results-table {
    page-break-inside: auto;
  }

  .print-results-table tr {
    page-break-inside: avoid;
    page-break-after: auto;
  }

  .print-results-table thead {
    display: table-header-group;
  }

  .print-results-table tbody {
    display: table-row-group;
  }

  /* Ensure header/footer repeat */
  thead {
    display: table-header-group;
  }

  /* Hide screen-only elements */
  .print\:hidden {
    display: none !important;
  }
}
</style>
