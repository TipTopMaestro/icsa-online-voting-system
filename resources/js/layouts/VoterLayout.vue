<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const profileOpen = ref(false);

// Get user from Inertia page props
const page = usePage();
const user = page.props.auth?.user || { name: 'Voter', email: '' };

// Check if link is active
const isActive = (path: string) => {
    return page.url.startsWith(path);
};

const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-background">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-card shadow-sm border-b dark:border-border">
            <div class="w-full px-3 sm:px-4 lg:px-6">
                <div class="flex h-16 justify-between items-center">
                    <!-- LEFT SECTION -->
                    <div class="flex items-center space-x-8">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-foreground">ICSA Voting System</h1>

                        <!-- DESKTOP NAV -->
                        <div class="hidden md:flex items-center space-x-6">
                            <Link 
                                href="/voter/dashboard" 
                                :class="isActive('/voter/dashboard') ? 'text-purple-600 dark:text-primary' : 'text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-primary'"
                                class="text-sm font-medium transition"
                            >
                                Dashboard
                            </Link>

                            <Link 
                                href="/voter/vote" 
                                :class="isActive('/voter/vote') ? 'text-purple-600 dark:text-primary' : 'text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-primary'"
                                class="text-sm font-medium transition"
                            >
                                Cast Vote
                            </Link>

                            <Link 
                                href="/voter/results" 
                                :class="isActive('/voter/results') ? 'text-purple-600 dark:text-primary' : 'text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-primary'"
                                class="text-sm font-medium transition"
                            >
                                Results
                            </Link>

                            <Link 
                                href="/voter/profile" 
                                :class="isActive('/voter/profile') ? 'text-purple-600 dark:text-primary' : 'text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-primary'"
                                class="text-sm font-medium transition"
                            >
                                Profile
                            </Link>
                        </div>
                    </div>

                    <!-- RIGHT SECTION -->
                    <div class="relative">
                        <button 
                            @click="profileOpen = !profileOpen"
                            class="flex items-center space-x-2 focus:outline-none hover:opacity-80 transition"
                        >
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ user.name }}</span>
                            <div class="w-9 h-9 rounded-full bg-purple-100 dark:bg-primary/10 flex items-center justify-center">
                                <span class="text-purple-600 dark:text-primary font-medium text-sm">
                                    {{ user.name?.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                        </button>

                        <!-- Dropdown Menu -->
                        <div 
                            v-if="profileOpen"
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-card border dark:border-border rounded-lg shadow-lg py-2 z-50"
                            @click.outside="profileOpen = false"
                        >
                            <Link 
                                href="/voter/profile"
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-accent transition"
                                @click="profileOpen = false"
                            >
                                My Profile
                            </Link>
                            <button 
                                @click="handleLogout"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-accent transition"
                            >
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
