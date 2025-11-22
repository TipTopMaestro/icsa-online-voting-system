<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps<{
    status: number;
}>();
</script>

<template>
    <div>
        <Head title="Access Denied" />

        <div class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
            <div class="max-w-md w-full bg-white shadow-lg rounded-lg p-8 text-center">
                <div class="mb-6">
                    <svg
                        class="mx-auto h-24 w-24 text-red-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    Access Denied
                </h1>

                <p class="text-gray-600 mb-2">
                    {{ status }} | You don't have permission to access this page.
                </p>

                <p class="text-sm text-gray-500 mb-6">
                    This area is restricted to specific user roles only.
                </p>

                <div class="space-y-3">
                    <Link href="/" class="block">
                        <Button class="w-full">
                            Go to Home
                        </Button>
                    </Link>
                    
                    <form method="POST" action="/logout" class="block">
                        <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                        <Button type="submit" variant="outline" class="w-full">
                            Logout
                        </Button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
