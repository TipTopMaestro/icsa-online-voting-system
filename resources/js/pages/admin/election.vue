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
import { Network, Plus, Calendar, Users, Briefcase, UserCheck, Trash, PencilLine, CheckCircle, CircleStop, Clock, History, AlertTriangle } from 'lucide-vue-next';

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
    voted_count: number; // Unique voters who voted
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
    if (editMode.value && selectedElection.value) {
        form.put(`/admin/election/${selectedElection.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/admin/election', {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
                form.reset();
            },
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
            preserveState: false,
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
            preserveState: false,
            onSuccess: () => {
                deactivateConfirmOpen.value = false;
                selectedElection.value = null;
            }
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Elections',
        href: '/admin/election',
    },
];

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const formatDateTimeForInput = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().slice(0, 16);
};

const formatDateRange = (start: string, end: string) => {
    const s = new Date(start);
    const e = new Date(end);
    return `${s.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${e.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
};

const getVoterTurnout = (election: Election) => {
    if (election.total_voters === 0) return 0;
    return ((election.voted_count / election.total_voters) * 100).toFixed(1);
};

const getStatusBadge = (election: Election) => {
    if (election.is_active) {
        return { label: 'Live', class: 'bg-green-50 text-green-700 border-green-100 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20', pulse: true };
    } else if (election.status === 'ended') {
        return { label: 'Ended', class: 'bg-gray-50 text-gray-500 border-gray-100 dark:bg-muted dark:text-gray-400 dark:border-border', pulse: false };
    } else {
        return { label: 'Scheduled', class: 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20', pulse: false };
    }
};
</script>

<template>
    <Head title="Election Systems" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 md:gap-8 p-4 md:p-8 min-h-[calc(100vh-64px)]">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Electoral Systems</h1>
                    <p class="text-muted-foreground mt-1 text-[11px] md:text-sm font-medium">Configure and monitor democratic voting cycles.</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="flex items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest bg-primary text-primary-foreground hover:bg-primary/90 transition-all active:scale-95"
                >
                    <Plus class="h-4 w-4" />
                    New Election
                </button>
            </div>

            <!-- Empty State -->
            <div v-if="electionsList.length === 0" class="flex flex-col items-center justify-center p-12 md:p-20 border-2 border-dashed border-gray-100 dark:border-border rounded-3xl bg-white dark:bg-card/50 shadow-sm max-w-2xl mx-auto w-full">
                <div class="rounded-2xl bg-gray-50 dark:bg-muted/50 p-6 mb-6">
                    <Network class="h-12 w-12 text-gray-300" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-foreground">Initialize System</h3>
                <p class="text-sm text-gray-500 mb-8 text-center max-w-xs font-medium">Create your first election system to begin the nomination and voting process.</p>
                <button @click="openCreateModal" class="px-8 py-3 bg-primary text-primary-foreground rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-primary/90 transition-all">Create Election Now</button>
            </div>
            
            <!-- Elections List -->
            <div v-else class="grid grid-cols-1 gap-6">
                <div
                    v-for="election in electionsList"
                    :key="election.id"
                    :class="[
                        'group rounded-3xl border transition-all duration-300 bg-white dark:bg-card p-6 md:p-8 shadow-sm relative overflow-hidden',
                        election.is_active ? 'border-primary/20 ring-4 ring-primary/5' : 'border-gray-100 dark:border-border hover:border-primary/20'
                    ]"
                >
                    <!-- Live Indicator Bar -->
                    <div v-if="election.is_active" class="absolute top-0 left-0 w-full h-1.5 bg-primary"></div>

                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6 mb-8">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-3 mb-2">
                                <h2 class="text-lg md:text-2xl font-black text-gray-900 dark:text-foreground leading-tight uppercase tracking-tight">{{ election.title }}</h2>
                                <span 
                                    :class="getStatusBadge(election).class"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border"
                                >
                                    <span v-if="getStatusBadge(election).pulse" class="h-1.5 w-1.5 rounded-full bg-current" />
                                    {{ getStatusBadge(election).label }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-muted-foreground font-medium leading-relaxed max-w-3xl">{{ election.description || 'No system description provided.' }}</p>
                        </div>

                        <!-- Main Actions -->
                        <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                            <div v-if="!election.is_active && election.status !== 'ended'" class="flex gap-2 w-full sm:w-auto">
                                <button @click="openActivateConfirm(election)" class="flex-1 sm:flex-none px-4 py-2 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-700 transition-all active:scale-95">Activate Polls</button>
                                <button @click="openEditModal(election)" class="flex-1 sm:flex-none p-2 bg-gray-50 dark:bg-muted text-blue-600 rounded-xl hover:bg-blue-50 transition-all border dark:border-border"><PencilLine class="w-5 h-5" /></button>
                                <button @click="openDeleteConfirm(election)" class="flex-1 sm:flex-none p-2 bg-gray-50 dark:bg-muted text-red-600 rounded-xl hover:bg-red-50 transition-all border dark:border-border"><Trash class="w-5 h-5" /></button>
                            </div>
                            <div v-else-if="election.is_active" class="w-full sm:w-auto">
                                <button @click="openDeactivateConfirm(election)" class="w-full sm:w-auto px-6 py-2.5 bg-red-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-700 transition-all active:scale-95">Force End Election</button>
                            </div>
                            <div v-else class="w-full sm:w-auto">
                                <button @click="openDeleteConfirm(election)" class="w-full sm:w-auto px-6 py-2.5 bg-gray-100 dark:bg-muted text-gray-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:text-red-600 transition-all">Archive Data</button>
                            </div>
                        </div>
                    </div>

                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-muted/30 border dark:border-border group-hover:bg-primary/[0.02] transition-colors">
                            <div class="flex items-center gap-2 text-gray-400 mb-1">
                                <Users class="w-3.5 h-3.5" />
                                <span class="text-[9px] font-black uppercase tracking-widest">Turnout</span>
                            </div>
                            <div class="flex items-end gap-2">
                                <p class="text-xl font-black text-gray-900 dark:text-foreground leading-none">{{ getVoterTurnout(election) }}%</p>
                                <span class="text-[10px] text-gray-400 font-bold mb-0.5">{{ election.voted_count }}/{{ election.total_voters }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-muted h-1 rounded-full mt-3 overflow-hidden">
                                <div class="bg-primary h-full transition-all duration-1000" :style="{ width: getVoterTurnout(election) + '%' }"></div>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-muted/30 border dark:border-border">
                            <div class="flex items-center gap-2 text-gray-400 mb-1">
                                <CheckCircle class="w-3.5 h-3.5" />
                                <span class="text-[9px] font-black uppercase tracking-widest">Valid Votes</span>
                            </div>
                            <p class="text-xl font-black text-gray-900 dark:text-foreground leading-none">{{ election.votes_count.toLocaleString() }}</p>
                            <p class="text-[9px] text-primary font-bold mt-2 uppercase tracking-widest">Cast Ballots</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-muted/30 border dark:border-border">
                            <div class="flex items-center gap-2 text-gray-400 mb-1">
                                <Briefcase class="w-3.5 h-3.5" />
                                <span class="text-[9px] font-black uppercase tracking-widest">Positions</span>
                            </div>
                            <p class="text-xl font-black text-gray-900 dark:text-foreground leading-none">{{ election.positions_count }}</p>
                            <p class="text-[9px] text-gray-400 font-bold mt-2 uppercase tracking-widest">Defined Roles</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-muted/30 border dark:border-border">
                            <div class="flex items-center gap-2 text-gray-400 mb-1">
                                <UserCheck class="w-3.5 h-3.5" />
                                <span class="text-[9px] font-black uppercase tracking-widest">Candidates</span>
                            </div>
                            <p class="text-xl font-black text-gray-900 dark:text-foreground leading-none">{{ election.candidates_count }}</p>
                            <p class="text-[9px] text-gray-400 font-bold mt-2 uppercase tracking-widest">Approved Nominees</p>
                        </div>
                    </div>

                    <!-- Footer Meta -->
                    <div class="mt-6 pt-6 border-t dark:border-border flex flex-wrap items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <div class="flex items-center gap-1.5 whitespace-normal sm:whitespace-nowrap">
                                <Calendar class="w-3.5 h-3.5 flex-shrink-0" />
                                <span>Cycle: {{ formatDateRange(election.start_datetime, election.end_datetime) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 whitespace-normal sm:whitespace-nowrap">
                                <Clock class="w-3.5 h-3.5 flex-shrink-0" />
                                <span>Created: {{ formatDate(election.created_at) }}</span>
                            </div>
                        </div>
                        <Link :href="`/admin/result?election_id=${election.id}`" class="text-[10px] font-black text-primary uppercase tracking-widest hover:underline flex items-center gap-1.5">
                            Real-time Analytics
                            <Icon name="arrowRight" class="w-3.5 h-3.5" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Modals -->
            <!-- Create/Edit Modal -->
            <Modal v-model="open">
                <div class="p-4 md:p-2">
                    <h2 class="text-2xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight mb-8">{{ editMode ? 'Modify Election' : 'Create Election' }}</h2>

                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="space-y-1.5">
                            <Label for="title" class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Election Title</Label>
                            <Input v-model="form.title" type="text" id="title" placeholder="e.g., ICSA Annual Elections 2025" class="h-12 rounded-2xl border-2 border-gray-100 dark:border-border focus:ring-2 focus:ring-primary/20 transition-all font-bold" required />
                            <p v-if="form.errors.title" class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ form.errors.title }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="description" class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Description</Label>
                            <textarea v-model="form.description" id="description" rows="3" class="w-full rounded-2xl border-2 border-gray-100 dark:border-border bg-white dark:bg-background p-4 text-sm font-medium focus:ring-2 focus:ring-primary/20 transition-all resize-none" placeholder="Provide context for voters and candidates..." />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                            <div class="space-y-1.5 min-w-0">
                                <Label for="start_datetime" class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 block">Opening</Label>
                                <Input 
                                    v-model="form.start_datetime" 
                                    type="datetime-local" 
                                    id="start_datetime" 
                                    class="h-12 px-3 rounded-2xl border-2 border-gray-100 dark:border-border font-semibold text-sm w-full block overflow-hidden" 
                                    required 
                                />
                            </div>
                            <div class="space-y-1.5 min-w-0">
                                <Label for="end_datetime" class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 block">Closing</Label>
                                <Input 
                                    v-model="form.end_datetime" 
                                    type="datetime-local" 
                                    id="end_datetime" 
                                    class="h-12 px-3 rounded-2xl border-2 border-gray-100 dark:border-border font-semibold text-sm w-full block overflow-hidden" 
                                    required 
                                />
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-8 border-t dark:border-border">
                            <button @click="open = false" type="button" class="h-12 px-6 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-50 transition-all order-2 sm:order-1">Discard</button>
                            <button type="submit" :disabled="form.processing" class="h-12 px-10 rounded-2xl bg-primary text-primary-foreground font-black text-[10px] uppercase tracking-widest hover:bg-primary/90 transition-all disabled:opacity-50 order-1 sm:order-2">
                                {{ form.processing ? 'Syncing...' : (editMode ? 'Save' : 'Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </Modal>

            <!-- Delete Confirmation -->
            <Modal v-model="deleteConfirmOpen">
                <div class="p-8 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/20 mb-6">
                        <Trash class="h-10 w-10 text-red-600" />
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight mb-2">Delete Election?</h3>
                    <p class="text-sm text-gray-500 font-medium mb-8">This will permanently remove "{{ selectedElection?.title }}" and all associated ballots. This action is terminal.</p>
                    <div class="flex flex-col gap-3">
                        <button @click="deleteElection" class="h-14 w-full bg-red-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-red-700 transition-all">TERMINATE ELECTION</button>
                        <button @click="deleteConfirmOpen = false" class="h-10 w-full text-[10px] font-black text-gray-400 uppercase tracking-widest">Abort Action</button>
                    </div>
                </div>
            </Modal>

            <!-- Status Action Modals (Activate/End) -->
            <Modal v-model="activateConfirmOpen">
                <div class="p-8 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/20 mb-6 text-emerald-600">
                        <CheckCircle class="h-10 w-10" />
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight mb-2">Go Live?</h3>
                    <p class="text-sm text-gray-500 font-medium mb-8">This will activate the ballots for "{{ selectedElection?.title }}". Only one system can be active at a time.</p>
                    <div class="flex flex-col gap-3">
                        <button @click="activateElection" class="h-14 w-full bg-emerald-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-emerald-700 transition-all">INITIALIZE POLLS</button>
                        <button @click="activateConfirmOpen = false" class="h-10 w-full text-[10px] font-black text-gray-400 uppercase tracking-widest">Keep Pending</button>
                    </div>
                </div>
            </Modal>

            <Modal v-model="deactivateConfirmOpen">
                <div class="p-8 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-orange-50 dark:bg-orange-900/10 border border-orange-100 dark:border-orange-900/20 mb-6 text-orange-600">
                        <CircleStop class="h-10 w-10" />
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight mb-2">Terminate Polls?</h3>
                    <p class="text-sm text-gray-500 font-medium mb-8">This will force-stop all voting for "{{ selectedElection?.title }}". Results will be finalized and archived.</p>
                    <div class="flex flex-col gap-3">
                        <button @click="deactivateElection" class="h-14 w-full bg-red-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-red-700 transition-all">END ELECTION NOW</button>
                        <button @click="deactivateConfirmOpen = false" class="h-10 w-full text-[10px] font-black text-gray-400 uppercase tracking-widest">Continue Voting</button>
                    </div>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template>

<style scoped>
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
</style>
