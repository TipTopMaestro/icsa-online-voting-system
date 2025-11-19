<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template class="bg-transparent">
    <img src="../../../public/images/icsalogo.png" alt="Logo" class="bg-transparent" style="background: transparent;"/>
</template>
