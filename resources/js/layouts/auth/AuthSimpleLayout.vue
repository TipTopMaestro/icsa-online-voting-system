<script setup lang="ts">
import { home } from '@/routes';



defineProps<{
    title?: string;
    description?: string;
}>();
</script>
    
<style>
    .custom-bg {
        background: linear-gradient(rgba(90, 45, 111, 0.69), rgba(90, 45, 111, 1)), url('../images/homebg.jpg');
        background-size: cover;
        background-position: center;
    }

</style>

<template>
    <div class="flex min-h-screen bg-white">
        <!-- Left Side - Form Section -->
        <div class="flex w-full flex-col justify-center px-8 py-12 lg:w-5/12 lg:px-16">
            <div class="mx-auto w-full max-w-md">


                <!-- Title and Description -->
                <div class="mb-8">
                    <h1 class="text-3xl font-semibold text-gray-900">{{ title }}</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ description }}
                    </p>
                </div>

                <!-- Form Slot -->
                <slot />
            </div>
        </div>

        <!-- Right Side - Gradient Section (Hidden on mobile) -->
        <div class="hidden lg:block lg:w-7/12 relative overflow-hidden custom-bg" style="clip-path: ellipse(100% 100% at 100% 50%);">
            
            

            <!-- Content Placeholder -->
            <div class="relative flex h-full items-center justify-center p-12 z-10">
                <div class="text-center">
                    <!-- link this image to my home page -->

                    
                        <a :href="home.url()" aria-label="Go to home">
                            <img
                                src="/images/icsalogo.png"
                                alt="ICSA Logo"
                                class="mx-auto mb-6 h-40 w-40 drop-shadow-2xl animate-pulse"
                                style="animation-duration: 3s;"
                            />
                        </a>
                    
                    
                    
                    <h2 class="text-5xl font-bold text-white drop-shadow-lg mb-4">
                        ICSA OVS
                    </h2>
                    <p class="text-xl text-white/90 drop-shadow-md max-w-md mx-auto">
                        Institute of Computing Student Association
                    </p>
                    <p class="text-lg text-white/80 drop-shadow-md mt-2">
                        Online Voting System
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
