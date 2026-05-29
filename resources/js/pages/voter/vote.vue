<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import VoterLayout from '@/layouts/VoterLayout.vue';
import { Button } from '@/components/ui/button';
import { ChevronRight, Eye, Info, CheckCircle2, AlertTriangle, Clock } from 'lucide-vue-next';
import Icon from '@/components/Icon.vue';

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
let countdownInterval: any = null;

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
        timeRemaining.value = 'Ended';
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
        <div class="max-w-7xl mx-auto px-4 py-6 md:p-6 pb-24 md:pb-6">
            
            <!-- No Active Election or Already Voted -->
            <div v-if="!election || hasVoted" class="text-center py-10 md:py-20">
                <div class="bg-white dark:bg-card rounded-2xl p-8 md:p-12 border dark:border-border shadow-sm">
                    <div v-if="hasVoted" class="text-green-500 mb-6">
                        <CheckCircle2 class="w-16 h-16 mx-auto drop-shadow-sm" />
                    </div>
                    <div v-else class="text-gray-400 dark:text-muted-foreground mb-6">
                        <Info class="w-16 h-16 mx-auto opacity-50" />
                    </div>
                    <h2 class="text-2xl font-bold mb-3 dark:text-foreground">
                        {{ hasVoted ? 'Already Voted' : 'No Active Election' }}
                    </h2>
                    <p class="text-gray-600 dark:text-muted-foreground mb-8 max-w-md mx-auto">
                        {{ message || 'There are no elections available at the moment.' }}
                    </p>
                    <div v-if="hasVoted" class="flex flex-col sm:flex-row gap-3 justify-center">
                        <Link href="/voter/dashboard">
                            <Button variant="outline" class="w-full sm:w-auto rounded-xl">
                                Back to Dashboard
                            </Button>
                        </Link>
                        <Link href="/voter/result">
                            <Button class="bg-primary hover:bg-primary/90 w-full sm:w-auto rounded-xl">
                                View Results
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Active Election (only show if election exists AND hasn't voted) -->
            <div v-else-if="election && !hasVoted">
                <!-- Header -->
                <div class="bg-white dark:bg-card rounded-2xl p-5 md:p-8 border dark:border-border mb-6 shadow-sm">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-6">
                        <div class="flex-1">
                            <h1 class="text-xl md:text-3xl font-bold mb-2 dark:text-foreground leading-tight">{{ election.title }}</h1>
                            <p class="text-sm text-gray-600 dark:text-muted-foreground line-clamp-3 md:line-clamp-none">{{ election.description }}</p>
                        </div>
                        <div class="bg-primary/5 dark:bg-primary/10 rounded-xl p-4 border border-primary/10 self-start md:self-auto min-w-[140px]">
                            <div class="text-[10px] font-bold text-primary uppercase tracking-widest mb-1 flex items-center gap-1.5">
                                <Clock class="w-3 h-3" />
                                Remaining
                            </div>
                            <div class="text-lg md:text-xl font-bold text-gray-900 dark:text-foreground">{{ timeRemaining }}</div>
                        </div>
                    </div>

                    <!-- Instructions (Compact on mobile) -->
                    <div class="pt-6 border-t dark:border-border">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-foreground mb-4 uppercase tracking-widest">Instructions</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                            <div v-for="(step, i) in [
                                'Select preferred candidate(s)',
                                'Skip to abstain',
                                'Minimum 1 selection required',
                                'Review before submitting',
                                'Votes are final'
                            ]" :key="i" class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-muted/20 rounded-xl border dark:border-border">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-primary/20 text-primary flex items-center justify-center text-[10px] font-bold">
                                    {{ i + 1 }}
                                </span>
                                <p class="text-[11px] font-medium text-gray-700 dark:text-gray-300 leading-tight">
                                    {{ step }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Positions & Candidates -->
                <div class="space-y-6">
                    <div v-for="position in positions" :key="position.id" class="bg-white dark:bg-card rounded-2xl p-5 md:p-8 border dark:border-border shadow-sm overflow-hidden">
                        
                        <!-- Position Header -->
                        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <div>
                                <h2 class="text-lg md:text-2xl font-bold text-gray-900 dark:text-foreground uppercase tracking-tight">{{ position.name }}</h2>
                                <p class="text-xs text-gray-500 dark:text-muted-foreground mt-1">
                                    Select up to <span class="font-bold text-primary">{{ position.max_selection }}</span> candidate{{ position.max_selection > 1 ? 's' : '' }}
                                </p>
                            </div>
                            <div v-if="selectedVotes[position.id]?.length > 0" class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-accent/10 text-accent text-xs font-bold self-start md:self-auto border border-accent/20">
                                <CheckCircle2 class="w-3.5 h-3.5" />
                                {{ selectedVotes[position.id].length }}/{{ position.max_selection }} selected
                            </div>
                        </div>

                        <!-- Candidates List (Desktop) -->
                        <div v-if="position.candidates.length > 0" class="hidden md:block">
                            <div class="overflow-hidden border dark:border-border rounded-xl">
                                <table class="min-w-full divide-y divide-border">
                                    <thead class="bg-gray-50/50 dark:bg-muted/30">
                                        <tr class="text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                            <th class="py-4 px-6 w-16 text-center">Pick</th>
                                            <th class="py-4 px-6 w-20">Avatar</th>
                                            <th class="py-4 px-6">Candidate</th>
                                            <th class="py-4 px-6">Affiliation</th>
                                            <th class="py-4 px-6">Program</th>
                                            <th class="py-4 px-6 w-24 text-center">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border text-sm">
                                        <tr 
                                            v-for="candidate in position.candidates" 
                                            :key="candidate.id"
                                            :data-candidate-id="candidate.id"
                                            @click="handleRowClick(position.id, candidate.id, position.max_selection)"
                                            :class="[
                                                'transition-colors duration-200 cursor-pointer group',
                                                isSelected(position.id, candidate.id)
                                                    ? 'bg-primary/5 dark:bg-primary/10'
                                                    : 'hover:bg-gray-50 dark:hover:bg-muted/50',
                                                highlightedCandidateId === candidate.id ? 'bg-accent/10 animate-pulse' : ''
                                            ]"
                                        >
                                            <td class="py-4 px-6" @click.stop>
                                                <div class="flex items-center justify-center">
                                                    <input 
                                                        v-if="position.max_selection === 1"
                                                        type="radio"
                                                        :checked="isSelected(position.id, candidate.id)"
                                                        @change="toggleCandidate(position.id, candidate.id, position.max_selection)"
                                                        class="w-5 h-5 text-accent focus:ring-accent cursor-pointer rounded-full border-2"
                                                    />
                                                    <input 
                                                        v-else
                                                        type="checkbox"
                                                        :checked="isSelected(position.id, candidate.id)"
                                                        @change="toggleCandidate(position.id, candidate.id, position.max_selection)"
                                                        :disabled="!isSelected(position.id, candidate.id) && (selectedVotes[position.id]?.length || 0) >= position.max_selection"
                                                        class="w-5 h-5 text-accent focus:ring-accent cursor-pointer rounded border-2"
                                                    />
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <img :src="getCandidatePhoto(candidate.photo)" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 dark:border-border group-hover:scale-110 transition-transform" />
                                            </td>
                                            <td class="py-4 px-6 font-bold text-gray-900 dark:text-foreground">{{ candidate.user.name }}</td>
                                            <td class="py-4 px-6">
                                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-gray-100 dark:bg-muted text-gray-600 dark:text-gray-400">
                                                    {{ candidate.partylist || 'Independent' }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-6 text-gray-500 dark:text-muted-foreground font-medium">
                                                {{ candidate.course }} {{ candidate.year_level }}{{ candidate.section }}
                                            </td>
                                            <td class="py-4 px-6 text-center" @click.stop>
                                                <button @click="openViewModal(candidate)" class="p-2 text-primary hover:bg-primary/10 rounded-full transition-colors" title="View Profile">
                                                    <Eye class="w-5 h-5" />
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Candidate Cards (Mobile) -->
                        <div v-if="position.candidates.length > 0" class="md:hidden space-y-3">
                            <div 
                                v-for="candidate in position.candidates" 
                                :key="`mobile-${candidate.id}`"
                                :data-candidate-id="candidate.id"
                                @click="handleRowClick(position.id, candidate.id, position.max_selection)"
                                :class="[
                                    'p-4 border-2 rounded-2xl transition-all duration-300 cursor-pointer',
                                    isSelected(position.id, candidate.id)
                                        ? 'border-accent bg-accent/5 dark:bg-accent/10 ring-4 ring-accent/10'
                                        : 'border-gray-100 dark:border-border bg-gray-50/50 dark:bg-muted/20',
                                    highlightedCandidateId === candidate.id ? 'border-accent animate-pulse' : ''
                                ]"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="flex-shrink-0" @click.stop>
                                        <input 
                                            v-if="position.max_selection === 1"
                                            type="radio"
                                            :checked="isSelected(position.id, candidate.id)"
                                            @change="toggleCandidate(position.id, candidate.id, position.max_selection)"
                                            class="w-6 h-6 text-accent focus:ring-accent cursor-pointer rounded-full border-2"
                                        />
                                        <input 
                                            v-else
                                            type="checkbox"
                                            :checked="isSelected(position.id, candidate.id)"
                                            @change="toggleCandidate(position.id, candidate.id, position.max_selection)"
                                            :disabled="!isSelected(position.id, candidate.id) && (selectedVotes[position.id]?.length || 0) >= position.max_selection"
                                            class="w-6 h-6 text-accent focus:ring-accent cursor-pointer rounded border-2"
                                        />
                                    </div>

                                    <img :src="getCandidatePhoto(candidate.photo)" class="w-14 h-14 rounded-full object-cover border-2 border-white dark:border-border shadow-sm" />

                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-sm text-gray-900 dark:text-foreground truncate">{{ candidate.user.name }}</h3>
                                        <p class="text-[11px] text-gray-500 dark:text-muted-foreground mt-0.5">{{ candidate.partylist || 'Independent' }}</p>
                                        <p class="text-[10px] text-primary font-bold mt-1 uppercase">{{ candidate.course }} {{ candidate.year_level }}</p>
                                    </div>

                                    <button @click.stop="openViewModal(candidate)" class="p-2.5 bg-white dark:bg-card border dark:border-border rounded-xl text-primary shadow-sm">
                                        <Eye class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ergonomic Floating Action Bar (Mobile & Desktop) -->
                <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/80 dark:bg-background/80 backdrop-blur-xl border-t dark:border-border p-4 md:p-5 safe-bottom">
                    <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center text-accent">
                                <CheckCircle2 class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Selected</p>
                                <p class="text-lg font-black text-gray-900 dark:text-foreground leading-none">{{ totalVotes }} <span class="text-xs font-medium text-gray-500 tracking-normal">votes</span></p>
                            </div>
                        </div>
                        
                        <Button 
                            @click="reviewBallot" 
                            :disabled="!hasMinimumVote"
                            class="flex-1 md:flex-none h-12 px-8 rounded-xl bg-primary hover:bg-primary/90 text-primary-foreground font-bold shadow-lg shadow-primary/20 transition-all active:scale-95 disabled:opacity-50 disabled:grayscale"
                        >
                            Review Ballot
                            <ChevronRight class="w-5 h-5 ml-2" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Modal (High-end scaling) -->
        <div v-if="showReviewModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[100] p-4 animate-in fade-in duration-300">
            <div class="bg-white dark:bg-card rounded-3xl w-full max-w-lg max-h-[85vh] overflow-hidden flex flex-col border dark:border-border shadow-2xl animate-in zoom-in-95 duration-300">
                <div class="p-6 border-b dark:border-border">
                    <h2 class="text-xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Review Ballot</h2>
                    <p class="text-xs text-gray-500 mt-1">Please confirm your selections before finalizing.</p>
                </div>
                
                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                    <div v-for="position in positions" :key="position.id">
                        <div v-if="selectedVotes[position.id]?.length > 0" class="space-y-3">
                            <h3 class="text-[10px] font-black text-primary uppercase tracking-widest">{{ position.name }}</h3>
                            <div class="space-y-2">
                                <div 
                                    v-for="candidate in getSelectedCandidates(position.id)" 
                                    :key="candidate.id"
                                    class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-muted/40 rounded-2xl border dark:border-border"
                                >
                                    <img :src="getCandidatePhoto(candidate.photo)" class="w-10 h-10 rounded-full object-cover ring-2 ring-white dark:ring-border shadow-sm" />
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-900 dark:text-foreground truncate">{{ candidate.user.name }}</p>
                                        <p class="text-[10px] text-gray-500 dark:text-muted-foreground font-medium">{{ candidate.partylist || 'Independent' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-gray-50/50 dark:bg-muted/20 border-t dark:border-border grid grid-cols-2 gap-3">
                    <Button variant="outline" @click="showReviewModal = false" class="rounded-xl h-12 font-bold border-2">Cancel</Button>
                    <Button @click="submitVotes" class="rounded-xl h-12 bg-primary hover:bg-primary/90 font-bold shadow-lg shadow-primary/10">Submit Final</Button>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div v-if="showConfirmModal" class="fixed inset-0 bg-black/80 backdrop-blur-md flex items-center justify-center z-[110] p-6 animate-in fade-in duration-300">
            <div class="bg-white dark:bg-card rounded-3xl max-w-sm w-full p-8 text-center border dark:border-border shadow-2xl animate-in zoom-in-90 duration-300">
                <div class="w-20 h-20 bg-yellow-50 dark:bg-yellow-500/10 rounded-full flex items-center justify-center mx-auto mb-6 border border-yellow-100 dark:border-yellow-500/20">
                    <AlertTriangle class="w-10 h-10 text-yellow-600 dark:text-yellow-500" />
                </div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-foreground mb-3 leading-tight uppercase tracking-tight">One Last Check</h2>
                <p class="text-sm text-gray-600 dark:text-muted-foreground mb-8">
                    Your vote is final and cannot be retracted once submitted. Proceed?
                </p>
                
                <div class="flex flex-col gap-3">
                    <Button @click="confirmSubmit" class="h-14 rounded-2xl bg-primary hover:bg-primary/90 text-lg font-bold shadow-xl shadow-primary/20">YES, SUBMIT VOTE</Button>
                    <button @click="showConfirmModal = false" class="h-10 text-gray-500 dark:text-muted-foreground font-bold hover:text-gray-700 transition-colors uppercase tracking-widest text-xs">I need more time</button>
                </div>
            </div>
        </div>

        <!-- Candidate Details Modal (Refined Scaling) -->
        <div v-if="showViewModal && selectedCandidate" class="fixed inset-0 z-[100] flex items-center justify-center p-4 animate-in fade-in duration-300">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeViewModal"></div>

            <div class="relative bg-white dark:bg-card rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col z-50 border dark:border-border animate-in slide-in-from-bottom-10 duration-300">
                <!-- Banner/Header -->
                <div class="h-24 md:h-32 bg-gradient-to-r from-primary to-purple-600 relative">
                    <button @click="closeViewModal" class="absolute top-4 right-4 p-2 bg-white/20 hover:bg-white/30 backdrop-blur-md rounded-full text-white transition-colors">
                        <Icon name="x" class="w-5 h-5" />
                    </button>
                </div>

                <div class="px-6 pb-6 flex flex-col flex-1 overflow-y-auto">
                    <!-- Profile Identity -->
                    <div class="flex flex-col items-center -mt-12 md:-mt-16 mb-6">
                        <img 
                            :src="getCandidatePhoto(selectedCandidate.photo)" 
                            class="w-24 h-24 md:w-32 md:h-32 rounded-full object-cover border-4 border-white dark:border-card shadow-xl"
                        />
                        <div class="mt-4 text-center">
                            <h4 class="text-xl md:text-2xl font-black text-gray-900 dark:text-foreground tracking-tight">{{ selectedCandidate.user.name }}</h4>
                            <p class="text-xs md:text-sm font-bold text-primary mt-1 uppercase tracking-widest">
                                {{ positions.find(p => p.id === selectedCandidate?.position_id)?.name || 'Candidate' }}
                            </p>
                        </div>
                    </div>

                    <!-- Meta Info Grid -->
                    <div class="grid grid-cols-2 gap-3 mb-8">
                        <div class="p-4 bg-gray-50 dark:bg-muted/30 rounded-2xl border dark:border-border text-center">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Affiliation</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-foreground truncate">{{ selectedCandidate.partylist || 'Independent' }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-muted/30 rounded-2xl border dark:border-border text-center">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Program</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-foreground truncate">{{ selectedCandidate.course }} {{ selectedCandidate.year_level }}</p>
                        </div>
                    </div>

                    <!-- Campaign Statement -->
                    <div class="space-y-3">
                        <h5 class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                            <Icon name="file-text" class="w-3 h-3" />
                            Campaign Platform
                        </h5>
                        <div class="p-5 bg-primary/5 dark:bg-primary/10 rounded-2xl border border-primary/10">
                            <p class="text-sm leading-relaxed text-gray-800 dark:text-gray-300 whitespace-pre-wrap italic">
                                "{{ selectedCandidate?.platform || 'This candidate has not provided a platform yet.' }}"
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t dark:border-border bg-gray-50/50 dark:bg-muted/10">
                    <Button 
                        @click="closeViewModal"
                        variant="outline"
                        class="w-full h-12 rounded-xl font-bold uppercase tracking-widest text-xs border-2"
                    >
                        Return to Ballot
                    </Button>
                </div>
            </div>
        </div>
    </VoterLayout>
</template>


