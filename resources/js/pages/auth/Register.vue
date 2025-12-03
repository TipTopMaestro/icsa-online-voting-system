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

<template>
    <AuthBase
        title="Create your account"
        description="Enter your details below to register for ICSA OVS"
    >
        <Head title="Register" />

        <div
            v-if="success"
            class="mb-4 flex items-center justify-center gap-2 rounded-lg bg-green-50 p-3 text-center text-sm font-medium text-green-700 animate-in fade-in slide-in-from-top-2 duration-300 dark:bg-green-950/50 dark:text-green-400"
        >
            <CheckCircle2 class="h-5 w-5 animate-in zoom-in duration-500" />
            {{ success }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <!-- Name -->
                <div class="grid gap-2">
                    <Label for="name">Full Name</Label>
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
                    />
                    <InputError :message="errors.name?.[0]" />
                </div>

                <!-- Email -->
                <div class="grid gap-2">
                    <Label for="email">Email Address</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        placeholder="student@example.com"
                        :disabled="processing"
                    />
                    <InputError :message="errors.email?.[0]" />
                </div>

                <!-- Student ID -->
                <div class="grid gap-2">
                    <Label for="student_id">Student ID</Label>
                    <Input
                        id="student_id"
                        v-model="form.student_id"
                        type="text"
                        required
                        :tabindex="3"
                        autocomplete="off"
                        placeholder="2024-12345"
                        :disabled="processing"
                    />
                    <InputError :message="errors.student_id?.[0]" />
                </div>

                <!-- Password -->
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        placeholder="Minimum 8 characters"
                        :disabled="processing"
                    />
                    <p class="text-xs text-muted-foreground">
                        Must be at least 8 characters with numbers and symbols
                    </p>
                    <InputError :message="errors.password?.[0]" />
                </div>

                <!-- Confirm Password -->
                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        :tabindex="5"
                        autocomplete="new-password"
                        placeholder="Re-enter your password"
                        :disabled="processing"
                    />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    :tabindex="6"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    Create Account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="login()" :tabindex="7">Log in</TextLink>
            </div>
        </form>
    </AuthBase>
</template>


