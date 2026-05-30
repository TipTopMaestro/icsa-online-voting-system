<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import VoterLayout from '@/layouts/VoterLayout.vue';
import { User, Mail, GraduationCap, Calendar, Hash, Hash as SectionIcon, PencilLine, Camera, X, CheckCircle2, Upload, AlertCircle } from 'lucide-vue-next';
import Icon from '@/components/Icon.vue';

interface Voter {
    id: number;
    student_id: string;
    name: string;
    email: string;
    program: string;
    year: string;
    section: string;
    photo: string | null;
    voted: boolean;
}

const props = defineProps<{
    voter: Voter;
}>();

const photoModal = ref(false);
const showEditModal = ref(false);
const avatarPreview = ref('');

const form = useForm({
    student_id: props.voter.student_id,
    name: props.voter.name,
    email: props.voter.email,
    program: props.voter.program,
    year: props.voter.year,
    section: props.voter.section || '',
});

const photoForm = useForm({
    photo: null as File | null,
});

function openEdit() {
    form.student_id = props.voter.student_id;
    form.name = props.voter.name;
    form.email = props.voter.email;
    form.program = props.voter.program;
    form.year = props.voter.year;
    form.section = props.voter.section || '';
    showEditModal.value = true;
}

function closeAllModals() {
    showEditModal.value = false;
    photoModal.value = false;
    avatarPreview.value = '';
    photoForm.reset();
    photoForm.clearErrors();
}

function onAvatarChange(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input || !input.files || input.files.length === 0) return;
    const file = input.files[0];
    
    photoForm.photo = file;
    const url = URL.createObjectURL(file);
    avatarPreview.value = url;
    
    // Clear previous errors when a new file is selected
    photoForm.clearErrors('photo');

    // Client-side validation for immediate feedback
    if (file.size > 2 * 1024 * 1024) {
        photoForm.setError('photo', 'The photo exceeds the 2MB size limit. Please choose a smaller file.');
    }
}

function savePhoto() {
    if (!photoForm.photo || photoForm.errors.photo) {
        if (!photoForm.photo) closeAllModals();
        return;
    }
    
    photoForm.post('/voter/profile/photo', {
        forceFormData: true,
        onSuccess: () => {
            closeAllModals();
        },
        preserveScroll: true,
    });
}

