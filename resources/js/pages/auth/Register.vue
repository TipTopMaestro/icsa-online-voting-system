<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Head } from '@inertiajs/vue3';
import { CheckCircle2 } from 'lucide-vue-next';
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

const form = ref({
    name: '',
    email: '',
    student_id: '',
    password: '',
    password_confirmation: '',
});

const errors = ref<Record<string, string[]>>({});
const success = ref('');
const processing = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = async () => {
    processing.value = true;
    errors.value = {};
    success.value = '';

    try {
        const response = await axios.post('/register', form.value);
        
        success.value = 'Registration successful!';
        console.log(response.data);
        
        // Redirect voter to voter dashboard
        setTimeout(() => {
            router.visit('/voter/dashboard');
        }, 1200);

    } catch (error: any) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
        console.error(error);
    }

    processing.value = false;
};
</script>

<style>
    .btn-custom {
        background: #4a1d5f;
        box-shadow: 0 4px 15px rgba(165, 55, 253, 0.4);
        transition: background 0.3s ease, box-shadow 0.3s ease;
    }
    .btn-custom:hover {
        background: rgba(74, 29, 95, 0.9);
        box-shadow: 0 6px 20px rgba(165, 55, 253, 0.6);
        cursor: pointer;
    }
</style>

<template>
    <AuthBase
        title="Create your account"
        description="Enter your details below to register"
        class="text-center"
    >
        <Head title="Register" />

        <div
            v-if="success"
            class="mb-4 flex items-center justify-center gap-2 rounded-lg bg-green-50 p-3 text-center text-sm font-medium text-green-700 animate-in fade-in slide-in-from-top-2 duration-300"
        >
            <CheckCircle2 class="h-5 w-5 animate-in zoom-in duration-500" />
            {{ success }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-4">
                <!-- Name -->
                <div class="space-y-2">
                    <Label for="name" class="text-sm font-medium text-gray-700">Full Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        placeholder="Juan Dela Cruz"
                        :disabled="processing"
                        class="h-11"
                    />
                    <InputError :message="errors.name?.[0]" />
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <Label for="email" class="text-sm font-medium text-gray-700">Email Address</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        placeholder="student@example.com"
                        :disabled="processing"
                        class="h-11"
                    />
                    <InputError :message="errors.email?.[0]" />
                </div>

                <!-- Student ID -->
                <div class="space-y-2">
                    <Label for="student_id" class="text-sm font-medium text-gray-700">Student ID</Label>
                    <Input
                        id="student_id"
                        v-model="form.student_id"
                        type="text"
                        required
                        :tabindex="3"
                        autocomplete="off"
                        placeholder="2024-12345"
                        :disabled="processing"
                        class="h-11"
                    />
                    <InputError :message="errors.student_id?.[0]" />
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <Label for="password" class="text-sm font-medium text-gray-700">Password</Label>
                    <div class="relative">
                        <Input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            :tabindex="4"
                            autocomplete="new-password"
                            placeholder="Minimum 8 characters"
                            :disabled="processing"
                            class="h-11 pr-10"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors"
                            :tabindex="-1"
                            aria-label="Toggle password visibility"
                        >
                            <EyeIcon v-if="!showPassword" class="h-5 w-5" />
                            <EyeSlashIcon v-else class="h-5 w-5" />
                        </button>
                    </div>
                    <InputError :message="errors.password?.[0]" />
                </div>

                <!-- Confirm Password -->
                <div class="space-y-2">
                    <Label for="password_confirmation" class="text-sm font-medium text-gray-700">Confirm Password</Label>
                    <div class="relative">
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showPasswordConfirmation ? 'text' : 'password'"
                            required
                            :tabindex="5"
                            autocomplete="new-password"
                            placeholder="Re-enter your password"
                            :disabled="processing"
                            class="h-11 pr-10"
                        />
                        <button
                            type="button"
                            @click="showPasswordConfirmation = !showPasswordConfirmation"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors"
                            :tabindex="-1"
                            aria-label="Toggle password confirmation visibility"
                        >
                            <EyeIcon v-if="!showPasswordConfirmation" class="h-5 w-5" />
                            <EyeSlashIcon v-else class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <Button
                    type="submit"
                    class="w-full h-11 text-white btn-custom"
                    :tabindex="6"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    Create Account
                </Button>
            </div>

            <div class="text-center text-sm text-gray-600">
                Already have an account?
                <TextLink :href="login()" :tabindex="7" class="font-medium text-purple-700 hover:text-purple-800 underline">Log in</TextLink>
            </div>
        </form>
    </AuthBase>
</template>


