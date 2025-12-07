<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import VoterLayout from '@/layouts/VoterLayout.vue';
import { Button } from '@/components/ui/button';

// Interfaces
interface User {
    id: number;
    name: string;
    email: string;
}

interface Candidate {
    id: number;
    user_id: number;
    position_id: number;
    photo: string | null;
    platform: string | null;
    partylist: string | null;
    course: string;
    year_level: string;
    section: string;
    user: User;
}

interface Position {
    id: number;
    name: string;
    max_selection: number;
    candidates: Candidate[];
}

interface Election {
    id: number;
    title: string;
    description: string;
    start_datetime: string;
    end_datetime: string;
    is_active: boolean;
}

// Props
const props = defineProps<{
    election: Election | null;
    positions: Position[];
    hasVoted: boolean;
    message?: string;
}>();

// State
const selectedVotes = ref<Record<number, number[]>>({});
const showReviewModal = ref(false);
const showConfirmModal = ref(false);
const showViewModal = ref(false);
const selectedCandidate = ref<Candidate | null>(null);
const timeRemaining = ref('');
const highlightedCandidateId = ref<number | null>(null);
let countdownInterval: number | null = null;

// Get URL query parameters
const page = usePage();
const urlParams = new URLSearchParams(window.location.search);
const highlightParam = urlParams.get('highlight');

