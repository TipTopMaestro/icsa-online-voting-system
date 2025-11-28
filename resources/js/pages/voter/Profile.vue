<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ref, reactive } from 'vue';



const open = ref(false);
const profileOpen = ref(false);

const handleLogout = () => router.post('/logout');

const user = {
    name: "Voter Name",
    avatar: "/images/profile.png",
    email: "voter@email.com",
};

// Reusable navigation links
const navLinks = [
    { label: "Profile", href: "/voter/profile" },
    { label: "View Candidates", href: "/candidate/view" },
    { label: "Cast Vote", href: "/voter/vote" },
    { label: "Announcement", href: "/voter/announcements" },
    { label: "Result", href: "/voter/results" },
];

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
  studentId?: string;
  phone?: string;

};

/* Local UI-only data (no backend) */
const voter = reactive<Voter>({
  id: '2026-54321',
  name: 'Froyd D. Carbajosa',
  program: 'Bachelor of Science in Information Technology',
  year: 'Second Year',
  email: 'froydcarbajosa@gmail.com',
  mobile: '09123456789',
  address: 'Prk. TinaPa Brgy. Odong Nowhere Davao Del Norte 8101',
  voted: true,
  // use the uploaded local image path as requested
  avatarUrl: '/mnt/data/fccb83b4-890c-4e59-b0d5-5dd3c31c9c20.png',
});

/* Modal state (UI-only) */
const showChangePhotoModal = ref(false);
const showEditModal = ref(false);
const previewPhoto = ref<string | null>(null);

/* Simple UI-only handlers */
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

/* UI-only "save" functions (mutates local reactive state) */
function savePhoto() {
  if (previewPhoto.value) {
    voter.avatarUrl = previewPhoto.value;
  }
  closeAllModals();
}

function saveVoter(updated: Voter) {
  // since 'voter' is reactive, copy values in place
  Object.assign(voter, updated);
  closeAllModals();
}

