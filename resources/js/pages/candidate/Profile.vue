<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import Icon from '@/components/Icon.vue';
import { ref, computed } from 'vue';
import CandidateLayout from '@/layouts/CandidateLayout.vue';

interface User {
    name: string;
    email: string;
    photo: string | null;
}

interface Candidate {
    position: string;
    partylist: string;
    platform: string;
    course: string;
    year_level: string;
    section: string;
}

const props = defineProps<{
    user: User;
    candidate: Candidate | null;
}>();

const photoModal = ref(false);
const editingPlatform = ref(false);
const photoFile = ref<File | null>(null);
const photoPreview = ref<string | null>(props.user.photo);

const platformForm = useForm({
    platform: props.candidate?.platform || '',
});

const openEditPlatform = () => {
    editingPlatform.value = true;
    platformForm.platform = props.candidate?.platform || '';
};

const savePlatform = () => {
    platformForm.post('/candidate/profile/platform', {
        preserveScroll: true,
        onSuccess: () => {
            editingPlatform.value = false;
        },
    });
};

function onPhotoChange(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input || !input.files || input.files.length === 0) return;
    const file = input.files[0];
    
    photoFile.value = file;
    photoPreview.value = URL.createObjectURL(file);
}

const uploadPhoto = () => {
    if (!photoFile.value) return;
    
    const form = new FormData();
    form.append('photo', photoFile.value);
    
    router.post('/candidate/profile/photo', form, {
        preserveScroll: true,
        onSuccess: () => {
            photoModal.value = false;
            photoFile.value = null;
        },
    });
};
</script>

