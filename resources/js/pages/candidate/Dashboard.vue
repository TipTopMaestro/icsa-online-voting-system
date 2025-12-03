<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';
import { ref, reactive } from 'vue';
import { usePage } from '@inertiajs/vue3'
import { urlIsActive } from '@/lib/utils'
import { email } from '@/routes/password';

const handleLogout = () => {
    router.post('/logout');
};

// Mobile menu toggle
const open = ref(false);

// Profile dropdown toggle
const profileOpen = ref(false);

// Modals
const photoModal = ref(false);
const editPlatformModal = ref(false);

// Platform functionality
const platformInput = ref("");
const savedPlatform = ref("");
const editingPlatform = ref(false);

const savePlatform = () => {
    savedPlatform.value = platformInput.value;
    editingPlatform.value = false;
};

const openEditPlatform = () => {
    editingPlatform.value = true;
    platformInput.value = savedPlatform.value;
};

// Reactive mock user data (replace with inertia user prop)
const user = reactive({
    name: "Candidate Name",
    avatar: "/images/profile.png",
    email: "candidate@email.com",
    position: "Governor",
    partylist: "DDS",
    program: "BSIT",
});

const avatarPreview = ref('');

function onAvatarChange(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input || !input.files || input.files.length === 0) return;
    const file = input.files[0];

    // Create a local URL for preview and immediately update the user's avatar
    const url = URL.createObjectURL(file);
    avatarPreview.value = url;
    user.avatar = url;
}

function triggerAvatarInput(id = 'avatar-input') {
    const el = document.getElementById(id) as HTMLInputElement | null;
    if (el) el.click();
}

// active link helper — keeps hover color when the nav href matches current page URL
const page = usePage();
const navClass = (href: string) => urlIsActive(href, page.url) ? 'text-purple-700' : 'text-gray-700 hover:text-purple-600 font-medium'
</script>

