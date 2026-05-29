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
import { Briefcase, Plus, Users, UserCheck, Trash, PencilLine, CheckCircle, Network, X, LayoutGrid } from 'lucide-vue-next';

interface Position {
    id: number;
    name: string;
    max_selection: number;
    election_id: number;
    election?: {
        id: number;
        title: string;
        description: string;
        is_active: boolean;
    };
    candidates_count?: number;
    created_at: string;
}

interface Election {
    id: number;
    title: string;
    description: string;
    is_active: boolean;
}

const props = defineProps<{
    positions: Position[];
    elections: Election[];
}>();

const open = ref(false);
const editMode = ref(false);
const deleteConfirmOpen = ref(false);
const selectedPosition = ref<Position | null>(null);

const form = useForm({
    election_id: '',
    name: '',
    max_selection: 1,
});

const openCreateModal = () => {
    editMode.value = false;
    form.reset();
    open.value = true;
};

const openEditModal = (position: Position) => {
    editMode.value = true;
    selectedPosition.value = position;
    form.election_id = position.election_id.toString();
    form.name = position.name;
    form.max_selection = position.max_selection;
    open.value = true;
};

const openDeleteConfirm = (position: Position) => {
    selectedPosition.value = position;
    deleteConfirmOpen.value = true;
};

const submitForm = () => {
    if (editMode.value && selectedPosition.value) {
        form.put(`/admin/position/${selectedPosition.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
                form.reset();
            }
        });
    } else {
        form.post('/admin/position', {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
                form.reset();
            }
        });
    }
};

const deletePosition = () => {
    if (selectedPosition.value) {
        router.delete(`/admin/position/${selectedPosition.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                deleteConfirmOpen.value = false;
                selectedPosition.value = null;
            }
        });
    }
};