function saveInfo() {
    form.put('/voter/profile', {
        onSuccess: () => {
            closeAllModals();
        },
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Profile" />
    
    <VoterLayout>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-10 min-h-[calc(100vh-64px)]">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-xl md:text-2xl font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Voter Profile</h1>
                    <p class="text-sm text-gray-500 dark:text-muted-foreground mt-1">Manage your digital identity and credentials</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Profile Card -->
                    <div class="bg-white dark:bg-card border border-gray-100 dark:border-border rounded-3xl p-8 shadow-sm text-center relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-br from-primary/10 to-accent/10 dark:from-primary/20 dark:to-accent/20"></div>
                        
                        <div class="relative flex flex-col items-center">
                            <div class="relative group mb-6">
                                <img :src="avatarPreview || voter.photo || '/images/default-avatar.png'"
                                    class="w-32 h-32 rounded-full object-cover border-4 border-white dark:border-card shadow-xl transition-transform group-hover:scale-105"
                                    alt="Profile" />
                                <button @click="photoModal = true" type="button"
                                    class="absolute bottom-0 right-0 bg-primary text-primary-foreground p-2.5 rounded-full hover:bg-primary/90 transition-all shadow-lg active:scale-95 border-2 border-white dark:border-card">
                                    <Camera class="w-5 h-5" />
                                </button>
                            </div>
                            
                            <h2 class="text-xl font-black text-gray-900 dark:text-foreground leading-tight">{{ voter.name }}</h2>
                            <p class="text-sm font-bold text-primary mt-1 uppercase tracking-widest">{{ voter.program }}</p>
                            
                            <div class="mt-8 w-full space-y-3">
                                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-muted/30 border dark:border-border flex flex-col items-center gap-2">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Election Status</p>
                                    <div class="flex items-center gap-2">
                                        <div class="w-2.5 h-2.5 rounded-full shadow-sm animate-pulse" :class="voter.voted ? 'bg-green-500' : 'bg-red-500'"></div>
                                        <span class="text-sm font-black" :class="voter.voted ? 'text-green-700 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                            {{ voter.voted ? 'PARTICIPATED' : 'VOTE PENDING' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Information Card -->
                    <div class="lg:col-span-2 bg-white dark:bg-card border border-gray-100 dark:border-border rounded-3xl p-6 md:p-8 shadow-sm">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-xl bg-accent/10 text-accent">
                                    <User class="w-5 h-5" />
                                </div>
                                <h2 class="text-lg font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Personal Details</h2>
                            </div>
                            <button @click="openEdit" type="button"
                                class="flex items-center gap-2 px-4 py-2 text-xs font-black uppercase tracking-widest text-primary hover:bg-primary/5 rounded-xl transition-all border-2 border-transparent hover:border-primary/10">
                                <PencilLine class="w-4 h-4" />
                                Edit Info
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <Hash class="w-3 h-3" />
                                    Student ID
                                </label>
                                <p class="text-sm font-bold text-gray-900 dark:text-foreground">{{ voter.student_id }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <Mail class="w-3 h-3" />
                                    Institutional Email
                                </label>
                                <p class="text-sm font-bold text-gray-900 dark:text-foreground break-all">{{ voter.email }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <GraduationCap class="w-3 h-3" />
                                    Academic Program
                                </label>
                                <p class="text-sm font-bold text-gray-900 dark:text-foreground">{{ voter.program }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <Calendar class="w-3 h-3" />
                                    Year Level
                                </label>
                                <p class="text-sm font-bold text-gray-900 dark:text-foreground">{{ voter.year }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <SectionIcon class="w-3 h-3" />
                                    Section
                                </label>
                                <p class="text-sm font-bold text-gray-900 dark:text-foreground">{{ voter.section || 'NOT ASSIGNED' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Edit Modal (High-end) -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 animate-in fade-in duration-300">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeAllModals"></div>
            
            <div class="relative bg-white dark:bg-card rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col border dark:border-border animate-in zoom-in-95 duration-300">
                <div class="p-6 border-b dark:border-border flex items-center justify-between">
                    <h3 class="text-lg font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Update Information</h3>
                    <button @click="closeAllModals" type="button" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-muted text-gray-400 transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="saveInfo" class="p-6 md:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Student ID</label>
                            <input v-model="form.student_id" required
                                class="w-full h-12 px-4 border-2 border-gray-100 dark:border-border rounded-2xl text-sm bg-white dark:bg-background dark:text-foreground focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-bold" />
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Full Name</label>
                            <input v-model="form.name" required
                                class="w-full h-12 px-4 border-2 border-gray-100 dark:border-border rounded-2xl text-sm bg-white dark:bg-background dark:text-foreground focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-bold" />
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Email Address</label>
                            <input v-model="form.email" type="email" required
                                class="w-full h-12 px-4 border-2 border-gray-100 dark:border-border rounded-2xl text-sm bg-white dark:bg-background dark:text-foreground focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-bold" />
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Program</label>
                            <select v-model="form.program" required
                                class="w-full h-12 px-4 border-2 border-gray-100 dark:border-border rounded-2xl text-sm bg-white dark:bg-background dark:text-foreground focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-bold appearance-none">
                                <option value="">Select program</option>
                                <option value="BSIT">BSIT</option>
                                <option value="BSIS">BSIS</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Year Level</label>
                            <select v-model="form.year" required
                                class="w-full h-12 px-4 border-2 border-gray-100 dark:border-border rounded-2xl text-sm bg-white dark:bg-background dark:text-foreground focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-bold appearance-none">
                                <option value="">Select year</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Section</label>
                            <input v-model="form.section"
                                class="w-full h-12 px-4 border-2 border-gray-100 dark:border-border rounded-2xl text-sm bg-white dark:bg-background dark:text-foreground focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-bold" 
                                placeholder="e.g., A, B, C" />
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-10 pt-6 border-t dark:border-border">
                        <button type="button" @click="closeAllModals" :disabled="form.processing"
                            class="h-12 px-6 text-xs font-black uppercase tracking-widest border-2 border-gray-100 dark:border-border rounded-2xl text-gray-500 dark:text-muted-foreground hover:bg-gray-50 dark:hover:bg-muted disabled:opacity-50 transition-all order-2 sm:order-1">
                            Cancel
                        </button>
                        <button type="submit" :disabled="form.processing" 
                            class="h-12 px-8 text-xs font-black uppercase tracking-widest bg-primary text-primary-foreground rounded-2xl hover:bg-primary/90 disabled:opacity-50 shadow-lg shadow-primary/20 transition-all order-1 sm:order-2">
                            {{ form.processing ? 'Syncing...' : 'Update Identity' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Photo Modal (Modern) -->
        <div v-if="photoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 animate-in fade-in duration-300">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeAllModals"></div>
            
            <div class="relative bg-white dark:bg-card rounded-3xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col border dark:border-border animate-in zoom-in-95 duration-300">
                <div class="p-6 border-b dark:border-border flex items-center justify-between">
                    <h3 class="text-lg font-black text-gray-900 dark:text-foreground uppercase tracking-tight">Upload Portrait</h3>
                    <button @click="closeAllModals" type="button" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-muted text-gray-400 transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <div class="p-6">
                    <input type="file" accept="image/jpeg,image/jpg,image/png" class="hidden" id="photo-input" @change="onAvatarChange" />
                    <label for="photo-input"
                        class="flex flex-col items-center justify-center gap-4 px-6 py-10 border-2 border-dashed border-gray-200 dark:border-border hover:border-primary dark:hover:border-primary/50 hover:bg-primary/5 dark:hover:bg-primary/10 cursor-pointer transition-all group"
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
                        <button @click="savePhoto" type="button" :disabled="!photoForm.photo || photoForm.errors.photo || photoForm.processing"
                            class="h-12 w-full text-xs font-black uppercase tracking-widest bg-primary text-primary-foreground rounded-2xl hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all disabled:opacity-50 disabled:grayscale disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <span v-if="photoForm.processing" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Uploading...
                            </span>
                            <span v-else>Apply Changes</span>
                        </button>
                        <button @click="closeAllModals" type="button"
                            class="h-10 w-full text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </VoterLayout>
</template>
