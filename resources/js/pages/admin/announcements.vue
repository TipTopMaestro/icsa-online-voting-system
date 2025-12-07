<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import Icon from '@/components/Icon.vue';

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
    creator?: User;
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
    const next = !sortDropdownOpen.value;
    sortDropdownOpen.value = next;
    if (next) audienceDropdownOpen.value = false;
}

function toggleAudienceDropdown() {
    const next = !audienceDropdownOpen.value;
    audienceDropdownOpen.value = next;
    if (next) sortDropdownOpen.value = false;
}

const sortOptions: { label: string; value: 'newest' | 'oldest' }[] = [
    { label: 'Newest First', value: 'newest' },
    { label: 'Oldest First', value: 'oldest' },
];

const audienceOptions: { label: string; value: 'all' | 'voters' | 'candidates' }[] = [
    { label: 'Everyone', value: 'all' },
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
    
    // Filter
    if (activeFilter.value === 'published') {
        list = list.filter(a => a.is_published);
    } else if (activeFilter.value === 'draft') {
        list = list.filter(a => !a.is_published);
    }
    
    // Sort
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
    try {
        return new Date(iso).toLocaleDateString(undefined, { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    } catch {
        return iso;
    }
}

function openCreateModal() {
    editMode.value = false;
    form.reset();
    form.clearErrors();
    form.is_published = false;
    showModal.value = true;
}

function openEditModal(announcement: Announcement) {
    if (announcement.is_published) {
        alert('Cannot edit published announcements. Unpublish first.');
        return;
    }
    
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
    form.clearErrors();
    selectedAnnouncement.value = null;
}

function saveAnnouncement() {
    if (editMode.value && selectedAnnouncement.value) {
        // Update
        form.put(`/admin/announcements/${selectedAnnouncement.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        });
    } else {
        // Create
        form.post('/admin/announcements', {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        });
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
        onSuccess: () => {
            // Close view modal if it's open
            if (showViewModal.value) {
                closeViewModal();
            }
        },
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
    
    router.delete(`/admin/announcements/${selectedAnnouncement.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal();
        },
    });
}

function getAudienceBadgeColor(audience: string) {
    const colors = {
        all: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        voters: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        candidates: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    };
    return colors[audience as keyof typeof colors] || colors.all;
}
</script>

<template>
    <Head title="Announcements" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <main class="p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Announcements</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create and manage announcements for your institution.</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <button type="button" @click.stop="toggleSortDropdown()" class="w-full flex items-center justify-between px-4 py-2 rounded-xl border border-slate-300 bg-white text-left shadow-sm focus:ring-2 focus:ring-purple-800 text-sm">
                                <span>{{ sortOptions.find(o => o.value === sortOrder)?.label ?? 'Newest First' }}</span>
                                <svg class="w-4 h-4 text-slate-600 transition-transform duration-200" :class="{ 'rotate-180': sortDropdownOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div v-if="sortDropdownOpen" class="absolute z-50 mt-2 w-48 rounded-xl border-2 border-purple-800 bg-white shadow-xl overflow-hidden">
                                <div v-for="opt in sortOptions" :key="opt.value" class="px-4 py-2 text-sm hover:bg-purple-100 cursor-pointer" @click="selectSort(opt)">
                                    {{ opt.label }}
                                </div>
                            </div>
                        </div>

                        <button 
                            @click="openCreateModal" 
                            type="button" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-[#5A2D6F] hover:bg-[#4b255c] dark:bg-[#5A2D6F] dark:hover:bg-[#4b255c] text-white text-sm font-medium rounded-md transition-colors"
                        >
                            <Icon name="plus" class="h-4 w-4" />
                            Create Announcement
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 inline-flex gap-2 p-1 bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <button 
                        @click="activeFilter = 'all'" 
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-md transition-colors',
                            activeFilter === 'all' 
                                ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm' 
                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'
                        ]"
                    >
                        All ({{ announcements.length }})
                    </button>
                    <button 
                        @click="activeFilter = 'published'" 
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-md transition-colors',
                            activeFilter === 'published' 
                                ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm' 
                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'
                        ]"
                    >
                        Published ({{ publishedCount }})
                    </button>
                    <button 
                        @click="activeFilter = 'draft'" 
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-md transition-colors',
                            activeFilter === 'draft' 
                                ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm' 
                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'
                        ]"
                    >
                        Draft ({{ draftCount }})
                    </button>
                </div>

                <!-- Grid -->
                <section>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <template v-if="filtered.length">
                            <article 
                                v-for="announcement in filtered" 
                                :key="announcement.id" 
                                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-gray-300 dark:hover:border-gray-600 transition-colors"
                            >
                                <!-- Header -->
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex-1 min-w-0 mb-3">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 truncate">
                                            {{ announcement.title }}
                                        </h3>
                                        <!-- Date and Audience -->
                                         
                                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ formatDate(announcement.created_at) }}
                                            </span>
                                            <span class="text-xs text-gray-400 dark:text-gray-600">•</span>
                                            <span :class="['inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded', getAudienceBadgeColor(announcement.audience)]">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                                {{ announcement.audience === 'all' ? 'Everyone' : announcement.audience.charAt(0).toUpperCase() + announcement.audience.slice(1) }}
                                            </span>
                                        </div>
                                    </div>

                                    <button 
                                        @click="togglePublish(announcement)"
                                        :class="[
                                            'px-2 py-1 text-xs font-medium rounded transition-colors whitespace-nowrap',
                                            announcement.is_published 
                                                ? 'bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/30' 
                                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600'
                                        ]"
                                    >
                                        {{ announcement.is_published ? 'Published' : 'Draft' }}
                                    </button>
                                </div>

                                <!-- Content Preview -->
                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3 mb-4">
                                    {{ announcement.content }}
                                </p>

                                <!-- Footer -->
                                <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        By {{ announcement.creator?.name || 'Admin' }}
                                    </span>
                                    <div class="flex items-center gap-1">
                                        <button 
                                            @click="openViewModal(announcement)"
                                            type="button" 
                                            class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                                        >
                                            View
                                        </button>
                                        <button 
                                            v-if="!announcement.is_published"
                                            @click="openEditModal(announcement)"
                                            type="button" 
                                            class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            @click="openDeleteModal(announcement)"
                                            type="button" 
                                            class="px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </template>

                        <template v-else>
                            <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-12 text-center">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No announcements yet</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Get started by creating your first announcement.</p>
                                <button 
                                    @click="openCreateModal" 
                                    type="button" 
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#5A2D6F] hover:bg-[#4b255c] dark:bg-[#5A2D6F] dark:hover:bg-[#4b255c] text-white text-sm font-medium rounded-md transition-colors"
                                >
                                    <Icon name="plus" class="h-4 w-4" />
                                    Create Announcement
                                </button>
                            </div>
                        </template>
                    </div>
                </section>
            </div>
        </main>
    </AppLayout>

    <!-- Create/Edit Modal -->
    <transition name="fade">
        <div v-if="showModal" class="fixed inset-0 z-40 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>

            <div class="relative z-50 w-full max-w-2xl bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ editMode ? 'Edit Announcement' : 'Create Announcement' }}
                    </h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="saveAndPublish" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input 
                            v-model="form.title" 
                            type="text" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                            placeholder="Enter announcement title" 
                        />
                        <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            v-model="form.content" 
                            rows="5" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none" 
                            placeholder="Write your announcement..."
                        ></textarea>
                        <div v-if="form.errors.content" class="text-red-500 text-xs mt-1">{{ form.errors.content }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Audience <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <button type="button" @click.stop="toggleAudienceDropdown()" class="w-full flex items-center justify-between px-4 py-2 rounded-xl border border-slate-300 bg-white text-left shadow-sm focus:ring-2 focus:ring-purple-800 text-sm">
                                <span>{{ audienceOptions.find(o => o.value === form.audience)?.label ?? 'Everyone' }}</span>
                                <svg class="w-4 h-4 text-slate-600 transition-transform duration-200" :class="{ 'rotate-180': audienceDropdownOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div v-if="audienceDropdownOpen" class="absolute z-50 mt-2 w-56 rounded-xl border-2 border-purple-800 bg-white shadow-xl overflow-hidden">
                                <div v-for="opt in audienceOptions" :key="opt.value" class="px-4 py-2 text-sm hover:bg-purple-100 cursor-pointer" @click="selectAudience(opt)">
                                    {{ opt.label }}
                                </div>
                            </div>
                        </div>
                        <div v-if="form.errors.audience" class="text-red-500 text-xs mt-1">{{ form.errors.audience }}</div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button 
                            type="button" 
                            @click="closeModal" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            type="button" 
                            @click="saveDraft" 
                            :disabled="form.processing"
                            class="px-4 py-2 text-sm font-medium bg-gray-600 hover:bg-gray-700 dark:bg-gray-600 dark:hover:bg-gray-500 text-white rounded-md disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            Save as Draft
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-4 py-2 bg-[#5A2D6F] hover:bg-[#4b255c] text-white text-sm font-medium rounded-md transition"
                        >
                            {{ editMode ? 'Update' : 'Publish' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>

    <!-- Delete Confirmation Modal -->
    <transition name="fade">
        <div v-if="showDeleteModal" class="fixed inset-0 z-40 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" @click="closeDeleteModal"></div>

            <div class="relative z-50 w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Delete Announcement</h3>
                    
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        Are you sure you want to delete "{{ selectedAnnouncement?.title }}"? This action cannot be undone.
                    </p>

                    <div class="flex justify-end gap-2">
                        <button 
                            @click="closeDeleteModal" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="confirmDelete"
                            class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <!-- View Modal -->
    <transition name="fade">
        <div v-if="showViewModal && selectedAnnouncement" class="fixed inset-0 z-40 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" @click="closeViewModal"></div>

            <div class="relative z-50 w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
                    <!-- Header -->
                    <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Announcement Details</h2>
                            <span 
                                :class="[
                                    'px-2 py-1 text-xs rounded-md font-medium',
                                    selectedAnnouncement.is_published 
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' 
                                        : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                ]"
                            >
                                {{ selectedAnnouncement.is_published ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                        <button @click="closeViewModal" class="text-gray-500 dark:text-gray-300 hover:text-gray-700">
                            <span class="sr-only">Close</span>
                            ✕
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-6">
                        <!-- Title -->
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ selectedAnnouncement.title }}
                            </h3>
                        </div>

                        <!-- Meta Information -->
                        <div class="flex flex-wrap items-center gap-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>{{ selectedAnnouncement.creator?.name || 'Admin' }}</span>
                            </div>
                            
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ formatDate(selectedAnnouncement.created_at) }}</span>
                            </div>

                            <span :class="['inline-flex items-center text-xs px-2 py-1 rounded-md', getAudienceBadgeColor(selectedAnnouncement.audience)]">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ selectedAnnouncement.audience === 'all' ? 'Everyone' : selectedAnnouncement.audience.charAt(0).toUpperCase() + selectedAnnouncement.audience.slice(1) }}
                            </span>

                            <span v-if="selectedAnnouncement.is_published && selectedAnnouncement.published_at" class="text-xs text-gray-500 dark:text-gray-400">
                                Published: {{ formatDate(selectedAnnouncement.published_at) }}
                            </span>
                        </div>

                        <!-- Content Body -->
                        <div class="prose prose-sm dark:prose-invert max-w-none">
                            <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">
                                {{ selectedAnnouncement.content }}
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button 
                                @click="togglePublish(selectedAnnouncement)"
                                :class="[
                                    'px-4 py-2 text-sm rounded-md font-medium transition-colors',
                                    selectedAnnouncement.is_published 
                                        ? 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' 
                                        : 'bg-green-600 text-white hover:bg-green-700'
                                ]"
                            >
                                {{ selectedAnnouncement.is_published ? 'Unpublish' : 'Publish' }}
                            </button>
                        </div>

                        <div class="flex items-center gap-2">
                            <button 
                                v-if="!selectedAnnouncement.is_published"
                                @click="openEditModal(selectedAnnouncement); closeViewModal()"
                                class="px-4 py-2 text-sm bg-transparent border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                            >
                                Edit
                            </button>
                            <button 
                                @click="closeViewModal" 
                                class="px-4 py-2 text-sm bg-gray-600 hover:bg-gray-700 text-white rounded-md"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
