<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import VoterLayout from '@/layouts/VoterLayout.vue';
import { ref, computed } from 'vue';
import { Bell, BellOff, CheckCircle2, MoreHorizontal, X, Clock, Inbox } from 'lucide-vue-next';

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

// Active modal ID for announcement details
const activeModal = ref<number | null>(null);

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
    activeModal.value = id;
    const item = announcementList.value.find(a => a.id === id);
    if (item && !item.read) {
        item.read = true;
    }
};

const closeModal = () => {
    activeModal.value = null;
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
    <VoterLayout>
        <Head title="Announcements" />

        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 md:py-10 space-y-8 min-h-[calc(100vh-64px)]">
            <!-- Header with Actions -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Announcements</h1>
                    <p class="text-sm text-gray-500 dark:text-muted-foreground mt-1">Stay updated with the latest election news</p>
                </div>

                <!-- Actions popover -->
                <div class="relative">
                    <div class="flex items-center gap-2 bg-white dark:bg-card border border-gray-100 dark:border-border rounded-2xl px-3 py-1.5 shadow-sm">
                        <div class="flex items-center gap-2 px-1">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest hidden sm:inline">Unread</span>
                            <span
                                class="inline-flex items-center justify-center min-w-[24px] h-6 px-1.5 rounded-full text-[10px] font-black"
                                :class="unreadCount > 0 ? 'bg-primary text-primary-foreground' : 'bg-gray-100 dark:bg-muted text-gray-500 dark:text-gray-400'"
                            >
                                {{ unreadCount }}
                            </span>
                        </div>

                        <button
                            @click="actionsOpen = !actionsOpen"
                            class="p-1.5 rounded-xl hover:bg-gray-50 dark:hover:bg-muted transition-all active:scale-95"
                            aria-label="Actions"
                        >
                            <MoreHorizontal class="w-5 h-5 text-gray-400" />
                        </button>
                    </div>

                    <!-- Popover (Admin Style) -->
                    <div v-if="actionsOpen" class="absolute right-0 top-full mt-2 w-56 bg-white dark:bg-purple-900 border-2 border-slate-100 dark:border-purple-600 rounded-2xl shadow-2xl z-50 overflow-hidden animate-in fade-in zoom-in-95 duration-200" @click.outside="actionsOpen = false">
                        <div class="py-1">
                            <button @click="handleMarkAllRead" class="w-full text-left px-4 py-3 flex items-center gap-3 hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors">
                                <CheckCircle2 class="w-4 h-4 text-primary" />
                                <span class="text-xs font-bold text-gray-700 dark:text-purple-100 uppercase tracking-wider">Mark all read</span>
                            </button>
                            <button @click="handleMarkAllUnread" class="w-full text-left px-4 py-3 flex items-center gap-3 hover:bg-primary/10 dark:hover:bg-purple-800 transition-colors">
                                <Bell class="w-4 h-4 text-gray-400" />
                                <span class="text-xs font-bold text-gray-700 dark:text-purple-100 uppercase tracking-wider">Mark all unread</span>
                            </button>
                            <div class="border-t border-gray-100 dark:border-purple-700 my-1"></div>
                            <button @click="actionsOpen = false" class="w-full text-center px-4 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-600 transition-colors">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Announcements -->
            <div v-if="announcementList.length === 0" class="text-center py-20 bg-white dark:bg-card border dark:border-border rounded-3xl shadow-sm max-w-lg mx-auto">
                <div class="w-20 h-20 bg-gray-50 dark:bg-muted/50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <BellOff class="w-10 h-10 text-gray-200" />
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-foreground">No Announcements</h3>
                <p class="text-sm text-gray-500 mt-1 px-8">You're all caught up! Important updates will appear here.</p>
            </div>

            <!-- Announcements List -->
            <div v-else class="space-y-4">
                <div
                    v-for="item in announcementList"
                    :key="item.id"
                    :class="[
                        'w-full rounded-3xl border transition-all duration-300 bg-white dark:bg-card p-6 md:p-8 shadow-sm group relative overflow-hidden',
                        !item.read ? 'border-primary/20 ring-4 ring-primary/5 dark:ring-primary/10' : 'border-gray-100 dark:border-border hover:border-primary/20'
                    ]"
                >
                    <!-- Read/Unread Indicator -->
                    <div v-if="!item.read" class="absolute top-0 left-0 w-1.5 h-full bg-primary"></div>

                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <h2 class="text-lg md:text-xl font-black text-gray-900 dark:text-foreground leading-tight group-hover:text-primary transition-colors">{{ item.title }}</h2>
                                <span v-if="!item.read" class="flex-shrink-0 px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[8px] font-black uppercase tracking-widest border border-primary/20">NEW</span>
                            </div>

                            <p class="text-sm text-gray-600 dark:text-muted-foreground leading-relaxed line-clamp-2">{{ item.content }}</p>

                            <div class="mt-6 flex items-center gap-4">
                                <button class="flex items-center gap-2 text-[10px] font-black text-primary uppercase tracking-widest hover:underline active:scale-95 transition-all" @click="openModal(item.id)">
                                    Read Full Announcement
                                    <Clock class="w-3 h-3 opacity-50" />
                                </button>
                            </div>
                        </div>

                        <div class="flex-shrink-0 flex items-center gap-1.5 md:flex-col md:items-end">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ formatDate(item.published_at || item.created_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details Modal (High-end) -->
            <div v-for="item in announcementList" :key="'modal-' + item.id">
                <div v-if="activeModal === item.id" class="fixed inset-0 z-[100] flex items-center justify-center p-4 animate-in fade-in duration-300">
                    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>
                    
                    <div class="relative bg-white dark:bg-card w-full max-w-xl rounded-3xl overflow-hidden flex flex-col border dark:border-border shadow-2xl animate-in zoom-in-95 duration-300">
                        <div class="h-2 bg-primary"></div>
                        
                        <div class="p-8">
                            <div class="flex justify-between items-start mb-6">
                                <div class="flex items-center gap-3 text-primary">
                                    <Bell class="w-6 h-6" />
                                    <span class="text-[10px] font-black uppercase tracking-widest">Election Update</span>
                                </div>
                                <button @click="closeModal" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-muted text-gray-400 transition-colors">
                                    <X class="w-5 h-5" />
                                </button>
                            </div>

                            <h2 class="text-2xl font-black text-gray-900 dark:text-foreground leading-tight mb-4 uppercase tracking-tight">{{ item.title }}</h2>
                            
                            <div class="p-6 rounded-2xl bg-gray-50 dark:bg-muted/30 border dark:border-border mb-8">
                                <p class="text-base text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">{{ item.content }}</p>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-6 border-t dark:border-border">
                                <div class="flex items-center gap-2">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                        <Inbox class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">Released On</p>
                                        <p class="text-xs font-bold text-gray-900 dark:text-foreground mt-1">{{ formatDate(item.published_at || item.created_at) }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 w-full sm:w-auto">
                                    <button
                                        class="flex-1 sm:flex-none h-12 px-6 rounded-2xl border-2 transition-all font-black text-[10px] uppercase tracking-widest active:scale-95"
                                        :class="item.read ? 'border-gray-100 text-gray-400 hover:bg-gray-50' : 'border-primary/20 text-primary hover:bg-primary/5'"
                                        @click="toggleRead(item.id)"
                                    >
                                        {{ item.read ? 'Keep as Unread' : 'Mark as Read' }}
                                    </button>
                                    <button class="flex-1 sm:flex-none h-12 px-8 bg-gray-900 dark:bg-primary text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:opacity-90 active:scale-95 transition-all shadow-lg shadow-gray-200 dark:shadow-primary/20" @click="closeModal">Dismiss</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Minimal snackbar -->
        <transition name="fade">
            <div
                v-if="snackbar.show"
                :class="[
                    'fixed right-6 bottom-8 z-[110] rounded-2xl px-5 py-3 text-xs font-bold uppercase tracking-widest shadow-2xl border flex items-center gap-3 animate-in slide-in-from-right-10 duration-300',
                    snackbar.tone === 'success' ? 'bg-white dark:bg-card border-green-100 dark:border-green-900/50 text-green-700 dark:text-green-400' : 'bg-white dark:bg-card border-gray-100 dark:border-border text-gray-500'
                ]"
            >
                <div v-if="snackbar.tone === 'success'" class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center text-white shadow-sm">
                    <CheckCircle2 class="w-4 h-4" />
                </div>
                <span>{{ snackbar.message }}</span>
            </div>
        </transition>
    </VoterLayout>
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
