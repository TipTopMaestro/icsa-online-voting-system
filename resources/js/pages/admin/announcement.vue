<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

/*
    Backend integration notes (for future implementation):

    - Data source:
        Replace `announcements` (the `ref` below) with Inertia props or an API call.
        Example with Inertia props: `const props = usePage().props; const announcements = ref(props.announcements || [])`
        Or load via `Inertia.get('/announcements')` or `fetch('/api/announcements')` in a lifecycle hook.

    - Endpoints:
        Recommended REST actions on the backend:
            GET    /api/announcements         -> list
            POST   /api/announcements         -> create (payload: { title, body, status })
            PATCH  /api/announcements/:id     -> update (payload: { title?, body?, status? })
            DELETE /api/announcements/:id     -> delete

        When integrating, prefer using Inertia's `router.post`/`router.patch` helpers or `fetch` for API endpoints.

    - Validation & UX:
        Add client-side validation for `title` (required) and `body` (max length) before sending to server.
        Show server validation errors returned in the response (map to form fields).

    - Optimistic UI / server reconciliation:
        Consider optimistic updates for better UX: add the new announcement locally, then reconcile with server response.
        If using optimistic updates, handle failure cases (rollback and show error notification).

    - Sorting & filtering:
        For large data sets, move sorting and filtering to the server and request paginated results.

    - Tests:
        Add feature tests (Pest) that cover creating announcements, changing status, and listing.
        Use factories for model creation in tests.

    - Security:
        Protect endpoints using authentication + authorization (policies/gates). Validate `status` values on backend.

*/

type Status = 'pending' | 'draft' | 'posted';

type Announcement = {
    id: number;
    title: string;
    body: string;
    date: string;
    status: Status;
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Announcements', href: '/announcement' },
];

// UI-only sample data (comment out or remove when wiring to backend)
// TODO: Replace this `ref` with Inertia props or an API response.
const announcements = ref<Announcement[]>([
    // Example seed items (uncomment to preview during development):
    // { id: 1, title: 'Semester results published', body: 'Final results are online. Check your student portal for details.', date: '2025-11-15', status: 'posted' },
    // { id: 2, title: 'Library maintenance', body: 'Library systems will be down for maintenance this weekend.', date: '2025-11-12', status: 'pending' },
    // { id: 3, title: 'Call for volunteers', body: 'We are seeking volunteers for the upcoming orientation.', date: '2025-11-10', status: 'draft' },
]);

// UI state
const showModal = ref(false);
const activeFilter = ref<'all' | Status | 'all'>('all');
const sortOrder = ref<'newest' | 'oldest'>('newest');

// Form state - on backend implementation, use a form helper (Inertia `useForm`) to manage validation/errors
const form = reactive({ title: '', body: '', status: 'draft' as Status });

/**
 * formatDate
 * - Consider using a shared date formatting utility or server-provided formatted timestamps.
 * - Handle timezone concerns on the server or use a library like `date-fns` / `luxon` if needed.
 */
function formatDate(iso: string) {
    try {
        return new Date(iso).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    } catch {
        return iso;
    }
}

/**
 * openCreateModal
 * - When integrating with backend, you might prefill `form` when editing an existing announcement
 *   by loading the announcement data into `form` and opening the modal.
 */
function openCreateModal(defaultStatus: Status = 'draft') {
    form.title = '';
    form.body = '';
    form.status = defaultStatus;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
}

/**
 * saveAnnouncement
 * UI-only: pushes a local announcement into the `announcements` array.
 * Backend integration:
 *  - Replace this with `Inertia.post('/announcements', form)` or `fetch` to POST the data.
 *  - After successful response, update local list from server response (do not rely solely on optimistic client id).
 */
function saveAnnouncement(asStatus: Status) {
    const id = announcements.value.length ? Math.max(...announcements.value.map((a) => a.id)) + 1 : 1;
    announcements.value.unshift({ id, title: form.title || '(Untitled)', body: form.body || '', date: new Date().toISOString(), status: asStatus });
    closeModal();
}

/**
 * updateStatus
 * UI-only: updates an item's status locally.
 * Backend integration:
 *  - Send `PATCH /api/announcements/:id` with { status }
 *  - Handle success/error from server and show notifications.
 */
function updateStatus(id: number, status: Status) {
    const item = announcements.value.find((a) => a.id === id);
    if (item) item.status = status;
}

// Filtering + sorting (client-side). For large datasets, implement server-side filtering/pagination.
const filtered = computed(() => {
    const list = activeFilter.value === 'all' ? announcements.value : announcements.value.filter((a) => a.status === activeFilter.value);
    return list.slice().sort((a, b) => (sortOrder.value === 'newest' ? +new Date(b.date) - +new Date(a.date) : +new Date(a.date) - +new Date(b.date)));
});
</script>

