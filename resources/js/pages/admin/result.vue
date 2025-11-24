<script setup>
    import AppLayout from '@/layouts/AppLayout.vue'
    import { Head } from '@inertiajs/vue3'
    import { ref, computed } from 'vue'
    import Icon from '@/components/Icon.vue';
    import Modal from '@/components/Modal.vue'
    import ModalTrigger from '@/components/ModalTrigger.vue'

    const breadcrumbs = [
    { 
        title: 'Results', 
        href: '/admin/result' 
    }
    ]

    // Dummy elections
    const elections = ref([
      {
        id: 1,
        title: 'ICSA Election 2025',
        description: 'Election for the new ICSA officer for the year 2025.',
        status: 'active',
        startDate: '01 Jan 2025 - 07 Jan 2025',
        votes: 450,
        totalVoters: 1000,
        positions: 5,
        candidates: 20,
      },
      {
        id: 2,
        title: 'IC Class Representative Election',
        description: 'IC Class Representative Election for the year 2025.',
        status: 'closed',
        startDate: '01 Jan 2025 - 07 Jan 2025',
        votes: 380,
        totalVoters: 950,
        positions: 5,
        candidates: 18,
      },
    ])

    const selectedElection = ref(elections.value[0])
    const currentElection = computed(() => selectedElection.value)

    const pastElections = computed(() => elections.value.filter(e => e.id !== selectedElection.value.id))
    const showPast = ref(false)
    const showPastModal = ref(false)

    // DUMMY POSITIONS
    const positions = ref([
    { id: 1, name: "Governor" },
    { id: 2, name: "Vice Governor" },
    { id: 3, name: "Executive Secretary" },
    { id: 4, name: "Mayor" },
    { id: 5, name: "Treasurer" },
    ])

    // DUMMY RESULTS (per election)
    const results = ref({
    "Governor": [
        { id: 1, name: "Juan Dela Cruz", photo: "https://via.placeholder.com/80", votes: 320, percentage: 45 },
        { id: 2, name: "Maria Santos",  photo: "https://via.placeholder.com/80", votes: 280, percentage: 40 },
        { id: 3, name: "Jose Ramirez",  photo: "https://via.placeholder.com/80", votes: 100, percentage: 15 },
    ],
    "Vice Governor": [
        { id: 4, name: "Anna Reyes", photo: "https://via.placeholder.com/80", votes: 450, percentage: 60 },
        { id: 5, name: "Carlo Gomez", photo: "https://via.placeholder.com/80", votes: 300, percentage: 40 },
    ],
    "Executive Secretary": [
        { id: 6, name: "Louise Tiongson", photo: "https://via.placeholder.com/80", votes: 500, percentage: 70 },
        { id: 7, name: "Rico Garcia",   photo: "https://via.placeholder.com/80", votes: 210, percentage: 30 },
    ],
    })

    // Map results to election ids (frontend-only sample data)
    const resultsByElection = ref({
      1: results.value,
      2: {
        "Mayor": [
          { id: 8, name: "A. PastCandidate", photo: "https://via.placeholder.com/80", votes: 200, percentage: 50 },
          { id: 9, name: "B. PastCandidate", photo: "https://via.placeholder.com/80", votes: 120, percentage: 30 },
          { id: 10, name: "C. PastCandidate", photo: "https://via.placeholder.com/80", votes: 80, percentage: 20 },
        ],
        "Treasurer": [
          { id: 11, name: "D. PastCandidate", photo: "https://via.placeholder.com/80", votes: 300, percentage: 60 },
          { id: 12, name: "E. PastCandidate", photo: "https://via.placeholder.com/80", votes: 200, percentage: 40 },
        ],
      }
    })

    // FILTER
    const selected = ref("All")

    const displayedResults = computed(() => {
      return resultsByElection.value[selectedElection.value.id] ?? {}
    })

    const filteredResults = computed(() => {
      if (selected.value === "All") return displayedResults.value
      return { [selected.value]: displayedResults.value[selected.value] ?? [] }
    })

    function togglePast() {
      if (showPast.value) {
        // Reset to latest election
        selectedElection.value = elections.value[0]
        showPast.value = false
      } else {
        // Open modal to pick a past election
        showPastModal.value = true
      }
    }

    function selectPastElection(election) {
      selectedElection.value = election
      showPast.value = election.id !== elections.value[0].id
      showPastModal.value = false
    }
