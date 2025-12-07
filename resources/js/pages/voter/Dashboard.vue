<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import VoterLayout from '@/layouts/VoterLayout.vue';
import Icon from '@/components/Icon.vue';
import { ref, onMounted, onUnmounted, computed } from 'vue';

interface User {
    name: string;
    photo: string | null;
}

interface Election {
    id: number;
    name: string;
    description: string;
    start_datetime: string;
    end_datetime: string;
    is_ongoing: boolean;
}

interface TimeRemaining {
    days: number;
    hours: number;
    minutes: number;
    seconds: number;
    total_seconds: number;
}

interface Announcement {
    id: number;
    title: string;
    content: string;
    created_at: string;
}

interface Statistics {
    totalElectionsParticipated: number;
    totalVotesCast: number;
}

const props = defineProps<{
    user: User;
    activeElection: Election | null;
    hasVoted: boolean;
    votingStatus: string;
    timeRemaining: TimeRemaining | null;
    recentAnnouncements: Announcement[];
    statistics: Statistics;
}>();

// Live countdown
const countdown = ref<TimeRemaining | null>(props.timeRemaining);
let countdownInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    if (countdown.value && countdown.value.total_seconds > 0) {
        countdownInterval = setInterval(() => {
            if (countdown.value && countdown.value.total_seconds > 0) {
                countdown.value.total_seconds--;
                
                const days = Math.floor(countdown.value.total_seconds / 86400);
                const hours = Math.floor((countdown.value.total_seconds % 86400) / 3600);
                const minutes = Math.floor((countdown.value.total_seconds % 3600) / 60);
                const seconds = Math.floor(countdown.value.total_seconds % 60);
                
                countdown.value = {
                    days,
                    hours,
                    minutes,
                    seconds,
                    total_seconds: countdown.value.total_seconds
                };
            } else {
                if (countdownInterval) clearInterval(countdownInterval);
            }
        }, 1000);
    }
});

onUnmounted(() => {
    if (countdownInterval) clearInterval(countdownInterval);
});

const formattedCountdown = computed(() => {
    if (!countdown.value) return null;
    return {
        days: String(countdown.value.days).padStart(2, '0'),
        hours: String(countdown.value.hours).padStart(2, '0'),
        minutes: String(countdown.value.minutes).padStart(2, '0'),
        seconds: String(countdown.value.seconds).padStart(2, '0'),
    };
});
</script>

<template>
    <Head title="Dashboard" />
    
    <VoterLayout>
        <div class="min-h-screen bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
                    <p class="text-sm text-gray-600 mt-1">Welcome back, {{ user.name }}</p>
                </div>

                <!-- Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Column -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Active Election -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">
                                        {{ activeElection ? activeElection.name : 'No Active Election' }}
                                    </h2>
                                    <p v-if="activeElection" class="text-sm text-gray-600 mt-1">{{ activeElection.description }}</p>
                                </div>
                                <span v-if="hasVoted" class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                    Voted
                                </span>
                            </div>

                            <!-- Countdown -->
                            <div v-if="activeElection && formattedCountdown" class="mt-6">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Time Remaining</p>
                                <div class="grid grid-cols-4 gap-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-semibold text-gray-900">{{ formattedCountdown.days }}</div>
                                        <div class="text-xs text-gray-500 mt-1">Days</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-semibold text-gray-900">{{ formattedCountdown.hours }}</div>
                                        <div class="text-xs text-gray-500 mt-1">Hours</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-semibold text-gray-900">{{ formattedCountdown.minutes }}</div>
                                        <div class="text-xs text-gray-500 mt-1">Minutes</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-semibold text-gray-900">{{ formattedCountdown.seconds }}</div>
                                        <div class="text-xs text-gray-500 mt-1">Seconds</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div v-if="activeElection" class="mt-6 pt-6 border-t border-gray-100 flex gap-3">
                                <Link v-if="!hasVoted" href="/voter/vote" 
                                    class="flex-1 bg-purple-600 text-white px-4 py-2.5 rounded-md text-sm font-medium hover:bg-purple-700 transition text-center">
                                    Cast Vote
                                </Link>
                                <Link href="/voter/candidates" 
                                    class="flex-1 bg-gray-100 text-gray-700 px-4 py-2.5 rounded-md text-sm font-medium hover:bg-gray-200 transition text-center">
                                    View Candidates
                                </Link>
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div class="grid grid-cols-2 gap-6">
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <p class="text-sm text-gray-600">Elections Participated</p>
                                <p class="text-3xl font-semibold text-gray-900 mt-2">{{ statistics.totalElectionsParticipated }}</p>
                            </div>
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <p class="text-sm text-gray-600">Votes Cast</p>
                                <p class="text-3xl font-semibold text-gray-900 mt-2">{{ statistics.totalVotesCast }}</p>
                            </div>
                        </div>

                        <!-- Announcements -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-semibold text-gray-900">Recent Announcements</h3>
                                <Link href="/voter/announcements" class="text-sm text-purple-600 hover:text-purple-700">
                                    View all
                                </Link>
                            </div>

                            <div v-if="recentAnnouncements.length > 0" class="space-y-3">
                                <div v-for="announcement in recentAnnouncements" :key="announcement.id" 
                                    class="border-l-2 border-purple-600 pl-4 py-2">
                                    <h4 class="text-sm font-medium text-gray-900">{{ announcement.title }}</h4>
                                    <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ announcement.content }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ announcement.created_at }}</p>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500 text-sm">
                                No announcements
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Status -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-base font-semibold text-gray-900 mb-4">Status</h3>
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full" :class="hasVoted ? 'bg-green-500' : 'bg-yellow-500'"></div>
                                <span class="text-sm" :class="hasVoted ? 'text-green-700' : 'text-yellow-700'">
                                    {{ votingStatus }}
                                </span>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-base font-semibold text-gray-900 mb-4">Quick Access</h3>
                            <div class="space-y-1">
                                <Link href="/voter/vote" 
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-50">
                                    Cast Vote
                                </Link>
                                <Link href="/voter/candidates" 
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-50">
                                    View Candidates
                                </Link>
                                <Link href="/voter/result" 
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-50">
                                    Results
                                </Link>
                                <Link href="/voter/profile" 
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-50">
                                    Profile
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </VoterLayout>
</template>