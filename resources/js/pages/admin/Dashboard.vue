<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import Icon from '@/components/Icon.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { createChart } from '@/composables/useChart';

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

    // Destroy existing instance
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
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(251, 146, 60, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(244, 63, 94, 0.8)',
                ],
                borderWidth: 3,
                borderColor: 'rgba(255, 255, 255, 0.8)',
                hoverBorderWidth: 4,
                hoverBorderColor: 'rgba(255, 255, 255, 1)',
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
                        padding: 15,
                        font: {
                            size: 12
                        },
                        usePointStyle: true,
                        pointStyle: 'circle',
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    displayColors: true,
                    callbacks: {
                        label: function(context: any) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const position = props.chartData?.positions[context.dataIndex] || '';
                            return `${label} (${position}): ${value} votes`;
                        }
                    }
                }
            }
        }
    });
};

const initLineChart = () => {
    if (!props.chartData) return;
    
    const canvas = document.getElementById('lineChart') as HTMLCanvasElement;
    if (!canvas) return;

    if (lineChartInstance) {
        lineChartInstance.destroy();
    }

    lineChartInstance = createChart(canvas, {
        type: 'line',
        data: {
            labels: props.chartData.labels,
            datasets: [{
                label: 'Vote Trend',
                data: props.chartData.data,
                borderColor: 'rgba(99, 102, 241, 1)',
                backgroundColor: (context: any) => {
                    const ctx = context.chart.ctx;
                    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.3)');
                    gradient.addColorStop(0.5, 'rgba(99, 102, 241, 0.15)');
                    gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');
                    return gradient;
                },
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 10,
                pointHoverBackgroundColor: 'rgba(99, 102, 241, 1)',
                pointHoverBorderWidth: 3,
                pointHitRadius: 15,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            animation: {
                duration: 2500,
                easing: 'easeInOutCubic',
                delay: (context: any) => {
                    let delay = 0;
                    if (context.type === 'data' && context.mode === 'default') {
                        delay = context.dataIndex * 150;
                    }
                    return delay;
                },
                x: {
                    type: 'number',
                    easing: 'easeInOutElastic',
                    duration: 2000,
                    from: 0,
                },
                y: {
                    type: 'number',
                    easing: 'easeInOutBounce',
                    duration: 2500,
                    from: (context: any) => {
                        const chart = context.chart;
                        const meta = chart.getDatasetMeta(0);
                        return chart.scales.y.getPixelForValue(0);
                    }
                },
                onProgress: (animation: any) => {
                    const chart = animation.chart;
                    const ctx = chart.ctx;
                    ctx.save();
                    ctx.shadowColor = 'rgba(99, 102, 241, 0.5)';
                    ctx.shadowBlur = 10 * (animation.currentStep / animation.numSteps);
                    ctx.restore();
                },
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    displayColors: true,
                    callbacks: {
                        label: function(context: any) {
                            const label = context.label || '';
                            const value = context.parsed.y || 0;
                            const position = props.chartData?.positions[context.dataIndex] || '';
                            return `${label} (${position}): ${value} votes`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                    }
                },
                x: {
                    grid: {
                        display: false,
                    }
                }
            }
        }
    });
};

const updateCharts = () => {
    // Destroy existing charts
    if (doughnutChartInstance) {
        doughnutChartInstance.destroy();
        doughnutChartInstance = null;
    }
    if (lineChartInstance) {
        lineChartInstance.destroy();
        lineChartInstance = null;
    }
    
    // Reinitialize with slight delay to ensure clean animation
    setTimeout(() => {
        initDoughnutChart();
        initLineChart();
    }, 100);
};

onMounted(() => {
    if (props.chartData) {
        // Wait for DOM to be fully ready
        setTimeout(() => {
            initDoughnutChart();
            initLineChart();
        }, 200);
    }
    
    refreshInterval = window.setInterval(() => {
        autoRefresh();
    }, 30000);
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    if (doughnutChartInstance) {
        doughnutChartInstance.destroy();
    }
    if (lineChartInstance) {
        lineChartInstance.destroy();
    }
});

const navigateToElection = () => {
    router.visit('/admin/election');
};

const navigateToCandidates = () => {
    router.visit('/admin/candidates');
};

const navigateToAnnouncements = () => {
    router.visit('/admin/announcements');
};

const navigateToResults = () => {
    router.visit('/admin/result');
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
                    <p class="text-muted-foreground mt-1">Welcome back! Here's what's happening with your elections.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div v-if="isRefreshing" class="flex items-center gap-2 text-sm text-muted-foreground">
                        <Icon name="loader" class="h-4 w-4 animate-spin" />
                        <span>Refreshing...</span>
                    </div>
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
                            <p class="text-xs text-purple-600/70 dark:text-purple-400/70">In active elections</p>
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

            <!-- Charts Section -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Doughnut Chart -->
                <div class="rounded-xl border bg-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold">Vote Distribution</h3>
                        <p class="text-sm text-muted-foreground" v-if="activeElection">{{ activeElection.title }}</p>
                        <p class="text-sm text-muted-foreground" v-else>Candidate vote breakdown</p>
                    </div>
                    
                    <div v-if="chartData && activeElection" class="h-[350px]">
                        <canvas id="doughnutChart"></canvas>
                    </div>
                    
                    <div v-else class="flex flex-col items-center justify-center h-[350px]">
                        <div class="rounded-full bg-muted p-4 mb-3">
                            <Icon name="pieChart" class="h-8 w-8 text-muted-foreground" />
                        </div>
                        <p class="text-sm text-muted-foreground text-center">No active election with votes yet</p>
                    </div>
                </div>

                <!-- Line Chart -->
                <div class="rounded-xl border bg-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold">Vote Trend</h3>
                        <p class="text-sm text-muted-foreground" v-if="activeElection">{{ activeElection.title }}</p>
                        <p class="text-sm text-muted-foreground" v-else>Candidate vote comparison</p>
                    </div>
                    
                    <div v-if="chartData && activeElection" class="h-[350px]">
                        <canvas id="lineChart"></canvas>
                    </div>
                    
                    <div v-else class="flex flex-col items-center justify-center h-[350px]">
                        <div class="rounded-full bg-muted p-4 mb-3">
                            <Icon name="lineChart" class="h-8 w-8 text-muted-foreground" />
                        </div>
                        <p class="text-sm text-muted-foreground text-center">No active election with votes yet</p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Elections List - Takes 2 columns -->
                <div class="lg:col-span-2">
                    <div class="mb-4">
                        <h2 class="text-xl font-semibold">Elections</h2>
                        <p class="text-sm text-muted-foreground">Monitor and manage all elections</p>
                    </div>
                    
                    <!-- Empty State -->
                    <div v-if="elections.length === 0" class="flex flex-col items-center justify-center p-12 border rounded-xl bg-card">
                        <div class="rounded-full bg-muted p-6 mb-4">
                            <Icon name="clipboardList" class="h-12 w-12 text-muted-foreground" />
                        </div>
                        <h3 class="text-lg font-semibold mb-2">No elections yet</h3>
                        <p class="text-sm text-muted-foreground mb-4 text-center">Get started by creating your first election</p>
                        <button 
                            @click="navigateToElection"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-colors"
                        >
                            <Icon name="plus" class="h-4 w-4" />
                            Create Election
                        </button>
                    </div>

                    <!-- Elections List -->
                    <div v-else class="space-y-4">
                        <!-- if election is ended it should not be hoverable -->
                        <div
                            v-for="election in elections"
                            :key="election.id"
                            
                            :class="[
                              'group rounded-xl border p-6 transition-all duration-300 bg-card',
                              election.status === 'ended' ? '' : 'hover:shadow-lg hover:border-primary/50'
                            ]"
                        >
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-lg font-semibold">{{ election.title }}</h3>
                                        <span 
                                            :class="
                                                getStatusBadge(election).pulse 
                                                    ? 'inline-flex items-center gap-1.5 rounded-md px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset ' + getStatusBadge(election).class
                                                    : 'inline-flex items-center gap-1.5 rounded-md px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset ' + getStatusBadge(election).class
                                            "
                                        >
                                            <span 
                                                v-if="getStatusBadge(election).pulse"
                                                class="h-1.5 w-1.5 rounded-full bg-green-600 dark:bg-green-400 animate-pulse" 
                                            />
                                            <span v-else class="h-1.5 w-1.5 rounded-full bg-muted-foreground" />
                                            {{ getStatusBadge(election).label }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-muted-foreground mb-4">{{ election.description }}</p>
                                    
                                    <!-- Progress Bar (only for active elections) -->
                                    <div class="mb-4" v-if="election.is_active">
                                        <div class="flex items-center justify-between text-xs mb-2">
                                            <span class="text-muted-foreground">Voter Turnout</span>
                                            <span class="font-medium">{{ election.turnout_percentage }}%</span>
                                        </div>
                                        <div class="h-2 w-full rounded-full bg-muted overflow-hidden">
                                            <div 
                                                class="h-full rounded-full bg-gradient-to-r from-purple-800 to-purple-900 transition-all duration-500"
                                                :style="{ width: `${election.turnout_percentage}%` }"
                                            />
                                        </div>
                                    </div>

                                    <!-- Election Metrics -->
                                    <div class="grid grid-cols-4 gap-4">
                                        <div class="flex items-center gap-2 text-sm">
                                            <Icon name="calendar" class="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p class="text-xs text-muted-foreground">Start Date</p>
                                                <p class="font-medium text-xs">{{ formatDate(election.start_datetime) }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <Icon name="users" class="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p class="text-xs text-muted-foreground">Votes</p>
                                                <p class="font-medium text-xs">{{ election.voted_count.toLocaleString() }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <Icon name="briefcase" class="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p class="text-xs text-muted-foreground">Positions</p>
                                                <p class="font-medium text-xs">{{ election.positions_count }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <Icon name="userCheck" class="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p class="text-xs text-muted-foreground">Candidates</p>
                                                <p class="font-medium text-xs">{{ election.candidates_count }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions Card -->
                    <div class="rounded-xl border bg-card p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold">Quick Actions</h3>
                            <p class="text-sm text-muted-foreground">Manage your election system</p>
                        </div>

                        <div class="space-y-3">
                            <button 
                                @click="navigateToElection"
                                class="flex items-start gap-3 w-full rounded-lg border bg-background p-4 hover:bg-accent hover:shadow-md transition-all duration-200 group text-left"
                            >
                                <div class="rounded-lg bg-primary/10 p-2 group-hover:bg-primary/20 transition-colors flex-shrink-0">
                                    <Icon name="plus" class="h-5 w-5 text-primary" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium">Create Election</p>
                                    <p class="text-xs text-muted-foreground">Start a new election</p>
                                </div>
                            </button>

                            <button 
                                @click="navigateToCandidates"
                                class="flex items-start gap-3 w-full rounded-lg border bg-background p-4 hover:bg-accent hover:shadow-md transition-all duration-200 group text-left"
                            >
                                <div class="rounded-lg bg-blue-500/10 p-2 group-hover:bg-blue-500/20 transition-colors flex-shrink-0">
                                    <Icon name="userPlus" class="h-5 w-5 text-blue-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium">Add Candidate</p>
                                    <p class="text-xs text-muted-foreground">Register new candidate</p>
                                </div>
                            </button>

                            <button 
                                @click="navigateToAnnouncements"
                                class="flex items-start gap-3 w-full rounded-lg border bg-background p-4 hover:bg-accent hover:shadow-md transition-all duration-200 group text-left"
                            >
                                <div class="rounded-lg bg-purple-500/10 p-2 group-hover:bg-purple-500/20 transition-colors flex-shrink-0">
                                    <Icon name="megaphone" class="h-5 w-5 text-purple-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium">Add Announcement</p>
                                    <p class="text-xs text-muted-foreground">Publish update</p>
                                </div>
                            </button>

                            <button 
                                @click="navigateToResults"
                                class="flex items-start gap-3 w-full rounded-lg border bg-background p-4 hover:bg-accent hover:shadow-md transition-all duration-200 group text-left"
                            >
                                <div class="rounded-lg bg-green-500/10 p-2 group-hover:bg-green-500/20 transition-colors flex-shrink-0">
                                    <Icon name="barChart2" class="h-5 w-5 text-green-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium">View Results</p>
                                    <p class="text-xs text-muted-foreground">Check outcomes</p>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Activity Feed -->
                    <div class="rounded-xl border bg-card p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold">Recent Activity</h3>
                            <p class="text-sm text-muted-foreground">Latest system updates</p>
                        </div>

                        <!-- Empty Activities State -->
                        <div v-if="activities.length === 0" class="flex flex-col items-center justify-center py-8">
                            <div class="rounded-full bg-muted p-3 mb-3">
                                <Icon name="activity" class="h-6 w-6 text-muted-foreground" />
                            </div>
                            <p class="text-sm text-muted-foreground text-center">No recent activities</p>
                        </div>

                        <!-- Activities List -->
                        <div v-else class="space-y-4">
                            <div
                                v-for="(activity, index) in activities"
                                :key="index"
                                class="flex items-start gap-3 pb-4 border-b last:border-0 last:pb-0"
                            >
                                <div 
                                    :class="
                                        activity.color === 'green' ? 'rounded-lg p-2 flex-shrink-0 bg-green-100 dark:bg-green-950' :
                                        activity.color === 'blue' ? 'rounded-lg p-2 flex-shrink-0 bg-blue-100 dark:bg-blue-950' :
                                        'rounded-lg p-2 flex-shrink-0 bg-purple-100 dark:bg-purple-950'
                                    "
                                >
                                    <Icon 
                                        :name="activity.icon" 
                                        :class="
                                            activity.color === 'green' ? 'h-4 w-4 text-green-600 dark:text-green-400' :
                                            activity.color === 'blue' ? 'h-4 w-4 text-blue-600 dark:text-blue-400' :
                                            'h-4 w-4 text-purple-600 dark:text-purple-400'
                                        "
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium">{{ activity.title }}</p>
                                    <p class="text-xs text-muted-foreground truncate">{{ activity.description }}</p>
                                    <p class="text-xs text-muted-foreground mt-1">{{ activity.time }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Last Updated -->
                    <div class="text-center">
                        <p class="text-xs text-muted-foreground">
                            Last updated: {{ lastUpdated.toLocaleTimeString() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
    button:hover {
        cursor: pointer;
    }
</style>