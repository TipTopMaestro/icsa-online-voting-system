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
        if (!grouped[electionTitle]) {
            grouped[electionTitle] = [];
        }
        grouped[electionTitle].push(position);
    });
    
    return grouped;
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Positions',
        href: '/admin/position',
    },
];
</script>

<template>
    <Head title="Positions" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <ModalTrigger v-model="open">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-semibold text-gray-800 dark:text-gray-100">Positions</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage positions for each election</p>
                    </div>
                    <button 
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-purple-800 hover:bg-purple-900 dark:bg-purple-800 dark:hover:bg-purple-900 text-white text-sm font-medium rounded-md transition-colors">
                        <Icon name="plus" class="h-4 w-4" />
                        New Position
                    </button>
                </div>
            </ModalTrigger>

            <!-- Create/Edit Modal -->
            <Modal v-model="open">
                <h2 class="text-xl font-semibold mb-4">{{ editMode ? 'Edit Position' : 'Create Position' }}</h2>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <Label for="election_id" class="text-sm font-medium mb-2">Election</Label>
                        <select
                            v-model="form.election_id"
                            id="election_id"
                            class="w-full rounded-md border bg-background p-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                            required
                        >
                            <option value="" disabled>Select Election</option>
                            <option v-for="election in elections" :key="election.id" :value="election.id">
                                {{ election.title }}
                            </option>
                        </select>
                        <p v-if="form.errors.election_id" class="text-xs text-red-500 mt-1">{{ form.errors.election_id }}</p>
                    </div>

                    <div>
                        <Label for="name" class="text-sm font-medium mb-2">Position Name</Label>
                        <Input
                            v-model="form.name"
                            type="text"
                            id="name"
                            placeholder="e.g., President, Vice President"
                            class="w-full"
                            required
                        />
                        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <Label for="max_selection" class="text-sm font-medium mb-2">Maximum Selection</Label>
                        <Input
                            v-model.number="form.max_selection"
                            type="number"
                            id="max_selection"
                            min="1"
                            placeholder="Number of candidates voters can select"
                            class="w-full"
                            required
                        />
                        <p class="text-xs text-muted-foreground mt-1">Number of candidates a voter can select for this position</p>
                        <p v-if="form.errors.max_selection" class="text-xs text-red-500 mt-1">{{ form.errors.max_selection }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Button variant="ghost" type="button" @click="open = false">Cancel</Button>
                        <Button variant="default" type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : (editMode ? 'Update Position' : 'Create Position') }}
                        </Button>
                    </div>
                </form>
            </Modal>

            <!-- Delete Confirmation Modal -->
            <Modal v-model="deleteConfirmOpen">
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                        <Icon name="trash" class="h-6 w-6 text-red-600 dark:text-red-400" />
                    </div>
                    <h3 class="mt-4 text-lg font-semibold">Delete Position</h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Are you sure you want to delete "{{ selectedPosition?.name }}"? This action cannot be undone.
                    </p>
                    <div class="mt-6 flex justify-center gap-3">
                        <Button variant="ghost" @click="deleteConfirmOpen = false">Cancel</Button>
                        <Button variant="default" class="bg-red-500 hover:bg-red-600" @click="deletePosition">
                            Delete
                        </Button>
                    </div>
                </div>
            </Modal>
        </div>

        <!-- Empty State -->
        <div v-if="positions.length === 0" class="flex flex-col items-center justify-center p-12">
            <div class="rounded-full bg-muted p-6 mb-4">
                <Icon name="briefcase" class="h-12 w-12 text-muted-foreground" />
            </div>
            <h3 class="text-lg font-semibold mb-2">No positions yet</h3>
            <p class="text-sm text-muted-foreground mb-4">Get started by creating your first position</p>
            <Button @click="openCreateModal">
                <Icon name="plus" class="h-4 w-4 mr-2" />
                Create Position
            </Button>
        </div>

        <!-- Positions List -->
        <div v-else class="space-y-6 p-6">
            <div v-for="(positions, electionTitle) in positionsByElection" :key="electionTitle" class="space-y-4">
                <!-- Election Header -->
                <div class="flex items-center gap-3 pb-2 border-b">
                    <Icon name="vote" class="h-5 w-5 text-primary" />
                    <h2 class="text-lg font-semibold">{{ electionTitle }}</h2>
                    <span class="text-xs text-muted-foreground">{{ positions.length }} position{{ positions.length !== 1 ? 's' : '' }}</span>
                </div>

                <!-- Position Cards -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="position in positions"
                        :key="position.id"
                        class="group rounded-xl border bg-card p-5 transition-all duration-300 hover:shadow-lg hover:border-primary/50"
                    >
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <Icon name="briefcase" class="h-4 w-4 text-primary" />
                                    <h3 class="font-semibold text-base">{{ position.name }}</h3>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    {{ position.election?.title }}
                                </p>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button
                                    @click="openEditModal(position)"
                                    class="p-1.5 rounded-md hover:bg-accent transition-colors"
                                    title="Edit"
                                >
                                    <Icon name="edit" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                </button>
                                <button
                                    @click="openDeleteConfirm(position)"
                                    class="p-1.5 rounded-md hover:bg-accent transition-colors"
                                    title="Delete"
                                >
                                    <Icon name="trash" class="h-4 w-4 text-red-600 dark:text-red-400" />
                                </button>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-muted/50">
                                <div class="flex items-center gap-2">
                                    <Icon name="users" class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-xs text-muted-foreground">Max Selection</span>
                                </div>
                                <span class="text-sm font-semibold">{{ position.max_selection }}</span>
                            </div>

                            <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-muted/50">
                                <div class="flex items-center gap-2">
                                    <Icon name="userCheck" class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-xs text-muted-foreground">Candidates</span>
                                </div>
                                <span class="text-sm font-semibold">{{ position.candidates_count || 0 }}</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t">
                            <div class="flex items-center gap-2">
                                <span 
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset',
                                        position.election?.is_active
                                            ? 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/30'
                                            : 'bg-muted text-muted-foreground ring-border'
                                    ]"
                                >
                                    <span 
                                        class="h-1.5 w-1.5 rounded-full" 
                                        :class="position.election?.is_active ? 'bg-green-600 dark:bg-green-400 animate-pulse' : 'bg-muted-foreground'" 
                                    />
                                    {{ position.election?.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
