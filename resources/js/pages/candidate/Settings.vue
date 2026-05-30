<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CandidateLayout from '@/layouts/CandidateLayout.vue';
import Icon from '@/components/Icon.vue';

interface User {
    name: string;
    email: string;
    photo: string | null;
}

const props = defineProps<{
    user: User;
}>();

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
});

const passwordForm = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const saveProfile = () => {
    profileForm.put('/candidate/settings/profile', {
        preserveScroll: true,
    });
};

const savePassword = () => {
    passwordForm.put('/candidate/settings/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Settings" />
    
    <CandidateLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-[calc(100vh-64px)]">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-foreground">Settings</h1>
                <p class="text-sm text-gray-600 dark:text-muted-foreground mt-1">Manage your account settings and preferences</p>
            </div>

            <div class="space-y-6">
                <!-- Profile Information -->
                <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 sm:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 rounded-lg bg-primary/10 text-primary">
                                <Icon name="user" class="w-5 h-5" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-foreground">Profile Information</h2>
                                <p class="text-sm text-gray-500 dark:text-muted-foreground">Update your account's profile information and email address.</p>
                            </div>
                        </div>

                        <form @submit.prevent="saveProfile" class="space-y-6 max-w-2xl">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Full Name</label>
                                    <input 
                                        type="text" 
                                        v-model="profileForm.name" 
                                        class="w-full bg-white dark:bg-background border border-gray-300 dark:border-border rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" 
                                        required 
                                    />
                                    <div v-if="profileForm.errors.name" class="text-xs text-red-600 mt-1 font-medium">{{ profileForm.errors.name }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                                    <input 
                                        type="email" 
                                        v-model="profileForm.email" 
                                        class="w-full bg-white dark:bg-background border border-gray-300 dark:border-border rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" 
                                        required 
                                    />
                                    <div v-if="profileForm.errors.email" class="text-xs text-red-600 mt-1 font-medium">{{ profileForm.errors.email }}</div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button 
                                    type="submit" 
                                    :disabled="profileForm.processing" 
                                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-all font-semibold shadow-sm disabled:opacity-50"
                                >
                                    <Icon name="save" class="w-4 h-4" />
                                    {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Update Password -->
                <div class="bg-white dark:bg-card border border-gray-200 dark:border-border rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 sm:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 rounded-lg bg-primary/10 text-primary">
                                <Icon name="lock" class="w-5 h-5" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-foreground">Update Password</h2>
                                <p class="text-sm text-gray-500 dark:text-muted-foreground">Ensure your account is using a long, random password to stay secure.</p>
                            </div>
                        </div>

                        <form @submit.prevent="savePassword" class="space-y-6 max-w-2xl">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Current Password</label>
                                    <input 
                                        type="password" 
                                        v-model="passwordForm.current_password" 
                                        class="w-full bg-white dark:bg-background border border-gray-300 dark:border-border rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" 
                                        required 
                                    />
                                    <div v-if="passwordForm.errors.current_password" class="text-xs text-red-600 mt-1 font-medium">{{ passwordForm.errors.current_password }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                                    <input 
                                        type="password" 
                                        v-model="passwordForm.new_password" 
                                        class="w-full bg-white dark:bg-background border border-gray-300 dark:border-border rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" 
                                        required 
                                    />
                                    <div v-if="passwordForm.errors.new_password" class="text-xs text-red-600 mt-1 font-medium">{{ passwordForm.errors.new_password }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Confirm New Password</label>
                                    <input 
                                        type="password" 
                                        v-model="passwordForm.new_password_confirmation" 
                                        class="w-full bg-white dark:bg-background border border-gray-300 dark:border-border rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" 
                                        required 
                                    />
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button 
                                    type="submit" 
                                    :disabled="passwordForm.processing" 
                                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-all font-semibold shadow-sm disabled:opacity-50"
                                >
                                    <Icon name="key" class="w-4 h-4" />
                                    {{ passwordForm.processing ? 'Saving...' : 'Update Password' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </CandidateLayout>
</template>
