<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CandidateLayout from '@/layouts/CandidateLayout.vue';
import Modal from '@/components/Modal.vue';
import { ref, computed } from 'vue';
import { Bell, CheckCircle2, MoreHorizontal, X, Clock, Inbox } from 'lucide-vue-next';

// Props from backend
interface Announcement {
    id: number;
    title: string;
    content: string;
    audience: string;
    is_published: boolean;
    published_at: string;
    created_at: string;
    creator: {
        name: string;
    };
    read?: boolean;
}

const props = defineProps<{
    announcements: Announcement[];
}>();

// Add read tracking to announcements
const announcementList = ref(props.announcements.map(a => ({ ...a, read: false })));

// Active modal state
const showModal = ref(false);
const selectedAnnouncement = ref<Announcement | null>(null);

// Computed unread count
const unreadCount = computed(() => announcementList.value.filter(a => !a.read).length);

// Actions popover state
const actionsOpen = ref(false);

// Snackbar state
const snackbar = ref<{ show: boolean; message: string; tone?: 'success' | 'muted' }>({ 
    show: false, 
    message: '', 
    tone: 'success' 
});
let snackbarTimer: number | null = null;

const openModal = (id: number) => {
    const item = announcementList.value.find(a => a.id === id);
    if (item) {
        selectedAnnouncement.value = item;
        showModal.value = true;
        if (!item.read) {
            item.read = true;
        }
    }
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => {
        selectedAnnouncement.value = null;
    }, 300);
};

// Toggle read/unread for single announcement
function toggleRead(id: number): void {
    const item = announcementList.value.find(a => a.id === id);
    if (!item) return;
    item.read = !item.read;
}

// Mark all as read
function markAllRead(): void {
    announcementList.value.forEach(a => { a.read = true; });
}

// Mark all as unread
function markAllUnread(): void {
    announcementList.value.forEach(a => { a.read = false; });
}

// Snackbar helper
function showSnackbar(message: string, tone: 'success' | 'muted' = 'success', duration = 2400) {
    if (snackbarTimer) {
        clearTimeout(snackbarTimer);
        snackbarTimer = null;
    }
    snackbar.value = { show: true, message, tone };
    snackbarTimer = window.setTimeout(() => {
        snackbar.value.show = false;
        snackbarTimer = null;
    }, duration);
}

function handleMarkAllRead(): void {
    if (unreadCount.value === 0) {
        showSnackbar('All announcements already read', 'muted');
        actionsOpen.value = false;
        return;
    }
    markAllRead();
    showSnackbar('All announcements marked read');
    actionsOpen.value = false;
}

function handleMarkAllUnread(): void {
    markAllUnread();
    showSnackbar('Notifications marked unread', 'muted');
    actionsOpen.value = false;
}

// Format date helper
function formatDate(dateString: string) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}
</script>

