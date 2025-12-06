<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import VoterLayout from '@/layouts/VoterLayout.vue';
import { Button } from '@/components/ui/button';

interface User {
    id: number;
    name: string;
}

interface Position {
    id: number;
    name: string;
}

interface Candidate {
    id: number;
    user: User;
    position: Position;
    photo: string | null;
    partylist: string | null;
}

interface Vote {
    id: number;
    candidate: Candidate;
    created_at: string;
}

interface Election {
    id: number;
    title: string;
}

const props = defineProps<{
    election: Election;
    votes: Vote[];
    votedAt: string;
}>();

function getCandidatePhoto(photo: string | null) {
    if (!photo) {
        return 'https://ui-avatars.com/api/?name=Candidate&background=random';
    }
    return `/storage/candidates/${photo}`;
}

// Group votes by position
const groupedVotes = props.votes.reduce((acc, vote) => {
    const positionName = vote.candidate.position.name;
    if (!acc[positionName]) {
        acc[positionName] = [];
    }
    acc[positionName].push(vote);
    return acc;
}, {} as Record<string, Vote[]>);
</script>

<template>
    <Head title="Voting Receipt" />
    
    <VoterLayout>
        <div class="max-w-4xl mx-auto p-4 md:p-6">
            <div class="bg-white dark:bg-card rounded-xl p-6 md:p-8 border dark:border-border">
                
                <!-- Success Icon -->
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-green-100 dark:bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-600 dark:text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold mb-2 dark:text-foreground">Vote Submitted Successfully!</h1>
                    <p class="text-gray-600 dark:text-muted-foreground">Thank you for participating in {{ election.title }}</p>
                </div>

                <!-- Voting Receipt -->
                <div class="bg-gray-50 dark:bg-muted/50 rounded-lg p-4 md:p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4 dark:text-foreground">Your Voting Receipt</h2>
                    
                    <div class="text-sm text-gray-600 dark:text-muted-foreground mb-4">
                        <p>Voted on: {{ new Date(votedAt).toLocaleString() }}</p>
                    </div>

                    <!-- Votes by Position -->
                    <div class="space-y-4">
                        <div v-for="(votes, positionName) in groupedVotes" :key="positionName">
                            <h3 class="font-semibold mb-2 dark:text-foreground">{{ positionName }}</h3>
                            <div class="space-y-2 ml-4">
                                <div 
                                    v-for="vote in votes" 
                                    :key="vote.id"
                                    class="flex items-center gap-3 p-3 bg-white dark:bg-background rounded border dark:border-border"
                                >
                                    <img 
                                        :src="getCandidatePhoto(vote.candidate.photo)" 
                                        :alt="vote.candidate.user.name"
                                        class="w-12 h-12 rounded-full object-cover"
                                    />
                                    <div>
                                        <p class="font-medium dark:text-foreground">{{ vote.candidate.user.name }}</p>
                                        <p class="text-xs text-gray-600 dark:text-muted-foreground">
                                            {{ vote.candidate.partylist || 'Independent' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-lg p-4 mb-6">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-blue-800 dark:text-blue-400">
                            <p class="font-medium mb-1">Important Information:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Your vote has been recorded securely</li>
                                <li>You cannot change your vote once submitted</li>
                                <li>Results will be available after the election ends</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row justify-center gap-3">
                    <Link href="/voter/dashboard">
                        <Button variant="outline" class="w-full sm:w-auto">
                            Back to Dashboard
                        </Button>
                    </Link>
                    <Link href="/voter/result">
                        <Button class="w-full sm:w-auto">
                            View Live Results
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </VoterLayout>
</template>