</script>

<template>
  <Head title="Results" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <!-- Left: Title + Description -->
        <div>
          <h1 class="text-2xl sm:text-3xl font-semibold text-gray-800 dark:text-gray-100">
            Election Results
          </h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Viewing results for <span class="font-medium">{{ currentElection.title }}</span>.
          </p>
        </div>
        <!-- Right: Buttons -->
        <div class="flex items-center gap-2">
          <ModalTrigger v-model="showPastModal">
            <button
              type="button"
              :aria-expanded="showPastModal"
              class="inline-flex items-center px-4 py-2 bg-card hover:bg-card text-black dark:text-white text-sm font-medium border rounded-lg transition-colors"
            >
              <Icon name="calendar" class="w-4 h-4 mr-2" />
              Select Election
              <Icon name="chevron-down" class="w-4 h-4 ml-2 text-gray-600 dark:text-gray-300" />
            </button>
          </ModalTrigger>

          <button
            @click="window.print()"
            class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors"
          >
            <Icon name="printer" class="w-4 h-4 mr-2" />
            Print Results
          </button>
        </div>
      </div>

        <Modal v-model="showPastModal">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-medium text-gray-800 dark:text-gray-100">Elections</h3>
            <button @click="showPastModal = false" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
              <Icon name="x" class="w-5 h-5 text-gray-600 dark:text-gray-300" />
            </button>
          </div>

          <ul class="space-y-2 max-h-64 overflow-auto">
            <li v-for="e in pastElections" :key="e.id">
              <button @click="selectPastElection(e)" class="w-full text-left px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between">
                <div>
                  <div class="font-medium text-gray-800 dark:text-gray-100">{{ e.title }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ e.startDate }}</div>
                </div>
                <Icon name="chevron-right" class="w-4 h-4 text-gray-400" />
              </button>
            </li>
            <li v-if="pastElections.length === 0" class="text-sm text-gray-500 dark:text-gray-400 p-2">No elections found.</li>
          </ul>
        </Modal>

      <!-- FILTER BAR -->
      <div class="flex items-center">
        <div class="relative">
          <Icon name="ListFilter" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
          <input
            v-model="selected"
            type="text"
            placeholder="Search position..."
            class="w-64 pl-10 pr-3 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 dark:text-gray-100 outline-none"
            list="positions-list"
          />
          <datalist id="positions-list">
            <option value="All" />
            <option v-for="p in positions" :key="p.id" :value="p.name" />
          </datalist>
        </div>
      </div>

      <!-- RESULTS -->
      <div class="space-y-8">
        <template v-for="(candidates, position) in filteredResults" :key="position">
          <div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-800 shadow-sm">
            <!-- POSITION HEADER -->
            <div class="items-center justify-center gap-2 bg-primary px-4 py-2 text-sm font-medium transition-colors">
              <h2 class="text-lg text-white dark:text-black">{{ position }}</h2>
            </div>

            <!-- CONTENT -->
            <div class="p-6">
              <div v-if="candidates.length === 0" class="py-16 text-center text-gray-500 dark:text-gray-400">
                No candidates found for {{ position }}.
              </div>

              <div v-else class="space-y-5">
                <div v-for="cand in candidates" :key="cand.id" class="flex items-center gap-4">
                  <img :src="cand.photo" class="w-12 h-12 rounded-full object-cover border border-gray-300 dark:border-gray-700" />
                  <div class="w-full">
                    <p class="text-gray-800 dark:text-gray-100 font-medium">{{ cand.name }}</p>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 h-2.5 rounded-full mt-2">
                      <div class="h-2.5 rounded-full bg-purple-500 transition-all duration-700" :style="{ width: cand.percentage + '%' }"></div>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ cand.votes }} votes • {{ cand.percentage }}%</p>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </template>
      </div>
    </div>
  </AppLayout>
</template>