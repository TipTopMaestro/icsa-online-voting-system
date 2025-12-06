<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CandidateLayout from '@/layouts/CandidateLayout.vue';

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
        <div class="min-h-screen bg-gray-100 dark:bg-background">
            <div class="py-8">
                <div class="mx-auto max-w-4xl px-6">
                    <div class="bg-white dark:bg-card rounded-lg shadow-sm p-8">
                        <div class="mb-6">
                            <h1 class="text-2xl font-semibold text-gray-900 dark:text-foreground">Settings</h1>
                            <p class="text-sm text-gray-600 dark:text-muted-foreground mt-1">Manage your account settings</p>
                        </div>

                        <div class="space-y-6">
                            <form @submit.prevent="saveProfile" class="p-4 border border-gray-200 dark:border-border rounded">
                                <h2 class="font-medium mb-2 dark:text-foreground">Profile Information</h2>
                                <p class="text-sm text-gray-500 dark:text-muted-foreground mb-4">Update your name and email address</p>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-foreground mb-1">Name</label>
                                        <input type="text" v-model="profileForm.name" class="w-full border border-gray-300 dark:border-border dark:bg-background dark:text-foreground rounded px-3 py-2" required />
                                        <div v-if="profileForm.errors.name" class="text-sm text-red-600 mt-1">{{ profileForm.errors.name }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-foreground mb-1">Email</label>
                                        <input type="email" v-model="profileForm.email" class="w-full border border-gray-300 dark:border-border dark:bg-background dark:text-foreground rounded px-3 py-2" required />
                                        <div v-if="profileForm.errors.email" class="text-sm text-red-600 mt-1">{{ profileForm.errors.email }}</div>
                                    </div>

                                    <div>
                                        <button type="submit" :disabled="profileForm.processing" class="inline-flex items-center px-4 py-2 bg-black dark:bg-primary text-white rounded hover:bg-black/80 dark:hover:bg-primary/80 disabled:opacity-50">
                                            {{ profileForm.processing ? 'Saving...' : 'Save' }}
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <form @submit.prevent="savePassword" class="p-4 border border-gray-200 dark:border-border rounded">
                                <h2 class="font-medium mb-2 dark:text-foreground">Update Password</h2>
                                <p class="text-sm text-gray-500 dark:text-muted-foreground mb-4">Ensure your account uses a strong password</p>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-foreground mb-1">Current Password</label>
                                        <input type="password" v-model="passwordForm.current_password" class="w-full border border-gray-300 dark:border-border dark:bg-background dark:text-foreground rounded px-3 py-2" required />
                                        <div v-if="passwordForm.errors.current_password" class="text-sm text-red-600 mt-1">{{ passwordForm.errors.current_password }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-foreground mb-1">New Password</label>
                                        <input type="password" v-model="passwordForm.new_password" class="w-full border border-gray-300 dark:border-border dark:bg-background dark:text-foreground rounded px-3 py-2" required />
                                        <div v-if="passwordForm.errors.new_password" class="text-sm text-red-600 mt-1">{{ passwordForm.errors.new_password }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-foreground mb-1">Confirm Password</label>
                                        <input type="password" v-model="passwordForm.new_password_confirmation" class="w-full border border-gray-300 dark:border-border dark:bg-background dark:text-foreground rounded px-3 py-2" required />
                                    </div>

                                    <div>
                                        <button type="submit" :disabled="passwordForm.processing" class="inline-flex items-center px-4 py-2 bg-black dark:bg-primary text-white rounded hover:bg-black/80 dark:hover:bg-primary/80 disabled:opacity-50">
                                            {{ passwordForm.processing ? 'Saving...' : 'Save Password' }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CandidateLayout>
</template>
