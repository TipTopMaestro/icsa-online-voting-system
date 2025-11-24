<!-- we used ai for the ui of this dashboard page so we need to study this page/fileswhat  -->





<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import Icon from '@/components/Icon.vue';
import { ref, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const stats = ref({
    totalVoters: 1247,
    activeElections: 2,
    totalVotes: 856,
    totalCandidates: 24
});

const elections = ref([
    {
        id: 1,
        title: 'ICSA Election 2025',
        description: 'Election for the new ICSA officer for the year 2025.',
        startDate: 'Jan 01, 2025',
        endDate: 'Jan 07, 2025',
        votes: 450,
        totalVoters: 1000,
        status: 'active',
        positions: 3,
        candidates: 7
    },
    {
        id: 2,
        title: 'IC Class Representative Election',
        description: 'IC Class Representative Election for the year 2025.',
        startDate: 'Jan 01, 2025',
        endDate: 'Jan 07, 2025',
        votes: 380,
        totalVoters: 950,
        status: 'active',
        positions: 2,
        candidates: 5
    },
    {
        id: 3,
        title: 'Freshmen Council Election',
        description: 'Election for first-year student representatives',
        startDate: 'Jan 15, 2025',
        endDate: 'Jan 16, 2025',
        votes: 342,
        totalVoters: 1247,
        status: 'ended',
        positions: 6,
        candidates: 12
    }
]);

const turnoutPercentage = computed(() => {
    return ((stats.value.totalVotes / stats.value.totalVoters) * 100).toFixed(1);
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
                    <p class="text-muted-foreground mt-1">Welcome back! Here's what's happening with your elections.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="rounded-lg border bg-card px-4 py-2">
                        <div class="flex items-center gap-2">
                            <Icon name="trendingUp" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                            <div>
                                <p class="text-xs text-muted-foreground">Overall Turnout</p>
                                <p class="text-lg font-bold">{{ turnoutPercentage }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Voters Card -->
                <div class="group relative overflow-hidden rounded-xl border bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950 dark:to-blue-900 p-6 transition-all duration-300 hover:shadow-xl hover:scale-[1.02]">
                    <div class="absolute right-0 top-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-blue-500/10" />
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="rounded-xl bg-blue-500 p-3 shadow-lg shadow-blue-500/50">
                                <Icon name="users" class="h-6 w-6 text-white" />
                            </div>
                            <Icon name="arrowUpRight" class="h-5 w-5 text-blue-600 dark:text-blue-400 opacity-0 group-hover:opacity-100 transition-opacity" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Total Voters</p>
                            <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ stats.totalVoters.toLocaleString() }}</p>
                            <p class="text-xs text-blue-600/70 dark:text-blue-400/70">Registered students</p>
                        </div>
                    </div>
                </div>

                <!-- Active Elections Card -->
                <div class="group relative overflow-hidden rounded-xl border bg-gradient-to-br from-green-50 to-green-100 dark:from-green-950 dark:to-green-900 p-6 transition-all duration-300 hover:shadow-xl hover:scale-[1.02]">
                    <div class="absolute right-0 top-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-green-500/10" />
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="rounded-xl bg-green-500 p-3 shadow-lg shadow-green-500/50">
                                <Icon name="clipboardList" class="h-6 w-6 text-white" />
                            </div>
                            <Icon name="arrowUpRight" class="h-5 w-5 text-green-600 dark:text-green-400 opacity-0 group-hover:opacity-100 transition-opacity" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-green-700 dark:text-green-300">Active Elections</p>
                            <p class="text-3xl font-bold text-green-900 dark:text-green-100">{{ stats.activeElections }}</p>
                            <p class="text-xs text-green-600/70 dark:text-green-400/70">Currently running</p>
                        </div>
                    </div>
                </div>

                <!-- Total Votes Card -->
                <div class="group relative overflow-hidden rounded-xl border bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-950 dark:to-purple-900 p-6 transition-all duration-300 hover:shadow-xl hover:scale-[1.02]">
                    <div class="absolute right-0 top-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-purple-500/10" />
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="rounded-xl bg-purple-500 p-3 shadow-lg shadow-purple-500/50">
                                <Icon name="checkCircle" class="h-6 w-6 text-white" />
                            </div>
                            <Icon name="arrowUpRight" class="h-5 w-5 text-purple-600 dark:text-purple-400 opacity-0 group-hover:opacity-100 transition-opacity" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-purple-700 dark:text-purple-300">Total Votes Cast</p>
                            <p class="text-3xl font-bold text-purple-900 dark:text-purple-100">{{ stats.totalVotes.toLocaleString() }}</p>
                            <p class="text-xs text-purple-600/70 dark:text-purple-400/70">Across all elections</p>
                        </div>
                    </div>
                </div>

                <!-- Total Candidates Card -->
                <div class="group relative overflow-hidden rounded-xl border bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-950 dark:to-orange-900 p-6 transition-all duration-300 hover:shadow-xl hover:scale-[1.02]">
                    <div class="absolute right-0 top-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-orange-500/10" />
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="rounded-xl bg-orange-500 p-3 shadow-lg shadow-orange-500/50">
                                <Icon name="userCheck" class="h-6 w-6 text-white" />
                            </div>
                            <Icon name="arrowUpRight" class="h-5 w-5 text-orange-600 dark:text-orange-400 opacity-0 group-hover:opacity-100 transition-opacity" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-orange-700 dark:text-orange-300">Total Candidates</p>
                            <p class="text-3xl font-bold text-orange-900 dark:text-orange-100">{{ stats.totalCandidates }}</p>
                            <p class="text-xs text-orange-600/70 dark:text-orange-400/70">Running for positions</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Elections Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Active Elections -->
                <div class="lg:col-span-2">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold">Elections</h2>
                            <p class="text-sm text-muted-foreground">Monitor and manage ongoing elections</p>
                        </div>
                        <button class="inline-flex items-center gap-2 rounded-lg border bg-card px-4 py-2 text-sm font-medium hover:bg-accent transition-colors">
                            <Icon name="plus" class="h-4 w-4" />
                            New Election
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div
                            v-for="election in elections"
                            :key="election.id"
                            :class="[
                                'group rounded-xl border p-6 transition-all duration-300',
                                election.status === 'active' 
                                    ? 'bg-card hover:shadow-lg hover:border-primary/50' 
                                    : 'bg-muted/30'
                            ]"
                        >
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-lg font-semibold" :class="election.status === 'ended' ? 'text-muted-foreground' : ''">
                                            {{ election.title }}
                                        </h3>
                                        <span 
                                            :class="[
                                                'inline-flex items-center gap-1.5 rounded-md px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset',
                                                election.status === 'active'
                                                    ? 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/30'
                                                    : 'bg-muted text-muted-foreground ring-border'
                                            ]"
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full" :class="election.status === 'active' ? 'bg-green-600 dark:bg-green-400 animate-pulse' : 'bg-muted-foreground'" />
                                            {{ election.status === 'active' ? 'Live' : 'Ended' }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-muted-foreground mb-4">{{ election.description }}</p>
                                    
                                    <!-- Progress Bar -->
                                    <div class="mb-4" v-if="election.status === 'active'">
                                        <div class="flex items-center justify-between text-xs mb-2">
                                            <span class="text-muted-foreground">Voter Turnout</span>
                                            <span class="font-medium">{{ ((election.votes / election.totalVoters) * 100).toFixed(1) }}%</span>
                                        </div>
                                        <div class="h-2 w-full rounded-full bg-muted overflow-hidden">
                                            <div 
                                                class="h-full rounded-full bg-purple-500 bg-purple-800 dark:bg-purple-900 transition-all duration-500"
                                                :style="{ width: `${(election.votes / election.totalVoters) * 100}%` }"
                                            />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-4 gap-4">
                                        <div class="flex items-center gap-2 text-sm">
                                            <Icon name="calendar" class="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p class="text-xs text-muted-foreground">Duration</p>
                                                <p class="font-medium">{{ election.startDate }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <Icon name="users" class="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p class="text-xs text-muted-foreground">Votes</p>
                                                <p class="font-medium">{{ election.votes.toLocaleString() }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <Icon name="briefcase" class="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p class="text-xs text-muted-foreground">Positions</p>
                                                <p class="font-medium">{{ election.positions }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <Icon name="userCheck" class="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p class="text-xs text-muted-foreground">Candidates</p>
                                                <p class="font-medium">{{ election.candidates }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 pt-4 border-t">
                                <button class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-colors">
                                    <Icon name="barChart" class="h-4 w-4" />
                                    View Results
                                </button>
                                <button class="inline-flex items-center justify-center gap-2 rounded-lg border bg-red-500 text-white px-4 py-2 text-sm font-medium hover:bg-red-400 transition-colors">
                                    <Icon name="CircleStop" class="h-4 w-4" />
                                    End Election
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions & Stats -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="rounded-xl border bg-card p-6">
                        <h3 class="font-semibold mb-4">Quick Actions</h3>
                        <div class="space-y-2">
                            <button class="w-full flex items-center gap-3 rounded-lg border bg-card p-3 text-sm font-medium hover:bg-accent transition-colors">
                                <div class="rounded-lg bg-blue-500/10 p-2">
                                    <Icon name="userPlus" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                <span class="flex-1 text-left">Add Voter</span>
                                <Icon name="chevronRight" class="h-4 w-4 text-muted-foreground" />
                            </button>
                            <button class="w-full flex items-center gap-3 rounded-lg border bg-card p-3 text-sm font-medium hover:bg-accent transition-colors">
                                <div class="rounded-lg bg-green-500/10 p-2">
                                    <Icon name="plus" class="h-4 w-4 text-green-600 dark:text-green-400" />
                                </div>
                                <span class="flex-1 text-left">Create Election</span>
                                <Icon name="chevronRight" class="h-4 w-4 text-muted-foreground" />
                            </button>
                            <button class="w-full flex items-center gap-3 rounded-lg border bg-card p-3 text-sm font-medium hover:bg-accent transition-colors">
                                <div class="rounded-lg bg-purple-500/10 p-2">
                                    <Icon name="userCheck" class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                </div>
                                <span class="flex-1 text-left">Add Candidate</span>
                                <Icon name="chevronRight" class="h-4 w-4 text-muted-foreground" />
                            </button>
                            <button class="w-full flex items-center gap-3 rounded-lg border bg-card p-3 text-sm font-medium hover:bg-accent transition-colors">
                                <div class="rounded-lg bg-orange-500/10 p-2">
                                    <Icon name="fileText" class="h-4 w-4 text-orange-600 dark:text-orange-400" />
                                </div>
                                <span class="flex-1 text-left">View Reports</span>
                                <Icon name="chevronRight" class="h-4 w-4 text-muted-foreground" />
                            </button>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="rounded-xl border bg-card p-6">
                        <h3 class="font-semibold mb-4">Recent Activity</h3>
                        <div class="space-y-4">
                            <div class="flex gap-3">
                                <div class="rounded-full bg-green-500/10 p-2 h-fit">
                                    <Icon name="checkCircle" class="h-4 w-4 text-green-600 dark:text-green-400" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">New vote cast</p>
                                    <p class="text-xs text-muted-foreground">Student Council Election</p>
                                    <p class="text-xs text-muted-foreground mt-1">2 minutes ago</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="rounded-full bg-blue-500/10 p-2 h-fit">
                                    <Icon name="userPlus" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">New voter registered</p>
                                    <p class="text-xs text-muted-foreground">John Doe (BSCS 3A)</p>
                                    <p class="text-xs text-muted-foreground mt-1">15 minutes ago</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="rounded-full bg-purple-500/10 p-2 h-fit">
                                    <Icon name="userCheck" class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">Candidate approved</p>
                                    <p class="text-xs text-muted-foreground">Jane Smith - President</p>
                                    <p class="text-xs text-muted-foreground mt-1">1 hour ago</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Status -->
                    <div class="rounded-xl border bg-card p-6">
                        <h3 class="font-semibold mb-4">System Status</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Voting System</span>
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse" />
                                    <span class="text-sm font-medium text-green-600 dark:text-green-400">Operational</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Database</span>
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse" />
                                    <span class="text-sm font-medium text-green-600 dark:text-green-400">Connected</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">API Status</span>
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse" />
                                    <span class="text-sm font-medium text-green-600 dark:text-green-400">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
