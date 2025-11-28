<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from "vue"
import Modal from "@/components/Modal.vue"
import ModalTrigger from "@/components/ModalTrigger.vue"
import Icon from '@/components/Icon.vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';

interface Election {
    id: number;
    title: string;
    description: string;
    start_datetime: string;
    end_datetime: string;
    is_active: boolean;
    status: 'scheduled' | 'active' | 'ended';
    positions_count: number;
    candidates_count: number;
    votes_count: number;
    total_voters: number;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    elections: Election[];
}>();

// Ensure elections is always an array
const electionsList = computed(() => props.elections || []);

const open = ref(false);
const editMode = ref(false);
const deleteConfirmOpen = ref(false);
const activateConfirmOpen = ref(false);
const deactivateConfirmOpen = ref(false);
const selectedElection = ref<Election | null>(null);

const form = useForm({
    title: '',
    description: '',
    start_datetime: '',
    end_datetime: '',
});

const openCreateModal = () => {
    editMode.value = false;
    form.reset();
    open.value = true;
};

const openEditModal = (election: Election) => {
    editMode.value = true;
    selectedElection.value = election;
    form.title = election.title;
    form.description = election.description;
    form.start_datetime = formatDateTimeForInput(election.start_datetime);
    form.end_datetime = formatDateTimeForInput(election.end_datetime);
    open.value = true;
};

const openDeleteConfirm = (election: Election) => {
    selectedElection.value = election;
    deleteConfirmOpen.value = true;
};

const openActivateConfirm = (election: Election) => {
    selectedElection.value = election;
    activateConfirmOpen.value = true;
};

const openDeactivateConfirm = (election: Election) => {
    selectedElection.value = election;
    deactivateConfirmOpen.value = true;
};

const submitForm = () => {
    console.log('Form submitted', form.data());
    console.log('Edit mode:', editMode.value);
    
    if (editMode.value && selectedElection.value) {
        form.put(`/admin/election/${selectedElection.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
                form.reset();
            },
            onError: (errors) => {
                console.error('Form errors:', errors);
            }
        });
    } else {
        console.log('Posting to: /admin/election');
        form.post('/admin/election', {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Election created successfully');
                open.value = false;
                form.reset();
            },
            onError: (errors) => {
                console.error('Form errors:', errors);
            }
        });
    }
};

const deleteElection = () => {
    if (selectedElection.value) {
        router.delete(`/admin/election/${selectedElection.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                deleteConfirmOpen.value = false;
                selectedElection.value = null;
            }
        });
    }
};

const activateElection = () => {
    if (selectedElection.value) {
        router.post(`/admin/election/${selectedElection.value.id}/activate`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                activateConfirmOpen.value = false;
                selectedElection.value = null;
            }
        });
    }
};

const deactivateElection = () => {
    if (selectedElection.value) {
        router.post(`/admin/election/${selectedElection.value.id}/deactivate`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                deactivateConfirmOpen.value = false;
                selectedElection.value = null;
            }
        });
    }
};

const formatDateTimeForInput = (datetime: string) => {
    if (!datetime) return '';
    const date = new Date(datetime);
    return date.toISOString().slice(0, 16);
};

const formatDateRange = (start: string, end: string) => {
    const startDate = new Date(start);
    const endDate = new Date(end);
    
    const options: Intl.DateTimeFormatOptions = { day: '2-digit', month: 'short', year: 'numeric' };
    return `${startDate.toLocaleDateString('en-US', options)} - ${endDate.toLocaleDateString('en-US', options)}`;
};

const getVoterTurnout = (election: Election) => {
    if (election.total_voters === 0) return 0;
    return ((election.votes_count / election.total_voters) * 100).toFixed(1);
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Election',
        href: '/admin/election',
    },
];
</script>