<template>
    <div>
        <Head title="Candidate Profile" />

        <CandidateLayout>
            <div class="py-8 px-4 sm:px-6 lg:px-8 bg-gray-50">
                <div class="mx-auto max-w-6xl">
                    <!-- Header Section -->
                    <div class="mb-6">
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-foreground">Profile</h1>
                        <p class="text-sm text-gray-600 dark:text-muted-foreground mt-1">Manage your profile information</p>
                    </div>

                    <!-- Main Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 auto-rows-max">
                        <!-- LEFT CONTAINER - Profile Card -->
                        <div class="lg:col-span-1">
                            <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow">
                                <!-- Avatar Section -->
                                <div class="flex flex-col items-center mb-6">
                                    <div class="relative group mb-4">
                                        <img 
                                            :src="photoPreview || '/images/profile.png'"
                                            class="w-32 h-32 rounded-full object-cover ring-4 ring-gray-200 dark:ring-border transition-all group-hover:ring-purple-300 dark:group-hover:ring-primary"
                                            alt="Candidate Photo"
                                        />
                                        <button 
                                            @click="photoModal = true"
                                            class="absolute bottom-0 right-0 bg-purple-700 dark:bg-primary text-white p-2.5 rounded-full shadow-lg hover:bg-purple-800 dark:hover:bg-primary/80 transition-all transform group-hover:scale-110"
                                        >
                                            <Icon name="camera" class="w-5 h-5" />
                                        </button>
                                    </div>
                                    <h2 class="text-xl font-bold text-gray-800 dark:text-foreground text-center">{{ user.name }}</h2>
                                    <p class="text-sm text-purple-700 dark:text-primary font-semibold mt-1" v-if="candidate">{{ candidate.position }}</p>
                                </div>

                                <!-- Divider -->
                                <div class="h-px bg-gray-200 dark:bg-border mb-6"></div>

                                <!-- Info Grid -->
                                <div class="space-y-4" v-if="candidate">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-800 dark:text-foreground uppercase tracking-wider mb-1">Email</p>
                                        <p class="text-sm text-gray-800 dark:text-muted-foreground">{{ user.email }}</p>
                                    </div>
                                    <div v-if="candidate.course">
                                        <p class="text-xs font-semibold text-gray-900 dark:text-foreground uppercase tracking-wider mb-1">Program</p>
                                        <p class="text-sm text-gray-800 dark:text-muted-foreground">{{ candidate.course }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-foreground uppercase tracking-wider mb-1">Partylist</p>
                                        <p class="text-sm text-gray-800 dark:text-muted-foreground">{{ candidate.partylist }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT CONTAINER - Campaign Platform -->
                        <div class="lg:col-span-2">
                            <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow">
                                <!-- Section Header -->
                                <div class="mb-6">
                                    <h3 class="text-2xl font-bold text-gray-800 dark:text-foreground flex items-center gap-2">
                                        Campaign Platform
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-muted-foreground mt-1">Share your vision and campaign objectives</p>
                                </div>

                                <!-- Platform Display -->
                                <div v-if="candidate && candidate.platform && !editingPlatform" class="space-y-4">
                                    <div class="mb-6">
                                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap break-words overflow-wrap-anywhere">{{ candidate.platform }}</p>
                                    </div>
                                    <div class="flex justify-end">
                                        <button 
                                            @click="openEditPlatform()"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 text-purple-800 dark:text-primary transition-all font-medium"
                                        >
                                            <Icon name="PencilLine" class="w-4 h-4" />
                                            Edit
                                        </button>
                                    </div>
                                </div>

                                <!-- Platform Input - New -->
                                <div v-else-if="!candidate?.platform && !editingPlatform" class="space-y-4">
                                    <textarea 
                                        v-model="platformForm.platform"
                                        placeholder="Share your campaign platform, vision, and key objectives here..."
                                        class="w-full bg-white dark:bg-background border border-gray-300 dark:border-border rounded-lg p-4 text-gray-800 dark:text-foreground placeholder-gray-400 dark:placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-primary focus:border-transparent resize-none transition-all"
                                        rows="6"
                                    ></textarea>
                                    <div class="flex gap-3 justify-end">
                                        <button 
                                            @click="savePlatform"
                                            :disabled="platformForm.processing"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 text-purple-800 dark:text-primary transition-all font-medium disabled:opacity-50"
                                        >
                                            <Icon name="save" class="w-4 h-4" />
                                            {{ platformForm.processing ? 'Saving...' : 'Save' }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Platform Input - Edit -->
                                <div v-if="editingPlatform" class="space-y-4">
                                    <textarea 
                                        v-model="platformForm.platform"
                                        class="w-full bg-white dark:bg-background border border-gray-300 dark:border-border rounded-lg p-4 text-gray-800 dark:text-foreground placeholder-gray-400 dark:placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-primary focus:border-transparent resize-none transition-all"
                                        rows="6"
                                    ></textarea>
                                    <div class="flex gap-3 justify-end">
                                        <button 
                                            @click="savePlatform"
                                            :disabled="platformForm.processing"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 text-purple-800 dark:text-primary transition-all font-medium disabled:opacity-50"
                                        >
                                            <Icon name="save" class="w-4 h-4" />
                                            {{ platformForm.processing ? 'Updating...' : 'Update' }}
                                        </button>
                                        <button 
                                            @click="editingPlatform = false"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 dark:bg-muted text-gray-800 dark:text-foreground rounded-lg hover:bg-gray-300 dark:hover:bg-muted/80 transition-all font-medium"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PHOTO CHANGE MODAL -->
            <div v-if="photoModal" class="fixed inset-0 flex items-center justify-center z-50 pointer-events-none">
                <!-- Overlay -->
                <div class="absolute inset-0 bg-black/10 backdrop-blur-sm pointer-events-none z-40"></div>

                <!-- Modal Panel -->
                <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-2xl p-8 w-96 pointer-events-auto shadow-2xl transform transition-all scale-100 z-50">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-foreground mb-2">Change Photo</h2>
                    <p class="text-sm text-gray-500 dark:text-muted-foreground mb-6">Upload a new profile picture (JPG, PNG — max 2MB)</p>

                    <div class="mb-6">
                        <input type="file" accept="image/*" class="hidden" id="modal-avatar-input" @change="onPhotoChange" />
                        <label for="modal-avatar-input" class="flex items-center justify-center gap-2 px-4 py-3 rounded-lg border-2 border-dashed border-gray-300 dark:border-border hover:border-purple-500 dark:hover:border-primary hover:bg-purple-50 dark:hover:bg-primary/10 cursor-pointer text-gray-700 dark:text-foreground font-medium transition-all">
                            <Icon name="upload" class="w-5 h-5 text-purple-700 dark:text-primary" />
                            Click to upload
                        </label>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button 
                            @click="photoModal = false"
                            class="px-4 py-2 border border-gray-300 dark:border-border rounded-lg text-gray-700 dark:text-foreground hover:bg-gray-50 dark:hover:bg-muted transition-all font-medium"
                        >
                            Cancel
                        </button>

                        <button 
                            @click="uploadPhoto"
                            :disabled="!photoFile"
                            class="px-4 py-2 bg-purple-700 dark:bg-primary text-white rounded-lg hover:bg-purple-800 dark:hover:bg-primary/80 transition-all font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Upload
                        </button>
                    </div>
                </div>
            </div>
        </CandidateLayout>
    </div>
</template>
