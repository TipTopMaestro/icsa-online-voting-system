<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue"

const props = withDefaults(defineProps<{
  padding?: boolean
}>(), {
  padding: true
})

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
      class="fixed inset-0 z-[100] flex items-center justify-center"
    >
      <!-- Overlay -->
      <div
        class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"
        @click="close"
      />

      <!-- Modal panel -->
      <div
        class="relative z-[110] bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl shadow-lg w-full max-w-[95%] md:max-w-2xl lg:max-w-3xl 
               transition-all duration-200 scale-95 opacity-0 overflow-y-auto max-h-[90vh]"
        :class="[
          open ? 'opacity-100 scale-100' : '',
          padding ? 'p-6' : ''
        ]"
      >
        <slot />
      </div>
    </div>
  </Teleport>
</template>