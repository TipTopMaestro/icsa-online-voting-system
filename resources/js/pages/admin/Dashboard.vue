<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import Icon from '@/components/Icon.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { createChart } from '@/composables/useChart';
import { Clock, TrendingUp, Users, ClipboardList, CheckCircle, UserCheck, Plus, UserPlus, Megaphone, BarChart2, History, Activity, Loader, ArrowUpRight } from 'lucide-vue-next';

interface Activity {
    type: string;
    title: string;
    description: string;
    time: string;
    timestamp: number;
    icon: string;
    color: string;
}

interface Election {
    id: number;
    title: string;
    description: string;
    start_datetime: string;
    end_datetime: string;
    is_active: boolean;
    status: string;
    positions_count: number;
    candidates_count: number;
    votes_count: number;
    voted_count: number;
    total_voters: number;
    turnout_percentage: number;
}

interface ChartData {
    labels: string[];
    data: number[];
    positions: string[];
}

const props = defineProps<{
    stats: {
        totalVoters: number;
        activeElections: number;
        totalVotes: number;
        totalCandidates: number;
    };
    chartData: ChartData | null;
    activities: Activity[];
    elections: Election[];
    activeElection: {
        id: number;
        title: string;
    } | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const isRefreshing = ref(false);
const lastUpdated = ref(new Date());
let refreshInterval: number | null = null;
let doughnutChartInstance: any = null;
let lineChartInstance: any = null;

const turnoutPercentage = computed(() => {
    if (props.stats.totalVoters === 0) return '0.0';
    return ((props.stats.totalVotes / props.stats.totalVoters) * 100).toFixed(1);
});

const getStatusBadge = (election: Election) => {
    if (election.is_active) {
        return { label: 'Live', class: 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/30', pulse: true };
    } else if (election.status === 'ended') {
        return { label: 'Ended', class: 'bg-muted text-muted-foreground ring-border', pulse: false };
    } else {
        return { label: 'Scheduled', class: 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/30', pulse: false };
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const autoRefresh = () => {
    isRefreshing.value = true;
    router.reload({ 
        only: ['stats', 'chartData', 'activities', 'elections', 'activeElection'],
        onSuccess: () => {
            isRefreshing.value = false;
            lastUpdated.value = new Date();
            updateCharts();
        },
        onError: () => {
            isRefreshing.value = false;
        }
    });
};

const initDoughnutChart = () => {
    if (!props.chartData) return;
    
    const canvas = document.getElementById('doughnutChart') as HTMLCanvasElement;
    if (!canvas) return;

    if (doughnutChartInstance) {
        doughnutChartInstance.destroy();
    }

    doughnutChartInstance = createChart(canvas, {
        type: 'doughnut',
        data: {
            labels: props.chartData.labels,
            datasets: [{
                label: 'Votes',
                data: props.chartData.data,
                backgroundColor: [
                    'rgba(92, 62, 148, 0.8)',
                    'rgba(242, 89, 18, 0.8)',
                    'rgba(65, 43, 107, 0.8)',
                    'rgba(33, 24, 50, 0.8)',
                    'rgba(147, 51, 234, 0.8)',
                    'rgba(249, 115, 22, 0.8)',
                ],
                borderWidth: 2,
                borderColor: 'white',
                hoverOffset: 15,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 10,
                        font: { size: 10, weight: '600' },
                        usePointStyle: true
                    }
                }
            }
        }
    });
};

const updateCharts = () => {
    initDoughnutChart();
};

onMounted(() => {
    initDoughnutChart();
    refreshInterval = window.setInterval(autoRefresh, 30000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
    if (doughnutChartInstance) doughnutChartInstance.destroy();
});

const navigateToElection = () => router.visit('/admin/election');
const navigateToCandidates = () => router.visit('/admin/candidates');
const navigateToAnnouncements = () => router.visit('/admin/announcements');
const navigateToResults = () => router.visit('/admin/result');
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 md:gap-6 p-4 md:p-8 min-h-[calc(100vh-64px)]">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Dashboard</h1>
                    <p class="text-muted-foreground mt-1 text-[11px] md:text-sm font-medium">Real-time election oversight and analytics.</p>
                </div>
                <div class="flex items-center gap-2 md:gap-3 flex-wrap">
                    <div v-if="isRefreshing" class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                        <Loader class="h-3 w-3 animate-spin" />
                        <span>Syncing...</span>
                    </div>
                    <div class="rounded-xl border bg-white dark:bg-card px-3 md:px-4 py-1.5 md:py-2 shadow-sm border-gray-100 dark:border-border">
                        <div class="flex items-center gap-2">
                            <TrendingUp class="h-3.5 w-3.5 md:h-4 md:w-4 text-primary flex-shrink-0" />
                            <div>
                                <p class="text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">Turnout</p>
                                <p class="text-sm md:text-lg font-black text-gray-900 dark:text-foreground leading-tight">{{ turnoutPercentage }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(stat, i) in [
                    { label: 'Total Voters', value: stats.totalVoters, sub: 'Registered students', icon: Users, color: 'primary' },
                    { label: 'Active Elections', value: stats.activeElections, sub: 'Currently running', icon: ClipboardList, color: 'accent' },
                    { label: 'Total Votes', value: stats.totalVotes, sub: 'Across active polls', icon: CheckCircle, color: 'primary' },
                    { label: 'Candidates', value: stats.totalCandidates, sub: 'Verified candidates', icon: UserCheck, color: 'accent' }
                ]" :key="i" class="group relative overflow-hidden rounded-2xl border border-gray-100 dark:border-border p-5 md:p-6 transition-all duration-300 hover:shadow-lg bg-white dark:bg-card">
                    <div class="absolute right-0 top-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full opacity-[0.03] group-hover:scale-110 transition-transform bg-current" :class="`text-${stat.color}`" />
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="rounded-xl p-2.5 shadow-sm" :class="stat.color === 'primary' ? 'bg-primary/10 text-primary' : 'bg-accent/10 text-accent'">
                                <component :is="stat.icon" class="h-5 w-5 md:h-6 md:w-6" />
                            </div>
                            <ArrowUpRight class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-all text-gray-300"/>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] md:text-xs font-black text-gray-400 uppercase tracking-widest">{{ stat.label }}</p>
                            <p class="text-xl md:text-3xl font-black text-gray-900 dark:text-foreground leading-tight">{{ stat.value.toLocaleString() }}</p>
                            <p class="text-[10px] text-gray-400 font-medium">{{ stat.sub }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts & Elections -->
            <div class="grid gap-6 grid-cols-1 lg:grid-cols-3">
                <div class="lg:col-span-1 rounded-2xl border border-gray-100 dark:border-border bg-white dark:bg-card p-5 md:p-6 shadow-sm">
                    <div class="mb-6 text-center lg:text-left">
                        <h3 class="text-sm md:text-base font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Vote Distribution</h3>
                        <p class="text-[10px] md:text-xs text-muted-foreground font-medium mt-1 uppercase tracking-widest">Share by position</p>
                    </div>
                    <div class="h-[250px] md:h-[300px] relative">
                        <canvas id="doughnutChart"></canvas>
                    </div>
                </div>

                <div class="lg:col-span-2 rounded-2xl border border-gray-100 dark:border-border bg-white dark:bg-card p-5 md:p-6 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-sm md:text-base font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Active Processes</h3>
                        <button @click="navigateToElection" class="text-[10px] font-black text-primary uppercase tracking-widest hover:underline">Full Directory</button>
                    </div>

                    <div class="overflow-x-auto -mx-5 md:-mx-6">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50 dark:bg-muted/30 border-y border-gray-100 dark:border-border">
                                <tr class="text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    <th class="px-5 md:px-6 py-3">Election Title</th>
                                    <th class="px-5 md:px-6 py-3">Statistics</th>
                                    <th class="px-5 md:px-6 py-3 text-right">Progress</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-border text-[11px] md:text-sm">
                                <tr v-for="election in elections.slice(0, 5)" :key="election.id" class="hover:bg-gray-50/50 dark:hover:bg-muted/10 transition-colors">
                                    <td class="px-5 md:px-6 py-4">
                                        <div class="flex flex-col gap-1.5">
                                            <p class="font-bold text-gray-900 dark:text-foreground leading-tight">{{ election.title }}</p>
                                            <span :class="getStatusBadge(election).class" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-widest border w-fit">
                                                {{ getStatusBadge(election).label }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 md:px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-700 dark:text-gray-300">{{ election.voted_count }} / {{ election.total_voters }}</span>
                                            <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest mt-0.5">Voted</span>
                                        </div>
                                    </td>
                                    <td class="px-5 md:px-6 py-4 text-right">
                                        <div class="flex flex-col items-end gap-1.5">
                                            <span class="font-black text-primary">{{ election.turnout_percentage }}%</span>
                                            <div class="w-12 md:w-24 bg-gray-100 dark:bg-muted h-1 rounded-full overflow-hidden">
                                                <div class="bg-primary h-full transition-all duration-1000" :style="{ width: election.turnout_percentage + '%' }"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Management & Audit -->
            <div class="grid gap-6 grid-cols-1 lg:grid-cols-3">
                <div class="rounded-2xl border border-gray-100 dark:border-border bg-white dark:bg-card p-6 shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-sm md:text-base font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Operations</h3>
                        <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-widest mt-1">Shortcuts</p>
                    </div>
                    <div class="space-y-2">
                        <button v-for="(btn, i) in [
                            { label: 'Candidate Hub', icon: UserPlus, color: 'text-primary', action: navigateToCandidates },
                            { label: 'Press Center', icon: Megaphone, color: 'text-accent', action: navigateToAnnouncements },
                            { label: 'Outcome Analytics', icon: BarChart2, color: 'text-green-500', action: navigateToResults }
                        ]" :key="i" @click="btn.action" class="flex items-center gap-3 w-full p-3 rounded-xl bg-gray-50 dark:bg-muted/30 border dark:border-border hover:bg-white dark:hover:bg-card hover:shadow-md transition-all group">
                            <div class="p-2 rounded-lg bg-white dark:bg-card shadow-sm" :class="btn.color">
                                <component :is="btn.icon" class="h-4 w-4" />
                            </div>
                            <span class="text-xs font-black text-gray-700 dark:text-foreground uppercase tracking-widest">{{ btn.label }}</span>
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-2 rounded-2xl border border-gray-100 dark:border-border bg-white dark:bg-card p-6 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-sm md:text-base font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Recent Activity Log</h3>
                            <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-widest mt-1">Audit Trail</p>
                        </div>
                        <History class="h-5 w-5 text-gray-200" />
                    </div>
                    <div v-if="activities.length === 0" class="flex flex-col items-center justify-center py-10 opacity-50">
                        <Activity class="h-8 w-8 text-gray-300 mb-2" />
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">All Systems Nominal</p>
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="(act, i) in activities.slice(0, 6)" :key="i" class="flex items-start gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-muted/20 border dark:border-border group transition-all hover:bg-white dark:hover:bg-card hover:shadow-sm">
                            <div class="h-10 w-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm" :class="act.color === 'blue' ? 'bg-blue-100 text-blue-600' : act.color === 'green' ? 'bg-green-100 text-green-600' : 'bg-purple-100 text-purple-600'">
                                <Icon :name="act.icon" class="h-5 w-5" />
                            </div>
                            <div class="flex-1 min-w-0 py-0.5">
                                <p class="text-[11px] font-black text-gray-900 dark:text-foreground uppercase tracking-tight truncate">{{ act.title }}</p>
                                <p class="text-[10px] text-muted-foreground font-medium truncate mt-0.5">{{ act.description }}</p>
                                <div class="flex items-center gap-1 mt-2 text-[8px] font-black text-gray-400 uppercase tracking-widest">
                                    <Clock class="h-2.5 w-2.5" />
                                    <span>{{ act.time }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center gap-2 pt-4">
                <div class="w-1.5 h-1.5 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.5)] animate-pulse"></div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">
                    Cloud Infrastructure Connected: {{ lastUpdated.toLocaleTimeString() }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
</style>
