<script setup lang="ts">
const open = defineModel<boolean>({ default: false })
</script>

<template>
  <Modal v-model="open">
    <div class="space-y-4">
      <slot />
    </div>
  </Modal>
</template>