<template>
    <CandidateLayout>
        <Head title="Announcements" />

        <div class="max-w-5xl mx-auto p-6 space-y-6">
            <!-- Header with Actions -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-foreground">Announcements</h1>

                <!-- Minimalist actions: unread badge + compact actions popover -->
                <div class="relative flex items-center gap-3">
                    <div class="flex items-center gap-3 bg-white dark:bg-card border border-slate-200 dark:border-border rounded-full px-3 py-1 shadow-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-slate-500 dark:text-muted-foreground">Unread</span>
                            <span
                                class="inline-flex items-center justify-center min-w-[28px] h-6 px-2 rounded-full text-sm font-medium"
                                :class="unreadCount > 0 ? 'bg-primary text-primary-foreground' : 'bg-slate-100 dark:bg-muted text-slate-600 dark:text-muted-foreground'"
                            >
                                {{ unreadCount }}
                            </span>
                        </div>

                        <!-- action toggle button -->
                        <button
                            @click="actionsOpen = !actionsOpen"
                            class="ml-2 -mr-1 inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-slate-50 dark:hover:bg-muted transition"
                            aria-label="Announcement actions"
                            title="Actions"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-600 dark:text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12h12M6 6h12M6 18h12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- popover (styled like the admin portal) -->
                    <div v-show="actionsOpen" class="absolute right-0 top-full mt-2 w-56 z-50 bg-white dark:bg-purple-900 border-2 border-slate-200 dark:border-purple-600 rounded-xl shadow-xl overflow-hidden ring-1 ring-primary/10">
                        <div @click="handleMarkAllRead" class="px-4 py-2 cursor-pointer hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors text-sm flex items-center gap-3 dark:text-purple-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="truncate dark:text-foreground">Mark all as read</span>
                            <span class="ml-auto text-xs text-slate-400 dark:text-muted-foreground" v-if="unreadCount === 0">none</span>
                        </div>

                        <div @click="handleMarkAllUnread" class="px-4 py-2 cursor-pointer hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors text-sm flex items-center gap-3 dark:text-purple-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 dark:text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m-4-4h8" />
                            </svg>
                            <span class="truncate dark:text-foreground">Mark all as unread</span>
                        </div>

                        <div class="border-t border-slate-100 dark:border-purple-700 mt-1"></div>

                        <div @click="() => { actionsOpen = false }" class="px-4 py-2 cursor-pointer hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors text-sm text-slate-500 dark:text-muted-foreground">Close</div>
                    </div>
                </div>
            </div>

            <!-- No Announcements -->
            <div v-if="announcementList.length === 0" class="text-center py-12 bg-white dark:bg-card border dark:border-border rounded-xl shadow-sm">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="mt-4 text-gray-600 dark:text-muted-foreground font-medium">No announcements available</p>
            </div>

            <!-- Announcements List -->
            <div v-else class="space-y-4">
                <div
                    v-for="item in announcementList"
                    :key="item.id"
                    :class="[
                        'w-full rounded-xl border border-slate-200 dark:border-border bg-white dark:bg-card p-6 shadow-sm hover:shadow-md transition',
                        !item.read ? 'ring-1 ring-primary/20 dark:ring-primary/30' : ''
                    ]"
                >
                    <div class="flex justify-between items-start">
                        <div class="space-y-1 max-w-[80%]">
                            <div class="flex items-center gap-3">
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-foreground truncate">{{ item.title }}</h2>
                                <span v-if="!item.read" class="text-xs bg-primary/10 dark:bg-primary/20 text-primary px-2 py-0.5 rounded-full font-medium">Unread</span>
                            </div>

                            <p class="text-sm text-slate-600 dark:text-muted-foreground leading-relaxed truncate">{{ item.content }}</p>

                            <div class="mt-2 flex items-center gap-3">
                                <button class="text-primary hover:text-primary/80 text-sm font-medium hover:underline transition-all" @click="openModal(item.id)">
                                    See full details
                                </button>
                            </div>
                        </div>

                        <span class="text-sm text-slate-500 dark:text-muted-foreground">{{ formatDate(item.published_at || item.created_at) }}</span>
                    </div>
                </div>
            </div>

            <!-- Modals (Unified) -->
            <Modal v-model="showModal" :padding="false">
                <div v-if="selectedAnnouncement" class="relative bg-white dark:bg-card overflow-hidden flex flex-col">
                    <div class="h-2 bg-primary"></div>
                    
                    <div class="p-8">
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-center gap-3 text-primary">
                                <Bell class="w-6 h-6" />
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Bulletin Detail</span>
                            </div>
                            <button @click="closeModal" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-muted text-gray-400 transition-colors">
                                <X class="w-5 h-5" />
                            </button>
                        </div>

                        <h2 class="text-2xl font-black text-slate-900 dark:text-foreground leading-tight mb-4 uppercase tracking-tight">{{ selectedAnnouncement.title }}</h2>
                        
                        <div class="p-6 rounded-2xl bg-slate-50 dark:bg-muted/30 border dark:border-border mb-8">
                            <p class="text-base text-slate-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap italic">"{{ selectedAnnouncement.content }}"</p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-6 border-t dark:border-border">
                            <div class="flex items-center gap-2">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                    <Inbox class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">Released On</p>
                                    <p class="text-xs font-bold text-slate-900 dark:text-foreground mt-1">{{ formatDate(selectedAnnouncement.published_at || selectedAnnouncement.created_at) }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <button
                                    class="flex-1 sm:flex-none h-12 px-6 rounded-2xl border-2 transition-all font-black text-[10px] uppercase tracking-widest active:scale-95"
                                    :class="selectedAnnouncement.read ? 'border-slate-100 text-slate-400 hover:bg-slate-50' : 'border-primary/20 text-primary hover:bg-primary/5'"
                                    @click="toggleRead(selectedAnnouncement.id)"
                                >
                                    {{ selectedAnnouncement.read ? 'Mark as Unread' : 'Mark as Read' }}
                                </button>
                                <button class="flex-1 sm:flex-none h-12 px-8 bg-slate-900 dark:bg-primary text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:opacity-90 active:scale-95 transition-all shadow-lg shadow-slate-200 dark:shadow-primary/20" @click="closeModal">Dismiss</button>
                            </div>
                        </div>
                    </div>
                </div>
            </Modal>
        </div>

        <!-- Minimal snackbar -->
        <transition name="fade">
            <div
                v-if="snackbar.show"
                :class="[
                    'fixed right-4 bottom-6 z-50 rounded-lg px-4 py-2.5 text-sm shadow-lg border flex items-center gap-3',
                    snackbar.tone === 'success' ? 'bg-white dark:bg-card border-green-100 dark:border-green-900/50 text-slate-800 dark:text-foreground' : 'bg-white dark:bg-card border-slate-200 dark:border-border text-slate-700 dark:text-foreground'
                ]"
            >
                <svg v-if="snackbar.tone === 'success'" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">{{ snackbar.message }}</span>
            </div>
        </transition>
    </CandidateLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