<template>
    <div>
        <Head title="Candidate Dashboard" />

        <div class="min-h-screen bg-gray-100">
            <!-- NAVIGATION BAR -->
            <nav class="bg-white shadow-sm">
                <div class="w-full px-3 sm:px-4 lg:px-6">
                    <div class="flex h-16 justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <img src="/images/icsalogo.png" alt="ICSA" class="w-8 h-8 object-contain" />
                            <h1 class="text-xl font-bold">ICSA Voting System</h1>
                            <div class="hidden sm:flex items-center space-x-6">
                                <Link href="/candidate/dashboard" :class="navClass('/candidate/dashboard')">
                                    Profile
                                </Link>
                                
                                <Link href="/candidate/announcements" :class="navClass('/candidate/announcements')">
                                    Announcement
                                </Link>

                                <Link href="/candidate/results" :class="navClass('/candidate/result')">
                                    Result
                                </Link>
                            </div>
                        </div>

                        <div class="relative">
                            <button 
                                @click="profileOpen = !profileOpen"
                                class="flex items-center space-x-2 focus:outline-none"
                            >
                                <span class="text-sm text-gray-700">{{ user.name }}</span>
                                <img 
                                    :src="avatarPreview || user.avatar"
                                    class="w-9 h-9 rounded-full object-cover"
                                    alt="Profile"
                                >
                            </button>
                            <div 
                                v-if="profileOpen"
                                class="absolute right-0 mt-2 w-40 bg-white border rounded-md shadow-lg py-2 z-50"
                            >
                                <Link 
                                     href="/candidate/dashboard"
                                    :class="navClass('/candidate/dashboard') + ' block px-4 py-2 text-sm'"
                                    @click="profileOpen = false"
                                >
                                    My Profile
                                </Link>

                                <button 
                                    @click="handleLogout"
                                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100"
                                >
                                    Logout
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <div v-if="open" class="sm:hidden border-t bg-white px-4 py-3 space-y-3">
                    <hr class="my-2" />

                    <div class="flex justify-between items-center">
                        <!--<span class="text-sm text-gray-700">Candidate Dashboard</span>-->
                        <Button @click="handleLogout" variant="outline" size="sm">
                            Logout
                        </Button>
                    </div>
                </div>
            </nav>

            <!-- PAGE CONTENT -->
            <div class="py-8">
                <div class="mx-auto max-w-[1500px] px-8 lg:px-16">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                        <!-- LEFT CONTAINER -->
                        <div class="bg-white shadow-sm rounded-lg p-10 flex flex-col items-center min-h-[500px]">
                            <img 
                                :src="avatarPreview || user.avatar"
                                class="w-45 h-45 rounded-full object-cover border mt-4"
                                alt="Candidate Photo"
                            />
                            <button 
                                @click="photoModal = true"
                                class="mt-4 inline-flex items-center gap-2 text-sm text-purple-700 hover:text-purple-800 bg-purple-50 border border-purple-100 px-3 py-1 rounded-lg shadow-sm"
                            >
                                <Icon name="camera" class="w-4 h-4 text-purple-700" />
                                <span>Change Photo</span>
                            </button>
                            <div class="mt-12 w-full space-y-4">
                                <p><span class="font-semibold">Name:</span> {{ user.name }}</p>
                                <p><span class="font-semibold">Email:</span> {{ user.email }}</p>
                                <p><span class="font-semibold">Program:</span> {{ user.program }}</p>
                            </div>
                        </div>

                        <!-- RIGHT CONTAINER -->
                        <div class="lg:col-span-2 bg-white shadow-sm rounded-lg p-10 min-h-[500px]">
                            <div class="space-y-2">
                                <p class="text-lg font-semibold">Position: {{ user.position }}</p>
                                <p class="text-lg font-semibold mt-1">Partylist: {{ user.partylist }}</p>
                            </div>
                            <hr class="my-8">

                            <div>
                                <h3 class="text-xl font-semibold mb-3">Platform</h3>

                                <div v-if="savedPlatform && !editingPlatform">
                                    <p class="bg-gray-100 p-4 rounded border">{{ savedPlatform }}</p>
                                    <button 
                                        @click="openEditPlatform()"
                                        class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-purple-700 text-white rounded hover:bg-purple-800"
                                    >
                                        <Icon name="PencilLine" class="w-4 h-4" />
                                        Edit Platform
                                    </button>
                                </div>

                                <div v-else-if="!savedPlatform">
                                    <textarea 
                                        v-model="platformInput"
                                        placeholder="Enter your platform..."
                                        class="w-full border rounded p-3"
                                        rows="4"
                                    ></textarea>
                                    <button 
                                        @click="savePlatform"
                                        class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-purple-700 text-white rounded hover:bg-purple-800"
                                    >
                                        <Icon name="save" class="w-4 h-4" />
                                        Save
                                    </button>
                                </div>

                                <div v-if="editingPlatform">
                                    <textarea 
                                        v-model="platformInput"
                                        class="w-full border rounded p-3"
                                        rows="4"
                                    ></textarea>
                                    <button 
                                        @click="savePlatform"
                                        class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-purple-700 text-white rounded hover:bg-purple-800"
                                    >
                                        <Icon name="save" class="w-4 h-4" />
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


            <!-- PHOTO CHANGE MODAL: subtle overlay + blur, stronger panel shadow -->
            <div
                v-if="photoModal"
                class="fixed inset-0 flex items-center justify-center z-50 pointer-events-none"
            >
                <!-- subtle overlay + backdrop blur (non-blocking) -->
                <div class="absolute inset-0 bg-black/10 backdrop-blur-sm pointer-events-none z-40"></div>

                <!-- modal panel: pointer-events enabled and stronger shadow -->
                <div class="bg-white rounded-lg p-6 w-80 pointer-events-auto shadow-2xl transform transition-all scale-100 z-50">
                    <h2 class="text-lg font-semibold mb-4">Change Photo</h2>

                    <div class="mb-4 flex items-center gap-3">
                        <input type="file" class="hidden" id="modal-avatar-input" @change="onAvatarChange" />
                        <label for="modal-avatar-input" class="inline-flex items-center gap-2 px-3 py-2 rounded-md border border-gray-200 hover:bg-gray-50 cursor-pointer text-sm">
                            <Icon name="upload" class="w-4 h-4 text-purple-700" />
                            Choose file
                        </label>
                        <div class="text-xs text-gray-500">(JPG, PNG — max 2MB)</div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button 
                            @click="photoModal = false"
                            class="px-4 py-2 border rounded inline-flex items-center gap-2"
                        >
                            <Icon name="x" class="w-4 h-4" />
                            Cancel
                        </button>

                        <button 
                            @click="photoModal = false"
                            class="px-4 py-2 bg-purple-600 text-white rounded inline-flex items-center gap-2"
                        >
                            <Icon name="save" class="w-4 h-4" />
                            Save
                        </button>
                    </div>
                </div>
            </div>


        </div>
    </div>
</template>