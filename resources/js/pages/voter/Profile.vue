<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import VoterLayout from '@/layouts/VoterLayout.vue';
import { ref, reactive } from 'vue';

type Voter = {
  id: string;
  name: string;
  program: string;
  year: string;
  email: string;
  mobile: string;
  address: string;
  voted: boolean;
  avatarUrl?: string;
};

const voter = reactive<Voter>({
  id: '2026-54321',
  name: 'Froyd D. Carbajosa',
  program: 'Bachelor of Science in Information Technology',
  year: 'Second Year',
  email: 'froydcarbajosa@gmail.com',
  mobile: '09123456789',
  address: 'Prk. TinaPa Brgy. Odong Nowhere Davao Del Norte 8101',
  voted: true,
  avatarUrl: '/images/profile.png',
});

const showChangePhotoModal = ref(false);
const showEditModal = ref(false);
const previewPhoto = ref<string | null>(null);

function openChangePhoto() {
  previewPhoto.value = voter.avatarUrl || null;
  showChangePhotoModal.value = true;
}

function openEdit() {
  showEditModal.value = true;
}

function closeAllModals() {
  showChangePhotoModal.value = false;
  showEditModal.value = false;
  previewPhoto.value = null;
}

function savePhoto() {
  if (previewPhoto.value) {
    voter.avatarUrl = previewPhoto.value;
  }
  closeAllModals();
}

function saveVoter(updated: Voter) {
  Object.assign(voter, updated);
  closeAllModals();
}

function onPhotoInputChange(e: Event) {
  const input = e.target as HTMLInputElement;
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = () => {
      previewPhoto.value = String(reader.result);
    };
    reader.readAsDataURL(file);
  }
}
</script>

