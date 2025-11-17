<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from "vue"
import Modal from "@/components/Modal.vue"
import ModalTrigger from "@/components/ModalTrigger.vue"


const open = ref(false)


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Election',
        href: '/election',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <ModalTrigger v-model="open">
                <Button variant="default" size="lg">
                Add New Student
                </Button>
            </ModalTrigger>
            <Modal v-model="open">
            <h2 class="text-xl font-semibold">Create New Student</h2>

            <div class="mt-4">
            <input
                type="text"
                class="w-full rounded-md border p-2"
                placeholder="Student name"
            />
            </div>

            <div class="mt-4">
            <input
                type="text"
                class="w-full rounded-md border p-2"
                placeholder="Student name"
            />
            </div>

            <div class="mt-6 flex justify-end gap-3">
            <Button variant="ghost" @click="open = false">Cancel</Button>
            <Button variant="default">Save</Button>
            </div>
        </Modal>
        </div>
          
    </AppLayout>
</template>