/* For the change-photo modal we allow selecting a local data URL (UI only) */
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
  <AppLayout title="Voter Profile">

     <div>
        <Head title="Voter Profile" />

        <div class="min-h-screen bg-gray-100">
            <!-- NAVBAR -->
            <nav class="bg-white shadow-sm">
                <div class="w-full px-3 sm:px-4 lg:px-6">
                    <div class="flex h-16 justify-between items-center">

                        <!-- LEFT SECTION -->
                        <div class="flex items-center space-x-8">
                            <h1 class="text-xl font-bold">ICSA Voting System</h1>

                            <!-- DESKTOP NAV -->
                            <div class="hidden sm:flex items-center space-x-6">
                                <Link
                                    v-for="(link, i) in navLinks"
                                    :key="i"
                                    :href="link.href"
                                    class="text-gray-700 hover:text-purple-600 font-medium"
                                >
                                    {{ link.label }}
                                </Link>
                            </div>
                        </div>

                        <!-- RIGHT SECTION -->
                        <div class="relative">
                            <button @click="profileOpen = !profileOpen" class="flex items-center space-x-2">
                                <span class="text-sm text-gray-700">{{ user.name }}</span>
                                <img :src="user.avatar" class="w-9 h-9 rounded-full object-cover" />
                            </button>

                            <!-- PROFILE DROPDOWN -->
                            <div
                                v-if="profileOpen"
                                class="absolute right-0 mt-2 w-40 bg-white border rounded-md shadow-lg py-2 z-50"
                            >
                                <Link
                                    href="/voter/profile"
                                    class="block px-4 py-2 text-sm hover:bg-gray-100"
                                    @click="profileOpen = false"
                                >
                                    My Profile
                                </Link>

                                <button
                                    @click="handleLogout"
                                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100"
                                >
                                    Logout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MOBILE MENU -->
                <div v-if="open" class="sm:hidden border-t bg-white px-4 py-3">
                    <Button @click="handleLogout" variant="outline" size="sm">Logout</Button>
                </div>
            </nav>

           
        
    <div class="w-full px-6 py-5">
      <!-- Welcome -->
      <div class="w-full bg-gray-100 py-4 px-6">
        <h2 class="text-xl font-bold text-slate-900">
        Welcome, <span class="font-semibold">{{ voter.name }}</span>
        </h2>
        </div>
    </div>
    <div class="p-6 max-w-6xl mx-auto">

  <!-- Main Card -->
  <div class="bg-white rounded-xl shadow-md border border-slate-300 p-25">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

      <!-- LEFT: Avatar -->
      <div class="flex flex-col items-center lg:items-start gap-2   ">
        <div class="w-48 h-48 rounded-full overflow-hidden bg-slate-100 shadow">
          <img :src="voter.avatarUrl" class="w-full h-full object-cover" />
        </div>

        <button
          @click="openChangePhoto"
          class="text-indigo-600 text-sm hover:underline"
        >
          Change Photo
        </button>
      </div>

      <!-- RIGHT CONTENT -->
      <div class="lg:col-span-2 ">
        <div class="flex justify-between items-start">
          <div>
            <h2 class="text-2xl font-bold text-slate-900">Voter’s Information</h2>
            <p class="text-sm text-slate-500">Profile & voting status</p>
          </div>

          <!-- Voting Status -->
          <div class="px-4 py-2 border bg-slate-50 rounded-lg shadow">
            <span class="text-sm text-slate-700">Voting Status:</span>
            <span
              class="ml-2 px-3 py-1 rounded-full text-white text-sm"
              :class="voter.voted ? 'bg-emerald-600' : 'bg-yellow-500'"
            >
              {{ voter.voted ? "Voted" : "Not Voted" }}
            </span>
          </div>
        </div>

        <!-- Info Block (fixed layout) -->
        <div class="mt-8 bg-slate-50 rounded-xl p-6 border shadow-sm">
            <div class="space-y-6">
            <ul class="space-y-2">
              <li class="flex justify-between">
                <span class="text-gray-600">Student ID</span>
                <span class="font-medium">{{ voter.studentId }}</span>
              </li>

              <li class="flex justify-between">
                <span class="text-gray-600">Student Name</span>
                <span class="font-medium">{{ voter.name }}</span>
              </li>

              <li class="flex justify-between">
                <span class="text-gray-600">Program/Degree</span>
                <span class="font-medium">{{ voter.program }}</span>
              </li>

              <li class="flex justify-between">
                <span class="text-gray-600">Year Level</span>
                <span class="font-medium">{{ voter.year }}</span>
              </li>

              <li class="flex justify-between">
                <span class="text-gray-600">Email Address</span>
                <span class="font-medium">{{ voter.email }}</span>
              </li>

              <li class="flex justify-between">
                <span class="text-gray-600">Mobile No.</span>
                <span class="font-medium">{{ voter.phone }}</span>
              </li>

              <li class="flex justify-between">
                <span class="text-gray-600">Address</span>
                <span class="font-medium text-right">
                  {{ voter.address }}
                </span>
              </li>
            </ul>
          </div><br>
              <div class="flex justify-end">
                <button
                  @click="openEdit"
                  class="px-4 py-2 bg-slate-800 text-white text-sm rounded-md"
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
</div>


    <!-- Change Photo Modal (UI-only) -->
    <div v-if="showChangePhotoModal" class="fixed inset-0 z-40 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/40" @click="closeAllModals"></div>

      <div class="relative z-50 bg-white rounded-xl shadow-lg w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">Change Photo</h3>
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

    <!-- Edit Info Modal (UI-only) -->
    <div v-if="showEditModal" class="fixed inset-0 z-40 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/40" @click="closeAllModals"></div>

      <div class="relative z-50 bg-white rounded-xl shadow-lg w-full max-w-2xl mx-4 p-6">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">Edit Voter Information</h3>
          <button @click="closeAllModals" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form @submit.prevent="saveVoter(voter)" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-slate-600 mb-1">Student ID</label>
            <input v-model="voter.id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          </div>

          <div>
            <label class="block text-sm text-slate-600 mb-1">Student Name</label>
            <input v-model="voter.name" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          </div>

          <div>
            <label class="block text-sm text-slate-600 mb-1">Program / Degree</label>
            <input v-model="voter.program" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          </div>

          <div>
            <label class="block text-sm text-slate-600 mb-1">Year Level</label>
            <input v-model="voter.year" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          </div>

          <div>
            <label class="block text-sm text-slate-600 mb-1">Email</label>
            <input v-model="voter.email" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          </div>

          <div>
            <label class="block text-sm text-slate-600 mb-1">Mobile</label>
            <input v-model="voter.mobile" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm text-slate-600 mb-1">Address</label>
            <textarea v-model="voter.address" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></textarea>
          </div>

          <div class="md:col-span-2 flex justify-end gap-2 mt-2">
            <button type="button" @click="closeAllModals" class="px-4 py-2 rounded-md bg-slate-100 text-slate-700">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-md bg-slate-800 text-white">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
    
  </AppLayout>
</template>


