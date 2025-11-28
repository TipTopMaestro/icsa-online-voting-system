<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ref } from 'vue';
import { email } from '@/routes/password';

const handleLogout = () => {
    router.post('/logout');
};

// Mobile menu toggle
const open = ref(false);

// Profile dropdown toggle
const profileOpen = ref(false);

// Mock user data (replace with inertia user prop)
const user = {
    name: "Voter Name",
    avatar: "/images/profile.png",
    email: "voter@email.com",
   
};


</script>

<template>
    <div>
        <Head title="Voter Dashboard" />

        <div class="min-h-screen bg-gray-100">

            <nav class="bg-white shadow-sm">
                <div class="w-full px-3 sm:px-4 lg:px-6">
                    <div class="flex h-16 justify-between items-center">

                        <!-- LEFT SECTION -->
                        <div class="flex items-center space-x-8">
                            <h1 class="text-xl font-bold">ICSA Voting System</h1>

                            <!-- DESKTOP NAV -->
                            <div class="hidden sm:flex items-center space-x-6">
                                <Link href="/voter/profile" class="text-gray-700 hover:text-purple-600 font-medium">
                                    Profile
                                </Link>

                                <Link href="/candidate/view" class="text-gray-700 hover:text-purple-600 font-medium">
                                    View Candidates
                                </Link>

                                <Link href="/voter/vote" class="text-gray-700 hover:text-purple-600 font-medium">
                                    Cast Vote
                                </Link>
                                
                                <Link href="/voter/announcements" class="text-gray-700 hover:text-purple-600 font-medium">
                                    Announcement
                                </Link>

                                <Link href="/voter/results" class="text-gray-700 hover:text-purple-600 font-medium">
                                    Result
                                </Link>

                                
                            </div>
                        </div>

                        <!-- RIGHT SECTION (Desktop) -->
                        <div class="relative">
                            <button 
                                @click="profileOpen = !profileOpen"
                                class="flex items-center space-x-2 focus:outline-none"
                            >
                                <!-- Name first -->
                                <span class="text-sm text-gray-700">{{ user.name }}</span>

                                <!-- Profile Image -->
                                <img 
                                    :src="user.avatar"
                                    class="w-9 h-9 rounded-full object-cover"
                                    alt="Profile"
                                >
                            </button>

                            <!-- Dropdown Menu -->
                            <div 
                                v-if="profileOpen"
                                class="absolute right-0 mt-2 w-40 bg-white border rounded-md shadow-lg py-2 z-50"
                            >
                                <Link 
                                    href="/voter/profile"
                                    class="block px-4 py-2 text-sm hover:bg-gray-100"
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
                <div class="w-full px-3 sm:px-4 lg:px-6">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h2 class="text-2xl font-semibold mb-4">Welcome, Voter!</h2>
                            <p>You are logged in as a voter.</p>
                            <p class="mt-2 text-gray-600">
                                Your voter dashboard will be available here.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>