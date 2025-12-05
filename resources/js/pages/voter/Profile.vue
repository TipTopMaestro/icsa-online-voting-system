<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import VoterLayout from '@/layouts/VoterLayout.vue';

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
const selectedFile = ref<File | null>(null);

const form = useForm({
    student_id: props.voter.student_id,
    name: props.voter.name,
    email: props.voter.email,
    program: props.voter.program,
    year: props.voter.year,
    section: props.voter.section || '',
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
    selectedFile.value = null;
}

function onAvatarChange(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input || !input.files || input.files.length === 0) return;
    const file = input.files[0];
    
    selectedFile.value = file;
    const url = URL.createObjectURL(file);
    avatarPreview.value = url;
}

function savePhoto() {
    if (!selectedFile.value) {
        closeAllModals();
        return;
    }
    
    const formData = new FormData();
    formData.append('photo', selectedFile.value);
    
    router.post('/voter/profile/photo', formData, {
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
        <div class="min-h-screen bg-gray-50">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Profile</h1>
                    <p class="text-sm text-gray-600 mt-1">Manage your personal information</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Profile Card -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex flex-col items-center">
                            <div class="relative mb-4">
                                <img :src="avatarPreview || voter.photo || '/images/default-avatar.png'"
                                    class="w-24 h-24 rounded-full object-cover border-2 border-gray-200"
                                    alt="Profile" />
                                <button @click="photoModal = true" type="button"
                                    class="absolute bottom-0 right-0 bg-purple-600 text-white p-1.5 rounded-full hover:bg-purple-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900 text-center">{{ voter.name }}</h2>
                            <p class="text-sm text-gray-600 mt-1">{{ voter.program }}</p>
                            <div class="mt-4 w-full pt-4 border-t border-gray-100">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-2 h-2 rounded-full" :class="voter.voted ? 'bg-green-500' : 'bg-red-500'"></div>
                                    <span class="text-sm font-medium" :class="voter.voted ? 'text-green-700' : 'text-red-600'">
                                        {{ voter.voted ? 'Voted' : 'Not Voted' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Information Card -->
                    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Personal Information</h2>
                            <button @click="openEdit" type="button"
                                class="px-4 py-2 text-sm text-purple-600 hover:text-purple-700 border border-purple-200 rounded-md hover:bg-purple-50 transition">
                                Edit
                            </button>
                        </div>

                        <dl class="space-y-4">
                            <div class="flex border-b border-gray-100 pb-3">
                                <dt class="text-sm text-gray-600 w-32">Student ID</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ voter.student_id }}</dd>
                            </div>
                            <div class="flex border-b border-gray-100 pb-3">
                                <dt class="text-sm text-gray-600 w-32">Name</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ voter.name }}</dd>
                            </div>
                            <div class="flex border-b border-gray-100 pb-3">
                                <dt class="text-sm text-gray-600 w-32">Email</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ voter.email }}</dd>
                            </div>
                            <div class="flex border-b border-gray-100 pb-3">
                                <dt class="text-sm text-gray-600 w-32">Program</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ voter.program }}</dd>
                            </div>
                            <div class="flex border-b border-gray-100 pb-3">
                                <dt class="text-sm text-gray-600 w-32">Year Level</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ voter.year }}</dd>
                            </div>
                            <div class="flex pb-3">
                                <dt class="text-sm text-gray-600 w-32">Section</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ voter.section || 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="closeAllModals"></div>
            
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Information</h3>
                    <button @click="closeAllModals" type="button" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveInfo" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Student ID</label>
                        <input v-model="form.student_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
                        <span v-if="form.errors.student_id" class="text-xs text-red-600 mt-1">{{ form.errors.student_id }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input v-model="form.name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
                        <span v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="form.email" type="email" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
                        <span v-if="form.errors.email" class="text-xs text-red-600 mt-1">{{ form.errors.email }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                        <select v-model="form.program" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select program</option>
                            <option value="BSIT">BSIT</option>
                            <option value="BSIS">BSIS</option>
                        </select>
                        <span v-if="form.errors.program" class="text-xs text-red-600 mt-1">{{ form.errors.program }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
                        <select v-model="form.year" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select year</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                        <span v-if="form.errors.year" class="text-xs text-red-600 mt-1">{{ form.errors.year }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Section (Optional)</label>
                        <input v-model="form.section"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                            placeholder="e.g., A, B, C" />
                        <span v-if="form.errors.section" class="text-xs text-red-600 mt-1">{{ form.errors.section }}</span>
                    </div>

                    <div class="md:col-span-2 flex justify-end gap-2 mt-4 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAllModals" :disabled="form.processing"
                            class="px-4 py-2 text-sm border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                            Cancel
                        </button>
                        <button type="submit" :disabled="form.processing" 
                            class="px-4 py-2 text-sm bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50">
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Photo Modal -->
        <div v-if="photoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="closeAllModals"></div>
            
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Profile Photo</h3>
                
                <div class="mb-6">
                    <input type="file" accept="image/jpeg,image/jpg,image/png" class="hidden" id="photo-input" @change="onAvatarChange" />
                    <label for="photo-input"
                        class="flex items-center justify-center gap-2 px-4 py-3 border-2 border-dashed border-gray-300 rounded-md hover:border-purple-500 hover:bg-purple-50 cursor-pointer transition">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <span class="text-sm text-gray-600">Click to upload (JPG, PNG, max 2MB)</span>
                    </label>
                    <p v-if="selectedFile" class="text-xs text-gray-600 mt-2">Selected: {{ selectedFile.name }}</p>
                </div>

                <div class="flex justify-end gap-2">
                    <button @click="closeAllModals" type="button"
                        class="px-4 py-2 text-sm border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button @click="savePhoto" type="button"
                        class="px-4 py-2 text-sm bg-purple-600 text-white rounded-md hover:bg-purple-700">
                        Save Photo
                    </button>
                </div>
            </div>
        </div>
    </VoterLayout>
</template>
