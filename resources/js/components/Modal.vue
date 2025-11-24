<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue"

const open = defineModel<boolean>({ default: false })

function close() {
  open.value = false
}

function onEsc(e: KeyboardEvent) {
  if (e.key === "Escape") close()
}

onMounted(() => window.addEventListener("keydown", onEsc))
onUnmounted(() => window.removeEventListener("keydown", onEsc))
</script>

<template>
  <Teleport to="body">
    <div
      v-show="open"
      class="fixed inset-0 z-50 flex items-center justify-center"
    >
      <!-- Overlay -->
      <div
        class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"
        @click="close"
      />

      <!-- Modal panel -->
      <div
        class="relative z-50 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl shadow-lg p-6 w-full max-w-md 
               transition-all duration-200 scale-95 opacity-0"
        :class="open ? 'opacity-100 scale-100' : ''"
      >
        <slot />
      </div>
    </div>
  </Teleport>
</template>