<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Menu, X, LayoutDashboard, User, BarChart3, Bell, Settings, LogOut } from 'lucide-vue-next';
import Icon from '@/components/Icon.vue';

const profileOpen = ref(false);
const mobileMenuOpen = ref(false);

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'Candidate', email: '', avatar: null });

const isActive = (path: string) => {
    return page.url.startsWith(path);
};

const handleLogout = () => {
    router.post('/logout');
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-background text-foreground transition-colors duration-300">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-card border-b dark:border-border sticky top-0 z-50 shadow-sm transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <!-- LEFT SECTION -->
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <div class="flex items-center gap-2 group cursor-pointer flex-shrink-0" @click="router.visit('/candidate/dashboard')">
                            <img src="/images/icsalogo.png" alt="ICSA" class="w-8 h-8 object-contain transition-transform group-hover:scale-110" />
                            <h1 class="text-lg md:text-xl font-bold text-gray-900 dark:text-foreground tracking-tight whitespace-nowrap">ICSA OVS</h1>
                        </div>

                        <!-- DESKTOP NAV -->
                        <div class="hidden lg:flex items-center space-x-6 ml-8">
                            <Link 
                                href="/candidate/dashboard" 
                                :class="isActive('/candidate/dashboard') ? 'text-primary dark:text-primary font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-primary'"
                                class="text-sm font-medium transition-colors"
                            >
                                Dashboard
                            </Link>

                            <Link 
                                href="/candidate/profile" 
                                :class="isActive('/candidate/profile') ? 'text-primary dark:text-primary font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-primary'"
                                class="text-sm font-medium transition-colors"
                            >
                                Profile
                            </Link>

                            <Link 
                                href="/candidate/results" 
                                :class="isActive('/candidate/results') ? 'text-primary dark:text-primary font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-primary'"
                                class="text-sm font-medium transition-colors"
                            >
                                Results
                            </Link>

                            <Link 
                                href="/candidate/announcements" 
                                :class="isActive('/candidate/announcements') ? 'text-primary dark:text-primary font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-primary'"
                                class="text-sm font-medium transition-colors"
                            >
                                Announcements
                            </Link>
                        </div>
                    </div>

                    <!-- RIGHT SECTION -->
                    <div class="flex items-center space-x-2 sm:space-x-4">
                        <!-- Mobile Menu Button -->
                        <button 
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="lg:hidden p-2 rounded-md hover:bg-gray-100 dark:hover:bg-accent transition-colors"
                            aria-label="Toggle menu"
                        >
                            <Menu v-if="!mobileMenuOpen" class="w-6 h-6 text-gray-700 dark:text-gray-300" />
                            <X v-else class="w-6 h-6 text-gray-700 dark:text-gray-300" />
                        </button>

                        <!-- Profile Dropdown -->
                        <div class="relative">
                            <button 
                                @click="profileOpen = !profileOpen"
                                class="flex items-center space-x-2 focus:outline-none hover:opacity-80 transition-opacity p-1 rounded-full hover:bg-gray-100 dark:hover:bg-accent"
                            >
                                <span class="hidden md:block text-sm font-medium text-gray-700 dark:text-gray-300 max-w-[120px] truncate">{{ user.name }}</span>
                                <div class="w-9 h-9 rounded-full bg-primary/10 dark:bg-primary/10 flex items-center justify-center overflow-hidden border-2 border-primary/20 flex-shrink-0">
                                    <img 
                                        v-if="user.avatar" 
                                        :src="user.avatar" 
                                        :alt="user.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <span v-else class="text-primary dark:text-primary font-bold text-sm">
                                        {{ user.name?.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                            </button>

                            <!-- Dropdown Menu -->
                            <div 
                                v-if="profileOpen"
                                class="absolute right-0 mt-2 w-52 bg-white dark:bg-card border-2 border-slate-200 dark:border-border rounded-xl shadow-xl py-1 z-50 overflow-hidden animate-in fade-in zoom-in duration-200 origin-top-right"
                                @click.outside="profileOpen = false"
                            >
                                <div class="px-4 py-2 border-b dark:border-border lg:hidden">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Candidate</p>
                                    <p class="text-sm font-bold truncate dark:text-foreground">{{ user.name }}</p>
                                </div>
                                <Link 
                                    href="/candidate/profile"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-primary/10 dark:hover:bg-primary/20 transition-colors font-medium"
                                    @click="profileOpen = false"
                                >
                                    <User class="w-4 h-4 text-gray-400" />
                                    My Profile
                                </Link>
                                <Link 
                                    href="/candidate/settings"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-primary/10 dark:hover:bg-primary/20 transition-colors font-medium"
                                    @click="profileOpen = false"
                                >
                                    <Settings class="w-4 h-4 text-gray-400" />
                                    Settings
                                </Link>
                                <div class="border-t dark:border-border my-1"></div>
                                <button 
                                    @click="handleLogout"
                                    class="flex items-center gap-3 w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors font-medium"
                                >
                                    <LogOut class="w-4 h-4" />
                                    Logout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MOBILE MENU -->
            <div 
                v-if="mobileMenuOpen"
                class="lg:hidden border-t dark:border-border bg-white dark:bg-card animate-in slide-in-from-top duration-300 overflow-hidden"
            >
                <div class="px-4 py-4 space-y-2">
                    <Link 
                        href="/candidate/dashboard" 
                        :class="isActive('/candidate/dashboard') ? 'text-primary dark:text-primary bg-primary/10 dark:bg-primary/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-accent'"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition"
                        @click="closeMobileMenu"
                    >
                        <LayoutDashboard class="w-5 h-5 opacity-70" />
                        Dashboard
                    </Link>

                    <Link 
                        href="/candidate/profile" 
                        :class="isActive('/candidate/profile') ? 'text-primary dark:text-primary bg-primary/10 dark:bg-primary/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-accent'"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition"
                        @click="closeMobileMenu"
                    >
                        <User class="w-5 h-5 opacity-70" />
                        Profile
                    </Link>

                    <Link 
                        href="/candidate/results" 
                        :class="isActive('/candidate/results') ? 'text-primary dark:text-primary bg-primary/10 dark:bg-primary/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-accent'"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition"
                        @click="closeMobileMenu"
                    >
                        <BarChart3 class="w-5 h-5 opacity-70" />
                        Results
                    </Link>

                    <Link 
                        href="/candidate/announcements" 
                        :class="isActive('/candidate/announcements') ? 'text-primary dark:text-primary bg-primary/10 dark:bg-primary/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-accent'"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition"
                        @click="closeMobileMenu"
                    >
                        <Bell class="w-5 h-5 opacity-70" />
                        Announcements
                    </Link>

                    <Link 
                        href="/candidate/settings" 
                        :class="isActive('/candidate/settings') ? 'text-primary dark:text-primary bg-primary/10 dark:bg-primary/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-accent'"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition"
                        @click="closeMobileMenu"
                    >
                        <Settings class="w-5 h-5 opacity-70" />
                        Settings
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
