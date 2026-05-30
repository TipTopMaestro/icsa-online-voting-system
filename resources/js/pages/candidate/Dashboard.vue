<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import CandidateLayout from '@/layouts/CandidateLayout.vue';
import Icon from '@/components/Icon.vue';
import { ref, computed } from 'vue';

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

interface Position {
    id: number;
    name: string;
}

interface Announcement {
    id: number;
    title: string;
    content: string;
    created_at: string;
}

interface Statistics {
    votesReceived: number;
    totalVoters: number;
    votePercentage: number;
    ranking: number;
    totalCandidates: number;
}

const props = defineProps<{
    user: User;
    activeElection: Election | null;
    candidatePosition: Position | null;
    recentAnnouncements: Announcement[];
    statistics: Statistics;
}>();
</script>

<template>
    <Head title="Candidate Dashboard" />
    <CandidateLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-[calc(100vh-64px)]">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-foreground">Dashboard</h1>
                <p class="text-sm text-gray-600 dark:text-muted-foreground mt-1">Welcome back, {{ user.name }}</p>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Active Election -->
                    <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-6 shadow-sm">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-foreground">Election Status</h2>
                                <p v-if="activeElection" class="text-md text-gray-600 dark:text-muted-foreground mt-1">{{ activeElection.name }}</p>
                                <p v-if="candidatePosition" class="text-sm text-primary mt-2 font-semibold">
                                    Running for: {{ candidatePosition.name }}
                                </p>
                            </div>
                            <span v-if="activeElection?.is_ongoing" class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
                                Active
                            </span>
                            <span v-else class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-gray-50 dark:bg-muted text-gray-700 dark:text-muted-foreground border border-gray-200 dark:border-border">
                                {{ activeElection ? 'Ended' : 'None' }}
                            </span>

                        </div>

                        <div v-if="!activeElection" class="text-center py-8 text-gray-500 dark:text-muted-foreground text-sm">
                            No active election at the moment
                        </div>
                    </div>

                    <!-- Vote Statistics -->
                    <div class="grid grid-cols-2 gap-6">
                        <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-6 shadow-sm">
                            <p class="text-sm text-gray-600 dark:text-muted-foreground">Votes Received</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-foreground mt-2">{{ statistics.votesReceived }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1 font-medium">
                                {{ statistics.votePercentage }}% of total voters
                            </p>
                        </div>
                        <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-6 shadow-sm">
                            <p class="text-sm text-gray-600 dark:text-muted-foreground">Current Ranking</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-foreground mt-2">
                                {{ statistics.ranking > 0 ? '#' + statistics.ranking : '-' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1 font-medium">
                                Out of {{ statistics.totalCandidates }} candidates
                            </p>
                        </div>
                    </div>

                    <!-- Performance Overview -->
                    <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-foreground mb-4">Performance Overview</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-gray-600 dark:text-muted-foreground">Vote Progress</span>
                                    <span class="font-bold text-gray-900 dark:text-foreground">{{ statistics.votesReceived }} / {{ statistics.totalVoters }}</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-muted rounded-full h-3">
                                    <div 
                                        class="bg-primary h-3 rounded-full transition-all duration-700 ease-out shadow-sm"
                                        :style="{ width: statistics.votePercentage + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Announcements -->
                    <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-foreground">Recent Announcements</h3>
                            <Link href="/candidate/announcements" class="text-sm text-accent dark:text-accent hover:text-accent/90 transition font-medium">
                                View all
                            </Link>
                        </div>

                        <div v-if="recentAnnouncements.length > 0" class="space-y-3">
                            <div v-for="announcement in recentAnnouncements" :key="announcement.id" 
                                class="border-l-4 border-primary pl-4 py-2 bg-primary/5 dark:bg-primary/10 rounded-r-md transition-all hover:bg-primary/10 dark:hover:bg-primary/20">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-foreground">{{ announcement.title }}</h4>
                                <p class="text-sm text-gray-600 dark:text-muted-foreground mt-1 line-clamp-2">{{ announcement.content }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-2 font-medium">{{ announcement.created_at }}</p>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500 dark:text-muted-foreground text-sm">
                            No announcements
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Campaign Status -->
                    <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-foreground mb-4">Campaign Status</h3>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-green-500 shadow-sm animate-pulse"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Profile Complete</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div v-if="activeElection?.is_ongoing" class="w-3 h-3 rounded-full bg-green-500 shadow-sm animate-pulse"></div>
                                <div v-else class="w-3 h-3 rounded-full bg-gray-300 dark:bg-muted"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ activeElection?.is_ongoing ? 'Election Active' : 'Election Ended' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-foreground mb-4">Quick Stats</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-muted-foreground">Total Voters</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-foreground">{{ statistics.totalVoters }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-muted-foreground">Your Votes</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-foreground">{{ statistics.votesReceived }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-muted-foreground">Vote Rate</span>
                                <span class="text-sm font-bold text-primary">{{ statistics.votePercentage }}%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-foreground mb-4">Quick Actions</h3>
                        <div class="space-y-2">
                            <Link href="/candidate/profile" 
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 rounded-lg hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary transition-all group border border-transparent hover:border-primary/20">
                                <Icon name="user" class="w-4 h-4 text-gray-400 group-hover:text-primary transition-colors" />
                                View Profile
                            </Link>
                            <Link href="/candidate/results" 
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 rounded-lg hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary transition-all group border border-transparent hover:border-primary/20">
                                <Icon name="bar-chart-2" class="w-4 h-4 text-gray-400 group-hover:text-primary transition-colors" />
                                View Results
                            </Link>
                            <Link href="/candidate/announcements" 
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 rounded-lg hover:bg-primary/10 dark:hover:bg-primary/20 hover:text-primary dark:hover:text-primary transition-all group border border-transparent hover:border-primary/20">
                                <Icon name="bell" class="w-4 h-4 text-gray-400 group-hover:text-primary transition-colors" />
                                Announcements
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CandidateLayout>
</template>
