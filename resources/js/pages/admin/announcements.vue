<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import Icon from '@/components/Icon.vue';
import Modal from '@/components/Modal.vue';
import { Bell, BellOff, CheckCircle2, MoreHorizontal, X, Clock, Inbox, Plus, PencilLine, Trash, Eye, Megaphone, Send, Globe, Users, User, ArrowUpRight, ChevronDown } from 'lucide-vue-next';

// TypeScript Interfaces
interface User {
    id: number;
    name: string;
    email: string;
}

interface Announcement {
    id: number;
    title: string;
    content: string;
    audience: 'all' | 'voters' | 'candidates';
    is_published: boolean;
    published_at: string | null;
    created_by: number;
    created_at: string;
    updated_at: string;
    creator_name?: string;
}

// Props
const props = defineProps<{
    announcements: Announcement[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Announcements', href: '/admin/announcements' },
];

// UI State
const showModal = ref(false);
const editMode = ref(false);
const selectedAnnouncement = ref<Announcement | null>(null);
const activeFilter = ref<'all' | 'published' | 'draft'>('all');
const sortOrder = ref<'newest' | 'oldest'>('newest');
const showDeleteModal = ref(false);
const showViewModal = ref(false);

// Dropdown state for styled selects
const sortDropdownOpen = ref(false);
const audienceDropdownOpen = ref(false);

function toggleSortDropdown() {
    sortDropdownOpen.value = !sortDropdownOpen.value;
    audienceDropdownOpen.value = false;
}

function toggleAudienceDropdown() {
    audienceDropdownOpen.value = !audienceDropdownOpen.value;
    sortDropdownOpen.value = false;
}

const sortOptions: { label: string; value: 'newest' | 'oldest' }[] = [
    { label: 'Newest First', value: 'newest' },
    { label: 'Oldest First', value: 'oldest' },
];

const audienceOptions: { label: string; value: 'all' | 'voters' | 'candidates' }[] = [
    { label: 'Global Audience', value: 'all' },
    { label: 'Voters Only', value: 'voters' },
    { label: 'Candidates Only', value: 'candidates' },
];

function selectSort(opt: { label: string; value: 'newest' | 'oldest' }) {
    sortOrder.value = opt.value;
    sortDropdownOpen.value = false;
}

function selectAudience(opt: { label: string; value: 'all' | 'voters' | 'candidates' }) {
    form.audience = opt.value;
    audienceDropdownOpen.value = false;
}

// Form
const form = useForm({
    title: '',
    content: '',
    audience: 'all' as 'all' | 'voters' | 'candidates',
    is_published: false,
});

// Computed
const filtered = computed(() => {
    let list = props.announcements;
    if (activeFilter.value === 'published') list = list.filter(a => a.is_published);
    else if (activeFilter.value === 'draft') list = list.filter(a => !a.is_published);
    
    return list.slice().sort((a, b) => {
        const dateA = new Date(a.created_at).getTime();
        const dateB = new Date(b.created_at).getTime();
        return sortOrder.value === 'newest' ? dateB - dateA : dateA - dateB;
    });
});

const publishedCount = computed(() => props.announcements.filter(a => a.is_published).length);
const draftCount = computed(() => props.announcements.filter(a => !a.is_published).length);

// Methods
function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

function openCreateModal() {
    editMode.value = false;
    form.reset();
    form.is_published = false;
    showModal.value = true;
}

function openEditModal(announcement: Announcement) {
    if (announcement.is_published) return;
    editMode.value = true;
    selectedAnnouncement.value = announcement;
    form.title = announcement.title;
    form.content = announcement.content;
    form.audience = announcement.audience;
    form.is_published = announcement.is_published;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.reset();
    selectedAnnouncement.value = null;
}

function saveAnnouncement() {
    if (editMode.value && selectedAnnouncement.value) {
        form.put(`/admin/announcements/${selectedAnnouncement.value.id}`, { onSuccess: () => closeModal() });
    } else {
        form.post('/admin/announcements', { onSuccess: () => closeModal() });
    }
}

function saveDraft() {
    form.is_published = false;
    saveAnnouncement();
}

function saveAndPublish() {
    form.is_published = true;
    saveAnnouncement();
}

function togglePublish(announcement: Announcement) {
    const action = announcement.is_published ? 'unpublish' : 'publish';
    router.post(`/admin/announcements/${announcement.id}/${action}`, {}, {
        preserveScroll: true,
        onSuccess: () => { if (showViewModal.value) closeViewModal(); }
    });
}

function openDeleteModal(announcement: Announcement) {
    selectedAnnouncement.value = announcement;
    showDeleteModal.value = true;
}

function closeDeleteModal() {
    showDeleteModal.value = false;
    selectedAnnouncement.value = null;
}

function openViewModal(announcement: Announcement) {
    selectedAnnouncement.value = announcement;
    showViewModal.value = true;
}

function closeViewModal() {
    showViewModal.value = false;
    selectedAnnouncement.value = null;
}

function confirmDelete() {
    if (!selectedAnnouncement.value) return;
    router.delete(`/admin/announcements/${selectedAnnouncement.value.id}`, { onSuccess: () => closeDeleteModal() });
}

function getAudienceBadge(audience: string) {
    const map = {
        all: { label: 'Global', icon: Globe, class: 'bg-blue-50 text-blue-600 border-blue-100' },
        voters: { label: 'Voters', icon: Users, class: 'bg-emerald-50 text-emerald-600 border-emerald-100' },
        candidates: { label: 'Candidates', icon: User, class: 'bg-accent/5 text-accent border-accent/10' }
    };
    return map[audience as keyof typeof map] || map.all;
}
</script>

<template>
    <Head title="Press & Announcements" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 md:gap-8 p-4 md:p-8 min-h-[calc(100vh-64px)]">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Press Center</h1>
                    <p class="text-muted-foreground mt-1 text-[11px] md:text-sm font-medium">Broadcast critical updates and election guidelines.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative">
                        <button @click="toggleSortDropdown" class="h-11 px-4 rounded-xl border-2 border-gray-100 dark:border-border bg-white dark:bg-card text-[10px] font-black uppercase tracking-widest flex items-center gap-3 dark:text-foreground hover:border-primary/50 transition-all shadow-sm">
                            <Clock class="w-3.5 h-3.5 text-primary" />
                            <span>{{ sortOptions.find(o => o.value === sortOrder)?.label }}</span>
                            <ChevronDown class="w-3.5 h-3.5 opacity-50" :class="{ 'rotate-180': sortDropdownOpen }" />
                        </button>
                        <div v-if="sortDropdownOpen" class="absolute z-50 mt-2 right-0 w-48 bg-white dark:bg-purple-900 border-2 border-slate-100 dark:border-purple-600 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200" @click.outside="sortDropdownOpen = false">
                            <div class="py-1">
                                <div v-for="opt in sortOptions" :key="opt.value" @click="selectSort(opt)" class="px-4 py-3 cursor-pointer hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors text-[10px] font-black uppercase tracking-widest" :class="sortOrder === opt.value ? 'text-primary bg-primary/5' : 'text-gray-700 dark:text-purple-100'">{{ opt.label }}</div>
                            </div>
                        </div>
                    </div>

                    <button @click="openCreateModal" class="flex items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-[10px] font-black uppercase tracking-widest bg-primary text-primary-foreground hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all active:scale-95">
                        <Plus class="h-4 w-4" />
                        Compose
                    </button>
                </div>
            </div>

            <!-- Dashboard Filters -->
            <div class="flex flex-wrap gap-2 p-1.5 bg-white dark:bg-card border border-gray-100 dark:border-border rounded-2xl shadow-sm w-fit">
                <button v-for="filter in ['all', 'published', 'draft']" :key="filter" @click="activeFilter = filter as any" :class="[
                    'px-5 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all',
                    activeFilter === filter ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50'
                ]">
                    {{ filter }} ({{ filter === 'all' ? announcements.length : filter === 'published' ? publishedCount : draftCount }})
                </button>
            </div>

            <!-- Grid Layout -->
            <div v-if="filtered.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                <article v-for="a in filtered" :key="a.id" class="group relative flex flex-col bg-white dark:bg-card rounded-3xl border border-gray-100 dark:border-border p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all">
                    <!-- Status Header -->
                    <div class="flex items-center justify-between mb-6">
                        <span :class="[
                            'inline-flex items-center gap-1.5 px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-full border',
                            a.is_published ? 'bg-green-50 text-green-700 border-green-100' : 'bg-gray-50 text-gray-500 border-gray-100'
                        ]">
                            <div class="w-1 h-1 rounded-full bg-current" :class="{ 'animate-pulse': a.is_published }"></div>
                            {{ a.is_published ? 'Live On Feed' : 'Draft Copy' }}
                        </span>
                        
                        <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="openViewModal(a)" class="p-2 bg-gray-50 dark:bg-muted text-gray-400 rounded-xl hover:text-primary transition-colors"><Eye class="w-4 h-4" /></button>
                            <button v-if="!a.is_published" @click="openEditModal(a)" class="p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 transition-colors"><PencilLine class="w-4 h-4" /></button>
                            <button @click="openDeleteModal(a)" class="p-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-colors"><Trash class="w-4 h-4" /></button>
                        </div>
                    </div>

                    <div class="flex-1 min-w-0 mb-6">
                        <h2 class="text-base md:text-lg font-black text-gray-900 dark:text-foreground leading-tight uppercase tracking-tight line-clamp-2 group-hover:text-primary transition-colors">{{ a.title }}</h2>
                        <p class="text-sm text-gray-500 dark:text-muted-foreground mt-4 line-clamp-3 leading-relaxed font-medium">{{ a.content }}</p>
                    </div>

                    <!-- Footer Meta -->
                    <div class="pt-6 border-t dark:border-border mt-auto flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-muted flex items-center justify-center">
                                <component :is="getAudienceBadge(a.audience).icon" class="w-3.5 h-3.5 text-gray-400" />
                            </div>
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ getAudienceBadge(a.audience).label }}</span>
                        </div>
                        <div class="text-right">
                            <p class="text-[8px] font-black text-gray-300 uppercase tracking-widest leading-none">Released</p>
                            <p class="text-[10px] font-bold text-gray-400 uppercase mt-1">{{ formatDate(a.created_at) }}</p>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center p-12 md:p-20 border-2 border-dashed border-gray-100 dark:border-border rounded-3xl bg-white dark:bg-card/50 shadow-sm max-w-2xl mx-auto w-full">
                <div class="rounded-2xl bg-gray-50 dark:bg-muted/50 p-6 mb-6">
                    <Megaphone class="h-12 w-12 text-gray-300" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-foreground">Press Feed Offline</h3>
                <p class="text-sm text-gray-500 mb-8 text-center max-w-xs font-medium">No announcements found matching the current criteria. Broadcast your first update to the association.</p>
                <button @click="openCreateModal" class="px-8 py-3 bg-primary text-primary-foreground rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">Compose Now</button>
            </div>
        </div>

        <!-- Modals -->
        <!-- Compose/Edit Modal -->
        <Modal v-model="showModal">
            <div class="md:p-2">
                <h2 class="text-2xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight mb-8">{{ editMode ? 'Modify Bulletin' : 'Compose Bulletin' }}</h2>

                <form @submit.prevent="saveAnnouncement" class="space-y-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Announcement Title</label>
                        <input v-model="form.title" required placeholder="e.g., Mandatory Candidate Assembly" class="w-full h-12 px-4 border-2 border-gray-100 dark:border-border rounded-2xl text-sm font-bold bg-white dark:bg-background focus:ring-2 focus:ring-primary/20 transition-all" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Target Audience</label>
                        <div class="relative">
                            <button type="button" @click="toggleAudienceDropdown" class="w-full h-12 px-4 border-2 border-gray-100 dark:border-border rounded-2xl bg-white dark:bg-background text-left text-xs font-bold uppercase tracking-widest flex items-center justify-between shadow-sm">
                                <span class="flex items-center gap-2">
                                    <component :is="getAudienceBadge(form.audience).icon" class="w-4 h-4 text-primary" />
                                    {{ audienceOptions.find(o => o.value === form.audience)?.label }}
                                </span>
                                <ChevronDown class="w-4 h-4 opacity-50" :class="{ 'rotate-180': audienceDropdownOpen }" />
                            </button>
                            <div v-if="audienceDropdownOpen" class="absolute z-50 mt-2 w-full bg-white dark:bg-purple-900 border-2 border-slate-100 dark:border-purple-600 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200" @click.outside="audienceDropdownOpen = false">
                                <div class="py-1">
                                    <div v-for="opt in audienceOptions" :key="opt.value" @click="selectAudience(opt)" class="px-4 py-3 cursor-pointer hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors text-[10px] font-black uppercase tracking-widest" :class="form.audience === opt.value ? 'text-primary bg-primary/5' : 'text-gray-700 dark:text-purple-100'">{{ opt.label }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Detailed Bulletin</label>
                        <textarea v-model="form.content" required rows="6" class="w-full p-6 border-2 border-gray-100 dark:border-border rounded-3xl text-sm font-medium bg-white dark:bg-background focus:ring-2 focus:ring-primary/20 transition-all resize-none" placeholder="Draft your message with clarity and precision..."></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-3 pt-8 border-t dark:border-border">
                        <button @click="closeModal" type="button" class="h-12 px-6 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-50 transition-all order-3 sm:order-1">Discard</button>
                        <button @click="saveDraft" type="button" :disabled="form.processing" class="h-12 px-6 rounded-2xl border-2 border-gray-100 dark:border-border font-black text-[10px] uppercase tracking-widest hover:bg-gray-50 transition-all order-2 sm:order-2 disabled:opacity-50">Save as Draft</button>
                        <button @click="saveAndPublish" type="button" :disabled="form.processing" class="h-12 px-10 rounded-2xl bg-primary text-primary-foreground font-black text-[10px] uppercase tracking-widest shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all flex items-center justify-center gap-2 order-1 sm:order-3 disabled:opacity-50">
                            <Send class="w-4 h-4" />
                            Broadcast Now
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- View Modal -->
        <Modal v-model="showViewModal" :padding="false">
            <div class="relative bg-white dark:bg-card overflow-hidden flex flex-col">
                <div class="h-24 bg-gradient-to-r from-primary to-purple-600 relative">
                    <button @click="closeViewModal" class="absolute top-4 right-4 p-2 bg-white/20 hover:bg-white/30 backdrop-blur-md rounded-full text-white transition-colors"><X class="w-5 h-5" /></button>
                </div>
                <div class="p-8 -mt-10 relative z-10">
                    <div class="bg-white dark:bg-card p-8 rounded-3xl shadow-xl border dark:border-border">
                        <div v-if="selectedAnnouncement" class="space-y-6">
                            <div class="flex items-center gap-3">
                                <span :class="[
                                    'inline-flex items-center gap-1.5 px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-full border',
                                    selectedAnnouncement.is_published ? 'bg-green-50 text-green-700' : 'bg-gray-50 text-gray-400'
                                ]">{{ selectedAnnouncement.is_published ? 'Live Broadcast' : 'Draft Copy' }}</span>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Released: {{ formatDate(selectedAnnouncement.created_at) }}</span>
                            </div>
                            <h2 class="text-2xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight leading-tight">{{ selectedAnnouncement.title }}</h2>
                            <div class="p-6 rounded-2xl bg-gray-50 dark:bg-muted/30 border dark:border-border">
                                <p class="text-sm md:text-base text-gray-700 dark:text-gray-300 leading-relaxed italic whitespace-pre-wrap">"{{ selectedAnnouncement.content }}"</p>
                            </div>
                            <div class="flex items-center justify-between pt-6 border-t dark:border-border">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary"><User class="w-4 h-4" /></div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Authored By {{ selectedAnnouncement.creator_name }}</span>
                                </div>
                                <button @click="togglePublish(selectedAnnouncement)" :class="[
                                    'px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95',
                                    selectedAnnouncement.is_published ? 'bg-gray-100 text-gray-400 hover:bg-red-50 hover:text-red-600' : 'bg-emerald-600 text-white shadow-lg shadow-emerald-100'
                                ]">{{ selectedAnnouncement.is_published ? 'Take Offline' : 'Release to Feed' }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal v-model="showDeleteModal">
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/20 mb-6">
                    <Trash class="h-10 w-10 text-red-600" />
                </div>
                <h3 class="text-xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight mb-2">Retract Bulletin?</h3>
                <p class="text-sm text-gray-500 font-medium mb-8">This will permanently delete the announcement from the system archives. This action is terminal.</p>
                <div class="flex flex-col gap-3">
                    <button @click="confirmDelete" class="h-14 w-full bg-red-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-red-100 hover:bg-red-700 transition-all">TERMINATE ANNOUNCEMENT</button>
                    <button @click="closeDeleteModal" class="h-10 w-full text-[10px] font-black text-gray-400 uppercase tracking-widest">Abort Action</button>
                </div>
            </div>
        </Modal>
    
</AppLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
