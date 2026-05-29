<script setup lang="ts">
import { cn } from '@/lib/utils';
import * as icons from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    name: string;
    class?: string;
    size?: number | string;
    color?: string;
    strokeWidth?: number | string;
}

const props = withDefaults(defineProps<Props>(), {
    class: '',
    size: 16,
    strokeWidth: 2,
});

const className = computed(() => cn('h-4 w-4', props.class));

const icon = computed(() => {
    // Support kebab-case (e.g., 'bar-chart-2') by converting to PascalCase (e.g., 'BarChart2')
    const pascalName = props.name
        .split('-')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join('');
    
    return (icons as Record<string, any>)[pascalName];
});
</script>

<template>
    <component
        :is="icon"
        :class="className"
        :size="size"
        :stroke-width="strokeWidth"
        :color="color"
    />
</template>