<template>
    <Head title="Announcements" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <main class="min-h-screen p-6">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <header class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                    <div class="w-full sm:w-auto text-center sm:text-left">
                        <h1 class="text-2xl sm:text-3xl font-semibold text-gray-800 dark:text-gray-100">Announcements</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage brief notices and communications for your institution.</p>
                    </div>

                    <div class="w-full sm:w-auto flex items-center justify-center sm:justify-end gap-3">
                        <div class="flex items-center gap-2">
                            <select v-model="sortOrder" class="text-sm px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-100">
                                <option value="newest">Newest</option>
                                <option value="oldest">Oldest</option>
                            </select>
                        </div>

                        <div class="hidden sm:flex items-center gap-2">
                            <button @click.prevent="openCreateModal('draft')" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create Announcement
                            </button>
                        </div>

                        <div class="sm:hidden">
                            <button @click.prevent="openCreateModal('draft')" type="button" class="inline-flex items-center gap-2 px-3 py-2 bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none">
                                New
                            </button>
                        </div>
                    </div>
                </header>

                <!-- Filters -->
                <nav class="mb-4">
                    <div class="inline-flex rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-1">
                        <button @click="activeFilter = 'all'" :class="['px-3 py-1.5 rounded-full text-sm', activeFilter === 'all' ? 'bg-purple-100 dark:bg-purple-700 text-purple-700 dark:text-white' : 'text-gray-600 dark:text-gray-300']">All</button>
                        <button @click="activeFilter = 'pending'" :class="['px-3 py-1.5 rounded-full text-sm', activeFilter === 'pending' ? 'bg-purple-100 dark:bg-purple-700 text-purple-700 dark:text-white' : 'text-gray-600 dark:text-gray-300']">Pending</button>
                        <button @click="activeFilter = 'draft'" :class="['px-3 py-1.5 rounded-full text-sm', activeFilter === 'draft' ? 'bg-purple-100 dark:bg-purple-700 text-purple-700 dark:text-white' : 'text-gray-600 dark:text-gray-300']">Draft</button>
                        <button @click="activeFilter = 'posted'" :class="['px-3 py-1.5 rounded-full text-sm', activeFilter === 'posted' ? 'bg-purple-100 dark:bg-purple-700 text-purple-700 dark:text-white' : 'text-gray-600 dark:text-gray-300']">Posted</button>
                    </div>
                </nav>

                <!-- Grid -->
                <section>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <template v-if="filtered.length">
                            <article v-for="a in filtered" :key="a.id" class="flex flex-col bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-xl p-5 shadow-sm dark:shadow-none">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 truncate">{{ a.title }}</h3>
                                        <time class="block mt-1 text-xs text-gray-500 dark:text-gray-400">{{ formatDate(a.date) }}</time>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <select v-model="a.status" @change.prevent="updateStatus(a.id, a.status)" class="text-xs px-2 py-1 bg-transparent border border-gray-200 dark:border-gray-700 rounded-md text-gray-800 dark:text-gray-100">
                                            <option value="pending">Pending</option>
                                            <option value="draft">Draft</option>
                                            <option value="posted">Posted</option>
                                        </select>
                                    </div>
                                </div>

                                <p class="mt-3 text-sm text-gray-700 dark:text-gray-300 flex-1 overflow-hidden" style="display:-webkit-box; -webkit-line-clamp:3; line-clamp:3; -webkit-box-orient:vertical;">
                                    {{ a.body }}
                                </p>

                                <div class="mt-4 flex items-center justify-between">
                                    <span class="inline-flex items-center text-xs px-2 py-1 rounded-md bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ a.status }}</span>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="text-sm px-3 py-1.5 bg-transparent border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-100 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition">View</button>
                                    </div>
                                </div>
                            </article>
                        </template>

                        <template v-else>
                            <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-8 shadow-sm dark:shadow-none flex flex-col items-center justify-center text-center">
                                <div class="h-20 w-20 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <span class="text-2xl">📣</span>
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-gray-100">No announcements yet</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Create a new announcement to share updates with your community.</p>
                                <div class="mt-4">
                                    <button @click.prevent="openCreateModal('draft')" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 text-white text-sm font-medium rounded-md">Create Announcement</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>
            </div>
        </main>
    </AppLayout>

    <!-- Modal -->
    <transition name="fade">
        <div v-if="showModal" class="fixed inset-0 z-40 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" @click="closeModal"></div>

            <div class="relative z-50 w-full max-w-2xl mx-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <header class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Create Announcement</h2>
                        <button @click="closeModal" class="text-gray-500 dark:text-gray-300 hover:text-gray-700">
                            <span class="sr-only">Close</span>
                            ✕
                        </button>
                    </header>

                    <form @submit.prevent="saveAnnouncement(form.status)" class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Title</label>
                            <input v-model="form.title" type="text" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100" placeholder="Announcement title" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea v-model="form.body" rows="5" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100" placeholder="Write a short announcement..."></textarea>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-700 dark:text-gray-300">Save as</label>
                                <select v-model="form.status" class="text-sm px-2 py-1 border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-100">
                                    <option value="draft">Draft</option>
                                    <option value="pending">Pending</option>
                                    <option value="posted">Posted</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="closeModal" class="text-sm px-3 py-1.5 bg-transparent border border-gray-200 dark:border-gray-700 rounded-md text-gray-700 dark:text-gray-200">Cancel</button>
                                <button type="button" @click="saveAnnouncement('draft')" class="text-sm px-3 py-1.5 bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 text-white rounded-md">Save draft</button>
                                <button type="submit" class="text-sm px-3 py-1.5 bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 text-white rounded-md">Save & Post</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>
</template>