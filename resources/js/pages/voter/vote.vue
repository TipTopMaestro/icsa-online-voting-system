<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
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
const timeRemaining = ref('');
let countdownInterval: number | null = null;

// Initialize selections
onMounted(() => {
    props.positions.forEach(position => {
        selectedVotes.value[position.id] = [];
    });
    
    if (props.election) {
        startCountdown();
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
        // Remove selection
        currentSelections.splice(index, 1);
    } else {
        // Add selection
        if (currentSelections.length < maxSelection) {
            currentSelections.push(candidateId);
        } else {
            // Max reached
            if (maxSelection === 1) {
                // Radio behavior - replace
                selectedVotes.value[positionId] = [candidateId];
            } else {
                alert(`You can only select up to ${maxSelection} candidates for this position.`);
            }
        }
    }
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
                        <Link href="/voter/results">
                            <Button>
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
                            <div class="text-xl md:text-2xl font-bold text-purple-600 dark:text-primary">{{ timeRemaining }}</div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="bg-gray-50 dark:bg-muted/50 rounded-lg p-4 mt-4">
                        <h3 class="font-semibold mb-2 flex items-center gap-2 dark:text-foreground">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Voting Instructions
                        </h3>
                        <ul class="text-sm text-gray-600 dark:text-muted-foreground space-y-1 ml-7">
                            <li>• Select your preferred candidate(s) for each position</li>
                            <li>• You can skip positions if you wish to abstain</li>
                            <li>• You must select at least 1 candidate to submit your ballot</li>
                            <li>• Review your selections before submitting</li>
                            <li>• Once submitted, votes cannot be changed</li>
                        </ul>
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

                        <!-- Candidates Grid -->
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div 
                                v-for="candidate in position.candidates" 
                                :key="candidate.id"
                                @click="toggleCandidate(position.id, candidate.id, position.max_selection)"
                                :class="[
                                    'cursor-pointer rounded-lg border-2 p-4 transition-all hover:shadow-md',
                                    isSelected(position.id, candidate.id) 
                                        ? 'border-purple-500 dark:border-primary bg-purple-50 dark:bg-primary/5' 
                                        : 'border-gray-200 dark:border-border hover:border-purple-300 dark:hover:border-primary/50'
                                ]"
                            >
                                <div class="flex items-start gap-3">
                                    <!-- Selection Indicator -->
                                    <div class="flex-shrink-0 mt-1">
                                        <div 
                                            :class="[
                                                'w-5 h-5 rounded-full border-2 flex items-center justify-center transition',
                                                isSelected(position.id, candidate.id) 
                                                    ? 'border-purple-500 dark:border-primary bg-purple-500 dark:bg-primary' 
                                                    : 'border-gray-400 dark:border-muted-foreground'
                                            ]"
                                        >
                                            <svg 
                                                v-if="isSelected(position.id, candidate.id)" 
                                                class="w-3 h-3 text-white" 
                                                fill="currentColor" 
                                                viewBox="0 0 20 20"
                                            >
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Candidate Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-2">
                                            <!-- Photo -->
                                            <img 
                                                :src="getCandidatePhoto(candidate.photo)" 
                                                :alt="candidate.user.name"
                                                class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 dark:border-muted"
                                            />
                                            <div class="flex-1 min-w-0">
                                                <h3 class="font-semibold truncate dark:text-foreground">{{ candidate.user.name }}</h3>
                                                <p class="text-xs text-gray-600 dark:text-muted-foreground">
                                                    {{ candidate.course }} {{ candidate.year_level }}{{ candidate.section }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Party -->
                                        <div v-if="candidate.partylist" class="mb-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 dark:bg-primary/10 text-purple-700 dark:text-primary">
                                                {{ candidate.partylist }}
                                            </span>
                                        </div>

                                        <!-- Platform -->
                                        <p v-if="candidate.platform" class="text-sm text-gray-600 dark:text-muted-foreground line-clamp-3">
                                            {{ candidate.platform }}
                                        </p>
                                    </div>
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
                    <Button @click="submitVotes" class="gap-2 w-full sm:w-auto">
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
                    <div class="bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/20 rounded-lg p-3">
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-500">
                            ⚠️ This action cannot be undone
                        </p>
                    </div>
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
                    >
                        Yes, Submit
                    </Button>
                </div>
            </div>
        </div>
    </VoterLayout>
</template>
