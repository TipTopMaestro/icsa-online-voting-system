<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import VoterLayout from '@/layouts/VoterLayout.vue';
</script>

<template>
    <Head title="Voter Dashboard" />
    
    <VoterLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white dark:bg-card shadow-sm rounded-lg border dark:border-border">
                    <div class="p-6">
                        <h2 class="text-2xl font-semibold mb-4 dark:text-foreground">Welcome, Voter!</h2>
                        <p class="dark:text-foreground">You are logged in as a voter.</p>
                        <p class="mt-2 text-gray-600 dark:text-muted-foreground">
                            Your voter dashboard will be available here.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </VoterLayout>
</template>