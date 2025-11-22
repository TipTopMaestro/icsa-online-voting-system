<script setup lang="ts">
 import Breadcrumbs from '@/components/Breadcrumbs.vue';
 import AppLayout from '@/layouts/AppLayout.vue';
 import { type BreadcrumbItem } from '@/types';
 import { Head } from '@inertiajs/vue3';

 const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Results', 
      href: '/admin/results',
    },
  ];
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      Result Page
    </div>
  </AppLayout>
</template>