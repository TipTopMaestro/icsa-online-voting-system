<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CandidateLayout from '@/layouts/CandidateLayout.vue';
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
                                    
                                    <p v-if="activeElection" class="text-md text-gray-600 mt-1">{{ activeElection.name }}</p>
                                    <p v-if="candidatePosition" class="text-sm text-purple-600 mt-2 font-medium">
                                        Running for: {{ candidatePosition.name }}
                                    </p>
                                </div>
                                <span v-if="activeElection?.is_ongoing" class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                    Active
                                </span>
                                <span v-if="!activeElection?.is_ongoing" class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-gray-50 text-gray-700 border border-gray-200">
                                    Ended
                                </span>

                            </div>

                            <div v-if="!activeElection" class="text-center py-8 text-gray-500 text-sm">
                                No active election at the moment
                            </div>
                        </div>

                        <!-- Vote Statistics -->
                        <div class="grid grid-cols-2 gap-6">
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <p class="text-sm text-gray-600">Votes Received</p>
                                <p class="text-3xl font-semibold text-gray-900 mt-2">{{ statistics.votesReceived }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ statistics.votePercentage }}% of total voters
                                </p>
                            </div>
                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                <p class="text-sm text-gray-600">Current Ranking</p>
                                <p class="text-3xl font-semibold text-gray-900 mt-2">
                                    {{ statistics.ranking > 0 ? '#' + statistics.ranking : '-' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Out of {{ statistics.totalCandidates }} candidates
                                </p>
                            </div>
                        </div>

                        <!-- Performance Overview -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-base font-semibold text-gray-900 mb-4">Performance Overview</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center justify-between text-sm mb-2">
                                        <span class="text-gray-600">Vote Progress</span>
                                        <span class="font-medium text-gray-900">{{ statistics.votesReceived }} / {{ statistics.totalVoters }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div 
                                            class="bg-purple-600 h-2 rounded-full transition-all duration-500"
                                            :style="{ width: statistics.votePercentage + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Announcements -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-semibold text-gray-900">Recent Announcements</h3>
                                <a href="/candidate/announcements" class="text-sm text-purple-600 hover:text-purple-700">
                                    View all
                                </a>
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
                        <!-- Campaign Status -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-base font-semibold text-gray-900 mb-4">Campaign Status</h3>
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                    <span class="text-sm text-gray-700">Profile Complete</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div v-if="activeElection?.is_ongoing" class="w-2 h-2 rounded-full bg-green-500"></div>
                                    <div v-else class="w-2 h-2 rounded-full bg-gray-300"></div>
                                    <span v-if="activeElection?.is_ongoing" class="text-sm text-gray-700">Election Active</span>
                                    <span v-else class="text-sm text-gray-700">Election Ended</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-base font-semibold text-gray-900 mb-4">Quick Stats</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Total Voters</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ statistics.totalVoters }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Your Votes</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ statistics.votesReceived }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Vote Rate</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ statistics.votePercentage }}%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-base font-semibold text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-1">
                                <a href="/candidate/profile" 
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-50">
                                    View Profile
                                </a>
                                <a href="/candidate/results" 
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-50">
                                    View Results
                                </a>
                                <a href="/candidate/announcements" 
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-50">
                                    Announcements
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CandidateLayout>
</template>