<template>
  <Head title="Voter Profile" />
  
  <VoterLayout>
    <div class="py-8">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome -->
        <div class="bg-gray-100 dark:bg-muted py-4 px-6 rounded-lg mb-6">
          <h2 class="text-xl font-bold text-slate-900 dark:text-foreground">
            Welcome, <span class="font-semibold">{{ voter.name }}</span>
          </h2>
        </div>

        <!-- Main Card -->
        <div class="bg-white dark:bg-card rounded-xl shadow-md border border-slate-300 dark:border-border p-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- LEFT: Avatar -->
            <div class="flex flex-col items-center lg:items-start gap-2">
              <div class="w-48 h-48 rounded-full overflow-hidden bg-slate-100 dark:bg-muted shadow">
                <img :src="voter.avatarUrl" class="w-full h-full object-cover" alt="Profile" />
              </div>
              <button
                @click="openChangePhoto"
                class="text-indigo-600 dark:text-primary text-sm hover:underline"
              >
                Change Photo
              </button>
            </div>

            <!-- RIGHT CONTENT -->
            <div class="lg:col-span-2">
              <div class="flex justify-between items-start mb-6">
                <div>
                  <h2 class="text-2xl font-bold text-slate-900 dark:text-foreground">Voter's Information</h2>
                  <p class="text-sm text-slate-500 dark:text-muted-foreground">Profile & voting status</p>
                </div>

                <!-- Voting Status -->
                <div class="px-4 py-2 border bg-slate-50 dark:bg-muted rounded-lg shadow">
                  <span class="text-sm text-slate-700 dark:text-foreground">Voting Status:</span>
                  <span
                    class="ml-2 px-3 py-1 rounded-full text-white text-sm"
                    :class="voter.voted ? 'bg-emerald-600' : 'bg-yellow-500'"
                  >
                    {{ voter.voted ? "Voted" : "Not Voted" }}
                  </span>
                </div>
              </div>

              <!-- Info Block -->
              <div class="bg-slate-50 dark:bg-muted/50 rounded-xl p-6 border dark:border-border shadow-sm">
                <ul class="space-y-3">
                  <li class="flex justify-between">
                    <span class="text-gray-600 dark:text-muted-foreground">Student ID</span>
                    <span class="font-medium dark:text-foreground">{{ voter.id }}</span>
                  </li>
                  <li class="flex justify-between">
                    <span class="text-gray-600 dark:text-muted-foreground">Student Name</span>
                    <span class="font-medium dark:text-foreground">{{ voter.name }}</span>
                  </li>
                  <li class="flex justify-between">
                    <span class="text-gray-600 dark:text-muted-foreground">Program/Degree</span>
                    <span class="font-medium dark:text-foreground">{{ voter.program }}</span>
                  </li>
                  <li class="flex justify-between">
                    <span class="text-gray-600 dark:text-muted-foreground">Year Level</span>
                    <span class="font-medium dark:text-foreground">{{ voter.year }}</span>
                  </li>
                  <li class="flex justify-between">
                    <span class="text-gray-600 dark:text-muted-foreground">Email Address</span>
                    <span class="font-medium dark:text-foreground">{{ voter.email }}</span>
                  </li>
                  <li class="flex justify-between">
                    <span class="text-gray-600 dark:text-muted-foreground">Mobile No.</span>
                    <span class="font-medium dark:text-foreground">{{ voter.mobile }}</span>
                  </li>
                  <li class="flex justify-between">
                    <span class="text-gray-600 dark:text-muted-foreground">Address</span>
                    <span class="font-medium text-right dark:text-foreground">{{ voter.address }}</span>
                  </li>
                </ul>
                
                <div class="flex justify-end mt-6">
                  <button
                    @click="openEdit"
                    class="px-4 py-2 bg-slate-800 dark:bg-primary text-white text-sm rounded-md hover:opacity-90 transition"
                  >
                    Edit Info
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Change Photo Modal -->
    <div v-if="showChangePhotoModal" class="fixed inset-0 z-40 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/40" @click="closeAllModals"></div>
      <div class="relative z-50 bg-white dark:bg-card rounded-xl shadow-lg w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold dark:text-foreground">Change Photo</h3>
          <button @click="closeAllModals" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <div class="mt-4 space-y-4">
          <div class="w-40 h-40 rounded-full mx-auto overflow-hidden bg-slate-100 flex items-center justify-center">
            <img :src="previewPhoto || voter.avatarUrl" alt="preview" class="w-full h-full object-cover" />
          </div>
          <div class="text-center">
            <input type="file" accept="image/*" @change="onPhotoInputChange" class="text-sm" />
          </div>
          <div class="flex justify-end gap-2 mt-2">
            <button @click="closeAllModals" class="px-4 py-2 rounded-md bg-slate-100 text-slate-700">Cancel</button>
            <button @click="savePhoto" class="px-4 py-2 rounded-md bg-slate-800 text-white">Save</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Info Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-40 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/40" @click="closeAllModals"></div>
      <div class="relative z-50 bg-white dark:bg-card rounded-xl shadow-lg w-full max-w-2xl mx-4 p-6">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold dark:text-foreground">Edit Voter Information</h3>
          <button @click="closeAllModals" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form @submit.prevent="saveVoter(voter)" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-slate-600 dark:text-muted-foreground mb-1">Student ID</label>
            <input v-model="voter.id" class="w-full rounded-lg border border-slate-200 dark:border-border px-3 py-2 text-sm dark:bg-background dark:text-foreground" />
          </div>
          <div>
            <label class="block text-sm text-slate-600 dark:text-muted-foreground mb-1">Student Name</label>
            <input v-model="voter.name" class="w-full rounded-lg border border-slate-200 dark:border-border px-3 py-2 text-sm dark:bg-background dark:text-foreground" />
          </div>
          <div>
            <label class="block text-sm text-slate-600 dark:text-muted-foreground mb-1">Program / Degree</label>
            <input v-model="voter.program" class="w-full rounded-lg border border-slate-200 dark:border-border px-3 py-2 text-sm dark:bg-background dark:text-foreground" />
          </div>
          <div>
            <label class="block text-sm text-slate-600 dark:text-muted-foreground mb-1">Year Level</label>
            <input v-model="voter.year" class="w-full rounded-lg border border-slate-200 dark:border-border px-3 py-2 text-sm dark:bg-background dark:text-foreground" />
          </div>
          <div>
            <label class="block text-sm text-slate-600 dark:text-muted-foreground mb-1">Email</label>
            <input v-model="voter.email" class="w-full rounded-lg border border-slate-200 dark:border-border px-3 py-2 text-sm dark:bg-background dark:text-foreground" />
          </div>
          <div>
            <label class="block text-sm text-slate-600 dark:text-muted-foreground mb-1">Mobile</label>
            <input v-model="voter.mobile" class="w-full rounded-lg border border-slate-200 dark:border-border px-3 py-2 text-sm dark:bg-background dark:text-foreground" />
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm text-slate-600 dark:text-muted-foreground mb-1">Address</label>
            <textarea v-model="voter.address" rows="3" class="w-full rounded-lg border border-slate-200 dark:border-border px-3 py-2 text-sm dark:bg-background dark:text-foreground"></textarea>
          </div>
          <div class="md:col-span-2 flex justify-end gap-2 mt-2">
            <button type="button" @click="closeAllModals" class="px-4 py-2 rounded-md bg-slate-100 text-slate-700">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-md bg-slate-800 text-white">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </VoterLayout>
</template>