// Initialize selections
onMounted(() => {
    props.positions.forEach(position => {
        selectedVotes.value[position.id] = [];
    });
    
    if (props.election) {
        startCountdown();
    }
    
    // Handle highlight parameter
    if (highlightParam) {
        const candidateId = parseInt(highlightParam);
        highlightedCandidateId.value = candidateId;
        
        // Scroll to candidate after a short delay
        setTimeout(() => {
            const candidateRow = document.querySelector(`[data-candidate-id="${candidateId}"]`);
            if (candidateRow) {
                candidateRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 300);
        
        // Remove highlight after 2 seconds
        setTimeout(() => {
            highlightedCandidateId.value = null;
        }, 2300);
    }
});

onBeforeUnmount(() => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
});

// Countdown timer
function startCountdown() {
    if (!props.election) return;
    
    updateCountdown();
    countdownInterval = setInterval(() => {
        updateCountdown();
    }, 1000);
}

function updateCountdown() {
    if (!props.election) return;
    
    const end = new Date(props.election.end_datetime).getTime();
    const now = new Date().getTime();
    const distance = end - now;
    
    if (distance < 0) {
        timeRemaining.value = 'Election has ended';
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
        // Redirect to dashboard
        setTimeout(() => {
            router.visit('/voter/dashboard');
        }, 2000);
        return;
    }
    
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    timeRemaining.value = `${days}d ${hours}h ${minutes}m ${seconds}s`;
}

// Toggle candidate selection
function toggleCandidate(positionId: number, candidateId: number, maxSelection: number) {
    const currentSelections = selectedVotes.value[positionId] || [];
    const index = currentSelections.indexOf(candidateId);
    
    if (index > -1) {
        // Remove selection (unselect)
        currentSelections.splice(index, 1);
    } else {
        // Add selection
        if (currentSelections.length < maxSelection) {
            currentSelections.push(candidateId);
        } else {
            // Max reached
            if (maxSelection === 1) {
                // Radio behavior - replace (allow re-click to unselect)
                selectedVotes.value[positionId] = [candidateId];
            } else {
                alert(`You can only select up to ${maxSelection} candidates for this position.`);
            }
        }
    }
}

// Handle row click to select/unselect
function handleRowClick(positionId: number, candidateId: number, maxSelection: number) {
    toggleCandidate(positionId, candidateId, maxSelection);
}

// Check if candidate is selected
function isSelected(positionId: number, candidateId: number) {
    return selectedVotes.value[positionId]?.includes(candidateId) || false;
}

// Get candidate photo URL
function getCandidatePhoto(photo: string | null) {
    if (!photo) {
        return 'https://ui-avatars.com/api/?name=Candidate&background=random';
    }
    return `/storage/candidates/${photo}`;
}

// View candidate details
function openViewModal(candidate: Candidate) {
    selectedCandidate.value = candidate;
    showViewModal.value = true;
}

function closeViewModal() {
    showViewModal.value = false;
    selectedCandidate.value = null;
}

// Computed
const totalVotes = computed(() => {
    return Object.values(selectedVotes.value).reduce((sum, votes) => sum + votes.length, 0);
});

const hasMinimumVote = computed(() => {
    return totalVotes.value >= 1;
});

// Review ballot
function reviewBallot() {
    if (!hasMinimumVote.value) {
        alert('Please select at least one candidate before reviewing your ballot.');
        return;
    }
    showReviewModal.value = true;
}

// Submit votes
function submitVotes() {
    showReviewModal.value = false;
    showConfirmModal.value = true;
}

function confirmSubmit() {
    if (!props.election) return;
    
    // Prepare votes data
    const votesData = Object.entries(selectedVotes.value)
        .filter(([_, candidateIds]) => candidateIds.length > 0)
        .map(([positionId, candidateIds]) => ({
            position_id: parseInt(positionId),
            candidate_ids: candidateIds
        }));
    
    // Submit via Inertia
    router.post('/voter/vote', {
        election_id: props.election.id,
        votes: votesData
    }, {
        onError: (errors) => {
            console.error('Voting failed:', errors);
            showConfirmModal.value = false;
        }
    });
}

// Get selected candidates for review
function getSelectedCandidates(positionId: number) {
    const candidateIds = selectedVotes.value[positionId] || [];
    const position = props.positions.find(p => p.id === positionId);
    if (!position) return [];
    
    return position.candidates.filter(c => candidateIds.includes(c.id));
}
</script>

<template>
    <Head title="Cast Your Vote" />
    
    <VoterLayout>
        <div class="max-w-7xl mx-auto p-4 md:p-6">
            
            <!-- No Active Election or Already Voted -->
            <div v-if="!election || hasVoted" class="text-center py-12">
                <div class="bg-white dark:bg-card rounded-xl p-12 border dark:border-border">
                    <div v-if="hasVoted" class="text-green-400 dark:text-green-500 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div v-else class="text-gray-400 dark:text-muted-foreground mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold mb-2 dark:text-foreground">
                        {{ hasVoted ? 'Already Voted' : 'No Active Election' }}
                    </h2>
                    <p class="text-gray-600 dark:text-muted-foreground mb-6">
                        {{ message || 'There are no elections available at the moment.' }}
                    </p>
                    <div v-if="hasVoted" class="flex flex-col sm:flex-row gap-3 justify-center">
                        <Link href="/voter/dashboard">
                            <Button variant="outline">
                                Back to Dashboard
                            </Button>
                        </Link>
                        <Link href="/voter/result">
                            <Button style="background-color: #5A2D6F;">
                                View Results
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Active Election (only show if election exists AND hasn't voted) -->
            <div v-else-if="election && !hasVoted">
                <!-- Header -->
                <div class="bg-white dark:bg-card rounded-xl p-4 md:p-6 border dark:border-border mb-6">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-4">
                        <div class="flex-1">
                            <h1 class="text-2xl md:text-3xl font-bold mb-2 dark:text-foreground">{{ election.title }}</h1>
                            <p class="text-gray-600 dark:text-muted-foreground">{{ election.description }}</p>
                        </div>
                        <div class="text-left md:text-right">
                            <div class="text-sm text-gray-600 dark:text-muted-foreground mb-1">Time Remaining</div>
                            <div class="text-xl md:text-2xl font-bold" style="color: #5A2D6F;">{{ timeRemaining }}</div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="mt-6 p-6 border">
                        <div class="flex items-center gap-2 mb-4">
                            
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-foreground">Voting Instructions</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <!-- Step 1 -->
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full text-white flex items-center justify-center text-sm font-bold" style="background-color: #5A2D6F;">
                                    1
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                        Select your preferred candidate(s) for each position
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Step 2 -->
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full text-white flex items-center justify-center text-sm font-bold" style="background-color: #5A2D6F;">
                                    2
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                        You can skip positions if you wish to abstain
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Step 3 -->
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full text-white flex items-center justify-center text-sm font-bold" style="background-color: #5A2D6F;">
                                    3
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                        You must select at least 1 candidate to submit
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Step 4 -->
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full text-white flex items-center justify-center text-sm font-bold" style="background-color: #5A2D6F;">
                                    4
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                        Review your selections before submitting
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Step 5 -->
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full text-white flex items-center justify-center text-sm font-bold" style="background-color: #5A2D6F;">
                                    5
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                        Once submitted, your vote cannot be changed
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Positions & Candidates -->
                <div class="space-y-6">
                    <div v-for="position in positions" :key="position.id" class="bg-white dark:bg-card rounded-xl p-4 md:p-6 border dark:border-border">
                        
                        <!-- Position Header -->
                        <div class="mb-6">
                            <h2 class="text-xl md:text-2xl font-bold mb-1 dark:text-foreground">{{ position.name }}</h2>
                            <p class="text-sm text-gray-600 dark:text-muted-foreground">
                                Select up to {{ position.max_selection }} candidate{{ position.max_selection > 1 ? 's' : '' }}
                                <span v-if="selectedVotes[position.id]?.length > 0" class="ml-2 text-purple-600 dark:text-primary font-medium">
                                    ({{ selectedVotes[position.id].length }}/{{ position.max_selection }} selected)
                                </span>
                            </p>
                        </div>

                        <!-- No Candidates -->
                        <div v-if="position.candidates.length === 0" class="text-center py-8 bg-gray-50 dark:bg-muted/50 rounded-lg">
                            <p class="text-gray-600 dark:text-muted-foreground">No candidates available for this position</p>
                        </div>

                        <!-- Candidates Table (Desktop Only) -->
                        <div v-else-if="position.candidates.length > 0" class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-border">
                                <thead>
                                    <tr class="text-left text-sm text-muted-foreground bg-muted/30">
                                        <th class="py-3 px-4 font-semibold w-12">Select</th>
                                        <th class="py-3 px-4 font-semibold w-16">Photo</th>
                                        <th class="py-3 px-4 font-semibold">Name</th>
                                        <th class="py-3 px-4 font-semibold">Partylist</th>
                                        <th class="py-3 px-4 font-semibold">Course/Year</th>
                                        <th class="py-3 px-4 font-semibold w-24 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border text-sm">
                                    <tr 
                                        v-for="candidate in position.candidates" 
                                        :key="candidate.id"
                                        :data-candidate-id="candidate.id"
                                        @click="handleRowClick(position.id, candidate.id, position.max_selection)"
                                        :class="[
                                            'transition-all duration-500 cursor-pointer',
                                            isSelected(position.id, candidate.id)
                                                ? 'bg-purple-50 dark:bg-primary/10 hover:bg-purple-100 dark:hover:bg-primary/20'
                                                : 'hover:bg-muted/50',
                                            highlightedCandidateId === candidate.id ? 'bg-purple-200 dark:bg-purple-900/50' : ''
                                        ]"
                                    >
                                        <!-- Select Radio/Checkbox -->
                                        <td class="py-3 px-4" @click.stop>
                                            <div class="flex items-center justify-center">
                                                <!-- Radio for max_selection = 1 -->
                                                <input 
                                                    v-if="position.max_selection === 1"
                                                    type="radio"
                                                    :name="`position-${position.id}`"
                                                    :checked="isSelected(position.id, candidate.id)"
                                                    @change="toggleCandidate(position.id, candidate.id, position.max_selection)"
                                                    class="w-4 h-4 text-purple-600 focus:ring-purple-500 cursor-pointer"
                                                />
                                                <!-- Checkbox for max_selection > 1 -->
                                                <input 
                                                    v-else
                                                    type="checkbox"
                                                    :checked="isSelected(position.id, candidate.id)"
                                                    @change="toggleCandidate(position.id, candidate.id, position.max_selection)"
                                                    :disabled="!isSelected(position.id, candidate.id) && (selectedVotes[position.id]?.length || 0) >= position.max_selection"
                                                    class="w-4 h-4 text-purple-600 focus:ring-purple-500 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                                />
                                            </div>
                                        </td>

                                        <!-- Photo -->
                                        <td class="py-3 px-4">
                                            <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-200 dark:border-muted flex-shrink-0">
                                                <img 
                                                    :src="getCandidatePhoto(candidate.photo)" 
                                                    :alt="candidate.user.name"
                                                    class="w-full h-full object-cover"
                                                />
                                            </div>
                                        </td>

                                        <!-- Name Only (No Email) -->
                                        <td class="py-3 px-4">
                                            <div class="font-medium dark:text-foreground">{{ candidate.user.name }}</div>
                                        </td>

                                        <!-- Partylist -->
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-primary/10  dark:text-primary">
                                                {{ candidate.partylist || 'Independent' }}
                                            </span>
                                        </td>

                                        <!-- Course/Year/Section -->
                                        <td class="py-3 px-4 text-muted-foreground">
                                            {{ candidate.course }} {{ candidate.year_level }}{{ candidate.section }}
                                        </td>

                                        <!-- Actions -->
                                        <td class="py-3 px-4 text-center" @click.stop>
                                            <button 
                                                @click="openViewModal(candidate)"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition"
                                                title="View candidate details"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Cards (Mobile Only) -->
                        <div v-if="position.candidates.length > 0" class="md:hidden space-y-3">
                            <div 
                                v-for="candidate in position.candidates" 
                                :key="`mobile-${candidate.id}`"
                                :data-candidate-id="candidate.id"
                                @click="handleRowClick(position.id, candidate.id, position.max_selection)"
                                :class="[
                                    'border-2 rounded-xl p-4 transition-all duration-500 cursor-pointer shadow-sm',
                                    isSelected(position.id, candidate.id)
                                        ? 'border-purple-500 bg-purple-50 dark:border-primary dark:bg-primary/10 shadow-md'
                                        : 'border-gray-200 dark:border-border hover:shadow-md',
                                    highlightedCandidateId === candidate.id ? 'bg-yellow-200 border-yellow-400 dark:bg-yellow-900/50 dark:border-yellow-600' : ''
                                ]"
                            >
                                <!-- Top Section: Radio/Checkbox + Photo + Name -->
                                <div class="flex items-start gap-4 mb-3">
                                    <!-- Select Radio/Checkbox -->
                                    <div class="flex-shrink-0 pt-1" @click.stop>
                                        <input 
                                            v-if="position.max_selection === 1"
                                            type="radio"
                                            :name="`mobile-position-${position.id}`"
                                            :checked="isSelected(position.id, candidate.id)"
                                            @change="toggleCandidate(position.id, candidate.id, position.max_selection)"
                                            class="w-5 h-5 text-purple-600 focus:ring-purple-500 cursor-pointer"
                                        />
                                        <input 
                                            v-else
                                            type="checkbox"
                                            :checked="isSelected(position.id, candidate.id)"
                                            @change="toggleCandidate(position.id, candidate.id, position.max_selection)"
                                            :disabled="!isSelected(position.id, candidate.id) && (selectedVotes[position.id]?.length || 0) >= position.max_selection"
                                            class="w-5 h-5 text-purple-600 focus:ring-purple-500 cursor-pointer disabled:opacity-50"
                                        />
                                    </div>

                                    <!-- Photo -->
                                    <div class="w-16 h-16 rounded-full overflow-hidden border-3 border-purple-200 dark:border-primary/30 flex-shrink-0">
                                        <img 
                                            :src="getCandidatePhoto(candidate.photo)" 
                                            :alt="candidate.user.name"
                                            class="w-full h-full object-cover"
                                        />
                                    </div>

                                    <!-- Name & Basic Info -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-base dark:text-foreground mb-1 truncate">
                                            {{ candidate.user.name }}
                                        </h3>
                                        <p class="text-xs text-muted-foreground mb-2">
                                            {{ candidate.course }} {{ candidate.year_level }}{{ candidate.section }}
                                        </p>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-primary/10 text-purple-700 dark:text-primary">
                                            {{ candidate.partylist || 'Independent' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Bottom Section: View Button -->
                                <div class="pt-3 border-t border-gray-200 dark:border-border">
                                    <button 
                                        @click.stop="openViewModal(candidate)"
                                        class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-blue-700 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View Full Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="sticky bottom-0 bg-white/95 dark:bg-background/95 backdrop-blur border-t dark:border-border p-4 mt-6 rounded-t-xl">
                    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="text-center sm:text-left">
                            <p class="text-sm font-medium dark:text-foreground">
                                Total Votes: <span class="text-purple-600 dark:text-primary text-lg">{{ totalVotes }}</span>
                            </p>
                            <p class="text-xs text-gray-600 dark:text-muted-foreground">
                                {{ hasMinimumVote ? 'Ready to submit' : 'Select at least 1 candidate' }}
                            </p>
                        </div>
                        <Button 
                            @click="reviewBallot" 
                            :disabled="!hasMinimumVote"
                            size="lg"
                            class="gap-2 w-full sm:w-auto"
                            style="background-color: #5A2D6F;"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Review Ballot
                </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Modal -->
        <div 
            v-if="showReviewModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="showReviewModal = false"
        >
            <div class="bg-white dark:bg-card rounded-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto p-6 border dark:border-border">
                <h2 class="text-2xl font-bold mb-4 dark:text-foreground">Review Your Ballot</h2>
                
                <div class="space-y-4 mb-6">
                    <div v-for="position in positions" :key="position.id">
                        <div v-if="selectedVotes[position.id]?.length > 0">
                            <h3 class="font-semibold mb-2 dark:text-foreground">{{ position.name }}</h3>
                            <div class="space-y-2 ml-4">
                                <div 
                                    v-for="candidate in getSelectedCandidates(position.id)" 
                                    :key="candidate.id"
                                    class="flex items-center gap-3 p-2 bg-gray-50 dark:bg-muted/50 rounded"
                                >
                                    <img 
                                        :src="getCandidatePhoto(candidate.photo)" 
                                        :alt="candidate.user.name"
                                        class="w-10 h-10 rounded-full object-cover"
                                    />
                                    <div>
                                        <p class="font-medium dark:text-foreground">{{ candidate.user.name }}</p>
                                        <p class="text-xs text-gray-600 dark:text-muted-foreground">{{ candidate.partylist || 'Independent' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3">
                    <Button variant="outline" @click="showReviewModal = false" class="w-full sm:w-auto">
                        Go Back
                    </Button>
                    <Button @click="submitVotes" class="gap-2 w-full sm:w-auto" style="background-color: #5A2D6F;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Submit Ballot
                    </Button>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div 
            v-if="showConfirmModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white dark:bg-card rounded-xl max-w-md w-full p-6 border dark:border-border">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-yellow-100 dark:bg-yellow-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold mb-2 dark:text-foreground">Confirm Your Vote</h2>
                    <p class="text-gray-600 dark:text-muted-foreground mb-4">
                        Are you sure you want to submit your ballot?
                    </p>
                    
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <Button 
                        variant="outline" 
                        @click="showConfirmModal = false"
                        class="flex-1"
                    >
                        Cancel
                    </Button>
                     <Button 
                        @click="confirmSubmit"
                        class="flex-1"
                        style="background-color: #5A2D6F;"
                    >
                        Yes, Submit
                    </Button>
                </div>
            </div>
        </div>

        <!-- View Candidate Modal -->
        <div v-if="showViewModal && selectedCandidate" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" @click="closeViewModal"></div>

            <div class="relative bg-white dark:bg-card rounded-xl shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto p-6 z-50 border dark:border-border">
                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <h3 class="text-xl font-semibold dark:text-foreground">Candidate Details</h3>
                    <button @click="closeViewModal" class="text-muted-foreground hover:text-foreground transition">
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
                            :src="getCandidatePhoto(selectedCandidate.photo)" 
                            :alt="selectedCandidate.user.name"
                            class="w-32 h-32 rounded-full object-cover border-4 border-purple-100 dark:border-primary/20"
                        />
                        <div class="flex-1 text-center sm:text-left">
                            <h4 class="text-2xl font-bold mb-2 dark:text-foreground">{{ selectedCandidate.user.name }}</h4>
                            <p class="text-muted-foreground mb-3">{{ selectedCandidate.user.email }}</p>
                            <div class="flex flex-wrap gap-2 justify-center sm:justify-start">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 dark:bg-primary/10 text-purple-700 dark:text-primary">
                                    {{ selectedCandidate.partylist || 'Independent' }}
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-muted text-gray-700 dark:text-muted-foreground">
                                    {{ selectedCandidate.course }} {{ selectedCandidate.year_level }}{{ selectedCandidate.section }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t dark:border-border">
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground mb-1">Position</label>
                            <p class="font-semibold dark:text-foreground">
                                {{ positions.find(p => p.id === selectedCandidate?.position_id)?.name || 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted-foreground mb-1">Election</label>
                            <p class="font-semibold dark:text-foreground">{{ election?.title || 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Platform -->
                    <div class="pt-4 border-t dark:border-border">
                        <label class="block text-sm font-medium text-muted-foreground mb-2">Platform</label>
                        <div class="bg-gray-50 dark:bg-muted/30 rounded-lg p-4">
                            <p class="text-sm leading-relaxed dark:text-foreground whitespace-pre-wrap">
                                {{ selectedCandidate?.platform || 'No platform provided' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 pt-4 border-t dark:border-border">
                    <Button 
                        @click="closeViewModal"
                        variant="outline"
                        class="w-full"
                    >
                        Close
                    </Button>
                </div>
            </div>
        </div>
    </VoterLayout>
</template>

