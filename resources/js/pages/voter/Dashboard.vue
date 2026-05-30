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
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8 min-h-[calc(100vh-64px)]">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-foreground">Dashboard</h1>
                    <p class="text-sm text-gray-600 dark:text-muted-foreground mt-1">Welcome back, {{ user.name }}</p>
                </div>

                <!-- Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Column -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Active Election -->
                        <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-5 md:p-6 shadow-sm">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                                <div class="flex-1">
                                    <h2 class="text-lg font-bold text-gray-900 dark:text-foreground leading-tight">
                                        {{ activeElection ? activeElection.name : 'No Active Election' }}
                                    </h2>
                                    <p v-if="activeElection" class="text-sm text-gray-600 dark:text-muted-foreground mt-1 line-clamp-2 md:line-clamp-none">{{ activeElection.description }}</p>
                                </div>
                                <span v-if="hasVoted" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800 self-start sm:self-auto">
                                    Voted
                                </span>
                            </div>

                            <!-- Countdown -->
                            <div v-if="activeElection && formattedCountdown" class="mt-8 bg-gray-50/50 dark:bg-muted/30 rounded-xl p-4 md:p-6 border dark:border-border">
                                <p class="text-[10px] font-bold text-gray-400 dark:text-muted-foreground uppercase tracking-widest mb-4 text-center">Time Remaining</p>
                                <div class="grid grid-cols-4 gap-2 md:gap-4">
                                    <div class="text-center">
                                        <div class="text-xl md:text-3xl font-bold text-gray-900 dark:text-foreground">{{ formattedCountdown.days }}</div>
                                        <div class="text-[10px] text-gray-500 dark:text-muted-foreground mt-1 uppercase font-medium">Days</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl md:text-3xl font-bold text-gray-900 dark:text-foreground">{{ formattedCountdown.hours }}</div>
                                        <div class="text-[10px] text-gray-500 dark:text-muted-foreground mt-1 uppercase font-medium">Hours</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl md:text-3xl font-bold text-gray-900 dark:text-foreground">{{ formattedCountdown.minutes }}</div>
                                        <div class="text-[10px] text-gray-500 dark:text-muted-foreground mt-1 uppercase font-medium">Mins</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl md:text-3xl font-bold text-gray-900 dark:text-foreground">{{ formattedCountdown.seconds }}</div>
                                        <div class="text-[10px] text-gray-500 dark:text-muted-foreground mt-1 uppercase font-medium">Secs</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div v-if="activeElection" class="mt-6 pt-6 border-t border-gray-100 dark:border-border flex flex-col sm:flex-row gap-3">
                                <Link v-if="!hasVoted" href="/voter/vote" 
                                    class="flex-1 bg-primary text-primary-foreground px-4 py-3 rounded-xl text-sm font-semibold hover:bg-primary/90 transition text-center shadow-sm">
                                    Cast Vote
                                </Link>
                                <Link href="/voter/candidates" 
                                    class="flex-1 bg-gray-100 dark:bg-muted text-gray-700 dark:text-foreground px-4 py-3 rounded-xl text-sm font-semibold hover:bg-gray-200 dark:hover:bg-muted/80 transition text-center border border-gray-200 dark:border-border shadow-sm">
                                    View Candidates
                                </Link>
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">
                            <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-5 md:p-6 shadow-sm flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
                                    <Icon name="calendar" class="w-6 h-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-muted-foreground uppercase tracking-wide">Elections</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-foreground mt-0.5">{{ statistics.totalElectionsParticipated }}</p>
                                </div>
                            </div>
                            <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-5 md:p-6 shadow-sm flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-accent/10 flex items-center justify-center text-accent flex-shrink-0">
                                    <Icon name="vote" class="w-6 h-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-muted-foreground uppercase tracking-wide">Votes Cast</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-foreground mt-0.5">{{ statistics.totalVotesCast }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Announcements -->
                        <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-5 md:p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-base font-bold text-gray-900 dark:text-foreground">Recent Announcements</h3>
                                <Link href="/voter/announcements" class="text-xs font-bold text-accent dark:text-accent hover:underline uppercase tracking-wider">
                                    View all
                                </Link>
                            </div>

                            <div v-if="recentAnnouncements.length > 0" class="space-y-4">
                                <div v-for="announcement in recentAnnouncements" :key="announcement.id" 
                                    class="border-l-4 border-primary pl-4 py-2 bg-primary/5 dark:bg-primary/10 rounded-r-xl transition-all hover:bg-primary/10 dark:hover:bg-primary/20 group">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-foreground group-hover:text-primary transition-colors">{{ announcement.title }}</h4>
                                    <p class="text-xs text-gray-600 dark:text-muted-foreground mt-1 line-clamp-2 leading-relaxed">{{ announcement.content }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2 font-medium flex items-center gap-1">
                                        <Icon name="clock" class="w-3 h-3" />
                                        {{ announcement.created_at }}
                                    </p>
                                </div>
                            </div>
                            <div v-else class="text-center py-10">
                                <div class="w-16 h-16 bg-gray-50 dark:bg-muted/50 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <Icon name="bell-off" class="w-8 h-8 text-gray-300 dark:text-muted-foreground" />
                                </div>
                                <p class="text-sm text-gray-500 dark:text-muted-foreground font-medium">No announcements yet</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Status -->
                        <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-5 md:p-6 shadow-sm">
                            <h3 class="text-sm font-bold text-gray-400 dark:text-muted-foreground uppercase tracking-widest mb-4">Status</h3>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-muted/30 border dark:border-border">
                                <div class="w-2.5 h-2.5 rounded-full animate-pulse shadow-sm" :class="hasVoted ? 'bg-green-500' : 'bg-yellow-500'"></div>
                                <span class="text-sm font-bold" :class="hasVoted ? 'text-green-700 dark:text-green-400' : 'text-yellow-700 dark:text-yellow-400'">
                                    {{ votingStatus }}
                                </span>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-5 md:p-6 shadow-sm">
                            <h3 class="text-sm font-bold text-gray-400 dark:text-muted-foreground uppercase tracking-widest mb-4">Quick Access</h3>
                            <div class="space-y-2">
                                <Link href="/voter/vote" 
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 rounded-xl hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary transition-all group border border-transparent hover:border-primary/20">
                                    <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-muted/50 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                        <Icon name="vote" class="w-4 h-4 text-gray-400 group-hover:text-primary transition-colors" />
                                    </div>
                                    <span class="font-semibold">Cast Vote</span>
                                </Link>
                                <Link href="/voter/candidates" 
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 rounded-xl hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary transition-all group border border-transparent hover:border-primary/20">
                                    <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-muted/50 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                        <Icon name="users" class="w-4 h-4 text-gray-400 group-hover:text-primary transition-colors" />
                                    </div>
                                    <span class="font-semibold">View Candidates</span>
                                </Link>
                                <Link href="/voter/result" 
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 rounded-xl hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary transition-all group border border-transparent hover:border-primary/20">
                                    <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-muted/50 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                        <Icon name="bar-chart-2" class="w-4 h-4 text-gray-400 group-hover:text-primary transition-colors" />
                                    </div>
                                    <span class="font-semibold">Results</span>
                                </Link>
                                <Link href="/voter/profile" 
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 rounded-xl hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary transition-all group border border-transparent hover:border-primary/20">
                                    <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-muted/50 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                        <Icon name="user" class="w-4 h-4 text-gray-400 group-hover:text-primary transition-colors" />
                                    </div>
                                    <span class="font-semibold">Profile Settings</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </VoterLayout>
</template>