const positionsByElection = computed(() => {
    const grouped: Record<string, Position[]> = {};
    props.positions.forEach(position => {
        const electionTitle = position.election?.title || 'No Election';
        if (!grouped[electionTitle]) grouped[electionTitle] = [];
        grouped[electionTitle].push(position);
    });
    return grouped;
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Positions', href: '/admin/position' },
];
</script>

<template>
    <Head title="Position Management" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 md:gap-8 p-4 md:p-8 min-h-[calc(100vh-64px)]">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Electoral Roles</h1>
                    <p class="text-muted-foreground mt-1 text-[11px] md:text-sm font-medium">Define and manage official positions for each system.</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="flex items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest bg-primary text-primary-foreground hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all active:scale-95"
                >
                    <Plus class="h-4 w-4" />
                    Create Position
                </button>
            </div>

            <!-- Empty State -->
            <div v-if="positions.length === 0" class="flex flex-col items-center justify-center p-12 md:p-20 border-2 border-dashed border-gray-100 dark:border-border rounded-3xl bg-white dark:bg-card/50 shadow-sm max-w-2xl mx-auto w-full">
                <div class="rounded-2xl bg-gray-50 dark:bg-muted/50 p-6 mb-6">
                    <Briefcase class="h-12 w-12 text-gray-300" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-foreground">Initialize Roles</h3>
                <p class="text-sm text-gray-500 mb-8 text-center max-w-xs font-medium">No positions have been defined yet. Start by defining roles for your active elections.</p>
                <button @click="openCreateModal" class="px-8 py-3 bg-primary text-primary-foreground rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">Define First Role</button>
            </div>

            <!-- Positions Grouped by Election -->
            <div v-else class="space-y-12">
                <div v-for="(group, electionTitle) in positionsByElection" :key="electionTitle" class="space-y-6">
                    <!-- Election Header -->
                    <div class="flex items-center gap-3 pb-3 border-b-2 border-gray-100 dark:border-border">
                        <div class="p-2 rounded-lg bg-primary/5 text-primary">
                            <Network class="h-5 w-5" />
                        </div>
                        <div class="flex flex-col">
                            <h2 class="text-sm md:text-lg font-black text-gray-900 dark:text-foreground uppercase tracking-tight">{{ electionTitle }}</h2>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ group.length }} Positions Active</p>
                        </div>
                    </div>

                    <!-- Position Cards Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div
                            v-for="position in group"
                            :key="position.id"
                            class="group rounded-3xl border border-gray-100 dark:border-border p-6 transition-all duration-300 bg-white dark:bg-card hover:shadow-lg relative overflow-hidden"
                        >
                            <div class="relative z-10">
                                <div class="flex items-start justify-between mb-6">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base md:text-lg font-black text-gray-900 dark:text-foreground leading-tight uppercase tracking-tight truncate group-hover:text-primary transition-colors">{{ position.name }}</h3>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">ID: {{ position.id }}</p>
                                    </div>
                                    <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-all">
                                        <button @click="openEditModal(position)" class="p-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors"><PencilLine class="w-4 h-4" /></button>
                                        <button @click="openDeleteConfirm(position)" class="p-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors"><Trash class="w-4 h-4" /></button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3 mb-6">
                                    <div class="p-3 rounded-2xl bg-gray-50 dark:bg-muted/30 border dark:border-border">
                                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Max Select</p>
                                        <div class="flex items-center gap-1.5">
                                            <Users class="w-3 h-3 text-primary" />
                                            <p class="text-sm font-black text-gray-900 dark:text-foreground">{{ position.max_selection }}</p>
                                        </div>
                                    </div>
                                    <div class="p-3 rounded-2xl bg-gray-50 dark:bg-muted/30 border dark:border-border">
                                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">Runners</p>
                                        <div class="flex items-center gap-1.5">
                                            <UserCheck class="w-3 h-3 text-accent" />
                                            <p class="text-sm font-black text-gray-900 dark:text-foreground">{{ position.candidates_count || 0 }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-4 border-t dark:border-border">
                                    <span 
                                        :class="[
                                            'inline-flex items-center gap-1.5 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest rounded-full border',
                                            position.election?.is_active
                                                ? 'bg-green-50 text-green-700 border-green-100 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20'
                                                : 'bg-gray-50 text-gray-500 border-gray-100 dark:bg-muted dark:text-gray-400 dark:border-border'
                                        ]"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full" :class="position.election?.is_active ? 'bg-current animate-pulse' : 'bg-current'" />
                                        {{ position.election?.is_active ? 'Live' : 'Archived' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modals -->
            <!-- Create/Edit Modal -->
            <Modal v-model="open">
                <div class="p-6 md:p-8">
                    <h2 class="text-2xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight mb-8">{{ editMode ? 'Modify Position' : 'Create Position' }}</h2>

                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="space-y-1.5">
                            <Label for="election_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Election</Label>
                            <select v-model="form.election_id" id="election_id" class="w-full h-12 px-4 rounded-2xl border-2 border-gray-100 dark:border-border bg-white dark:bg-background text-sm font-bold appearance-none focus:ring-2 focus:ring-primary/20 transition-all" required>
                                <option value="" disabled>Select Election</option>
                                <option v-for="e in elections" :key="e.id" :value="e.id">{{ e.title }}</option>
                            </select>
                            <p v-if="form.errors.election_id" class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ form.errors.election_id }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="name" class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Position Name</Label>
                            <Input v-model="form.name" type="text" id="name" placeholder="e.g., Executive President" class="h-12 rounded-2xl border-2 border-gray-100 dark:border-border focus:ring-2 focus:ring-primary/20 transition-all font-bold" required />
                            <p v-if="form.errors.name" class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="max_selection" class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Selection Cap</Label>
                            <Input v-model.number="form.max_selection" type="number" id="max_selection" min="1" class="h-12 rounded-2xl border-2 border-gray-100 dark:border-border font-bold" required />
                            <p class="text-[9px] text-gray-400 font-medium ml-1">Define the number of candidates a single voter can choose for this role.</p>
                            <p v-if="form.errors.max_selection" class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ form.errors.max_selection }}</p>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-8 border-t dark:border-border">
                            <button @click="open = false" type="button" class="h-12 px-6 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-50 transition-all order-2 sm:order-1">Discard</button>
                            <button type="submit" :disabled="form.processing" class="h-12 px-10 rounded-2xl bg-primary text-primary-foreground font-black text-[10px] uppercase tracking-widest shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all disabled:opacity-50 order-1 sm:order-2">
                                {{ form.processing ? 'Syncing...' : (editMode ? 'Update Position' : 'Save Role') }}
                            </button>
                        </div>
                    </form>
                </div>
            </Modal>

            <!-- Delete Modal -->
            <Modal v-model="deleteConfirmOpen">
                <div class="p-8 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/20 mb-6">
                        <Trash class="h-10 w-10 text-red-600" />
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight mb-2">Delete Role?</h3>
                    <p class="text-sm text-gray-500 font-medium mb-8">This will permanently delete "{{ selectedPosition?.name }}" from the election. This cannot be undone.</p>
                    <div class="flex flex-col gap-3">
                        <button @click="deletePosition" class="h-14 w-full bg-red-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-red-100 hover:bg-red-700 transition-all">TERMINATE ROLE</button>
                        <button @click="deleteConfirmOpen = false" class="h-10 w-full text-[10px] font-black text-gray-400 uppercase tracking-widest">Keep Role</button>
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