<template>
    <Head title="Elections" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <ModalTrigger v-model="open">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-semibold text-gray-800 dark:text-gray-100">Elections</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monitor and manage ongoing elections</p>
                    </div>
                    <button 
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-card hover:bg-accent transition-colors text-sm font-medium border rounded-md focus:outline-none">
                        <Icon name="plus" class="h-4 w-4" />
                        New Election
                    </button>
                </div>
            </ModalTrigger>

            <!-- Create/Edit Modal -->
            <Modal v-model="open">
                <h2 class="text-xl font-semibold mb-4">{{ editMode ? 'Edit Election' : 'Create Election' }}</h2>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <Label for="title" class="text-sm font-medium mb-2">Election Title</Label>
                        <Input
                            v-model="form.title"
                            type="text"
                            id="title"
                            placeholder="e.g., ICSA Election 2025"
                            class="w-full"
                            required
                        />
                        <p v-if="form.errors.title" class="text-xs text-red-500 mt-1">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <Label for="description" class="text-sm font-medium mb-2">Description</Label>
                        <textarea
                            v-model="form.description"
                            id="description"
                            rows="3"
                            class="w-full rounded-md border bg-background p-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                            placeholder="Brief description of the election"
                        />
                        <p v-if="form.errors.description" class="text-xs text-red-500 mt-1">{{ form.errors.description }}</p>
                    </div>

                    <div>
                        <Label for="start_datetime" class="text-sm font-medium mb-2">Start Date & Time</Label>
                        <Input
                            v-model="form.start_datetime"
                            type="datetime-local"
                            id="start_datetime"
                            class="w-full"
                            required
                        />
                        <p v-if="form.errors.start_datetime" class="text-xs text-red-500 mt-1">{{ form.errors.start_datetime }}</p>
                    </div>

                    <div>
                        <Label for="end_datetime" class="text-sm font-medium mb-2">End Date & Time</Label>
                        <Input
                            v-model="form.end_datetime"
                            type="datetime-local"
                            id="end_datetime"
                            class="w-full"
                            required
                        />
                        <p v-if="form.errors.end_datetime" class="text-xs text-red-500 mt-1">{{ form.errors.end_datetime }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Button variant="ghost" type="button" @click="open = false">Cancel</Button>
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ form.processing ? 'Saving...' : (editMode ? 'Update Election' : 'Create Election') }}
                        </button>
                    </div>
                </form>
            </Modal>

            <!-- Delete Confirmation Modal -->
            <Modal v-model="deleteConfirmOpen">
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                        <Icon name="trash" class="h-6 w-6 text-red-600 dark:text-red-400" />
                    </div>
                    <h3 class="mt-4 text-lg font-semibold">Delete Election</h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Are you sure you want to delete "{{ selectedElection?.title }}"? This action cannot be undone.
                    </p>
                    <div class="mt-6 flex justify-center gap-3">
                        <Button variant="ghost" @click="deleteConfirmOpen = false">Cancel</Button>
                        <Button variant="default" class="bg-red-500 hover:bg-red-600" @click="deleteElection">
                            Delete
                        </Button>
                    </div>
                </div>
            </Modal>

            <!-- Activate Confirmation Modal -->
            <Modal v-model="activateConfirmOpen">
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                        <Icon name="checkCircle" class="h-6 w-6 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="mt-4 text-lg font-semibold">Activate Election</h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        This will activate "{{ selectedElection?.title }}" and deactivate any other active elections. Voters will be able to cast their votes.
                    </p>
                    <div class="mt-6 flex justify-center gap-3">
                        <Button variant="ghost" @click="activateConfirmOpen = false">Cancel</Button>
                        <Button variant="default" class="bg-green-500 hover:bg-green-600" @click="activateElection">
                            Activate
                        </Button>
                    </div>
                </div>
            </Modal>

            <!-- Deactivate Confirmation Modal -->
            <Modal v-model="deactivateConfirmOpen">
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/20">
                        <Icon name="CircleStop" class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                    </div>
                    <h3 class="mt-4 text-lg font-semibold">End Election</h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        This will end "{{ selectedElection?.title }}". No more votes will be accepted. Are you sure?
                    </p>
                    <div class="mt-6 flex justify-center gap-3">
                        <Button variant="ghost" @click="deactivateConfirmOpen = false">Cancel</Button>
                        <Button variant="default" class="bg-red-500 hover:bg-red-600" @click="deactivateElection">
                            End Election
                        </Button>
                    </div>
                </div>
            </Modal>
        </div>

        <!-- Empty State -->
        <div v-if="electionsList.length === 0" class="flex flex-col items-center justify-center p-12">
            <div class="rounded-full bg-muted p-6 mb-4">
                <Icon name="vote" class="h-12 w-12 text-muted-foreground" />
            </div>
            <h3 class="text-lg font-semibold mb-2">No elections yet</h3>
            <p class="text-sm text-muted-foreground mb-4">Get started by creating your first election</p>
            <Button @click="openCreateModal">
                <Icon name="plus" class="h-4 w-4 mr-2" />
                Create Election
            </Button>
        </div>
          
        <!-- Elections List -->
        <div v-else class="space-y-4 p-4">
            <div
                v-for="election in electionsList"
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
                            : election.status === 'scheduled'
                            ? 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/30'
                            : 'bg-muted text-muted-foreground ring-border'
                            ]">
                            <span class="h-1.5 w-1.5 rounded-full" :class="election.status === 'active' ? 'bg-green-600 dark:bg-green-400 animate-pulse' : election.status === 'scheduled' ? 'bg-blue-600 dark:bg-blue-400' : 'bg-muted-foreground'" />
                                {{ election.status === 'active' ? 'Live' : election.status === 'scheduled' ? 'Scheduled' : 'Ended' }}
                            </span>
                        </div>
                            <p class="text-sm text-muted-foreground mb-4">{{ election.description || 'No description provided' }}</p>
                                    
                        <!-- Progress Bar (only for active elections) -->
                        <div class="mb-4" v-if="election.status === 'active'">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <span class="text-muted-foreground">Voter Turnout</span>
                                <span class="font-medium">{{ getVoterTurnout(election) }}%</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-muted overflow-hidden">
                                <div 
                                    class="h-full rounded-full bg-primary transition-all duration-500"
                                    :style="{ width: `${getVoterTurnout(election)}%` }"
                                />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div class="flex items-center gap-2 text-sm">
                                    <Icon name="calendar" class="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <p class="text-xs text-muted-foreground">Duration</p>
                                        <p class="font-medium text-xs">{{ formatDateRange(election.start_datetime, election.end_datetime) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <Icon name="users" class="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <p class="text-xs text-muted-foreground">Votes</p>
                                        <p class="font-medium">{{ election.votes_count }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <Icon name="briefcase" class="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <p class="text-xs text-muted-foreground">Positions</p>
                                        <p class="font-medium">{{ election.positions_count }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <Icon name="userCheck" class="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <p class="text-xs text-muted-foreground">Candidates</p>
                                        <p class="font-medium">{{ election.candidates_count }}</p>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
                            
                <div class="flex items-center gap-2 pt-4 border-t">
                    <!-- Activate Button (for scheduled elections) -->
                    <button 
                        v-if="election.status === 'scheduled' && !election.is_active"
                        @click="openActivateConfirm(election)"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-green-500 hover:bg-green-600 text-white px-4 py-2 text-sm font-medium transition-colors">
                        <Icon name="play" class="h-4 w-4" />
                        Activate Election
                    </button>
                    
                    <!-- View Results Button -->
                    <button 
                        v-if="election.status !== 'scheduled'"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-colors">
                        <Icon name="barChart" class="h-4 w-4" />
                        View Results
                    </button>

                    <!-- End Election Button (for active elections) -->
                    <button 
                        v-if="election.status === 'active' && election.is_active"
                        @click="openDeactivateConfirm(election)"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border bg-red-500 text-white px-4 py-2 text-sm font-medium hover:bg-red-600 transition-colors">
                        <Icon name="CircleStop" class="h-4 w-4" />
                        End Election
                    </button>

                    <!-- Edit Button (for all elections) -->
                    <button 
                        @click="openEditModal(election)"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border bg-card hover:bg-accent px-4 py-2 text-sm font-medium transition-colors">
                        <Icon name="edit" class="h-4 w-4" />
                        Edit
                    </button>

                    <!-- Delete Button (for all elections) -->
                    <button 
                        @click="openDeleteConfirm(election)"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border bg-card hover:bg-accent px-4 py-2 text-sm font-medium transition-colors text-red-600">
                        <Icon name="trash" class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

