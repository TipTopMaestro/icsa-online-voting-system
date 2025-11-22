<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from "vue"
import Modal from "@/components/Modal.vue"
import ModalTrigger from "@/components/ModalTrigger.vue"
import Icon from '@/components/Icon.vue';


const open = ref(false)


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Election',
        href: '/admin/election',
    },
];
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <ModalTrigger v-model="open">
                <button class="inline-flex items-center gap-2 rounded-lg border bg-card px-4 py-2 text-sm font-medium hover:bg-accent transition-colors">
                    <Icon name="plus" class="h-4 w-4" />
                    New Election
                </button>
            </ModalTrigger>
            <Modal v-model="open">
            <h2 class="text-xl font-semibold">Create Election</h2>

            <div class="mt-4">
                <label for="title">Election Title</label>
                <input
                    type="text"
                    class="w-full rounded-md border p-2"
                    placeholder="Enter Election Title"
                />
            </div>

            <div class="mt-4">
                <label for="end-date">Election Description</label>
                <input
                    type="text"
                    class="w-full rounded-md border p-2"
                    placeholder="Enter Election Description"
                />
            </div>

            <div class="mt-4">
                <label for="end-date">End Date</label>
                <input
                    type="date"
                    class="w-full rounded-md border p-2"
                    placeholder="dd/mm/yy"
                />
            </div>

            <div class="mt-6 flex justify-end gap-3">
            <Button variant="ghost" @click="open = false">Cancel</Button>
            <Button variant="default">Start Election</Button>
            </div>
        </Modal>
        </div>
          
        <div class="space-y-4 p-4">
            <div
                v-for="election in [
                    {
                        id: 1,
                        title: 'ICSA Election 2025',
                        description: 'Election for the new ICSA officer for the year 2025.',
                        status: 'active',
                        startDate: '01 Jan 2025 - 07 Jan 2025',
                        votes: 450,
                        totalVoters: 1000,
                        positions: 5,
                        candidates: 20,
                    }
                    
                ]"
                :key="election.id"
                :class="[
                    'group rounded-xl border p-6 transition-all duration-300',
                    election.status === 'active' 
                    ? 'bg-card hover:shadow-lg hover:border-primary/50' 
                    : 'bg-muted/30'
                ]">
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
                            ]">
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
                                    class="h-full rounded-full bg-gradient-to-r from-green-500 to-green-600 transition-all duration-500"
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
    </AppLayout>
</template>

