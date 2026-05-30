<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import CandidateLayout from '@/layouts/CandidateLayout.vue';
import { Camera, PencilLine, Save, X, Upload, CheckCircle2, GraduationCap, Users, Mail, User, AlertCircle } from 'lucide-vue-next';
import Icon from '@/components/Icon.vue';

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
const photoPreview = ref<string | null>(props.user.photo);

const platformForm = useForm({
    platform: props.candidate?.platform || '',
});

const photoForm = useForm({
    photo: null as File | null,
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
    
    photoForm.photo = file;
    photoPreview.value = URL.createObjectURL(file);
    
    // Clear previous errors when a new file is selected
    photoForm.clearErrors('photo');

    // Client-side validation for immediate feedback
    if (file.size > 2 * 1024 * 1024) {
        photoForm.setError('photo', 'The photo exceeds the 2MB size limit. Please choose a smaller file.');
    }
}

const uploadPhoto = () => {
    if (!photoForm.photo || photoForm.errors.photo) return;
    
    photoForm.post('/candidate/profile/photo', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            photoModal.value = false;
            photoForm.reset();
        },
    });
};
</script>

<template>
    <div>
        <Head title="Candidate Profile" />

        <CandidateLayout>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-10 min-h-[calc(100vh-64px)]">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-xl md:text-2xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Candidate Profile</h1>
                    <p class="text-sm text-gray-500 dark:text-muted-foreground mt-1">Manage your public image and campaign platform</p>
                </div>

                <!-- Main Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    <!-- LEFT CONTAINER - Profile Card -->
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-card border border-gray-100 dark:border-border rounded-3xl p-8 shadow-sm text-center relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-br from-primary/10 to-accent/10 dark:from-primary/20 dark:to-accent/20"></div>
                            
                            <!-- Avatar Section -->
                            <div class="relative flex flex-col items-center">
                                <div class="relative group mb-6">
                                    <img 
                                        :src="photoPreview || '/images/profile.png'"
                                        class="w-32 h-32 rounded-full object-cover border-4 border-white dark:border-card shadow-xl transition-transform group-hover:scale-105"
                                        alt="Candidate Photo"
                                    />
                                    <button 
                                        @click="photoModal = true"
                                        class="absolute bottom-0 right-0 p-2.5 rounded-full shadow-lg bg-primary hover:bg-primary/90 text-primary-foreground transition-all border-2 border-white dark:border-card active:scale-95"
                                    >
                                        <Camera class="w-5 h-5" />
                                    </button>
                                </div>
                                <h2 class="text-xl font-black text-gray-900 dark:text-foreground leading-tight">{{ user.name }}</h2>
                                <p class="text-sm font-bold text-primary mt-1 uppercase tracking-widest" v-if="candidate">{{ candidate.position }}</p>
                            </div>

                            <!-- Divider -->
                            <div class="h-px bg-gray-100 dark:bg-border my-6"></div>

                            <!-- Info Grid -->
                            <div class="space-y-5 text-left" v-if="candidate">
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
                                        <Mail class="w-3 h-3" />
                                        Email
                                    </p>
                                    <p class="text-sm font-bold text-gray-800 dark:text-muted-foreground break-all">{{ user.email }}</p>
                                </div>
                                <div v-if="candidate.course">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
                                        <GraduationCap class="w-3 h-3" />
                                        Program
                                    </p>
                                    <p class="text-sm font-bold text-gray-800 dark:text-muted-foreground">{{ candidate.course }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
                                        <Users class="w-3 h-3" />
                                        Partylist
                                    </p>
                                    <p class="text-sm font-bold text-gray-800 dark:text-muted-foreground uppercase">{{ candidate.partylist || 'Independent' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT CONTAINER - Campaign Platform -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white dark:bg-card border border-gray-100 dark:border-border rounded-3xl p-6 md:p-10 shadow-sm relative">
                            <!-- Section Header -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                                <div>
                                    <h3 class="text-xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight flex items-center gap-2">
                                        Campaign Platform
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-muted-foreground mt-1 font-medium">Your vision and objectives for the association</p>
                                </div>
                                
                                <button 
                                    v-if="candidate?.platform && !editingPlatform"
                                    @click="openEditPlatform()"
                                    class="flex items-center justify-center gap-2 px-5 py-2.5 bg-gray-50 dark:bg-muted/30 text-primary hover:bg-primary/10 rounded-2xl transition-all font-black text-[10px] uppercase tracking-widest border dark:border-border self-start sm:self-auto"
                                >
                                    <PencilLine class="w-4 h-4" />
                                    Modify Statement
                                </button>
                            </div>

                            <!-- Platform Display -->
                            <div v-if="candidate && candidate.platform && !editingPlatform" class="relative group">
                                <div class="p-6 md:p-8 bg-primary/[0.02] dark:bg-primary/[0.05] rounded-3xl border-2 border-primary/5 dark:border-primary/10 relative">
                                    <div class="absolute -top-4 -left-2 text-6xl text-primary/10 font-serif">"</div>
                                    <p class="text-base md:text-lg text-gray-800 dark:text-gray-300 leading-relaxed whitespace-pre-wrap break-words italic">{{ candidate.platform }}</p>
                                </div>
                            </div>

                            <!-- Platform Input - Empty -->
                            <div v-else-if="!candidate?.platform && !editingPlatform" class="text-center py-10 md:py-20 border-2 border-dashed border-gray-200 dark:border-border rounded-3xl bg-gray-50/50 dark:bg-muted/10">
                                <div class="w-16 h-16 bg-white dark:bg-card rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                                    <PencilLine class="w-8 h-8 text-gray-300" />
                                </div>
                                <h4 class="text-lg font-bold text-gray-900 dark:text-foreground mb-2">No Platform Provided</h4>
                                <p class="text-sm text-gray-500 dark:text-muted-foreground mb-8 max-w-xs mx-auto font-medium">Add a campaign statement to let voters know your goals and objectives.</p>
                                <button 
                                    @click="editingPlatform = true"
                                    class="px-8 py-3 bg-primary text-primary-foreground rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-primary/90 transition-all shadow-lg shadow-primary/20"
                                >
                                    Write Platform Now
                                </button>
                            </div>

                            <!-- Platform Input - Editor -->
                            <div v-if="editingPlatform" class="space-y-5 animate-in fade-in slide-in-from-top-4 duration-300">
                                <div class="relative">
                                    <textarea 
                                        v-model="platformForm.platform"
                                        placeholder="Enter your campaign platform, vision, and key objectives here..."
                                        class="w-full bg-gray-50 dark:bg-background border-2 border-gray-100 dark:border-border rounded-3xl p-6 md:p-8 text-base md:text-lg text-gray-800 dark:text-foreground placeholder-gray-400 focus:ring-2 focus:ring-primary/20 focus:border-primary resize-none transition-all min-h-[300px] font-medium"
                                    ></textarea>
                                    <div class="absolute bottom-4 right-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        {{ platformForm.platform.length }} characters
                                    </div>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-3 justify-end">
                                    <button 
                                        @click="editingPlatform = false"
                                        class="h-12 px-6 bg-white dark:bg-muted text-gray-500 dark:text-gray-300 rounded-2xl hover:bg-gray-50 dark:hover:bg-muted/80 transition-all font-black text-[10px] uppercase tracking-widest border-2 border-gray-100 dark:border-border order-2 sm:order-1"
                                    >
                                        Cancel Changes
                                    </button>
                                    <button 
                                        @click="savePlatform"
                                        :disabled="platformForm.processing"
                                        class="h-12 px-10 bg-primary text-primary-foreground rounded-2xl hover:bg-primary/90 transition-all font-black text-[10px] uppercase tracking-widest shadow-lg shadow-primary/20 flex items-center justify-center gap-2 order-1 sm:order-2 disabled:opacity-50"
                                    >
                                        <Save class="w-4 h-4" />
                                        {{ platformForm.processing ? 'Syncing...' : 'Save Statement' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PHOTO CHANGE MODAL (Modern) -->
            <div v-if="photoModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 animate-in fade-in duration-300">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="photoModal = false"></div>

                <!-- Modal Panel -->
                <div class="bg-white dark:bg-card border dark:border-border rounded-3xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col relative z-50 animate-in zoom-in-95 duration-300">
                    <div class="p-6 border-b dark:border-border flex items-center justify-between">
                        <h3 class="text-lg font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Update Portrait</h3>
                        <button @click="photoModal = false" type="button" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-muted text-gray-400 transition-colors">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="p-8">
                        <input type="file" accept="image/*" class="hidden" id="modal-avatar-input" @change="onPhotoChange" />
                        <label for="modal-avatar-input" 
                            class="flex flex-col items-center justify-center gap-4 px-6 py-10 rounded-3xl border-2 border-dashed border-gray-200 dark:border-border hover:border-primary dark:hover:border-primary/50 hover:bg-primary/5 dark:hover:bg-primary/10 cursor-pointer transition-all group"
                            :class="{'border-destructive/50 bg-destructive/5': photoForm.errors.photo}"
                        >
                            <div class="w-16 h-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center group-hover:scale-110 transition-transform">
                                <Upload class="w-8 h-8" />
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-bold text-gray-700 dark:text-foreground">Click to select photo</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">JPG or PNG • Max 2MB</p>
                            </div>
                        </label>
                        
                        <!-- Error Display -->
                        <div v-if="photoForm.errors.photo" class="mt-4 p-4 rounded-2xl bg-destructive/10 border border-destructive/20 flex items-start gap-3 animate-in slide-in-from-top-2 duration-300">
                            <AlertCircle class="w-5 h-5 text-destructive shrink-0 mt-0.5" />
                            <div class="space-y-1">
                                <p class="text-xs font-black text-destructive uppercase tracking-tight">Validation Error</p>
                                <p class="text-xs font-medium text-destructive/80 leading-relaxed">{{ photoForm.errors.photo }}</p>
                            </div>
                        </div>

                        <div v-if="photoForm.photo && !photoForm.errors.photo" class="mt-6 p-4 rounded-2xl bg-green-50 dark:bg-green-900/10 border border-green-100 dark:border-green-900/20 flex items-center gap-3">
                            <CheckCircle2 class="w-5 h-5 text-green-500" />
                            <span class="text-xs font-bold text-green-700 dark:text-green-400 truncate">{{ photoForm.photo.name }}</span>
                        </div>

                        <div class="flex flex-col gap-3 mt-8">
                            <button 
                                @click="uploadPhoto"
                                :disabled="!photoForm.photo || photoForm.errors.photo || photoForm.processing"
                                class="h-12 w-full text-xs font-black uppercase tracking-widest bg-primary text-primary-foreground rounded-2xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all disabled:opacity-50 disabled:grayscale disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <span v-if="photoForm.processing" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Uploading...
                                </span>
                                <span v-else>Apply New Photo</span>
                            </button>
                            <button @click="photoModal = false" type="button"
                                class="h-10 w-full text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </CandidateLayout>
    </div>
</template>
