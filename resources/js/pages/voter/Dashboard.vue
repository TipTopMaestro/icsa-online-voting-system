<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <div>
        <Head title="Voter Dashboard" />
        
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white shadow-sm">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <div class="flex flex-shrink-0 items-center">
                                <h1 class="text-xl font-bold">ICSA Voting System</h1>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-700">Voter Dashboard</span>
                            <Button 
                                @click="handleLogout" 
                                variant="outline" 
                                size="sm"
                            >
                                Logout
                            </Button>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="py-12">
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h2 class="text-2xl font-semibold mb-4">Welcome, Voter!</h2>
                            <p>You are logged in as a voter.</p>
                            <p class="mt-2 text-gray-600">Your voting dashboard will be available here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
